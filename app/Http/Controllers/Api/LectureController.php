<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LectureController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Lecture::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lectures Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'password' => Hash::make($request->password),
        ]);

        $this->validateData($request);

        $lecture = Lecture::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Lecture Success',
            'result' => $lecture,
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

        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $lecture->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Lecture Success',
            'result' => $lecture,
        ]);
    }

    public function show($id)
    {
        $lecture = Lecture::find($id);

        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lecture Success',
            'result' => $lecture,
        ]);
    }

    public function destroy($id)
    {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $lecture->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Lecture Success',
            'result' => null,
        ]);
    }

    public function list()
    {
        $lectures = Lecture::whereStatus(1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lecture List Success',
            'result' => $lectures,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required_without:id',
            'name_alt' => 'nullable',
            'last_act' => 'nullable',
            'status' => 'nullable',
            'is_in_house' => 'nullable|boolean',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
