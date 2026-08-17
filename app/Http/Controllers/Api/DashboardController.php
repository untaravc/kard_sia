<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;

class DashboardController extends Controller
{
    public function stat()
    {
        return response()->json([
            'success' => true,
            'text' => 'Retrieve Dashboard Stats Success',
            'result' => [
                'student_active' => Student::where('status', 'active')->count(),
                'lecture_active' => Lecture::where('status', 'active')->count(),
            ],
        ]);
    }

    /**
     * Open stase tasks bucketed by the owning student's `year`, split into
     * scored vs. no-score counts. A task counts as scored when a
     * stase_task_logs row exists for the same student/lecture/stase_task
     * with a point_average, mirroring OpenStaseTask::getScoreAttribute().
     */
    public function openStaseTaskScoreMatric()
    {
        $openTasks = OpenStaseTask::query()
            ->join('students', 'students.id', '=', 'open_stase_tasks.student_id')
            ->select(
                'open_stase_tasks.student_id',
                'open_stase_tasks.stase_task_id',
                'open_stase_tasks.lecture_id',
                'students.year as student_year'
            )
            ->get();

        $scoredKeys = StaseTaskLog::query()
            ->where('point_average', '>', 0)
            ->select('student_id', 'lecture_id', 'stase_task_id')
            ->get()
            ->map(function ($log) {
                return $log->student_id . '-' . $log->lecture_id . '-' . $log->stase_task_id;
            })
            ->unique()
            ->flip();

        $buckets = [];
        foreach ($openTasks as $task) {
            $year = $task->student_year ?: 'Unknown';
            if (!isset($buckets[$year])) {
                $buckets[$year] = ['year' => $year, 'scored' => 0, 'no_score' => 0];
            }

            $key = $task->student_id . '-' . $task->lecture_id . '-' . $task->stase_task_id;
            if (isset($scoredKeys[$key])) {
                $buckets[$year]['scored']++;
            } else {
                $buckets[$year]['no_score']++;
            }
        }

        ksort($buckets);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Open Stase Task Score Matric Success',
            'result' => array_values($buckets),
        ]);
    }
}
