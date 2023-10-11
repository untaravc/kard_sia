<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\FormOption;
use App\Models\StudentLog;
use Illuminate\Http\Request;

class StudentLogController extends Controller
{
    public function index(Request $request) {
        $dataContent = StudentLog::with(['student', 'lecture', 'stase'])
            ->orderByDesc('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->paginate(25);

        $stase_id = $dataContent->unique('stase_id')->pluck('stase_id');
        $options  = FormOption::whereIn('relation_id', $stase_id)
            ->whereType('stase-logbook')
            ->get();
        foreach ($dataContent as $log) {
            $log->setAttribute('options', $options->where('relation_id', $log->stase_id)->first());
        }

        return $dataContent;
    }

    public function withFilter($dataContent, $request) {
        if ($request->resident != null) {
            $dataContent = $dataContent->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->resident . '%');
            });
        }

        if ($request->status != null) {
            $dataContent = $dataContent->whereStatus($request->status);
        }

        if ($request->lecture != null) {
            $dataContent = $dataContent->whereHas('lecture', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->lecture . '%');
            });
        }

        if ($request->stase != null) {
            $dataContent = $dataContent->whereHas('stase', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->stase . '%');
            });
        }

        if ($request->q != null) {
            $dataContent = $dataContent->where(function ($q) use ($request){
                $q->where('field_1', 'LIKE', '%' . $request->q . '%')
                    ->orWhere('field_2', 'LIKE', '%' . $request->q . '%')
                    ->orWhere('field_3', 'LIKE', '%' . $request->q . '%')
                    ->orWhere('field_4', 'LIKE', '%' . $request->q . '%');
            });
        }

        return $dataContent;
    }

    public function properties(){
        $data['log_pending'] = StudentLog::whereStatus(0)->count();
        $data['log_verified'] = StudentLog::whereStatus(1)->count();
        $data['resident_pending'] = StudentLog::whereStatus(0)->groupBy('student_id')->get()->count();
        $data['lecture_pending'] = StudentLog::whereStatus(0)->groupBy('lecture_id')->get()->count();

        return $data;
    }
}
