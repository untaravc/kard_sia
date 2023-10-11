<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;

class OpenStaseTaskController extends Controller
{
    public function index(Request $request){
        $three_month = date('Y-m-d', strtotime(date('Y-m-d') . ' -3months'));
        $dataContent = OpenStaseTask::with([
            'student',
            'staseTask',
            'lecture',
        ])->where('created_at', '>', $three_month)->orderByDesc('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);

        foreach ($dataContent as $d){
            $d->setAttribute('scores', StaseTaskLog::with('lecture')
                ->whereStudentId($d['student']['id'])
                ->when($d['lecture']['id'] > 0, function ($q) use ($d){
                    $q->whereLectureId($d['lecture']['id']);
                })
                ->whereStaseTaskId($d['staseTask']['id'])
                ->get());
        }

        return $dataContent;
    }

    public function destroy($id){
        OpenStaseTask::find($id)->delete();
    }

    public function withFilter($dataContent, $request){
        if($request->keyword != null){
            $dataContent = $dataContent->search($request->keyword, null, true);
        }

        return $dataContent;
    }
}
