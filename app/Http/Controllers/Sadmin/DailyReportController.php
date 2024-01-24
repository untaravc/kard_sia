<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Presence;
use App\Models\Student;
use App\Models\StudentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function daily_report(Request $request)
    {
        $query['date'] = $request->date ?? date('Y-m-d');
        $query['last_week'] = date('Y-m-d', strtotime($query['date'] . '-7 days'));
        $query['last_month'] = date('Y-m-d', strtotime($query['date'] . '-30 days'));

        // Event Presensi
        $students = Student::whereStatus(1)
            ->orderBy('id')
            ->get();

        $activities = Activity::whereDate('start_date', $query['date'])
            ->withCount(['activity_students'])
            ->with('activity_students')
            ->orderBy('start_date')
            ->get();

        $presences = Presence::whereDate('checkin', $query['date'])
            ->get();

        $student_log_today = StudentLog::whereDate('created_at', $query['date'])
            ->select('student_id', DB::raw('count(*) as total'))
            ->groupBy('student_id')
            ->get();

        $student_log_week = StudentLog::whereDate('created_at', '>', $query['last_week'])
            ->select('student_id', DB::raw('count(*) as total'))
            ->groupBy('student_id')
            ->get();

        $student_log_month = StudentLog::whereDate('created_at', '>', $query['last_month'])
            ->select('student_id', DB::raw('count(*) as total'))
            ->groupBy('student_id')
            ->get();

        $this->response['result'] = [
            'students'          => $students,
            'activities'        => $activities,
            'presences'         => $presences,
            'student_log_today' => $student_log_today,
            'student_log_week'  => $student_log_week,
            'student_log_month' => $student_log_month,
        ];

        return $this->response;
    }
}
