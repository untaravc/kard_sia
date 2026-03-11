<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    private const DEFAULT_START_AT = '2022-10-21 09:50:00';
    private const DEFAULT_IN_ROOM = 6;
    private const DEFAULT_TRANSITION = 1;
    private const DEFAULT_REMINDER = 121;
    private const DEFAULT_STATUS = 1;

    public function index()
    {
        return view('home.timer');
    }

    private function roomName(string $room): string
    {
        return 'room_' . $room;
    }

    private function roomSettings(string $room): array
    {
        $settings = Setting::query()
            ->whereName($this->roomName($room))
            ->whereIn('label', [
                'transition_minute',
                'in_room_minute',
                'start_time',
                'status',
            ])
            ->pluck('value', 'label');

        return [
            'start_time' => $settings->get('start_time', self::DEFAULT_START_AT),
            'in_room_minute' => (int) $settings->get('in_room_minute', self::DEFAULT_IN_ROOM),
            'transition_minute' => (int) $settings->get('transition_minute', self::DEFAULT_TRANSITION),
            'status' => (int) $settings->get('status', self::DEFAULT_STATUS),
        ];
    }

    private function now(): array
    {
        $time = now();

        return [
            'formatted' => $time->format('Y-m-d H:i:s'),
            'timestamp' => $time->timestamp,
        ];
    }

    public function timer(Request $request)
    {
        $settings = $this->roomSettings((string) $request->r);
        $now = $this->now();

        $startAt = $settings['start_time'];
        $inRoomSeconds = $settings['in_room_minute'] * 60;
        $transitionSeconds = $settings['transition_minute'] * 60;
        $totalDuration = $inRoomSeconds + $transitionSeconds;
        $diffSeconds = $now['timestamp'] - strtotime($startAt);
        $cycleOffset = $totalDuration > 0 ? $diffSeconds % $totalDuration : 0;
        $isTransition = $cycleOffset >= $inRoomSeconds;

        $data = [
            'start_at' => $startAt,
            'this_time' => $now['formatted'],
            'order' => max((int) ceil($diffSeconds / max($totalDuration, 1)), 0),
            'reminder' => self::DEFAULT_REMINDER,
            'text' => $isTransition ? 'PERPINDAHAN ruang peserta ujian' : 'Ujian sedang BERLANGSUNG',
            'countdown' => $isTransition
                ? $totalDuration - $cycleOffset
                : $inRoomSeconds - $cycleOffset,
            'transition_time' => $isTransition,
            'diff_min' => (int) floor($diffSeconds / 60),
            'in_room_sec' => $inRoomSeconds,
        ];

        if ($diffSeconds < 0) {
            $data['countdown'] = abs($diffSeconds);
            $data['transition_time'] = true;
        }

        if ($settings['status'] === 0) {
            $data['countdown'] = 0;
            $data['transition_time'] = true;
        }

        $this->response['result'] = $data;
        return $this->response;
    }

    public function timer_setting(Request $request)
    {
        $settings = $this->roomSettings((string) $request->r);

        return view('home.timer_setting', [
            'setting' => [
                'transition_minute' => $settings['transition_minute'],
                'in_room_minute' => $settings['in_room_minute'],
                'start_time' => $settings['start_time'],
                'status' => $settings['status'],
            ]
        ]);
    }

    public function timer_setting_update(Request $request)
    {
        $data = $request->except('_token', 'name');

        foreach ($data as $key => $datum) {
            $has_setting = Setting::whereLabel($key)
                ->whereName($this->roomName($request->name))
                ->first();

            if ($has_setting) {
                $has_setting->update([
                    'value' => $datum
                ]);
            } else {
                Setting::create([
                    'name' => $this->roomName($request->name),
                    'label' => $key,
                    'value' => $datum,
                ]);
            }
        }

        return redirect('/timer_setting?r=' . $request->name);
    }

    public function timer_start(Request $request)
    {
        $start_time = Setting::whereLabel('start_time')
            ->whereName($this->roomName($request->name))
            ->first();

        if ($start_time) {
            $start_time->update([
                'value' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $status = Setting::whereLabel('status')
            ->whereName($this->roomName($request->name))
            ->first();

        if ($status) {
            $status->update([
                'value' => 1,
            ]);
        }

        return redirect('/timer_setting?r=' . $request->name);
    }
}
