<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenStaseTask;
use App\Models\StaseLog;
use App\Models\StaseTask;
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

    // Have to be refactored
    public function score(Request $request, $student_id)
    {
        $staseLogId = $request->stase_log_id;
        $stase_log = StaseLog::myOwn()->find($staseLogId);
        $stase_id = $stase_log->stase_id;
        $staseTasks = StaseTask::where('stase_id', $stase_id)
            ->whereStatus(1)
            ->get();

        $staseTaskLogs = StaseTaskLog::where('student_id', $student_id)
            ->select('stase_task_logs.*', 'tasks.name as task_name', 'lectures.name as lecture_name')
            ->leftJoin('tasks', 'tasks.id', '=', 'stase_task_logs.task_id')
            ->leftJoin('lectures', 'lectures.id', '=', 'stase_task_logs.lecture_id')
            ->where('stase_id', $stase_id)
            ->get();

        $openStaseTasks = OpenStaseTask::where('student_id', $student_id)
            ->withTrashed()
            ->with(['files'])
            ->leftJoin('stase_tasks', 'stase_tasks.id', '=', 'open_stase_tasks.stase_task_id')
            ->leftJoin('lectures', 'lectures.id', '=', 'open_stase_tasks.lecture_id')
            ->select('open_stase_tasks.*', 'stase_tasks.task_id as task_id', 'lectures.name as lecture_name')
            ->whereIn('open_stase_tasks.stase_task_id', $staseTasks->pluck('id')->toArray())
            ->get();

        $openStaseTasksByTask = $openStaseTasks->groupBy('task_id');
        $staseTaskLogsByTask = $staseTaskLogs->groupBy('task_id');

        foreach ($staseTasks as $task) {
            $taskId = $task->task_id;
            $openTasks = $openStaseTasksByTask->get($taskId, collect())->values();
            $logs = $staseTaskLogsByTask->get($taskId, collect())->values();

            if ($openTasks->count() && $logs->count()) {
                $usedOpenTaskIds = collect();
                foreach ($logs as $log) {
                    if (!$log->lecture_id) {
                        $log->setAttribute('openStaseTasks', collect());
                        continue;
                    }
                    $matchedOpenTasks = $openTasks->where('lecture_id', $log->lecture_id)->values();
                    $log->setAttribute('openStaseTasks', $matchedOpenTasks);
                    if ($matchedOpenTasks->count()) {
                        $usedOpenTaskIds = $usedOpenTaskIds->merge($matchedOpenTasks->pluck('id'));
                    }
                }
                if ($usedOpenTaskIds->count()) {
                    $openTasks = $openTasks->reject(function ($item) use ($usedOpenTaskIds) {
                        return $usedOpenTaskIds->contains($item->id);
                    })->where('deleted_at', null)->values();
                }
            }

            $task->setAttribute('openStaseTasks', $openTasks);
            $task->setAttribute('staseTaskLogs', $logs);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Stase Task Success',
            'result' => $staseTasks,
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
