<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\StaseTask;
use Illuminate\Http\Request;

class StaseTaskLogController extends Controller
{
    public function index(Request $request)
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

//        return $authType;
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

        $dataContent = $dataContent->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Task Logs Success',
            'result' => $dataContent,
        ]);
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

        $log = StaseTaskLog::create([
            'stase_log_id' => $request->stase_log_id,
            'stase_task_id' => $request->stase_task_id,
            'student_id' => $request->student_id,
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'point_average' => $request->point_average,
            'admin' => true,
            'status' => 'publish',
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
