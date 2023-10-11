<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\TaskDetail;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaseLogController extends Controller {
    public function index(Request $request) {
        $dataContent = StaseLog::whereDeletedAt(null);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->orderByDesc('created_at')->paginate(10);
        return $dataContent;
    }

    public function store(Request $request) {
        $this->validateData($request);
        if ($request->start_date == null) {
            $request->merge([
                'start_date' => now(),
            ]);
        }
        $stase_log = StaseLog::create($request->all());

        $tasks = StaseTask::whereStaseId($request->stase_id)->get();
        foreach ($tasks as $task) {
            $task_log = StaseTaskLog::create([
                'task_id'       => $task->task_id,
                'stase_task_id' => $task->id,
                'stase_id'      => $request->stase_id,
                'stase_log_id'  => $stase_log->id,
                'student_id'    => $request->student_id,
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

    public function getStaseLog() {
        StaseLog::whereStudentId()->get();
    }

    public function update(Request $request, $id) {
        $this->validateData($request);
        if ($request->status == 'finish') {
            $request->merge([
                'end_date' => $request->end_date ?? now(),
                'duration' => $request->end_date == null
                    ?
                    date_diff(date_create($request->start_date), now())->format('%R%a hari')
                    :
                    date_diff(date_create($request->start_date), date_create($request->end_date))->format('%R%a hari'),
            ]);
        }
        StaseLog::find($id)->update($request->all());
    }

    public function destroy($id) {
        StaseLog::findOrFail($id)->delete();
    }

    public function validateData($request) {
        $this->validate($request, [
            "stase_id" => 'required',
            "status"   => 'required',
        ]);
    }

    public function withFilter($dataContent, $request) {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where('name', 'LIKE', "%" . $request->keyword . "%");
        }

        if ($request->status != null || $request->status != "") {
            $dataContent = $dataContent->whereStatus($request->status);
        }

        if ($request->type != null) {
            $dataContent = $dataContent->whereType($request->type);
        }

        if ($request->start_date != null) {
            $dataContent = $dataContent->withStartDate($request->start_date);
        }

        if ($request->end_date != null) {
            $dataContent = $dataContent->withEndDate($request->end_date);
        }
        return $dataContent;
    }

    public function print(Request $request)
    {
        $request['student_id'] = $request->student_id ?? Auth::guard('student')->id();

        $data = StaseTaskLog::whereStudentId($request['student_id'])
            ->whereIn('stase_log_id', $request->stase_log_ids)
            ->with([
                'lecture',
                'stase',
            ])->orderBy($request->stase_log_ids)
            ->get();
        return view('admin.pdf.score', compact('data'));
    }
}
