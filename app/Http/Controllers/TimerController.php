<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public $start_at = '2022-10-21 09:50:00';
    public $in_room = 6;
    public $transition = 1;
    public $reminder = 121;

    public function index(){
        return view('home.timer');
    }

    public function cal_setting($request){
        $settings = Setting::whereIn('label',[
            'transition_minute',
            'in_room_minute',
            'start_time',
        ])->whereName('room_'.$request->r)
            ->get();

        $this->start_at = $settings->where('label', 'start_time')->first() ?
            $settings->where('label', 'start_time')->first()['value'] : $this->start_at;

        $this->in_room = $settings->where('label', 'in_room_minute')->first() ?
            $settings->where('label', 'in_room_minute')->first()['value'] : $this->in_room;

        $this->transition = $settings->where('label', 'transition_minute')->first() ?
            $settings->where('label', 'transition_minute')->first()['value'] : $this->transition;
    }

    private function this_time($int = true){
//        $time = '2022-08-03 17:15:20';
        $time = date('Y-m-d H:i:s');
        if($int){
            return strtotime($time);
        }
        return $time;
    }

    public function timer(Request $request){
        $this->cal_setting($request);
        $data['start_at'] = $this->start_at;
        $data['this_time'] = $this->this_time(false);
        $in_room = $this->in_room * 60;
        $transition = $this->transition * 60;
        $total_duration = ($this->in_room + $this->transition) * 60;
        $diff_sec = $this->this_time() - strtotime($data['start_at']);

        $sisa_bagi = $diff_sec % $total_duration;
        $data['order'] = max(ceil($diff_sec / $total_duration), 0);
        $data['reminder'] = $this->reminder;

        if($sisa_bagi < $in_room){
            $data['text'] = 'Ujian sedang BERLANGSUNG';
            $data['countdown'] = $in_room - $sisa_bagi;
            $data['transition_time'] = false;
        }else{
            $data['text'] = 'PERPINDAHAN ruang peserta ujian';
            $data['transition_time'] = true;
            $data['countdown'] = $in_room + $transition - $sisa_bagi;
        }

        if($diff_sec < 0){
            $data['countdown'] = ($diff_sec * -1);
        }

        $data['diff_min'] = floor($diff_sec / 60);

        $this->response['result'] = $data;
        return $this->response;
    }

    public function timer_setting(Request $request){
        $settings = Setting::whereIn('label',[
            'transition_minute',
            'in_room_minute',
            'start_time',
        ])->whereName('room_' . $request->r)->get();

        $setting = [];
        $setting['transition_minute'] = $settings->where('label', 'transition_minute')->first()
            ? $settings->where('label', 'transition_minute')->first()['value'] : 1;

        $setting['in_room_minute'] = $settings->where('label', 'in_room_minute')->first()
            ? $settings->where('label', 'in_room_minute')->first()['value'] : 6;

        $setting['start_time'] = $settings->where('label', 'start_time')->first()
            ? $settings->where('label', 'start_time')->first()['value'] : now();
        return view('home.timer_setting', [
            'setting' => $setting
        ]);
    }

    public function timer_setting_update(Request $request){
        $data = $request->except('_token', 'name');

        foreach ($data as $key => $datum){
            $has_setting = Setting::whereLabel($key)
                ->whereName('room_' . $request->name)
                ->first();

            if($has_setting){
                $has_setting->update([
                   'value' => $datum
                ]);
            }else{
                Setting::create([
                    'name' => 'room_'.$request->name,
                    'label' => $key,
                    'value' => $datum,
                ]);
            }
        }

        return redirect('/timer_setting?r=' . $request->name);
    }
}
