<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CountDownController extends Controller
{
    public $start_at = '2025-11-16 21:28:00';
    public $in_room_second = 60;

    public function index()
    {
        return view('home.countdown');
    }

    public function cal_setting($request)
    {
        $settings = Setting::whereIn('label', [
            'in_room_second',
            'start_time',
        ])->whereName('countdown_' . $request->c)
            ->get();

        $this->start_at = $settings->where('label', 'start_time')->first() ?
            $settings->where('label', 'start_time')->first()['value'] : $this->start_at;

        $this->in_room_second = $settings->where('label', 'in_room_second')->first() ?
            $settings->where('label', 'in_room_second')->first()['value'] : $this->in_room_second;
    }

    public function countdown(Request $request)
    {
        $this->cal_setting($request);
        $data['start_at'] = $this->start_at;
        $data['in_room_second'] = $this->in_room_second;
        $finish_at = strtotime($this->start_at) + $this->in_room_second;

        $data['countdown'] =  $finish_at - time();

        $this->response['result'] = $data;
        return $this->response;
    }

    public function timer_setting(Request $request)
    {
        $settings = Setting::whereIn('label', [
            'in_room_second',
            'start_time',
        ])->whereName('countdown_' . $request->c)->get();

        $setting = [];

        $setting['in_room_second'] = $settings->where('label', 'in_room_second')->first()
            ? $settings->where('label', 'in_room_second')->first()['value'] : 6;

        $setting['start_time'] = $settings->where('label', 'start_time')->first()
            ? $settings->where('label', 'start_time')->first()['value'] : now();

        return view('home.countdown_setting', [
            'setting' => $setting
        ]);
    }

    public function timer_setting_update(Request $request)
    {
        $data = $request->except('_token', 'name');
//        return $data;
        foreach ($data as $key => $datum) {
            $has_setting = Setting::whereLabel($key)
                ->whereName('countdown_' . $request->name)
                ->first();

            if ($has_setting) {
                $has_setting->update([
                    'value' => $datum
                ]);
            } else {
                Setting::create([
                    'name' => 'countdown_' . $request->name,
                    'label' => $key,
                    'value' => $datum,
                ]);
            }
        }

        return redirect('/cd-s?c=' . $request->name);
    }
}

