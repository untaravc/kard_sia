<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CmdController extends Controller
{
    public function celarOpenStaseTask(Request $request)
    {
        $cutoff = Carbon::now()->subMonths(3);
        $inactiveStudentIds = Student::whereStatus('nonactive')->select('id');

        $deleted = OpenStaseTask::where(function ($query) use ($inactiveStudentIds, $cutoff) {
            $query->whereIn('student_id', $inactiveStudentIds)
                ->orWhere('created_at', '<', $cutoff);
        })->delete();

        return response()->json([
            'success' => true,
            'text' => 'Clear Open Stase Task Success',
            'result' => [
                'deleted' => $deleted,
            ],
        ]);
    }
}
