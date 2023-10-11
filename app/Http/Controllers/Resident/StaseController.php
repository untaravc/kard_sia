<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Traits\BaseTrait;
use App\Models\OpenStaseTask;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaseController extends Controller {
    use BaseTrait;

    public function index(Request $request) {
        $id    = Auth::guard('student')->id();
        $stase = StaseLog::with([
            'stase'
        ])->whereStudentId($id)
            ->orderByDesc('created_at')
            ->when($request->name, function ($q) use ($request) {
                $q->whereHas('stase', function ($q2) use ($request) {
                    $q2->where('name', 'LIKE', '%' . $request->name . '%');
                });
            })->get();

        return $stase;
    }

    public function active() {
        $id    = Auth::guard('student')->id();
        $stase = StaseLog::with([
            'stase',
            'staseTaskLogs' => function ($q) {
                $q->with([
                    'staseTask' => function ($q2) {
                        $q2->with(['lecture', 'openStaseTask' => function ($q3) {
                            $q3->with('lecture');
                        }]);
                    },
                    'stase'
                ]);
            }
        ])->whereStudentId($id)
            ->where('status', 'ongoing')
            ->first();

        return $stase;
    }

    public function openStaseTask(Request $request) {
        $request->validate([
            'title'       => 'required',
            'plan'        => 'required',
            'lecture_ids' => 'required',
        ], [
            'title.required'       => 'Judul harus diisi',
            'plan.required'        => 'Rencana Tanggal Ujian harus diisi',
            'lecture_ids.required' => 'Desen harus diisi',
        ]);

        foreach ($request->lecture_ids as $key => $lecture_id) {
            if ($lecture_id === true) {
                OpenStaseTask::create([
                    'student_id'    => Auth::guard('student')->user()->id,
                    'stase_task_id' => $request->stase_task_id,
                    'lecture_id'    => $key,
                    'title'         => $request->title,
                    'plan'          => $request->plan,
                    'link_token'    => $this->generateRandomString(17),
                ]);
            }
        }

        return $request;
    }

    public function closeStaseTask(Request $request) {
        OpenStaseTask::whereStudentId($request->student_id)
            ->whereStaseTaskId($request->stase_task_id)
            ->delete();
    }

    public function staseScore($id) {
        $auth_id = Auth::guard('student')->id();
        $group   = StaseTaskLog::whereStaseId($id)
            ->whereStudentId($auth_id)
            ->with([
                'staseTask',
            ])
            ->groupBy('stase_task_id')
            ->get();

        $data = StaseTaskLog::whereStudentId($auth_id)
            ->whereStaseId($id)
            ->with([
                'lecture',
                'staseTaskLogPoint' => function ($q2) {
                    $q2->with(['taskDetail']);
                },
            ])
            ->get();

//        return $group;

        foreach ($group as $item) {
            $item->setAttribute('scores', $data->where('stase_task_id', $item['stase_task_id'])->flatten());
        }

        return $group;
    }

    public function evaluation_link($stase_log_id){
        $stase_log = StaseLog::myOwn()->find($stase_log_id);

        if($stase_log){
            $stase_log->update([
               'evaluated_at' => now()
            ]);

            $stase = Stase::find($stase_log->stase_id);

            return redirect($stase->evaluation_link);
        }

        return 'no data';
    }
}
