<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\AppLog;
use Illuminate\Http\Request;

class AppLogController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = AppLog::with([
            ''
        ])->orderBy('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function withFilter($dataContent, $request){
        if ($request->status != null || $request->status != ""){
            $dataContent = $dataContent->whereStatus($request->status);
        }

        if ($request->type != null){
            $dataContent = $dataContent->whereType($request->type);
        }

        if ($request->start_date != null){
            $dataContent = $dataContent->withStartDate($request->start_date);
        }

        if ($request->end_date != null){
            $dataContent = $dataContent->withEndDate($request->end_date);
        }
        return $dataContent;
    }
}
