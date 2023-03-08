<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;

class ScoringController extends Controller
{
    public function index() {
        $lecture = auth_dosen();
        $data = OpenStaseTask::with([
            'student',
            'lecture',
            'files',
            'staseTask'=>function($q1){
                $q1->with([
                    'stase',
                    'task',
                ]);
            },
        ])->where(function ($q) use($lecture){
            $q->where('lecture_id', $lecture['id']);
        })
            ->orderByDesc('created_at')
            ->get();

        foreach ($data as $d){
            $d->setAttribute('score', StaseTaskLog::whereLectureId($lecture->id)
                ->whereStudentId($d['student']['id'])
                ->whereStaseTaskId($d->staseTask->id)
                ->first());
        }

        $this->res['status'] = true;
        $this->res['text'] = 'Retrieve scoring data success';
        $this->res['data'] = [
            'private_scoring' => $data
        ];

        return $this->res;
    }
}
