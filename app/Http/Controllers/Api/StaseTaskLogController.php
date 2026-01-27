<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;

class StaseTaskLogController extends Controller
{
    public function updateScore(Request $request)
    {
        $this->validate($request, [
            'stase_task_log_id' => 'required|integer',
            'lecture_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'point_average' => 'nullable|numeric',
        ]);

        $log = StaseTaskLog::find($request->stase_task_log_id);
        if (!$log) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $log->update([
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'point_average' => $request->point_average,
            'admin' => true,
            'status' => 'publish',
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Score Success',
            'result' => $log->fresh(),
        ]);
    }

    public function createScore(Request $request)
    {
        $this->validate($request, [
            'stase_log_id' => 'required|integer',
            'stase_task_id' => 'required|integer',
            'student_id' => 'required|integer',
            'lecture_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'point_average' => 'nullable|numeric',
        ]);

        $log = StaseTaskLog::create([
            'stase_log_id' => $request->stase_log_id,
            'stase_task_id' => $request->stase_task_id,
            'student_id' => $request->student_id,
            'lecture_id' => $request->lecture_id,
            'date' => $request->date,
            'point_average' => $request->point_average,
            'admin' => true,
            'status' => 'publish',
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Create Score Success',
            'result' => $log,
        ]);
    }

    public function deleteScore(Request $request)
    {
        $this->validate($request, [
            'stase_task_log_id' => 'required|integer',
        ]);

        $log = StaseTaskLog::find($request->stase_task_log_id);
        if (!$log) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task log not found',
                'result' => null,
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Score Success',
            'result' => null,
        ]);
    }
}
