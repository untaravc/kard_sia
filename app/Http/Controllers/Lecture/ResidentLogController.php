<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\FormOption;
use App\Models\StudentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResidentLogController extends Controller {
    public function resident_list(Request $request) {
        $student_log = StudentLog::lectureHas()
            ->with('student')
            ->select('student_id', 'status', DB::raw('count(*) as pending'))
            ->groupBy('student_id')
            ->whereStatus(0)
            ->get();

        $this->response['result'] = $student_log;
        return $this->response;
    }

    public function resident_log_list(Request $request, $resident_id) {
        $logs = StudentLog::lectureHas()
            ->with('stase')
            ->whereStudentId($resident_id)
            ->whereStatus(0)
            ->orderByDesc('date')
            ->get();

        $stase_id = $logs->unique('stase_id')->pluck('stase_id');
        $options  = FormOption::whereIn('relation_id', $stase_id)
            ->whereType('stase-logbook')
            ->get();
        foreach ($logs as $log) {
            $log->setAttribute('options', $options->where('relation_id', $log->stase_id)->first());
        }

        $this->response['result'] = $logs;
        return $this->response;
    }

    public function resident_log_list_acc($resident_id) {
        StudentLog::lectureHas()
            ->whereStudentId($resident_id)
            ->whereStatus(0)
            ->update([
                'status' => 1
            ]);

        return $this->response;
    }
}
