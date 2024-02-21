<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\FormOption;
use App\Models\Lecture;
use App\Models\Presence;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentLog;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $kps = [
        'name'    => 'dr. Anggoro Budi Hartopo, MSc, SpPD, PhD, SpJP',
        'nip'     => '197807182010121004',
        'gol'     => 'Penata Tingkat I / III D',
        'jabatan' => 'Ketua Program Studi Jantung dan Pembuluh Darah'
    ];

    public function reportStase(Request $request)
    {
        $query = [];
        $query['student_id'] = $request->student_id ?? 114;
        $query['stase_id'] = $request->stase_id ?? 8;

        $student = Student::find($query['student_id']);
        $student_profile = StudentProfile::whereStudentId($query['student_id'])->first();

        $stase = Stase::find($query['stase_id']);

        $stase_log = StaseLog::whereStudentId($query['student_id'])
            ->whereStaseId($query['stase_id'])
            ->first();

        $stase_task_logs = StaseTaskLog::whereStudentId($query['student_id'])
            ->with('lecture')
            ->whereStaseId($query['stase_id'])
            ->where('point_average', '>', 0)
            ->orderBy('stase_id')
            ->get();

        $this->response['result'] = [
            'student'         => $student,
            'student_profile' => $student_profile,
            'stase'           => $stase,
            'stase_log'       => $stase_log,
            'stase_task_logs' => $stase_task_logs
        ];

        if ($request->dev) {
            return $this->response;
        }

        return view('admin.pdf.student_stase', $this->response['result']);
    }

    public function reportScore(Request $request)
    {
        $query = [];
        $query['student_id'] = $request->student_id ?? 114;
        $query['tahap'] = $request->tahap ?? 2;

        $student = Student::find($query['student_id']);
        $student_profile = StudentProfile::whereStudentId($query['student_id'])->first();

        $stases = Stase::where('desc', 'tahap_' . $query['tahap'])->get();
        $stase_logs = StaseLog::whereStudentId($query['student_id'])
            ->whereIn("stase_id", $stases->pluck('id')->toArray())
            ->with('stase')
            ->get();

        $stase_task_logs = StaseTaskLog::whereStudentId($query['student_id'])
            ->whereTaskId(12)
            ->with('staseLog')
            ->whereIn("stase_id", $stases->pluck('id')->toArray())
            ->get();

        $date_start = null;
        $date_end = null;

        foreach ($stase_logs as $log) {
            $log->setAttribute('log', $stase_task_logs->where('stase_log_id', $log->id)->first());
            if ($date_start == null) {
                $date_start = $log->start_date;
            } else if ($date_start > $log->start_date) {
                $date_start = $log->start_date;
            }

            if ($date_end == null) {
                $date_end = $log->end_date;
            } else if ($date_end < $log->end_date) {
                $date_end = $log->end_date;
            }
        }

//        $active_date = Activity::where('name', "Laporan Jaga")
//            ->where('start_date', '>=', $date_start)
//            ->where('start_date', '<=', $date_end)
//            ->select('start_date', "name")
//            ->get();
//
//        $presences = Presence::whereStudentId($query['student_id'])
//            ->where('checkin', '>=', $date_start)
//            ->where('checkin', '<=', $date_end)
//            ->get();
//
//        $activities = ActivityStudent::whereStudentId($query['student_id'])
//            ->where('created_at', '>=', $date_start)
//            ->where('created_at', '<=', $date_end)
//            ->get();
//
//        for ($i = 0; $i < count($active_date); $i++){
//            foreach ($presences as $presence){
//                if(isset($active_date[$i])){
//                    if(substr($presence->checkin, 0, 10) == substr($active_date[$i]['start_date'], 0, 10)){
//                        unset($active_date[$i]);
//                    }
//                }
//            }
//        }
//
//        for ($i = 0; $i < count($active_date); $i++){
//            foreach ($activities as $activity){
//                if(isset($active_date[$i])){
//                    if(substr($activity->created_at, 0, 10) == substr($active_date[$i]['start_date'], 0, 10)){
//                        unset($active_date[$i]);
//                    }
//                }
//            }
//        }
        $dpa = null;
        if(isset($student_profile->lecture_id)){
            $dpa = Lecture::find($student_profile->lecture_id);
        }

        $this->response['result'] = [
            'student'         => $student,
            'student_profile' => $student_profile,
            "tahap"           => $query['tahap'],
            "date_start"      => $date_start,
            "date_end"        => $date_end,
            "stase_logs"      => $stase_logs,
            "kps"             => $this->kps,
            "dpa"             => $dpa,
//            "presence_off"      => count($active_date),
        ];

        if ($request->dev) {
            return $this->response;
        }

        return view('admin.pdf.score', $this->response['result']);
    }

    public function logbookInStase(Request $request)
    {
        $stase_id = $request->student_id ?? 17;
        $group = StudentLog::whereStaseId($stase_id)
            ->with([
                'lecture',
                'student'
            ])->get();

        $form_data = FormOption::whereRelationId($stase_id)
            ->whereType('stase-logbook')
            ->get();

        $categories = FormOption::whereRelationId($stase_id)
            ->whereType('logbook-cat')
            ->get();

        foreach ($form_data as $data) {
            $data->setAttribute('logbook', $group->where('type', $data->value)->flatten());
        }
//        return $form_data;
        $result = [];
        foreach ($form_data as $data) {
            if (count($data['logbook']) > 0 || $data['status'] == 1) {
                $result[] = $data;
            }
        }

        $this->response['result'] = $result;
        $this->response['categories'] = $categories;

        if ($request->json) {
            return $this->response;
        }

        return view('admin.logbook_in_stase', $this->response);
    }
}
