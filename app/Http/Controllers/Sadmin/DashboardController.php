<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\AppLog;
use App\Models\Lecture;
use App\Models\OpenStaseTask;
use App\Models\Presence;
use App\Models\Stase;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function home()
    {
        return view('welcome');
    }
    public function index(){
        return view('admin.layout');
    }

    public function index2(){
        return view('admin.layout2');
    }

    public function stase(){
        return Stase::where('desc', '!=', null)->with([
            'staseLogOngoing'=>function($q){
                $q->with(['student'=> function($q){
                    $q->whereStatus('active');
                }]);
            },
        ])->withCount('staseLogOngoing')
            ->orderBy('desc')->get();
    }

    public function card(){
        $data['student'] = Student::whereStatus('active')->count();
        $data['student_active'] = Student::whereStatus('active')->whereHas('staseLogsActive')->count();
        $data['lecture'] = Lecture::count();
        $data['lecture_active'] = Lecture::whereHas('staseTaskLogs')->count();
        $data['open_exam'] = OpenStaseTask::count();
        $data['activities'] = Activity::whereStatus('active')->count();

        return $data;
    }

    public function activities(){
        return AppLog::with([
            'admin',
            'student',
            'lecture',
        ])->orderByDesc('created_at')->paginate(10);
    }

    public function chart(Request $request)
    {
        $query['start_date'] = $request->start_date ?? date('Y-m') . '-01';
        $query['end_date'] = $request->end_date ??  date('Y-m') .  '-' .cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));

        if($request->month){
            $expl = explode('-', $request->month);
            $query['start_date'] = $request->month . '-01';
            $query['end_date'] = $request->month .  '-' . cal_days_in_month(CAL_GREGORIAN, $expl[1] ,$expl[0]);
        }

        $period = new \DatePeriod(
            new \DateTime( $query['start_date'] ),
            new \DateInterval('P1D'),
            new \DateTime( $query['end_date'])
        );

        $array = []; $i = 1;
        foreach ($period as $key => $value) {
            $array[$i]['date'] = $value->format('Y-m-d'); $i++;
        }

        $absensi_day = Presence::where('checkin', '>=', Carbon::now()->sub('30 days'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get(array(
                DB::raw("Date(checkin) as date"),
                DB::raw("COUNT(*) as 'total'"),
                DB::raw("SUM(CASE WHEN status = 'on' THEN 1 ELSE 0 END) AS 'on'"),
                DB::raw("SUM(CASE WHEN status = 'off' THEN 1 ELSE 0 END) AS 'off'"),
            ));

        for($i=1; $i <= count($array); $i++){
            foreach ($absensi_day as $abs){
                if(date('Y-m-d', strtotime($abs['date'])) === $array[$i]['date']){
                    $array[$i]['presences'] = $abs;
                    $array[$i]['residents'] = Student::whereStatus('active')
                        ->count();
                }
            }
        }
        return $array;
    }

    public function form(){
        return view('admin.form_layout');
    }
}
