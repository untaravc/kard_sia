<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
