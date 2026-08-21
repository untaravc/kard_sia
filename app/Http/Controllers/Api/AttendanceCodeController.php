<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\OpenStaseTask;
use App\Services\Attendance\AttendanceCodeService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceCodeController extends Controller
{
    /**
     * [auth_type, auth_id] of the caller, preferring the "log as" identity
     * when impersonating.
     */
    private function authContext(Request $request): array
    {
        $payload = $request->attributes->get('jwt_payload');

        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }

        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        return [$authType, $authId];
    }

    /**
     * The rotating code a lecturer shows on their profile screen, as both a
     * scannable QR (encoding a deep link) and plain digits the student can
     * type when scanning is impractical.
     */
    public function lectureCode(Request $request)
    {
        [$authType, $authId] = $this->authContext($request);

        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $lecture = Lecture::find($authId);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $service = app(AttendanceCodeService::class);
        $code = $service->generate($lecture);
        $link = $service->deepLink($code);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Attendance Code Success',
            'result' => [
                'code' => $code,
                'expires_in' => $service->secondsRemaining(),
                'period' => AttendanceCodeService::PERIOD,
                'link' => $link,
                'qr_svg' => 'data:image/svg+xml;base64,' . base64_encode(
                    QrCode::format('svg')->size(220)->margin(0)->generate($link)
                ),
            ],
        ]);
    }

    /**
     * Student-side confirmation that an assessment actually took place.
     *
     * The code is only ever checked against lecturers the student already has
     * a pending assessment with, so the search space is the student's own
     * open_stase_tasks rather than every lecturer in the system, and a code
     * cannot be redeemed against an unrelated assessment.
     */
    public function confirm(Request $request)
    {
        [$authType, $authId] = $this->authContext($request);

        if ($authType !== 'student') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $this->validate($request, [
            'code' => 'required|string',
            'open_stase_task_id' => 'nullable|integer',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'method' => 'nullable|in:qr,code',
        ]);

        $query = OpenStaseTask::where('student_id', $authId)
            ->whereNull('validated_at')
            ->with('lecture');

        if ($request->filled('open_stase_task_id')) {
            $query->where('id', $request->open_stase_task_id);
        }

        $candidates = $query->get();

        if ($candidates->isEmpty()) {
            return response()->json([
                'success' => false,
                'text' => 'Tidak ada agenda yang menunggu konfirmasi.',
                'result' => null,
            ], 404);
        }

        $service = app(AttendanceCodeService::class);

        $matches = $candidates->filter(function ($task) use ($service, $request) {
            return $task->lecture && $service->verify($task->lecture, $request->code);
        })->values();

        if ($matches->isEmpty()) {
            return response()->json([
                'success' => false,
                'text' => 'Kode tidak valid atau sudah kedaluwarsa. Minta dosen menampilkan kode terbaru.',
                'result' => null,
            ], 422);
        }

        // The same lecturer can supervise several pending assessments for one
        // student; the code alone cannot say which, so let the student pick.
        if ($matches->count() > 1) {
            return response()->json([
                'success' => true,
                'text' => 'Pilih agenda yang ingin dikonfirmasi.',
                'result' => [
                    'needs_selection' => true,
                    'candidates' => $matches->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'plan' => $task->plan,
                            'lecture_name' => $task->lecture ? $task->lecture->name : null,
                        ];
                    })->values(),
                ],
            ]);
        }

        $task = $matches->first();
        $task->update([
            'validated_at' => now(),
            'validated_method' => $request->method ?: 'code',
            'validated_lat' => $request->lat,
            'validated_lng' => $request->lng,
            'validated_by' => $task->lecture_id,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Agenda berhasil dikonfirmasi.',
            'result' => [
                'needs_selection' => false,
                'open_stase_task' => $task->fresh(),
            ],
        ]);
    }
}
