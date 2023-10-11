<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\Student;
use Illuminate\Http\Request;

class StasePlotController extends Controller {
    public function index(Request $request) {
        $request['year'] = $request->year ?? date('Y');
        $request['week_start'] = 0;
        if($request->month){
            $date = new \DateTime($request->month);
            $request['week_start'] = $date->format("W") < 52 ? $date->format("W") : 0 ;
        }

        $weeks           = [];
        for ($i = $request['week_start']; $i < 53; $i++) {
            $week_data         = $this->getStartAndEndDate($i, $request['year']);
            $week_data['week'] = $i;

            $weeks[$i] = $week_data;
        }

        $stase_logs = StaseLog::with('stase')
            ->when($request->stase_id, function ($q)use ($request){
                $q->whereStaseId($request->stase_id);
            })
//            ->where('start_date', '>', $weeks[$request['week_start']]['start'])
            ->where('end_date', '>', $weeks[$request['week_start']]['start'])
            ->where('start_date', '<', $weeks[52]['end'])
            ->get();

        $residents = Student::whereStatus('active')
            ->with(['staseLogsActive'=>function($q){
                $q->with('stase');
            }])->when($request->stase_id, function ($q)use($request){
                $q->whereHas('staseLogsActive', function ($q2)use($request){
                   $q2->whereStaseId($request->stase_id);
                });
            })
            ->when($request->section, function ($q) use($request){
                $q->whereHas('staseLogsActive', function ($q2) use($request) {
                    $q2->whereHas('stase', function ($q3) use($request){
                        $q3->where('desc', $request->section);
                    });
                });
            })
            ->skip(0)
            ->take($request->per_page ?? 10)
            ->get();

        foreach ($residents as $resident) {
            for ($i = $request['week_start']; $i < 53; $i++) {
                $weeks[$i]['stase'] = $stase_logs
                    ->where('student_id', $resident->id)
                    ->where('start_date', '<=', $weeks[$i]['start'])
                    ->where('end_date', '>=', $weeks[$i]['start'])
                    ->first();
            }

            $resident->setAttribute('weeks', $weeks);
        }

        return [
            'data' => $residents
        ];
    }

    public function getStartAndEndDate($week, $year) {
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $ret['start']     = $dto->format('Y-m-d');
        $ret['start_str'] = $dto->format('d M');
        $dto->modify('+6 days');
        $ret['end'] = $dto->format('Y-m-d');
        return $ret;
    }
}
