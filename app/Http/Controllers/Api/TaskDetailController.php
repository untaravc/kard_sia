<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskDetail;
use Illuminate\Http\Request;

class TaskDetailController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = TaskDetail::orderBy('order')->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Task Details Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $taskDetail = TaskDetail::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Task Detail Success',
            'result' => $taskDetail,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $taskDetail = TaskDetail::find($id);
        if (!$taskDetail) {
            return response()->json([
                'success' => false,
                'text' => 'Task detail not found',
                'result' => null,
            ], 404);
        }

        $taskDetail->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Task Detail Success',
            'result' => $taskDetail,
        ]);
    }

    public function show($id)
    {
        $taskDetail = TaskDetail::find($id);

        if (!$taskDetail) {
            return response()->json([
                'success' => false,
                'text' => 'Task detail not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Task Detail Success',
            'result' => $taskDetail,
        ]);
    }

    public function destroy($id)
    {
        $taskDetail = TaskDetail::find($id);
        if (!$taskDetail) {
            return response()->json([
                'success' => false,
                'text' => 'Task detail not found',
                'result' => null,
            ], 404);
        }

        $taskDetail->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Task Detail Success',
            'result' => null,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'task_id' => 'required|integer',
            'name' => 'required|string',
            'order' => 'nullable|integer',
            'type' => 'nullable|in:option,text,bool,score',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->task_id != null) {
            $dataContent = $dataContent->where('task_id', $request->task_id);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->keyword . '%');
        }

        return $dataContent;
    }
}
