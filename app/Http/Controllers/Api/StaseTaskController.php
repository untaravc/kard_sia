<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Task;
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

    // Should mirored
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

    public function studentStaseTask2(Request $request, $stase_id)
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
        $staseTasks = StaseTask::where('stase_id', $stase_id)
            ->whereStatus(1)
            ->get();

        $staseTaskLogs = StaseTaskLog::where('student_id', $authId)
            ->select('stase_task_logs.*', 'tasks.name as task_name', 'lectures.name as lecture_name')
            ->leftJoin('tasks', 'tasks.id', '=', 'stase_task_logs.task_id')
            ->leftJoin('lectures', 'lectures.id', '=', 'stase_task_logs.lecture_id')
            ->where('point_average', '>', 0)
            ->where('stase_id', $stase_id)
            ->get();

        $openStaseTasks = OpenStaseTask::where('student_id', $authId)
            ->withTrashed()
            ->with(['files'])
            ->leftJoin('stase_tasks', 'stase_tasks.id', '=', 'open_stase_tasks.stase_task_id')
            ->leftJoin('lectures', 'lectures.id', '=', 'open_stase_tasks.lecture_id')
            ->select('open_stase_tasks.*', 'stase_tasks.task_id as task_id', 'lectures.name as lecture_name')
            ->whereIn('open_stase_tasks.stase_task_id', $staseTasks->pluck('id')->toArray())
            ->get();

        $openStaseTasksByTask = $openStaseTasks->groupBy('stase_task_id');
        $staseTaskLogsByTask = $staseTaskLogs->groupBy('stase_task_id');

        foreach ($staseTasks as $task) {
            $taskId = $task->id;
            $openTasksAll = $openStaseTasksByTask->get($taskId, collect())->values();
            $openTasks = $openTasksAll->filter(function ($item) {
                return $item->deleted_at === null;
            })->values();
            $logs = $staseTaskLogsByTask->get($taskId, collect())->values();

            if ($openTasksAll->count() && $logs->count()) {
                $usedOpenTaskIds = collect();
                foreach ($logs as $log) {
                    if (!$log->lecture_id) {
                        $log->setAttribute('openStaseTasks', collect());
                        continue;
                    }
                    $matchedOpenTasks = $openTasksAll->where('lecture_id', $log->lecture_id)->values();
                    $log->setAttribute('openStaseTasks', $matchedOpenTasks);
                    if ($matchedOpenTasks->count()) {
                        $usedOpenTaskIds = $usedOpenTaskIds->merge($matchedOpenTasks->pluck('id'));
                    }
                }
                if ($usedOpenTaskIds->count()) {
                    $openTasks = $openTasks->reject(function ($item) use ($usedOpenTaskIds) {
                        return $usedOpenTaskIds->contains($item->id);
                    })->values();
                }
            }

            $task->setAttribute('openStaseTasks', $openTasks);
            $task->setAttribute('staseTaskLogs', $logs);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Stase Task Success',
            'result' => $staseTasks,
        ]);
    }

    protected function jwtStudentId(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $studentId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$studentId) {
            $studentId = $payload ? data_get($payload, 'auth_id') : null;
        }

        return $studentId;
    }

    /**
     * Per-component breakdown of one scored task log of the logged-in student.
     */
    public function studentScoreDetail(Request $request, $id)
    {
        $studentId = $this->jwtStudentId($request);
        if (!$studentId) {
            return response()->json(['success' => false, 'text' => 'Unauthorized', 'result' => null], 401);
        }

        $log = StaseTaskLog::with(['lecture:id,name', 'task:id,name'])
            ->where('student_id', $studentId)
            ->where('point_average', '>', 0)
            ->find($id);

        if (!$log) {
            return response()->json(['success' => false, 'text' => 'Scoring result not found', 'result' => null], 404);
        }

        $points = \Illuminate\Support\Facades\DB::table('stase_task_log_points')
            ->where('stase_task_log_id', $log->id)
            ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
            ->orderBy('task_details.order')
            ->get([
                'stase_task_log_points.id',
                'stase_task_log_points.score',
                'task_details.name',
                'task_details.order',
                'task_details.type',
            ]);

        $log->setAttribute('points', $points);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Scoring Detail Success',
            'result' => $log,
        ]);
    }

    /**
     * Student's rating (1-5) and written feedback for the lecture who scored.
     */
    public function storeScoreFeedback(Request $request, $id)
    {
        $studentId = $this->jwtStudentId($request);
        if (!$studentId) {
            return response()->json(['success' => false, 'text' => 'Unauthorized', 'result' => null], 401);
        }

        $this->validate($request, [
            'feedback_rating' => 'required|integer|between:1,5',
            'feedback_text' => 'nullable|string|max:2000',
        ]);

        $log = StaseTaskLog::where('student_id', $studentId)
            ->where('point_average', '>', 0)
            ->find($id);

        if (!$log) {
            return response()->json(['success' => false, 'text' => 'Scoring result not found', 'result' => null], 404);
        }

        $log->update([
            'feedback_rating' => (int) $request->feedback_rating,
            'feedback_text' => $request->feedback_text,
            'feedback_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Feedback saved',
            'result' => $log->only(['id', 'feedback_rating', 'feedback_text', 'feedback_at']),
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
