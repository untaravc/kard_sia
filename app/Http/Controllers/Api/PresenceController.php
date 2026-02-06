<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\Presence;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Facades\Agent;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Presence::with('student')->orderByDesc('checkin');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Presences Success',
            'result' => $dataContent,
        ]);
    }

    public function daily(Request $request)
    {
        $date = $request->date ?? date('Y-m-d', strtotime(date('Y-m-d')));

        $presences = Presence::whereDate('checkin', $date)->get();
        $students = Student::with('last_presence')->whereStatus('active')->get();

        $data['no_presence'] = $students->where('status', 'active')
            ->whereNotIn('id', $presences->pluck('student_id'))->flatten();
        $data['no_presence_count'] = count($data['no_presence']);

        $presentStudents = $students->whereIn('id', $presences->pluck('student_id'));
        $data['presence_count'] = count($presentStudents);

        foreach ($presentStudents as $student) {
            $student['presence'] = $presences->where('student_id', $student['id'])->first();
        }

        $array = $presentStudents->toArray();
        usort($array, function ($item1, $item2) {
            return $item1['presence']['checkin'] <=> $item2['presence']['checkin'];
        });

        $data['presence'] = $array;

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Daily Presences Success',
            'result' => $data,
            'date' => $date,
        ]);
    }

    public function studentDailyCheck(Request $request)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ($authType !== 'student' || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $student = Student::with('today_presence')->find($authId);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $platform = strtolower(Agent::platform() ?? '');
        $available = false;

        if (strpos($platform, 'andro') > -1) {
            $available = true;
        }

        if (strpos($platform, 'ios') > -1) {
            $available = true;
        }

        if (strpos($platform, 'linux') > -1) {
            $available = true;
        }

        $completed = false;
        if ($student->today_presence && $student->today_presence->checkout !== null) {
            $completed = true;
            $available = false;
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Daily Check Success',
            'result' => [
                'available' => $available,
                'completed' => $completed,
                'platform' => $platform,
                'bg' => $this->bgColor(),
                'student' => $student,
            ],
        ]);
    }

    public function studentPresenceCheck(Request $request)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ($authType !== 'student' || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $presence = Presence::whereStudentId($authId)
            ->where('checkin', '>', date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-15 hours')))
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Presence Check Success',
            'result' => $presence,
        ]);
    }

    public function studentDaily(Request $request)
    {
        [$authType, $authId] = $this->resolveAuthIdentity($request);
        if ($authType !== 'student' || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $photoUrl = $request->photo_url ?: null;

        $data = [
            'lat' => $request->lat,
            'lng' => $request->lng,
            'accuracy' => $request->accuracy,
            'distance' => $request->distance,
        ];

        $exist = Presence::orderByDesc('id')
            ->where('checkin', '>', date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-15 hours')))
            ->whereStudentId($authId)
            ->first();

        $mode = 'checkin';
        if (!$exist) {
            $presence = Presence::create([
                'student_id' => $authId,
                'checkin' => now(),
                'checkin_photo' => $photoUrl,
                'checkin_note' => $request->note,
                'checkin_data' => $data,
                'status' => $request->status ?? 'on',
            ]);
        } else {
            $presence = $exist;
            if (strtotime(now()) - strtotime($exist->checkin) > 600) {
                $presence->update([
                    'checkout' => now(),
                    'checkout_photo' => $photoUrl,
                    'checkout_note' => $request->note,
                    'checkout_data' => $data,
                ]);
                $mode = 'checkout';
            }
        }

        Log::channel('presences')->info($authId . ' | ' . json_encode($data) . ' | ' . $data['agent'] = $_SERVER['HTTP_USER_AGENT']);

        return response()->json([
            'success' => true,
            'text' => 'Student daily presence saved',
            'result' => [
                'mode' => $mode,
                'presence' => $presence,
            ],
        ]);
    }

    public function monthly(Request $request)
    {
        $query['month'] = $request->date ? substr($request->date, 5, 2) : date('m');
        $query['year'] = $request->date ? substr($request->date, 0, 4) : date('Y');
        $query['key'] = $request->key ?: 'laporan jaga';
        $query['date'] = $query['year'] . '-' . $query['month'];
        $query['today'] = date('d');
        $query['date_sum'] = cal_days_in_month(CAL_GREGORIAN, $query['month'], $query['year']);

        $students = Student::whereStatus(1)
            ->orderBy('year')
            ->orderBy('name')
            ->get();

        $laporanJaga = Activity::where('name', 'LIKE', '%laporan jaga%')
            ->whereYear('start_date', $query['year'])
            ->whereMonth('start_date', $query['month'])
            ->get();

        if ($query['key'] === 'laporan jaga') {
            $query['activity_count'] = count($laporanJaga);
            $lapjagIds = $laporanJaga->pluck('id');
            $activityStudent = ActivityStudent::whereIn('activity_id', $lapjagIds)->get();

            foreach ($students as $student) {
                $student->setAttribute('lapjag', $activityStudent->where('student_id', $student->id)->count());
            }
        } else if ($query['key'] === 'presensi') {
            $query['activity_count'] = count($laporanJaga);
            $presences = Presence::whereYear('checkin', $query['year'])
                ->whereMonth('checkin', $query['month'])
                ->get();

            foreach ($students as $student) {
                $student->setAttribute('lapjag', $presences->where('student_id', $student->id)->count());
            }
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Monthly Presences Success',
            'result' => $students,
            'query' => $query,
        ]);
    }

    public function student(Request $request, $student_id)
    {
        $period = $request->period;
        $week = $period ? date('W', strtotime($period)) : date('W', strtotime(date('Y-m')));
        if ((int) $week === 52) {
            $week = 1;
        }

        $month = $period ? substr($period, 5, 2) : date('m');
        $year = $period ? substr($period, 0, 4) : date('Y');

        $presences = Presence::whereStudentId($student_id)
            ->whereYear('checkin', $year)
            ->whereMonth('checkin', $month)
            ->get();

        $activities = ActivityStudent::with('activity')
            ->whereStudentId($student_id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        $weeks = [];
        for ($i = 0; $i < 5; $i += 1) {
            $currentWeek = $this->getStartToEndDate((int) $week + $i, (int) $year);
            for ($d = 0; $d < 7; $d += 1) {
                foreach ($presences as $presence) {
                    if (substr($presence->checkin, 0, 10) === $currentWeek[$d]['date']) {
                        $currentWeek[$d]['presence'] = $presence;
                    }
                }

                foreach ($activities as $activity) {
                    if (substr($activity->created_at, 0, 10) === $currentWeek[$d]['date']) {
                        $currentWeek[$d]['activities'][] = $activity;
                    }
                }
            }
            $weeks[] = $currentWeek;
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Presence Summary Success',
            'result' => [
                'data' => $weeks,
                'resident' => Student::find($student_id),
                'period' => sprintf('%04d-%02d', (int) $year, (int) $month),
            ],
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->date != null && $request->date !== '') {
            $dataContent = $dataContent->whereDate('checkin', $request->date);
        }

        if ($request->name != null && $request->name !== '') {
            $dataContent = $dataContent->whereHas('student', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            });
        }

        return $dataContent;
    }

    private function resolveAuthIdentity(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        return [$authType, $authId];
    }

    private function bgColor()
    {
        $array = [
            'background: linear-gradient(90deg, rgb(217, 160, 0) 0%, rgb(206, 116, 0) 100%);',
            'background: linear-gradient(90deg, #01b9c0 0%, #0044c2 100%);',
            'background: linear-gradient(90deg, #ec7575 0%, #9b0000 100%);',
            'background: linear-gradient(90deg, #54b700 0%, #1a7900 100%);',
        ];

        return $array[rand(0, 3)];
    }

    private function getStartToEndDate($week, $year)
    {
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $ret = [];

        for ($i = 0; $i < 7; $i += 1) {
            $ret[] = [
                'date' => $dto->format('Y-m-d'),
            ];

            $dto->modify('+1 days');
        }

        return $ret;
    }
}
