<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaseTaskLogController extends Controller
{
    public function updateScore(Request $request, $stase_task_log_id){
        $points = $request->params['no'];

        //UPDATE POIN
        foreach ($points as $point){
            $box = explode(',', $point);
            if(isset($point)){
                $stlp = StaseTaskLogPoint::find($box[0]);
                $stlp->update([
                    'score'  => $box[1]
                ]);
            }
        }

        //UPDATE FILE
        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        $open = OpenStaseTask::find($request->params['open_stase_task_id']);
        if(isset($open->files[0])){
            foreach ($open->files as $item){
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id
                ]);
            }
        }

        //UPDATE SCORE
        $filled = $staseTaskLog->staseTaskLogPointFilled->count();
        $total = $staseTaskLog->staseTaskLogPointFilled->sum('score');
        $avg = $filled > 0 ? $total / $filled : 0;
        $staseTaskLog->update([
            'lecture_id'      => Auth::guard('lecture')->user()->id,
            'point_amount'  => $filled,
            'point_total'   => $total,
            'point_average' => $avg,
            'symbol'        => $this->pointProcess($avg),
            'title'         => $open->title,
            'status'        => 'publish',
            'date'          => date('Y-m-d H:i:s'),
            'note'          => $request->params['note'],
            'plan'          => $open['plan'],
        ]);
    }

    public function pointProcess($avg){
        switch (true){
            case $avg >= 95:
                $val = 'A';
                break;

            case $avg >= 90 && $avg < 95:
                $val = 'B';
                break;

            case $avg >= 85 && $avg < 90:
                $val = 'C';
                break;

            default:
                $val = 'E';
        }
        return $val;
    }

    public function updateScoreTesis(Request $request, $stase_task_log_id){
        $points = $request->params['no'];

        //UPDATE POIN

        foreach ($points as $point){
            $box = explode(',', $point);
            if(isset($point)){
                $stlp = StaseTaskLogPoint::find($box[0]);
                $stlp->update([
                    'score'  => $box[1]
                ]);
            }
        }

        //UPDATE FILE
        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        $open = OpenStaseTask::find($request->params['open_stase_task_id']);
        if(isset($open->files[0])){
            foreach ($open->files as $item){
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id
                ]);
            }
        }

        //UPDATE SCORE

        $arr = $staseTaskLog->staseTaskLogPoint;
        $result = 0;
        foreach ($arr as $item) {
            $result += $this->tesisScoreIndex($item->order, $item->score);
        }
//        return response()->json(round($result/24), 500);

        $staseTaskLog->update([
            'lecture_id'      => Auth::guard('lecture')->user()->id,
            'point_amount'  => 14,
            'point_total'   => $result,
            'point_average' => round($result / 24),
//            'symbol'        => $this->pointProcess($avg),
            'title'         => $open->title,
            'status'        => 'publish',
            'date'          => date('Y-m-d H:i:s'),
            'plan'          => $open['plan'],
            'note'          => $request->params['note'],
            'conclusion'    => $request->params['conclusion'],
        ]);
    }

    public function tesisScoreIndex($order, $score){
        $result = $score;
        switch ($order){
            case 4:
            case 8:
            case 9:
            case 10:
            case 11:
                $result = $score;
                break;
            case 12:
                $result = $score * 3;
                break;
            default:
                $result = $score * 2;
        }

        return $result;
    }

    public function updateScoreProposal(Request $request, $stase_task_log_id){
        $points = $request->params['no'];

        //UPDATE POIN

        foreach ($points as $point){
            $box = explode(',', $point);
            if(isset($point)){
                $stlp = StaseTaskLogPoint::find($box[0]);
                $stlp->update([
                    'score'  => $box[1]
                ]);
            }
        }

        //UPDATE FILE
        $staseTaskLog = StaseTaskLog::find($stase_task_log_id);
        $open = OpenStaseTask::find($request->params['open_stase_task_id']);
        if(isset($open->files[0])){
            foreach ($open->files as $item){
                $item->update([
                    'stase_task_log_id' => $stase_task_log_id
                ]);
            }
        }

        //UPDATE SCORE

        $arr = $staseTaskLog->staseTaskLogPoint;
        $result = 0;
        foreach ($arr as $item) {
            $result += $this->proposalScoreIndex($item->order, $item->score);
        }

        $staseTaskLog->update([
            'lecture_id'      => Auth::guard('lecture')->user()->id,
            'point_amount'  => 27,
            'point_total'   => $result,
            'point_average' => round($result / 45),
//            'symbol'        => $this->pointProcess($avg),
            'title'         => $open->title,
            'status'        => 'publish',
            'date'          => date('Y-m-d H:i:s'),
            'plan'          => $open['plan'],
            'note'          => $request->params['note'],
            'conclusion'    => $request->params['conclusion'],
        ]);
    }

    public function proposalScoreIndex($order, $score){
        $result = $score;
        switch ($order){
            case 3:
            case 4:
            case 6:
            case 7:

            case 15:
            case 18:
            case 20:

            case 22:
            case 23:
            case 25:

            case 27:
                $result = $score;
                break;

            case 17:
            case 26:
                $result = $score * 3;
                break;

            default:
                $result = $score * 2;
        }

        return $result;
    }
}
