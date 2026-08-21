<?php

namespace App\Http\Controllers\Api;

use App\Exports\LectureScoringExport;
use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\StaseTask;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StaseTaskLogController extends Controller
{
    /**
     * [auth_type, auth_id] of the caller, preferring the "log as" identity
     * when impersonating (matches the rest of this controller).
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
     * Shared, filtered base query for both index() and exportExcel(). A
     * lecture/student is always scoped to their own logs; only an admin
     * ('user') may browse a specific lecture's history via lecture_id
     * (e.g. the Lectures > Scoring page).
     */
    private function scopedQuery(Request $request)
    {
        [$authType, $authId] = $this->authContext($request);

        $dataContent = StaseTaskLog::with([
            'student',
            'lecture',
            'staseTask' => function ($query) {
                $query->with(['stase', 'task']);
            },
        ])->orderByDesc('created_at');

        if ($authType === 'lecture') {
            $dataContent = $dataContent->whereLectureId($authId);
        } elseif ($authType === 'student') {
            $dataContent = $dataContent->whereStudentId($authId);
        } elseif ($authType === 'user' && $request->filled('lecture_id')) {
            $dataContent = $dataContent->whereLectureId($request->lecture_id);
        }

        if ($request->filled('task_id')) {
            $dataContent = $dataContent->where('task_id', $request->task_id);
        }

        if ($request->filled('date_from')) {
            $dataContent = $dataContent->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $dataContent = $dataContent->whereDate('date', '<=', $request->date_to);
        }

        if ($request->keyword != null) {
            $keyword = $request->keyword;
            $dataContent = $dataContent->where(function ($query) use ($keyword) {
                $query->where('title', 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('email', 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('lecture', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('email', 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('staseTask', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', '%' . $keyword . '%')
                            ->orWhereHas('task', function ($taskQuery) use ($keyword) {
                                $taskQuery->where('name', 'LIKE', '%' . $keyword . '%');
                            });
                    });
            });
        }

        return $dataContent;
    }

    public function index(Request $request)
    {
        $dataContent = $this->scopedQuery($request)->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Task Logs Success',
            'result' => $dataContent,
        ]);
    }

    /**
     * Excel export of a lecture's scoring history, using the same filters
     * (lecture_id / task_id / date_from / date_to) as index().
     */
    public function exportExcel(Request $request)
    {
        [$authType, $authId] = $this->authContext($request);
        $lectureId = $authType === 'lecture' ? $authId : $request->lecture_id;
        $lecture = $lectureId ? Lecture::find($lectureId) : null;

        $logs = $this->scopedQuery($request)->get();

        $rows = $logs->map(function ($log) {
            return [
                'date' => $log->date,
                'student' => $log->student ? $log->student->name : '',
                'stase' => $log->staseTask && $log->staseTask->stase ? $log->staseTask->stase->name : '',
                'task' => $log->staseTask
                    ? ($log->staseTask->task ? $log->staseTask->task->name : $log->staseTask->name)
                    : '',
                'point_average' => $log->point_average,
                'symbol' => $log->symbol,
                'status' => $log->status,
            ];
        });

        $lectureName = $lecture ? $lecture->name : 'Lecture';

        return Excel::download(
            new LectureScoringExport(['rows' => $rows, 'lecture_name' => $lectureName]),
            'Scoring History - ' . $lectureName . '.xlsx'
        );
    }

    public function updateScore(Request $request)
    {
        $this->validate($request, [
            'stase_task_log_id' => 'required|integer',
            'lecture_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'point_average' => 'nullable|numeric',
        ]);

        $log = StaseTaskLog::find($request->stase_task_log_id);
        if (!$log) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $log->update([
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'point_average' => $request->point_average,
            'admin' => true,
            'status' => 'publish',
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Score Success',
            'result' => $log->fresh(),
        ]);
    }

    public function createScore(Request $request)
    {
        $this->validate($request, [
            'stase_log_id' => 'required|integer',
            'stase_task_id' => 'required|integer',
            'student_id' => 'required|integer',
            'lecture_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'point_average' => 'nullable|numeric',
        ]);

        $stase_task = StaseTask::find($request->stase_task_id);
        if (!$stase_task) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        $log = StaseTaskLog::create([
            'stase_log_id' => $request->stase_log_id,
            'stase_task_id' => $request->stase_task_id,
            'student_id' => $request->student_id,
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'point_average' => $request->point_average,
            'admin' => true,
            'status' => 'publish',
            'stase_id'=> $stase_task->stase_id,
            'task_id'=> $stase_task->task_id
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Create Score Success',
            'result' => $log,
        ]);
    }

    public function lectureAddScore(Request $request)
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

        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $this->validate($request, [
            'stase_task_id' => 'required|integer',
            'student_id' => 'required|integer',
            'point_average' => 'required|numeric',
            'stase_id' => 'required|integer',
            'task_id' => 'required|integer',
        ]);

        $staseLog = StaseLog::whereStudentId($request->student_id)
            ->whereStaseId($request->stase_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$staseLog) {
            return response()->json([
                'success' => false,
                'text' => 'Stase log not found',
                'result' => null,
            ], 404);
        }

        $log = StaseTaskLog::firstOrNew([
            'stase_log_id' => $staseLog->id,
            'stase_task_id' => $request->stase_task_id,
            'student_id' => $request->student_id,
            'lecture_id' => $authId,
        ]);

        $log->fill([
            'point_average' => $request->point_average,
            'date' => date('Y-m-d H:i:s'),
            'admin' => true,
            'status' => 'publish',
            'stase_id' => $request->stase_id,
            'task_id' => $request->task_id,
        ]);
        $log->save();

        return response()->json([
            'success' => true,
            'text' => 'Create Score Success',
            'result' => $log->fresh(),
        ]);
    }

    public function deleteScore(Request $request)
    {
        $this->validate($request, [
            'stase_task_log_id' => 'required|integer',
        ]);

        $log = StaseTaskLog::find($request->stase_task_log_id);
        if (!$log) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Score Success',
            'result' => null,
        ]);
    }
}
