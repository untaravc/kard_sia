<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\StudentExams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ExamController extends Controller
{
    public function index(){
        $auth = Auth::guard('student')->check();
        if($auth){
            $student_exam = StudentExams::with([
                'exam'
            ])->whereStatus(1)
                ->whereStudentId(Auth::guard('student')->id())
                ->get();

            $this->response['data'] = $student_exam;
            return $this->response;
        }
    }
}
