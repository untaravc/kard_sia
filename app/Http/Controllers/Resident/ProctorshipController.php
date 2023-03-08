<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\StudentExams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Facades\Agent;

class ProctorshipController extends Controller {
    public function exam(Request $request, $token) {
        $os = strtolower(Agent::platform());
        if (strpos($os, 'andro') > -1 || strpos($os, 'ios') > -1) {
            return 'hanya bisa di buka di tampilan laptop';
        }

        $exam = Exam::whereToken($token)->first();
        if ($exam == null) {
            return 'data tidak ditemukan';
        }

        $student_exam = StudentExams::whereExamId($exam->id)
            ->whereStatus(1)
            ->whereStudentId(Auth::guard('student')->id())
            ->first();

        if($request->finish == 'true'){
            if($student_exam){
                $student_exam->update([
                    'finish_at'  => now(),
                    'status'     => 2,
                ]);
            }

            return redirect('/resident');
        }

        if ($student_exam == null) {
            return 'ujian belum aktif.';
        }

        $student_exam->update([
            'start_at'  => now(),
        ]);
        $student_exam->increment('attempts');

        return view('resident.exam', compact('student_exam', 'exam'));
    }
}
