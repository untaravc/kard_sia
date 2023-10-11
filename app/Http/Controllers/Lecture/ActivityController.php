<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request) {
        $dataContent = Activity::with([
            'lectures',
            'students',
            'creator'
        ])->whereStatus('active')
            ->orderByDesc('start_date');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function show($id) {
        return Activity::with([
            'creator',
            'activity_lectures' => function($q){
                $q->with('lecture');
            },
            'activity_students'=> function($q){
                $q->with('student');
            },
        ])->find($id);
    }

    public function withFilter($dataContent, $request) {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%'.$request->name.'%');
        }
        return $dataContent;
    }
}
