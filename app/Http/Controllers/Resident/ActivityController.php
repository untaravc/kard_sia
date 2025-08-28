<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller {
    public function index(Request $request) {
        $dataContent = Activity::with([
            'creator'
        ])->whereStatus('active')
            ->orderByDesc('id');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function init() {
        $act = Activity::create([
            'created_by'         => Auth::guard('student')->user()->id ?? 0,
            'status'             => 'draft',
            'lecture_pengampu'   => json_encode([]),
            'lecture_penguji'    => json_encode([]),
            'lecture_pembimbing' => json_encode([]),
        ]);

        $act["pengampu"] = [];
        $act["penguji"] = [];
        $act["pembimbing"] = [];

        return $act;
    }

    public function show($id) {
        $data = Activity::with([
            'creator',
            'activity_lectures' => function ($q) {
                $q->with('lecture');
            },
            'activity_students' => function ($q) {
                $q->with('student');
            },
        ])->find($id);

        $data['penguji'] = Lecture::whereIn('id', json_decode($data['lecture_penguji'], true))->get();
        $data['pembimbing'] = Lecture::whereIn('id', json_decode($data['lecture_pembimbing'], true))->get();
        $data['pengampu'] = Lecture::whereIn('id', json_decode($data['lecture_pengampu'], true))->get();

        return $data;
    }

    public function store(Request $request) {
        $this->validateData($request);
        $request->merge([
            'created_by' => Auth::guard('student')->check() ?
                Auth::guard('student')->id() : 0,
        ]);

        if ($request->end_date == null) {
            $request->merge([
                'end_date' => date('Y-m-d H:i:s', strtotime($request->start_date) . '+2 hours'),
            ]);
        }

        Activity::create($request->all());
    }

    public function update(Request $request, $id) {
        $this->validateData($request);
        $request->merge([
            'status'     => 'active',
            'created_by' => Auth::guard('student')->user()->id,
        ]);

        Activity::find($id)->update($request->all());
    }

    public function destroy($id) {
        return 'ok';
        Activity::findOrFail($id)->delete();
    }

    public function validateData($request) {
        $this->validate($request, [
            "name"       => 'required',
            "start_date" => 'required',
            "category"   => 'required',
        ]);
    }

    public function withFilter($dataContent, $request) {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->keyword . '%');
        }
        return $dataContent;
    }

    public function addParticipans(Request $request, $id) {
        $ids = $request->ids;
        $arr = [];

        foreach ($ids as $key => $val) {
            if ($val) {
                array_push($arr, $key);
            }
        }
        $act = Activity::find($id);
        $act->update([
            $request->type => json_encode($arr)
        ]);
        $list = [];
        if ($request->type == 'lecture_penguji') {
            $list =  Lecture::whereIn('id', $arr)->get();
        } else if ($request->type == 'lecture_pengampu') {
            $list =  Lecture::whereIn('id', $arr)->get();
        } else if ($request->type == 'lecture_pembimbing') {
            $list =  Lecture::whereIn('id', $arr)->get();
        }
        return [
            'ids'  => $arr,
            'list' => $list,
        ];
    }
}
