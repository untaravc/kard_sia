<?php

namespace App\Traits;

use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Facades\Agent;

trait AppLogTrait {

    public function createAppLog($name, $note = null) {
        $data = [];
        if (auth()->guard('web')->check()) {
            $data['type'] = 'admin';
            $data['name'] = Auth::guard()->user()->name;
        } else if (auth()->guard('lecture')->check()) {
            $data['type'] = 'dosen';
            $data['name'] = Auth::guard('lecture')->user()->name;
            Lecture::find(Auth::guard('lecture')->id())->update([
               'last_act' => now()
            ]);
        } else if (auth()->guard('student')->check()) {
            $data['type'] = 'residen';
            $data['name'] = Auth::guard('student')->user()->name;
            Student::find(Auth::guard('student')->id())->update([
                'last_act' => now()
            ]);
        } else {
            $data['type'] = 'guest';
            $data['name'] = 'anonymous';
        }

        $data['url'] = $note;
        $browser     = Agent::browser();
        $platform    = Agent::platform();
        $device      = Agent::device();
        if ($data['name'] != 'Untara' && !strpos($note, 'get-') && !strpos($note, 'cmsd') && !strpos($note, 'cmsr') ) {
            $data['desc']  = $device . ' | ' . $platform . Agent::version($platform) . ' | ' . $browser . Agent::version($browser);
            $data['agent'] = $_SERVER['HTTP_USER_AGENT'];
            config(['logging.channels.access_point.path' => storage_path('logs/access_point/'. date('ymd') .'.log')]);
            Log::channel('access_point')->info(json_encode($data));
        }
    }
}
