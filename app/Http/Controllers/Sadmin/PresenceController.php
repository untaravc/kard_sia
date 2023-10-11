<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\Presence;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller {
    public function index(Request $request) {
        $dataContent = Presence::with('student')
//            ->whereHas('student', function ($q){
//            $q->whereStatus('active');
//        })
            ->orderByDesc('checkin');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->paginate(25);
        return $dataContent;
    }

    public function withFilter($dataContent, $request) {
        if ($request->name != null) {
            $dataContent = $dataContent->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            });
        }

        if ($request->status != null) {
            $dataContent = $dataContent->whereStatus($request->status);
        }

        if ($request->checkin != null) {
            $dataContent = $dataContent->withStartDate($request->start_date);
        }
        return $dataContent;
    }

    public function presence_today() {
        $presences = Presence::whereDate('checkin', now())->get();
        $data      = [
            'all'      => $presences->count(),
            'on'       => $presences->where('status', 'on')->count(),
            'off'      => $presences->where('status', 'off')->count(),
            'checkout' => $presences->where('checkout', '!=', null)->count(),
        ];

        return $data;
    }

    public function show(Request $request)
    {
        $request['student_id'] = $request->student_id ?? Auth::guard('student')->id();
        $request['week'] = $request->period ? date('W', strtotime($request->period)) : date('W', strtotime(date('Y-m')));
        if($request['week'] == 52) $request['week'] = 1;
        $request['month'] = $request->period ? substr($request->period, 5, 2) : date('m');
        $request['year'] = $request->period ? substr($request->period, 0, 4) : date('Y');

        $presences = Presence::whereStudentId($request['student_id'])
            ->whereYear('checkin', $request['year'])
            ->whereMonth('checkin', $request['month'])
            ->get();

        $activities = ActivityStudent::with('activity')
            ->whereStudentId($request['student_id'])
            ->whereYear('created_at', $request['year'])
            ->whereMonth('created_at', $request['month'])
            ->get();

        $weeks = [];
        for ($i = 0; $i < 5; $i++){
            $week = $this->getStartToEndDate($request['week'] + $i, $request['year']);
            for($w=0; $w < 7; $w++){
                foreach ($presences as $presence){
                    if(substr($presence->checkin, 0, 10) === $week[$w]['date']){
                        $week[$w]['presence'] = $presence;
                    }
                }

                foreach ($activities as $activity){
                    if(substr($activity->created_at, 0, 10) === $week[$w]['date']){
                        $week[$w]['activities'][] = $activity;
                    }
                }
            }
            $weeks[] = $week;
        }

        return [
            'data' => $weeks,
            'resident' => Student::find($request['student_id'])
        ];
    }

    function getStartToEndDate($week, $year) {
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $ret = [];

        for($i=0; $i < 7; $i++){
            $ret[] = [
                'date' => $dto->format('Y-m-d')
            ];

            $dto->modify('+1 days');
        }



//        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }

    function dates_month($month, $year) {
        return $this->build_calendar(4, 2021);
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dates_month = array();

        for ($i = 1; $i <= $num; $i++) {
            $mktime = mktime(0, 0, 0, $month, $i, $year);
            $date = date("Y-m-d", $mktime);

            $day = '';
            switch (date('N', $mktime)){
                case 1:
                    $day = 'Sen';
                    break;
                case 2:
                    $day = 'Sel';
                    break;
                case 3:
                    $day = 'Rab';
                    break;
                case 4:
                    $day = 'Kam';
                    break;
                case 5:
                    $day = 'Jum';
                    break;
                case 6:
                    $day = 'Sab';
                    break;
                case 7:
                    $day = 'Ming';
                    break;

            }
            if(date('N', $mktime) < 7){
                $dates_month[$i]['date'] = $date;
                $dates_month[$i]['date_str'] = $day . date(" d M", $mktime);
            }
        }

        return collect($dates_month);
    }

    public function ilmiah(Request $request){
        $query['month'] = $request->date ? substr($request->date, 5, 2) : date('m');
        $query['year'] = $request->date ? substr($request->date, 0, 4) : date('Y');
        $query['key'] = $request->key ?: "laporan jaga";
        $query['date'] = $query['year'].'-'.$query['month'];
        $query['today'] = date('d');
        $query['date_sum'] = cal_days_in_month(CAL_GREGORIAN,$query['month'],$query['year']);;

        $students = Student::whereStatus(1)
            ->orderBy('year')
            ->orderBy('name')
            ->get();

        $key = "laporan jaga";

        $laporan_jaga = Activity::where('name', 'LIKE', "%".$key."%")
            ->whereYear('start_date', $query['year'])
            ->whereMonth('start_date', $query['month'])
            ->get();

        if($query['key'] == 'laporan jaga'){
            $query['activity_count'] = count($laporan_jaga);
            $lapjag_ids = $laporan_jaga->pluck('id');
            $activity_student = ActivityStudent::whereIn('activity_id', $lapjag_ids)
                ->get();

            foreach ($students as $student){
                $student->setAttribute('lapjag', $activity_student->where('student_id', $student->id)->count());
            }
        }else if($query['key'] == 'presensi'){
            $weekday = 0;
//            $month_days = $query['month'] == date('m') ? $query['today'] : $query['date_sum'];
//
//            for($i = 1; $i <= $month_days ; $i++){
//                if(date('N', strtotime($query['year'] . '-' . $query['month'] . '-' . $i)) < 6){
//                    $weekday++;
//                }
//            }
            $query['activity_count'] = count($laporan_jaga);

            $presences = Presence::whereYear('checkin', $query['year'])
                ->whereMonth('checkin', $query['month'])
                ->get();

            foreach ($students as $student){
                $student->setAttribute('lapjag', $presences->where('student_id', $student->id)->count());
            }
        }

        $this->response['query'] = $query;
        $this->response['result'] = $students;
        return $this->response;
    }
}
