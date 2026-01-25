<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseTask;
use Illuminate\Http\Request;

class StaseTaskController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = StaseTask::with(['task', 'lecture', 'stase'])->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(20);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Tasks Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $staseTask = StaseTask::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $staseTask = StaseTask::find($id);
        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        $staseTask->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function show($id)
    {
        $staseTask = StaseTask::with(['task', 'lecture', 'stase'])->find($id);

        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Stase Task Success',
            'result' => $staseTask,
        ]);
    }

    public function destroy($id)
    {
        $staseTask = StaseTask::find($id);
        if (!$staseTask) {
            return response()->json([
                'success' => false,
                'text' => 'Stase task not found',
                'result' => null,
            ], 404);
        }

        $staseTask->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Stase Task Success',
            'result' => null,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'stase_id' => 'required|integer',
            'name' => 'required',
            'status' => 'nullable',
            'task_id' => 'nullable|integer',
            'lecture_id' => 'nullable|integer',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->stase_id != null) {
            $dataContent = $dataContent->where('stase_id', $request->stase_id);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('status', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
