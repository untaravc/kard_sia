<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\ActivityLecture;
use App\Models\Lecture;
use App\Models\LectureProfile;
use App\Models\StaseTaskLog;
use App\Models\StudentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller {
    use ImageThumbnailTrait;

    public function user() {
        $lecture_id = Auth::guard('lecture')->user()->id;

        $lecture = Lecture::leftJoin('lecture_profiles', 'lecture_profiles.lecture_id', '=', 'lectures.id')
            ->select('lectures.*', 'lecture_profiles.image', 'lecture_profiles.address', 'lecture_profiles.phone')
            ->find($lecture_id);

        // total nilai
        $scoring = StaseTaskLog::whereLectureId($lecture_id)
            ->where('point_average', '>', 10)
            ->sum('point_average');

        // total menilai
        $avg_scoring = StaseTaskLog::whereLectureId($lecture_id)
            ->where('point_average', '>', 10)
            ->count();

        $act_lect = ActivityLecture::whereLectureId($lecture_id)
            ->count();

        $final_score = 0;
        if ($avg_scoring > 0) {
            $final_score = floor(($scoring / $avg_scoring) * 10) / 10;
        }

        // log pending
        $log_pending = StudentLog::whereLectureId($lecture_id)
            ->where('status', 0)
            ->count();

        return [
            'lecture'     => $lecture,
            'scoring'     => $final_score,
            'avg_scoring' => $avg_scoring,
            'act_lect'    => $act_lect,
            'log_pending' => $log_pending,
        ];
    }

    public function update_profile(Request $request) {
        $auth_id = Auth::guard('lecture')->id();
        $this->validate($request, [
            'password' => 'confirmed',
            'email'    => 'email',
        ]);

        if ($request->password != null) {
            Lecture::find($request->id)->update([
                'password' => Hash::make($request->password),
            ]);
        }

        Lecture::find($auth_id)->update([
            'email' => $request->email,
            'name'  => $request->name,
        ]);

        $profile   = LectureProfile::whereLectureId($auth_id)->first();
        $path_name = $profile->image ?? null;

        if ($request->image && strlen($request->image) > 100) {
            $path_name = $this->imageProcessing($request->image, 'lectures', false);
        }

        if ($profile != null) {
            $profile->update([
                'image'   => $path_name,
                'address' => $request->address,
                'phone'   => $request->phone,
            ]);
        } else {
            LectureProfile::create([
                'lecture_id' => $auth_id,
                'image'      => $path_name,
                'address'    => $request->address,
                'phone'      => $request->phone,
            ]);
        }
    }
}
