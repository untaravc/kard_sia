<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Task::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Tasks Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $task = Task::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Task Success',
            'result' => $task,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Task not found',
                'result' => null,
            ], 404);
        }

        $task->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Task Success',
            'result' => $task,
        ]);
    }

    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Task not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Task Success',
            'result' => $task,
        ]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Task not found',
                'result' => null,
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Task Success',
            'result' => null,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
            'desc' => 'nullable',
            'is_latter' => 'nullable|boolean',
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
