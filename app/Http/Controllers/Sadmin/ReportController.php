<?php

namespace App\Http\Controllers\Sadmin;

use App\Exports\DefaultExport;
use App\Exports\ExportExcelTemplate;
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
use Maatwebsite\Excel\Facades\Excel;

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
            ->orderBy('stase_id')
            ->first();

        $stase_task_logs = StaseTaskLog::whereStudentId($query['student_id'])
            ->orderBy('stase_task_id')
            ->with('lecture')
            ->whereStaseId($query['stase_id'])
            ->where('point_average', '>', 0)
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
            ->orderBy('stase_id')
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
        if (isset($student_profile->lecture_id)) {
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

    public function reportStudent(Request $request)
    {
        $query = [];
        $query['student_id'] = $request->student_id ?? 114;

        $data['student'] = Student::find($query['student_id']);

        $data['stases'] = Stase::where('desc', '!=', null)
            ->with(['staseTasks'])
            ->whereIn('desc', ['tahap_1', 'tahap_2', 'tahap_3'])
            ->orderBy('desc')
            ->get();

        $data['stase_task_logs'] = StaseTaskLog::whereStudentId($query['student_id'])
            ->with('lecture')
            ->whereStatus('publish')
            ->get();

        $data['stase_logs'] = StaseLog::whereStudentId($query['student_id'])
            ->get();

        $data['stase_date'] = $this->staseLogToStase($data['stases'], $data['stase_logs']);
        $data['stase'] = $this->staseTaskLogToStase($data['stases'], $data['stase_task_logs']);

        if ($request->dev == 1) {
            return $data;
        }

        $data['template'] = 'admin.report_student';
        $student_name = $data['student']['name'] ?? "";
//        return Excel::download(new ExportExcelTemplate($data), 'Report ' . $student_name . '.xls');
        return view('admin.report_student', $data);
    }

    private function staseLogToStase($stases, $stase_logs)
    {
        $logs = collect($stase_logs);
        for ($s = 0; $s < count($stases); $s++) {
            $has_log = $logs->where('stase_id', $stases[$s]['id'])->first();

            $stases[$s]['stase_log'] = $has_log;
        }
        return $stases;
    }

    private function staseTaskLogToStase($stases, $stase_task_logs)
    {
        for ($s = 0; $s < count($stases); $s++) {
            for ($t = 0; $t < count($stases[$s]['staseTasks']); $t++) {
                $has_penilaian = $stase_task_logs->where('stase_id', $stases[$s]['staseTasks'][$t]['stase_id'])
                    ->where('task_id', $stases[$s]['staseTasks'][$t]['task_id'])
                    ->first();
                $stases[$s]['staseTasks'][$t]['stase_task_log'] = $has_penilaian;
            }
        }
        return $stases;
    }

    public function presences(Request $request)
    {
        $student = Student::find($request->student_id);
        if(!$student){
            return $this->response->errorNotFound();
        }
        $presences = Presence::whereStudentId($request->student_id)
            ->whereDate('checkin', '>=', $request->from)
            ->whereDate('checkin', '<=', $request->to)
            ->get();

        $activities = ActivityStudent::whereStudentId($request->student_id)
            ->leftJoin('activities', 'activities.id', '=', 'activity_students.activity_id')
            ->whereDate('activity_students.created_at', '>=', $request->from)
            ->whereDate('activity_students.created_at', '<=', $request->to)
            ->select('activity_students.*', 'activities.name')
            ->get();

        $data = [];
        $download_ctrl = new DownloadController();

        for($date = $request->from; $date <= $request->to; $date = date('Y-m-d',strtotime("+1 day", strtotime($date))) ) {
            $data[] = [
                'date' => $date,
                'day' => $download_ctrl->hari(date('w',strtotime($date))),
                'presence' => $presences->where('checkin', '>', $date . ' 00:00:00')->where('checkin', '<', $date . ' 23:59:59')->first(),
                'activities' => $activities->where('created_at', '>', $date . ' 00:00:00')->where('created_at', '<', $date . ' 23:59:59')->flatten(),
            ];
        }

        return view('admin.report_presences', compact('data', 'student'));
    }
}
