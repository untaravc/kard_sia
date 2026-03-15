<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\OpenStaseTask;
use App\Models\Student;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stat()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Dashboard Stats Success',
            'result' => [
                'student_active' => Student::where('status', 'active')->count(),
                'student_guest' => Student::where('status', 'guest')->count(),
                'lecture_active' => Lecture::where('status', 'active')
                    ->where('is_in_house', 1)
                    ->count(),
                'lecture_guest' => Lecture::where('status', 'active')
                    ->where('is_in_house', 0)
                    ->count(),
                'open_exam_active' => OpenStaseTask::where('plan', '>', $now)->count(),
                'open_exam_today' => OpenStaseTask::whereDate('plan', $today)->count(),
            ],
        ]);
    }
}
