<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\Presence;
use App\Models\Student;
use Illuminate\Http\Request;

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
            ->whereNotIn('id', $presences->pluck('student_id'));
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
}
