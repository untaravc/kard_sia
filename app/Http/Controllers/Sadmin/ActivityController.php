<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Activity::with([
            'creator',
        ])
            ->where('status', '!=', 'draft')
            ->withCount([
                'activity_lectures',
                'activity_students'
            ])
            ->orderByDesc('id');

        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);
        $request->merge([
            'created_by' => 0,
        ]);

        Activity::create($request->all());
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

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        $request->merge([
            'status'     => 'active',
        ]);

        Activity::find($id)->update($request->all());

        return $this->response;
    }

    public function destroy($id)
    {
        $dataContent = Activity::findOrFail($id)->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
//            "start_date"    => 'required',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where(function ($q)use ($request){
                $q->where('name', 'LIKE', '%'.$request->keyword.'%')
                    ->orWhere('speaker', 'LIKE', '%'.$request->keyword.'%');
            });
        }

        if ($request->lecture_id != null){
            $dataContent = $dataContent->where(function ($q)use ($request){
                $q->where('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_pembimbing', 'LIKE', '%'.$request->lecture_id.']'.'%')
                    ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_penguji', 'LIKE', '%'.$request->lecture_id.']'.'%')
                    ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.','.'%')
                    ->orWhere('lecture_pengampu', 'LIKE', '%'.$request->lecture_id.']'.'%');
            });
        }

        if ($request->start_date != null){
            $dataContent = $dataContent->withStartDate($request->start_date);
        }

        if ($request->end_date != null){
            $dataContent = $dataContent->withEndDate($request->end_date);
        }
        return $dataContent;
    }
}
