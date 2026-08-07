<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyProgram;
use Illuminate\Http\Request;

class StudyProgramController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = StudyProgram::query()->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Study Programs Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $studyProgram = StudyProgram::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Study Program Success',
            'result' => $studyProgram,
        ]);
    }

    public function show($id)
    {
        $studyProgram = StudyProgram::find($id);

        if (!$studyProgram) {
            return response()->json([
                'success' => false,
                'text' => 'Study Program not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Study Program Success',
            'result' => $studyProgram,
        ]);
    }

    public function update(Request $request, $id)
    {
        $studyProgram = StudyProgram::find($id);
        if (!$studyProgram) {
            return response()->json([
                'success' => false,
                'text' => 'Study Program not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        unset($data['code']);
        $studyProgram->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Study Program Success',
            'result' => $studyProgram,
        ]);
    }

    public function destroy($id)
    {
        $studyProgram = StudyProgram::find($id);
        if (!$studyProgram) {
            return response()->json([
                'success' => false,
                'text' => 'Study Program not found',
                'result' => null,
            ], 404);
        }

        $studyProgram->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Study Program Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'address' => 'nullable|string',
            'head_name' => 'nullable|string|max:255',
            'deputy_head_name' => 'nullable|string|max:255',
        ]);
    }

    public function list()
    {
        $studyPrograms = StudyProgram::select('id', 'code', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Study Program List Success',
            'result' => $studyPrograms,
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('head_name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('deputy_head_name', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
