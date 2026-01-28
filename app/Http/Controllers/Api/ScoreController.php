<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use App\Models\TaskDetail;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    private function resolveLecture(Request $request)
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

    public function generateTaskLogDetail(Request $request)
    {
        [$authType, $lectureId] = $this->resolveLecture($request);
        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $openTaskId = $request->get('open_stase_task_id');
        if (!$openTaskId) {
            return response()->json([
                'success' => false,
                'text' => 'Open stase task id is required',
                'result' => null,
            ], 422);
        }

        $openTask = OpenStaseTask::with(['staseTask.stase', 'staseTask.task'])->find($openTaskId);
        if (!$openTask) {
            return response()->json([
                'success' => false,
                'text' => 'Open stase task not found',
                'result' => null,
            ], 404);
        }

        $lecturelog = StaseTaskLog::whereStaseTaskId($openTask->stase_task_id)
            ->whereStudentId($openTask->student_id)
            ->whereLectureId($lectureId)
            ->first();

        if ($lecturelog) {
            $stase_task_log = StaseTaskLog::with([
                'staseTaskLogPoint' => function ($q) {
                    $q->with('taskDetail')
                        ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                        ->select(
                            'stase_task_log_points.*',
                            'task_details.order'
                        )
                        ->orderBy('order', 'asc');
                },
                'staseTask',
                'task',
            ])
                ->whereLectureId($lectureId)
                ->whereStaseTaskId($openTask->stase_task_id)
                ->whereStudentId($openTask->student_id)
                ->first();

            return response()->json([
                'success' => true,
                'text' => 'Retrieve Task Log Detail Success',
                'result' => $stase_task_log,
            ]);
        }

        $availablelog = StaseTaskLog::whereStaseTaskId($openTask->stase_task_id)
            ->whereStudentId($openTask->student_id)
            ->whereNull('lecture_id')
            ->first();

        if ($availablelog) {
            $availablelog->update([
                'lecture_id' => $lectureId,
            ]);

            $stase_task_log = StaseTaskLog::with([
                'staseTaskLogPoint' => function ($q) {
                    $q->with('taskDetail')
                        ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                        ->select(
                            'stase_task_log_points.*',
                            'task_details.order'
                        )
                        ->orderBy('order', 'asc');
                },
                'staseTask',
                'task',
            ])
                ->whereStaseTaskId($openTask->stase_task_id)
                ->whereStudentId($openTask->student_id)
                ->first();

            return response()->json([
                'success' => true,
                'text' => 'Retrieve Task Log Detail Success',
                'result' => $stase_task_log,
            ]);
        }

        $staseLog = StaseLog::whereStudentId($openTask->student_id)
            ->whereStaseId($openTask->staseTask->stase->id)
            ->first();

        if (!$staseLog) {
            return response()->json([
                'success' => false,
                'text' => 'Stase log not found',
                'result' => null,
            ], 404);
        }

        $task_log = StaseTaskLog::create([
            'lecture_id' => $lectureId,
            'student_id' => $openTask->student_id,
            'stase_task_id' => $openTask->stase_task_id,
            'stase_id' => $openTask->staseTask->stase->id,
            'task_id' => $openTask->staseTask->task->id,
            'stase_log_id' => $staseLog->id,
            'status' => 'pending',
        ]);

        $task_details = TaskDetail::whereTaskId($task_log->task_id)->get();
        foreach ($task_details as $task_detail) {
            StaseTaskLogPoint::create([
                'stase_task_log_id' => $task_log->id,
                'task_detail_id' => $task_detail->id,
            ]);
        }

        $stase_task_log = StaseTaskLog::with([
            'staseTaskLogPoint' => function ($q) {
                $q->with('taskDetail')
                    ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                    ->select(
                        'stase_task_log_points.*',
                        'task_details.order'
                    )
                    ->orderBy('order', 'asc');
            },
            'staseTask',
            'task',
        ])
            ->find($task_log->id);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Task Log Detail Success',
            'result' => $stase_task_log,
        ]);
    }

    public function staseTaskLogUpdate(Request $request, $stase_task_log_id)
    {
        [$authType, $lectureId] = $this->resolveLecture($request);
        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $points = $request->input('params.no', []);
        if (!is_array($points)) {
            $points = [];
        }

        foreach ($points as $point) {
            $box = explode(',', $point);
            if (!isset($box[0], $box[1])) {
                continue;
            }
            $stlp = StaseTaskLogPoint::find($box[0]);
            if ($stlp) {
                $stlp->update([
                    'score' => $box[1],
                ]);
            }
        }

        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        if (!$staseTaskLog) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $openTaskId = $request->input('params.open_stase_task_id');
        $open = $openTaskId ? OpenStaseTask::find($openTaskId) : null;
        if ($open && isset($open->files[0])) {
            foreach ($open->files as $item) {
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id,
                ]);
            }
        }

        $filled = $staseTaskLog->staseTaskLogPointFilled->count();
        $total = $staseTaskLog->staseTaskLogPointFilled->sum('score');
        $avg = $filled > 0 ? $total / $filled : 0;

        $staseTaskLog->update([
            'lecture_id' => $lectureId,
            'point_amount' => $filled,
            'point_total' => $total,
            'point_average' => $avg,
            'symbol' => $this->pointProcess($avg),
            'title' => $open ? $open->title : $staseTaskLog->title,
            'status' => 'publish',
            'date' => date('Y-m-d H:i:s'),
            'note' => $request->input('params.note'),
            'plan' => $open ? $open->plan : $staseTaskLog->plan,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Score Success',
            'result' => $staseTaskLog->fresh(),
        ]);
    }

    public function staseTaskLogUpdateTesis(Request $request, $stase_task_log_id)
    {
        [$authType, $lectureId] = $this->resolveLecture($request);
        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $points = $request->input('params.no', []);
        if (!is_array($points)) {
            $points = [];
        }

        foreach ($points as $point) {
            $box = explode(',', $point);
            if (!isset($box[0], $box[1])) {
                continue;
            }
            $stlp = StaseTaskLogPoint::find($box[0]);
            if ($stlp) {
                $stlp->update([
                    'score' => $box[1],
                ]);
            }
        }

        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        if (!$staseTaskLog) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $openTaskId = $request->input('params.open_stase_task_id');
        $open = $openTaskId ? OpenStaseTask::find($openTaskId) : null;
        if ($open && isset($open->files[0])) {
            foreach ($open->files as $item) {
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id,
                ]);
            }
        }

        $arr = $staseTaskLog->staseTaskLogPoint;
        $result = 0;
        foreach ($arr as $item) {
            $result += $this->tesisScoreIndex($item->order, $item->score);
        }

        $staseTaskLog->update([
            'lecture_id' => $lectureId,
            'point_amount' => 14,
            'point_total' => $result,
            'point_average' => round($result / 24),
            'title' => $open ? $open->title : $staseTaskLog->title,
            'status' => 'publish',
            'date' => date('Y-m-d H:i:s'),
            'plan' => $open ? $open->plan : $staseTaskLog->plan,
            'note' => $request->input('params.note'),
            'conclusion' => $request->input('params.conclusion'),
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Score Tesis Success',
            'result' => $staseTaskLog->fresh(),
        ]);
    }

    public function staseTaskLogUpdateProposal(Request $request, $stase_task_log_id)
    {
        [$authType, $lectureId] = $this->resolveLecture($request);
        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $points = $request->input('params.no', []);
        if (!is_array($points)) {
            $points = [];
        }

        foreach ($points as $point) {
            $box = explode(',', $point);
            if (!isset($box[0], $box[1])) {
                continue;
            }
            $stlp = StaseTaskLogPoint::find($box[0]);
            if ($stlp) {
                $stlp->update([
                    'score' => $box[1],
                ]);
            }
        }

        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        if (!$staseTaskLog) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $openTaskId = $request->input('params.open_stase_task_id');
        $open = $openTaskId ? OpenStaseTask::find($openTaskId) : null;
        if ($open && isset($open->files[0])) {
            foreach ($open->files as $item) {
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id,
                ]);
            }
        }

        $arr = $staseTaskLog->staseTaskLogPoint;
        $result = 0;
        foreach ($arr as $item) {
            $result += $this->proposalScoreIndex($item->order, $item->score);
        }

        $staseTaskLog->update([
            'lecture_id' => $lectureId,
            'point_amount' => 27,
            'point_total' => $result,
            'point_average' => round($result / 45),
            'title' => $open ? $open->title : $staseTaskLog->title,
            'status' => 'publish',
            'date' => date('Y-m-d H:i:s'),
            'plan' => $open ? $open->plan : $staseTaskLog->plan,
            'note' => $request->input('params.note'),
            'conclusion' => $request->input('params.conclusion'),
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Score Proposal Success',
            'result' => $staseTaskLog->fresh(),
        ]);
    }

    public function pointProcess($avg)
    {
        switch (true) {
            case $avg >= 95:
                $val = 'A';
                break;
            case $avg >= 90 && $avg < 95:
                $val = 'B';
                break;
            case $avg >= 85 && $avg < 90:
                $val = 'C';
                break;
            default:
                $val = 'E';
        }
        return $val;
    }

    public function tesisScoreIndex($order, $score)
    {
        $result = $score;
        switch ($order) {
            case 4:
            case 8:
            case 9:
            case 10:
            case 11:
                $result = $score;
                break;
            case 12:
                $result = $score * 3;
                break;
            default:
                $result = $score * 2;
        }

        return $result;
    }

    public function proposalScoreIndex($order, $score)
    {
        $result = $score;
        switch ($order) {
            case 3:
            case 4:
            case 6:
            case 7:
            case 15:
            case 18:
            case 20:
            case 22:
            case 23:
            case 25:
            case 27:
                $result = $score;
                break;
            case 17:
            case 26:
                $result = $score * 3;
                break;
            default:
                $result = $score * 2;
        }

        return $result;
    }

    public function stat(Request $request)
    {
        [$authType, $authId] = $this->resolveLecture($request);

        $query = StaseTaskLog::query();

        if ($authType === 'lecture') {
            $query->whereLectureId($authId);
        } elseif ($authType === 'student') {
            $query->whereStudentId($authId);
        }

        $total = (clone $query)->count();

        $pendingQuery = OpenStaseTask::query();
        if ($authType === 'lecture') {
            $pendingQuery->where(function ($q) use ($authId) {
                $q->where('open_stase_tasks.lecture_id', $authId)
                    ->orWhere('open_stase_tasks.lecture_id', 0);
            });
        } elseif ($authType === 'student') {
            $pendingQuery->whereStudentId($authId);
        }

        $pendingQuery->leftJoin('stase_task_logs as stl', function ($join) use ($authType, $authId) {
            $join->on('stl.student_id', '=', 'open_stase_tasks.student_id')
                ->on('stl.stase_task_id', '=', 'open_stase_tasks.stase_task_id');

            if ($authType === 'lecture') {
                $join->where('stl.lecture_id', '=', $authId);
            } else {
                $join->on('stl.lecture_id', '=', 'open_stase_tasks.lecture_id');
            }
        });

        $pending = (clone $pendingQuery)
            ->where(function ($q) {
                $q->whereNull('stl.id')
                    ->orWhereNull('stl.point_average');
            })
            ->count();

        $monthStart = date('Y-m-d H:i:s', strtotime('-30 days'));
        $monthEnd = date('Y-m-d H:i:s');
        $doneThisMonth = (clone $query)
            ->where('status', 'publish')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->count();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Scoring Stat Success',
            'result' => [
                'pending' => $pending,
                'done_this_month' => $doneThisMonth,
                'total' => $total,
            ],
        ]);
    }
}
