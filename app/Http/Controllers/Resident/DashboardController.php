<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use App\Models\Student;
use App\Models\TaskDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index() {
        $auth     = Auth::guard('student')->user();
        $resident = Student::with('today_presence')->find($auth->id);
        $is_admin = Auth::guard('web')->check();
//        if(!$resident->today_presence && !$is_admin){
//            if($resident->year === '2020-07'){
//                return redirect('/daily');
//            }
//        }

        return view('resident.layout');
    }

    public function getSchedule() {
        $data = Activity::whereDate('start_date', Carbon::now())->get();
        return $data;
    }

    public function presence(Request $request) {
        ActivityStudent::create([
            'activity_id' => $request->id,
            'student_id'  => Auth::guard('student')->user()->id,
            'note'        => $request->presensi_note,
        ]);
    }

    public function select_active_stase(Request $request) {
        $user   = Auth::guard('student')->user();
        $active = StaseLog::whereStudentId($user->id)
            ->whereStatus('ongoing')
            ->first();

        if ($active) {
            $active->update([
                'status' => 'finish',
            ]);
        }

        StaseLog::whereStudentId($user->id)
            ->find($request->stase_id)
            ->update([
                'status'     => 'ongoing',
                'start_date' => $request->start_date ?? null,
                'end_date'   => $request->end_date ?? null,
            ]);
    }

    public function add_stase(Request $request) {
        $this->validateData($request);
        $student = Auth::guard('student')->user();
        $active  = StaseLog::whereStudentId($student->id)
            ->whereStatus('ongoing')
            ->first();
        if ($active) {
            $active->update([
                'status' => 'finish',
            ]);
        }

        $stase_log = StaseLog::create([
            'student_id' => $student->id,
            'stase_id'   => $request->stase_id,
            'status'     => 'ongoing',
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        $tasks = StaseTask::whereStaseId($request->stase_id)->get();
        foreach ($tasks as $task) {
            $task_log = StaseTaskLog::create([
                'task_id'       => $task->task_id,
                'stase_task_id' => $task->id,
                'stase_id'      => $request->stase_id,
                'stase_log_id'  => $stase_log->id,
                'student_id'    => $student->id,
                'status'        => 'pending'
            ]);

            $task_details = TaskDetail::whereTaskId($task->task_id)->get();

            foreach ($task_details as $task_detail) {
                StaseTaskLogPoint::create([
                    'stase_task_log_id' => $task_log->id,
                    'task_detail_id'    => $task_detail->id,
                ]);
            }
        }
    }

    private function validateData($request) {
        $this->validate($request, [
            "stase_id" => 'required',
        ]);
    }

    public function info_cards() {
        $avg_point = StaseTaskLog::myOwn()->where('point_average', '>', 50)->avg('point_average');
        $presences = ActivityStudent::myOwn()->count();
        $stases    = StaseLog::myOwn()->count();
        return [
            'avg_point' => floor($avg_point),
            'presences' => $presences,
            'stases'    => $stases,
        ];
    }

    public function stase_log(Request $request) {
        $auth_id                = Auth::guard('student')->id();
        $this->response['data'] = StaseLog::whereStudentId($auth_id)
            ->find($request->stase_id);

        return $this->response;
    }
}
