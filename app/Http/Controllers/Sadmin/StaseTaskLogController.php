<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\StaseTaskLog;
use App\Models\StaseTaskLogPoint;
use Illuminate\Http\Request;

class StaseTaskLogController extends Controller
{
    public function update(Request $request, $id){
        $points = $request->params['no'];
        foreach ($points as $point){
            $box = explode(',', $point);
            if(isset($point)){
                StaseTaskLogPoint::find($box[0])
                    ->update([
                        'score'  => $box[1]
                    ]);
            }
        }
        $staseTaskLog = StaseTaskLog::find($id);
        $filled = $staseTaskLog->staseTaskLogPointFilled->count();
        $total = $staseTaskLog->staseTaskLogPointFilled->sum('score');
        $avg = $total / $filled;
        $staseTaskLog->update([
            'lectures'      => [],
            'point_amount'  => $filled,
            'point_total'   => $total,
            'point_average' => $avg,
            'symbol'        => $this->pointProcess($avg),
            'status'        => 'draft'
        ]);
        return 'loss';
    }

    public function showJurnal($id){
        return StaseTaskLog::with([
            'staseTaskLogPoint'=>function($q){
                $q->with('taskDetail');
            }
        ])->find($id);
    }

    public function showMinicex($id){
        return StaseTaskLog::with([
            'staseTaskLogPoint'=>function($q){
                $q->with('taskDetail');
            }
        ])->find($id);
    }

    public function pointProcess($avg){
        switch (true){
            case $avg >= 90:
                $val = 'A';
                break;
            case $avg >= 85 && $avg < 90:
                $val = 'B';
                break;
            case $avg >= 80 && $avg < 85:
                $val = 'C';
                break;
            case $avg >= 75 && $avg < 80:
                $val = 'D';
                break;
            default:
                $val = 'E';
        }
        return $val;
    }
}
