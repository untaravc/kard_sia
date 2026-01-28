<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\Student;
use Illuminate\Http\Request;

class CmdController extends Controller
{
    public function celarOpenStaseTask(Request $request)
    {
        $deleted = OpenStaseTask::whereIn(
            'student_id',
            Student::whereStatus('nonactive')->select('id')
        )->delete();

        return response()->json([
            'success' => true,
            'text' => 'Clear Open Stase Task Success',
            'result' => [
                'deleted' => $deleted,
            ],
        ]);
    }
}
