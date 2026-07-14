<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentMonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Columns: stases (optionally filtered by phase), ordered as on the board.
        $staseQuery = Stase::orderByDesc('stase_order')->orderBy('name');
        if ($request->stase_desc !== null && $request->stase_desc !== '') {
            $staseQuery->where('desc', $request->stase_desc);
        }
        $stases = $staseQuery->get(['id', 'name', 'alias']);

        // Denominator per stase = number of its active tasks.
        $activeTasks = StaseTask::where('status', 1)->get(['id', 'stase_id']);
        $totalByStase = $activeTasks->groupBy('stase_id')->map->count();
        $activeTaskIds = $activeTasks->pluck('id')->flip();

        // Paginated students, filtered by status / year / name.
        $studentQuery = Student::query()->orderBy('name');
        $studentQuery = $this->withFilter($studentQuery, $request);
        $students = $studentQuery->paginate($request->get('per_page', 20));

        $studentIds = collect($students->items())->pluck('id')->all();

        // stase_logs of the current page's students (enrollment / progress context).
        $staseLogs = StaseLog::whereIn('student_id', $studentIds)
            ->get(['student_id', 'stase_id', 'status', 'start_date', 'end_date'])
            ->groupBy('student_id');

        // stase_task_logs of the current page's students that have been scored.
        $taskLogs = StaseTaskLog::whereIn('student_id', $studentIds)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->get(['student_id', 'stase_id', 'stase_task_id']);

        // completed[student_id][stase_id] = set of distinct completed active task ids.
        $completed = [];
        foreach ($taskLogs as $log) {
            if (!$activeTaskIds->has($log->stase_task_id)) {
                continue;
            }
            $completed[$log->student_id][$log->stase_id][$log->stase_task_id] = true;
        }

        $students->getCollection()->transform(function ($student) use ($stases, $totalByStase, $completed, $staseLogs) {
            $logsForStudent = $staseLogs->get($student->id);
            // Stases the student actually has a stase_log for; the rest render grey.
            $loggedStaseIds = $logsForStudent
                ? $logsForStudent->pluck('stase_id')->all()
                : [];
            // The student's latest / currently ongoing stase, by start_date & end_date.
            $currentStaseId = $this->currentStaseId($logsForStudent);

            $cells = [];
            // Fulfilment counts only stases the student has actually taken.
            $takenDone = 0;
            $takenTotal = 0;
            foreach ($stases as $stase) {
                $total = (int) ($totalByStase[$stase->id] ?? 0);
                $done = isset($completed[$student->id][$stase->id])
                    ? count($completed[$student->id][$stase->id])
                    : 0;
                $done = min($done, $total);

                $hasLog = in_array($stase->id, $loggedStaseIds, true);

                if ($hasLog) {
                    $takenDone += $done;
                    $takenTotal += $total;
                }

                $cells[$stase->id] = [
                    'done' => $done,
                    'total' => $total,
                    'status' => $this->cellStatus($done, $total, $hasLog),
                    'ongoing' => $stase->id === $currentStaseId,
                ];
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'year' => $student->year,
                'link_token' => $student->link_token,
                'fulfilment' => $takenTotal > 0 ? (int) round($takenDone / $takenTotal * 100) : null,
                'cells' => $cells,
            ];
        });

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Success',
            'result' => $students,
            'stases' => $stases,
        ]);
    }

    public function detail(Request $request)
    {
        $studentId = $request->student_id;
        $staseId = $request->stase_id;

        $student = Student::find($studentId);
        $stase = Stase::find($staseId);

        if (!$student || !$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Student or Stase not found',
                'result' => null,
            ], 404);
        }

        // Enrollment window for this student/stase, if any.
        $staseLog = StaseLog::where('student_id', $studentId)
            ->where('stase_id', $staseId)
            ->orderByDesc('start_date')
            ->first(['start_date', 'end_date']);

        // Active tasks that make up this stase.
        $tasks = StaseTask::where('stase_id', $staseId)
            ->where('status', 1)
            ->orderBy('id')
            ->get(['id', 'task_id', 'name']);

        // Scored logs of this student within this stase, best score kept per task.
        $logs = StaseTaskLog::where('student_id', $studentId)
            ->where('stase_id', $staseId)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->orderByDesc('point_average')
            ->get(['stase_task_id', 'point_average', 'date', 'title']);

        $logByTask = $logs->keyBy('stase_task_id');

        $items = $tasks->map(function ($task) use ($logByTask) {
            $log = $logByTask->get($task->id);

            return [
                'stase_task_id' => $task->id,
                'name' => $task->name,
                'done' => (bool) $log,
                'point_average' => $log ? $log->point_average : null,
                'date' => $log ? $log->date : null,
            ];
        })->values();

        $doneCount = $items->where('done', true)->count();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Detail Success',
            'result' => [
                'student' => ['id' => $student->id, 'name' => $student->name, 'year' => $student->year],
                'stase' => ['id' => $stase->id, 'name' => $stase->name, 'alias' => $stase->alias],
                'stase_log' => $staseLog ? [
                    'start_date' => $staseLog->start_date,
                    'end_date' => $staseLog->end_date,
                ] : null,
                'summary' => ['done' => $doneCount, 'total' => $items->count()],
                'tasks' => $items,
            ],
        ]);
    }

    /**
     * The student's current stase = the stase_log whose date range contains
     * today; if none is active, the most recently started one. Returns the
     * stase_id to mark, or null when the student has no started stase.
     */
    private function currentStaseId($logsForStudent)
    {
        if (!$logsForStudent || $logsForStudent->isEmpty()) {
            return null;
        }

        $today = date('Y-m-d');

        // Only stases that have already started.
        $started = $logsForStudent->filter(function ($log) use ($today) {
            return $log->start_date && substr($log->start_date, 0, 10) <= $today;
        });

        if ($started->isEmpty()) {
            return null;
        }

        // Prefer the one currently in range (today <= end_date, or open-ended).
        $ongoing = $started->filter(function ($log) use ($today) {
            return !$log->end_date || substr($log->end_date, 0, 10) >= $today;
        });

        $pool = $ongoing->isNotEmpty() ? $ongoing : $started;

        $current = $pool->sortByDesc('start_date')->first();

        return $current ? $current->stase_id : null;
    }

    private function cellStatus($done, $total, $hasLog = true)
    {
        // No stase_log for this student/stase → no data, render grey.
        if (!$hasLog) {
            return 'empty';
        }

        if ($total <= 0) {
            return 'empty';
        }

        if ($done >= $total) {
            return 'green';
        }

        return ($done / $total) >= 0.5 ? 'yellow' : 'red';
    }

    private function withFilter($query, $request)
    {
        // Defaults to active when the status param is not supplied at all.
        $status = $request->has('status') ? $request->status : 'active';
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($request->year !== null && $request->year !== '') {
            $query->where('year', $request->year);
        }

        $name = $request->name ?? $request->keyword;
        if ($name !== null && $name !== '') {
            $query->where('students.name', 'LIKE', '%' . $name . '%');
        }

        return $query;
    }
}
