<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;

class StaseTaskController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = StaseTask::with(['task', 'lecture', 'stase'])->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(20);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Tasks Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $staseTask = StaseTask::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $staseTask = StaseTask::find($id);
        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        $staseTask->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function show($id)
    {
        $staseTask = StaseTask::with(['task', 'lecture', 'stase'])->find($id);

        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function destroy($id)
    {
        $staseTask = StaseTask::find($id);
        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        $staseTask->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Stase Task Success',
            'result' => null,
        ]);
    }

    public function studentStaseTask(Request $request, $stase_id)
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

        $tasks = StaseTask::with([
            'task',
            'lecture',
            'stase',
            'openStaseTasks' => function ($query) use ($authType, $authId) {
                if ($authType === 'student' && $authId) {
                    $query->whereStudentId($authId);
                }
                $query->with(['lecture', 'files'])->orderByDesc('created_at');
            },
        ])
            ->where('stase_id', $stase_id)
            ->orderBy('name')
            ->get();

        if ($authType === 'student' && $authId) {
            foreach ($tasks as $task) {
                $logs = StaseTaskLog::with('lecture')
                    ->whereStudentId($authId)
                    ->whereStaseTaskId($task->id)
                    ->orderByDesc('date')
                    ->orderByDesc('id')
                    ->get();
                if ($logs->count()) {
                    $task->setAttribute('stase_task_logs', $logs);
                }
                foreach ($task->openStaseTasks as $openTask) {
                    $logId = StaseTaskLog::whereStudentId($authId)
                        ->whereLectureId($openTask->lecture_id)
                        ->whereStaseTaskId($openTask->stase_task_id)
                        ->value('id');
                    $openTask->setAttribute('stase_task_log_id', $logId);
                    if ($logId) {
                        $openTask->setAttribute('stase_task_log', StaseTaskLog::with('lecture')->find($logId));
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Stase Task Success',
            'result' => $tasks,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'stase_id' => 'required|integer',
            'name' => 'required',
            'status' => 'nullable',
            'task_id' => 'nullable|integer',
            'lecture_id' => 'nullable|integer',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->stase_id != null) {
            $dataContent = $dataContent->where('stase_id', $request->stase_id);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('status', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
