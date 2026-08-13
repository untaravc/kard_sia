<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
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
}
