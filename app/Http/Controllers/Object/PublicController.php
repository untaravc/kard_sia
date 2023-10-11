<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Models\Object\Quiz;
use App\Models\Object\QuizLog;
use App\Models\Object\QuizSection;
use App\Models\Object\QuizStudent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function quiz(Request $request)
    {
        $quiz_student = QuizStudent::whereToken($request->t)
            ->with('section')->first();

        $auth = Auth::guard('student')->user();
        if (!$auth) {
            $auth = Auth::guard('lecture')->user();
        }
        if (!$auth) {
            $auth = Auth::guard()->user();
        }

        // jika closed
        if($quiz_student->is_closed == 1){
            if($auth['id'] != $quiz_student->student_id){
                $student = Student::find($quiz_student->student_id);
                $this->response['status'] = false;
                $this->response['text'] = 'Hanya bisa di akses oleh user ' . $student->email;
                return $this->response;
            }
        }

        // status tidak 1
        if($quiz_student->status != 1){
            $this->response['status'] = false;
            $this->response['text'] = 'Quis tidak tersedia.' . ($quiz_student->status == 2 ? ' Telah selesai.' : ' Belum diaktifkan.');
            return $this->response;
        }

        $quiz_sections = QuizSection::with('quiz')->whereSectionId($quiz_student->section_id)->get();
        $quiz_logs = QuizLog::whereStudentId($quiz_student->student_id)
            ->whereQuizStudentId($quiz_student->id)
            ->get();

        foreach ($quiz_sections as $quiz_section) {
            $q_id = $quiz_section['quiz']['id'];
            $quiz_log = $quiz_logs->where('quiz_id', $q_id)->first();
            if ($quiz_log) {
                $quiz_section['answered'] = $quiz_log['answer'];
            }
        }

        $this->response['auth'] = $auth;
        $this->response['result'] = $quiz_student;
        $this->response['quiz_sections'] = $quiz_sections;
        return $this->response;
    }

    public function select_option(Request $request)
    {
        $quiz_student = $request->quiz_student;
        $quiz_section = $request->quiz_section;
        $option = $request->option;
        $answer_true = $quiz_section['quiz']['answer'];

        $payload = [
            'student_id'      => $quiz_student['student_id'],
            'section_id'      => $quiz_student['section_id'],
            'quiz_id'         => $quiz_section['quiz_id'],
            'quiz_student_id' => $quiz_student['id'],
            'answer'          => $option['option'],
            'result'          => $answer_true == $option['option'],
        ];

        $quiz_log = QuizLog::whereQuizStudentId($payload['quiz_student_id'])
            ->whereQuizId($payload['quiz_id'])
            ->first();

        if ($quiz_log) {
            $quiz_log->update($payload);
        } else {
            QuizLog::create($payload);
        }

        $quiz_logs = QuizLog::whereQuizStudentId($payload['quiz_student_id'])
            ->whereQuizStudentId($payload['quiz_student_id'])
            ->get();

        QuizStudent::find($payload['quiz_student_id'])->update([
            'answered'    => count($quiz_logs),
            'answer_true' => $quiz_logs->sum('result'),
        ]);

        return $this->response;
    }

    public function quiz_finish(Request $request){
        $quiz_student = QuizStudent::whereToken($request->t)
            ->with('section')->first();
        if($quiz_student){
            $quiz_student->update([
               'status' => 2,
            ]);
        }

        $this->response['push'] = '/resident';
        return $this->response;
    }
}
