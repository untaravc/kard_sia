<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Student::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Students Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'password' => Hash::make($request->password),
        ]);

        $this->validateData($request);

        $student = Student::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Student Success',
            'result' => $student,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        if ($request->password) {
            $request->merge([
                'password' => Hash::make($request->password),
            ]);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $student->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Student Success',
            'result' => $student,
        ]);
    }

    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Success',
            'result' => $student,
        ]);
    }

    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Student Success',
            'result' => null,
        ]);
    }

    public function score(Request $request, $student_id)
    {
        $staseLogId = $request->stase_log_id;
        if (!$staseLogId) {
            $ongoing = StaseLog::whereStudentId($student_id)
                ->whereStatus('ongoing')
                ->first();
            $staseLogId = $ongoing ? $ongoing->id : null;
        }

        if (!$staseLogId) {
            $latest = StaseLog::whereStudentId($student_id)
                ->orderByDesc('created_at')
                ->first();
            $staseLogId = $latest ? $latest->id : null;
        }

        if (!$staseLogId) {
            return response()->json([
                'success' => true,
                'text' => 'No stase log found for this student',
                'result' => [],
            ]);
        }

        $grouped = StaseTaskLog::whereStudentId($student_id)
            ->whereStaseLogId($staseLogId)
            ->groupBy('stase_task_id')
            ->with([
                'staseTask',
                'stase',
                'files',
            ])
            ->get();

        $scores = StaseTaskLog::whereStudentId($student_id)
            ->whereStaseLogId($staseLogId)
            ->with([
                'lecture',
                'staseTaskLogPoint' => function ($query) {
                    $query->with('taskDetail');
                },
            ])
            ->get();

        foreach ($grouped as $item) {
            $item->setAttribute('scores', $scores->where('stase_task_id', $item->stase_task_id)->values());
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Score Success',
            'result' => $grouped,
            'meta' => [
                'stase_log_id' => (int) $staseLogId,
            ],
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required_without:id',
            'year' => 'nullable',
            'status' => 'nullable',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->status !== null && $request->status !== '') {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->year !== null && $request->year !== '') {
            $dataContent = $dataContent->where('year', $request->year);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
