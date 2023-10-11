<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use App\Models\TaskDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaseTaskController extends Controller
{
    public function generateTaskLogDetail(Request $request){
        $lecture_id = Auth::guard('lecture')->user()->id;
        $openTask = OpenStaseTask::find($request->open_stase_task_id);

        // Apakah lecture pernah menilai?
        $lecturelog = StaseTaskLog::whereStaseTaskId($openTask->stase_task_id)
            ->whereStudentId($openTask->student_id)
            ->whereLectureId($lecture_id)
            ->first();

        //Jika ada data
        if($lecturelog){
            $stase_task_log = StaseTaskLog::with(['staseTaskLogPoint'=>function($q){
                $q->with('taskDetail')
                    ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                    ->select(
                        'stase_task_log_points.*',
                        'task_details.order'
                    )
                    ->orderBy('order','asc');
                    },
                    'staseTask',
                    'task'
                ])
                ->whereLectureId($lecture_id)
                ->whereStaseTaskId($openTask->stase_task_id)
                ->whereStudentId($openTask->student_id)
                ->first();
            return $stase_task_log;

        //Jika tidak ada data
        } else {
            // Apakah ada form yg belum diisi, field dosen kosong?
            $availablelog = StaseTaskLog::whereStaseTaskId($openTask->stase_task_id)
                ->whereStudentId($openTask->student_id)
                ->whereLectureId(null)
                ->first();

            // Ada form kosong.
            if($availablelog){
                $availablelog->update([
                    'lecture_id' => $lecture_id
                ]);
                $stase_task_log = StaseTaskLog::with(['staseTaskLogPoint'=>function($q){
                    $q->with('taskDetail')
                        ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                        ->select(
                            'stase_task_log_points.*',
                            'task_details.order'
                        )
                        ->orderBy('order','asc');
                        },
                        'staseTask',
                        'task',
                    ])
                    ->whereStaseTaskId($openTask->stase_task_id)
                    ->whereStudentId($openTask->student_id)
                    ->first();
                return $stase_task_log;

            // Tidak ada form kosong. Sudah diisi oleh dosen lain.
            }else{
                $task_log = StaseTaskLog::create([
                    'lecture_id' => $lecture_id,
                    'student_id' => $openTask->student_id,
                    'stase_task_id' => $openTask->stase_task_id,

                    'stase_id' => $openTask->staseTask->stase->id,
                    'task_id' => $openTask->staseTask->task->id,

                    'stase_log_id' => StaseLog::whereStudentId($openTask->student_id)->whereStaseId($openTask->staseTask->stase->id)->first()->id,
                    'status'=>'pending'
                ]);
                $task_details = TaskDetail::whereTaskId($task_log->task_id)->get();
                foreach ($task_details as $task_detail){
                    StaseTaskLogPoint::create([
                        'stase_task_log_id'    =>  $task_log->id,
                        'task_detail_id'    =>  $task_detail->id,
                    ]);
                }

                return StaseTaskLog::with([
                    'staseTaskLogPoint'=>function($q){
                    $q->with('taskDetail')
                        ->join('task_details', 'task_details.id', '=', 'stase_task_log_points.task_detail_id')
                        ->select(
                            'stase_task_log_points.*',
                            'task_details.order'
                        )
                        ->orderBy('order','asc');
                    },
                    'staseTask',
                    'task',
                ])
                    ->find($task_log->id);
            }
        }
    }
}
