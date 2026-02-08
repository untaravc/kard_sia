<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stase;
use App\Models\StaseLog;
use Illuminate\Http\Request;

class StaseController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Stase::withCount('staseTasks')->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stases Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $stase = Stase::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Stase Success',
            'result' => $stase,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $stase = Stase::find($id);
        if (!$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Stase not found',
                'result' => null,
            ], 404);
        }

        $stase->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Stase Success',
            'result' => $stase,
        ]);
    }

    public function show($id)
    {
        $stase = Stase::with(['staseTasks' => function ($q) {
            $q->with(['lecture', 'task']);
        }])->find($id);

        if (!$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Stase not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Success',
            'result' => $stase,
        ]);
    }

    public function destroy($id)
    {
        $stase = Stase::find($id);
        if (!$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Stase not found',
                'result' => null,
            ], 404);
        }

        $stase->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Stase Success',
            'result' => null,
        ]);
    }

    public function list(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $studentId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;

        if (!$studentId) {
            $studentId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType === 'user' && $request->student_id) {
            $studentId = (int) $request->student_id;
        }

        $staseLogs = StaseLog::with('stase')
            ->whereStudentId($studentId)
            ->leftJoin('stases', 'stases.id', '=', 'stase_logs.stase_id')
            ->orderBy('stases.name')
            ->select('stase_logs.*')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase List Success',
            'result' => $staseLogs,
        ]);
    }

    public function studentStase(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $studentId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;

        if (!$studentId) {
            $studentId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType === 'user' && $request->student_id) {
            $studentId = (int) $request->student_id;
        }

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $takenLogs = StaseLog::with('stase')
            ->whereStudentId($studentId)
            ->leftJoin('stases', 'stases.id', '=', 'stase_logs.stase_id')
            ->orderBy('stase_logs.start_date', 'desc')
            ->select('stase_logs.*')
            ->get();

        $takenIds = $takenLogs->pluck('stase_id')->filter()->unique()->values();

        $availableStase = $takenIds->isEmpty()
            ? Stase::orderBy('name')->get()
            : Stase::whereNotIn('id', $takenIds)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Stase Success',
            'result' => [
                'taken_stase' => $takenLogs,
                'available_stase' => $availableStase,
            ],
        ]);
    }

    public function storeStudentStase(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $studentId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;

        if (!$studentId) {
            $studentId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType === 'user' && $request->student_id) {
            $studentId = (int) $request->student_id;
        }

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $this->validate($request, [
            'stase_id' => 'required|integer|exists:stases,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $log = StaseLog::create([
            'student_id' => $studentId,
            'stase_id' => $request->stase_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Create Student Stase Success',
            'result' => $log,
        ]);
    }

    public function updateStudentStase(Request $request, $id)
    {
        $payload = $request->attributes->get('jwt_payload');
        $studentId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        $authType = $payload ? data_get($payload, 'auth_type') : null;

        if (!$studentId) {
            $studentId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType === 'user' && $request->student_id) {
            $studentId = (int) $request->student_id;
        }

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $this->validate($request, [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $log = StaseLog::where('id', $id)
            ->where('student_id', $studentId)
            ->first();

        if (!$log) {
            return response()->json([
                'success' => false,
                'text' => 'Stase log not found.',
                'result' => null,
            ], 404);
        }

        $log->start_date = $request->start_date;
        $log->end_date = $request->end_date;
        $log->save();

        return response()->json([
            'success' => true,
            'text' => 'Update Student Stase Success',
            'result' => $log,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
            'alias' => 'required',
            'color' => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('desc', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
