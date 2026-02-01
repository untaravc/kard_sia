<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Student::leftJoin('student_profiles', 'student_profiles.student_id', '=', 'students.id')
            ->select('students.*', 'student_profiles.phone as phone')
            ->orderBy('students.name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(15);

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
                $q->where('students.name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('students.email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }

    public function profile(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType !== 'student') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $student = Student::find($authId);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $profile = StudentProfile::whereStudentId($authId)->first();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Profile Success',
            'result' => [
                'student' => $student,
                'profile' => $profile,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType !== 'student') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $this->validate($request, [
            'email' => 'nullable|email',
            'name' => 'nullable',
            'password' => 'nullable|confirmed',
        ]);

        $student = Student::find($authId);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        if ($request->password) {
            $student->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $studentData = [];
        if ($request->has('email')) {
            $studentData['email'] = $request->email;
        }
        if ($request->has('name')) {
            $studentData['name'] = $request->name;
        }
        if (!empty($studentData)) {
            $student->update($studentData);
        }

        $profile = StudentProfile::whereStudentId($authId)->first();
        $pathName = $profile ? $profile->image : null;

        if ($request->image) {
            if (str_starts_with($request->image, 'http')) {
                $pathName = $request->image;
            } elseif (strlen($request->image) > 100) {
                $pathName = $this->imageProcessing($request->image, 'students', false);
            }
        }

        $profileData = [];
        if ($request->has('address')) {
            $profileData['address'] = $request->address;
        }
        if ($request->has('phone')) {
            $profileData['phone'] = $request->phone;
        }
        if ($pathName !== null) {
            $profileData['image'] = $pathName;
        }

        if ($profile) {
            if (!empty($profileData)) {
                $profile->update($profileData);
            }
        } else {
            $profile = StudentProfile::create(array_merge([
                'student_id' => $authId,
            ], $profileData));
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Student Profile Success',
            'result' => [
                'student' => $student,
                'profile' => $profile,
            ],
        ]);
    }
}
