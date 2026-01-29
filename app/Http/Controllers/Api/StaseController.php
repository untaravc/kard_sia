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
