<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller {
    public function index() {
        return view('lecture.layout');
    }

    public function openStaseTask() {
        $lecture = Auth::guard('lecture')->user();

        $data = OpenStaseTask::with([
            'student',
            'lecture',
            'files',
            'staseTask' => function ($q1) {
                $q1->with([
                    'stase',
                    'task',
                ]);
            },
        ])->where(function ($q) use ($lecture) {
            $q->where('lecture_id', $lecture->id);
//                ->orWhere('lecture_id', 0);
        })
            ->whereDate('created_at', '>', date('Y-m-d', strtotime(date('Y-m-d') . ' -3 months')))
            ->orderByDesc('created_at')->get();

        foreach ($data as $d) {
            $d->setAttribute('data', StaseTaskLog::whereLectureId($lecture->id)
                ->whereStudentId($d['student']['id'])
                ->whereStaseTaskId($d->staseTask->id)
                ->first());
        }

        $blank   = [];
        $clicked = [];

        foreach ($data as $item) {
            $nt = Auth::guard('lecture')->id();

            if ($item['data'] && $item['data']['point_average'] > 0) {
                if ($nt !== 2) {
                    $clicked[] = $item;
                }
            } else {
                $blank[] = $item;
            }
        }

        return array_merge($blank, $clicked);
    }

    public function openStaseTaskAll() {
        $lecture = Auth::guard('lecture')->user();

        $data = OpenStaseTask::with([
            'student',
            'lecture',
            'files',
            'staseTask' => function ($q1) {
                $q1->with([
                    'stase',
                    'task',
                ]);
            },
        ])->whereLectureId(0)
            ->whereDate('created_at', '>', date('Y-m-d', strtotime(date('Y-m-d') . ' -2 months')))
            ->orderByDesc('created_at')
            ->get();

        foreach ($data as $d) {
            $d->setAttribute('data', StaseTaskLog::whereLectureId($lecture->id)
                ->whereStudentId($d['student']['id'])
                ->whereStaseTaskId($d->staseTask->id)
                ->first());
        }

        return $data;
    }

    public function getSchedule() {
        $data = Activity::whereDate('start_date', Carbon::now())->get();
        return $data;
    }

    public function presence(Request $request) {
        ActivityLecture::create([
            'activity_id' => $request->id,
            'lecture_id'  => Auth::guard('lecture')->user()->id,
            'note'        => $request->presensi_note,
        ]);
    }

    public function history(Request $request) {
        $lecture_id = Auth::guard('lecture')->user()->id;
        return StaseTaskLog::with([
            'stase',
            'staseTask',
            'files',
            'student',
        ])->when($request->name, function ($q) use ($request) {
            $q->whereHas('student', function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->name . '%');
            });
        })
            ->whereLectureId($lecture_id)
            ->orderByDesc('created_at')
            ->paginate(25);
    }
}
