<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use App\Models\Presence;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Jenssegers\Agent\Facades\Agent;

class PresensiController extends Controller {

    public function view_presence($id) {
        $user = null;
        if (Auth::guard('student')->check()) {

            $exist = ActivityStudent::whereActivityId($id)
                ->whereStudentId(Auth::guard('student')->user()->id)
                ->first();
            if (!$exist) {
                $user    = Auth::guard('student')->user();
                $records = ActivityStudent::whereStudentId($user->id)->count();
            } else {
                return redirect('/resident');
            }

        } else if (Auth::guard('lecture')->check()) {

            $exist = ActivityLecture::whereActivityId($id)
                ->whereLectureId(Auth::guard('lecture')->user()->id)
                ->first();
            if (!$exist) {
                $user    = Auth::guard('lecture')->user();
                $records = ActivityLecture::whereLectureId($user->id)->count();
            } else {
                return redirect('/dosen');
            }

        } else if (Auth::guard()->check()) {
            $user    = Auth::guard()->user();
            $records = 0;
        } else {
            return redirect('/');
        }

        $act       = Activity::whereStatus('active')
            ->whereDate('start_date', date('Y-m-d'))
            ->where('start_date', '<', date('Y-m-d H:i:s', strtotime(now() . '+30 minutes')))
            ->where(function ($q){
                $q->where('end_date', '>', date('Y-m-d H:i:s', strtotime(now() . '-2 hours')))
                    ->orWhere('end_date', null);
            })
            ->find($id);

        $available = true;
        if (!$act) {
            $act       = Activity::find($id);
//            $available = false;
        }

        $attend_l = ActivityLecture::whereActivityId($act->id)->count();
        $attend_s = ActivityStudent::whereActivityId($act->id)->count();
        $attends  = $attend_s + $attend_l;

        return view('agenda', compact(
            'user',
            'act',
            'attends',
            'available',
            'records'));
    }

    public function presence(Request $request, $id) {
        $user = null;
        if (Auth::guard('student')->check()) {
            $exist = ActivityStudent::whereActivityId($id)
                ->whereStudentId(Auth::guard('student')->user()->id)
                ->first();

            if (!$exist) {
                ActivityStudent::create([
                    'activity_id' => $id,
                    'student_id'  => Auth::guard('student')->user()->id,
                ]);
            }
            return redirect('/resident');
        } else if (Auth::guard('lecture')->check()) {
            $exist = ActivityLecture::whereActivityId($id)
                ->whereLectureId(Auth::guard('lecture')->user()->id)
                ->first();
            if (!$exist) {
                ActivityLecture::create([
                    'activity_id' => $id,
                    'lecture_id'  => Auth::guard('lecture')->user()->id,
                ]);
            }
            return redirect('/dosen');
        } else if (Auth::guard()->check()) {
            return redirect('/cmss/activity');
        } else {
            return redirect('/');
        }


    }

    public function view_daily() {
//        return 'Presensi dialikan menggunakan presensi RSS.';
        $os = strtolower(Agent::platform());
        if (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
        } else {
            return redirect('/resident');
        }

        $user = Student::with('today_presence')->find($user->id);

        if($user->today_presence && $user->today_presence->checkout != null){
            return redirect('/resident');
        }

        $available = false;
        if (strpos($os, 'andro') > -1) {
//            if (strpos($os, '5') > -1) {
                $available = true;
//            } else if (strpos($os, '5') > -1) {
//                $available = true;
//            } else if (strpos($os, '6') > -1) {
//                $available = true;
//            } else if (strpos($os, '7') > -1) {
//                $available = true;
//            } else if (strpos($os, '8') > -1) {
//                $available = true;
//            } else if (strpos($os, '9') > -1) {
//                $available = true;
//            } else if (strpos($os, '10') > -1) {
//                $available = true;
//            } else if (strpos($os, '11') > -1) {
//                $available = true;
//            } else if (strpos($os, '12') > -1) {
//                $available = true;
//            }
        }

        if (strpos($os, 'ios') > -1) {
//            if (strpos($os, '11') > -1) {
                $available = true;
//            } else if (strpos($os, '12') > -1) {
//                $available = true;
//            } else if (strpos($os, '13') > -1) {
//                $available = true;
//            } else if (strpos($os, '14') > -1) {
//                $available = true;
//            } else if (strpos($os, '15') > -1) {
//                $available = true;
//            }
        }

        if (strpos($os, 'linux') > -1) {
            $available = true;
        }

        $user['bg'] = $this->bg_color();

        if ($available) {
            return view('resident/presensi', compact('user'));
        }
        return view('resident/presensi-exception', compact('user'));
    }

    private function bg_color(){
        $array = [
            "background: linear-gradient(90deg, rgb(217, 160, 0) 0%, rgb(206, 116, 0) 100%);",
            "background: linear-gradient(90deg, #01b9c0 0%, #0044c2 100%);",
            "background: linear-gradient(90deg, #ec7575 0%, #9b0000 100%);",
            "background: linear-gradient(90deg, #54b700 0%, #1a7900 100%);",
        ];

        return $array[rand(0,3)];
    }

    public function view_daily_beta() {
        $os = strtolower(Agent::platform());
        if (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
        } else {
            return redirect('/resident');
        }

        $user = Student::with('today_presence')->find($user->id);

        $available = false;
        if (strpos($os, 'andro') > -1) {
//            if (strpos($os, '5') > -1) {
                $available = true;
//            } else if (strpos($os, '5') > -1) {
//                $available = true;
//            } else if (strpos($os, '6') > -1) {
//                $available = true;
//            } else if (strpos($os, '7') > -1) {
//                $available = true;
//            } else if (strpos($os, '8') > -1) {
//                $available = true;
//            } else if (strpos($os, '9') > -1) {
//                $available = true;
//            } else if (strpos($os, '10') > -1) {
//                $available = true;
//            } else if (strpos($os, '11') > -1) {
//                $available = true;
//            } else if (strpos($os, '12') > -1) {
//                $available = true;
//            }
        }

        if (strpos($os, 'ios') > -1) {
//            if (strpos($os, '11') > -1) {
                $available = true;
//            } else if (strpos($os, '12') > -1) {
//                $available = true;
//            } else if (strpos($os, '13') > -1) {
//                $available = true;
//            } else if (strpos($os, '14') > -1) {
//                $available = true;
//            } else if (strpos($os, '15') > -1) {
//                $available = true;
//            }
        }

        if ($available) {
            return view('resident/presensi_beta', compact('user'));
        }
        return view('resident/presensi-exception', compact('user'));
    }

    public function daily(Request $request) {
        if (Auth::guard('student')->check()) {
            $user_id = Auth::guard('student')->user()->id;
        } else {
            $user_id = 0;
        }

        $fileNameToStore = null;
        if ($request->photo) {
            $this->validate($request, [
                'photo' => 'image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            $file      = $request->file('photo');
            $extension = $file->getClientOriginalExtension();

            $fileNameToStore = '/daily/' . date('ymd') . '/' . $user_id . '_' . time() . '.' . $extension;

            $resize = Image::make($file)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->orientate()
                ->encode('jpg');

            Storage::disk('public')->put($fileNameToStore, $resize->__toString());
        }

        $data = [
            'lat' => $request->lat,
            'lng' => $request->lng,
            'accuracy' => $request->accuracy,
            'distance' => $request->distance,
        ];

        $exist = Presence::orderByDesc('id')
//            ->whereDate('checkin', now())
            ->where('checkin', '>', date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-15 hours')))
            ->whereStudentId($user_id)
            ->first();

        if (!$exist) {
            Presence::create([
                'student_id'     => $user_id,
                'checkin'       => now(),
                'checkin_photo' => $fileNameToStore,
                'checkin_note'  => $request->note,
                'checkin_data'  => $data,
                'status'        => $request->status ?? 'on',
            ]);
        } else {
            if(strtotime(now()) - strtotime($exist->checkin) > 600){
                $exist->update([
                    'checkout'       => now(),
                    'checkout_photo' => $fileNameToStore,
                    'checkout_note'  => $request->note,
                    'checkout_data'  => $data,
                ]);
            }
        }

        Log::channel('presences')->info($user_id . ' | ' . json_encode($data) . ' | ' . $data['agent'] = $_SERVER['HTTP_USER_AGENT']);

        return redirect('/resident');
    }

    public function daily_beta(Request $request) {
        $data = $request->toArray();
        $data['_token'] = '';
        return view('request_handler', compact('data'));
    }

    public function daily_resume(Request $request){
        $date = $request->date ?? date('Y-m-d', strtotime(date('Y-m-d')));

        $preseces = Presence::whereDate('checkin', $date)
            ->get();

        $student = Student::with('last_presence')->get();

        $data['no_presence'] = $student->where('status', 'active')
            ->whereNotIn('id', $preseces->pluck('student_id'));
        $data['no_presence_count'] = count($data['no_presence']);
        $presences = $student
            ->whereIn('id', $preseces->pluck('student_id'));
        $data['presence_count'] = count($presences);

        foreach ($presences as $presence){
            $presence['presence'] = $preseces->where('student_id', $presence['id'])->first();
        }

        $array = $presences->toArray();
        usort($array, function ($item1, $item2) {
            return $item1['presence']['checkin'] <=> $item2['presence']['checkin'];
        });

        $data['presence'] = $array;

        $this->response['data'] = $data;
        $this->response['date'] = $date;
        return $this->response;
    }
}
