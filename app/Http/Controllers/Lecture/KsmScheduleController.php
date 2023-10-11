<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\KsmSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KsmScheduleController extends Controller
{
    public function index(Request $request){
        $request['week'] = $request->period ? date('W', strtotime($request->period)) : date('W', strtotime(date('Y-m')));
        $request['month'] = $request->period ? substr($request->period, 5, 2) : date('m');
        $request['year'] = $request->period ? substr($request->period, 0, 4) : date('Y');

        $schedules = KsmSchedule::whereYear('schedule', $request['year'])
            ->with('lecture')
            ->whereMonth('schedule', $request['month'])
            ->get();

        $weeks = [];
        for ($i = 0; $i < 6; $i++){
            $week = $this->getStartToEndDate($request['week'] + $i, $request['year']);
            for($w=0; $w < 7; $w++){
                foreach ($schedules as $schedule){
                    if(substr($schedule->schedule, 0, 10) === $week[$w]['date']){
                        $week[$w]['schedules'][] = $schedule;
                    }
                }

            }
            $weeks[] = $week;
        }

        $this->response['data'] = $weeks;
        $this->response['request'] = $request->all();

        return $this->response;
    }

    public function store(Request $request){
        $this->validate($request, [
           'name'   => 'required',
           'action'   => 'required',
           'schedule'   => 'required',
        ]);

        $lecture_id = Auth::guard('lecture')->id() ?? 0;

        $request->merge([
           'lecture_id' => $lecture_id
        ]);

        KsmSchedule::create($request->all());

        return $this->response;
    }

    public function update(Request $request){
        $this->validate($request, [
            'name'   => 'required',
            'action'   => 'required',
            'schedule'   => 'required',
        ]);

        $lecture_id = Auth::guard('lecture')->id() ?? 0;

        $data = KsmSchedule::find($request->id);

        if($data && $data->lecture_id == $lecture_id){
            $data->update($request);
        }

        return $this->response;
    }

    public function destroy($id){
        $data = KsmSchedule::find($id);
        $lecture_id = Auth::guard('lecture')->id() ?? 0;

        if($data && $data->lecture_id == $lecture_id){
            $data->delete();
        }

        return $this->response;
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
}
