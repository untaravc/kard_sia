<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\BaseTrait;
use App\Models\OpenStaseTask;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class StudentController extends Controller
{
    use BaseTrait;

    public function index(Request $request)
    {
        $dataContent = Student::leftJoin('student_profiles', 'student_profiles.student_id', '=', 'students.id')
            ->select(
                'students.*',
                'student_profiles.code as code',
                'student_profiles.degree as degree',
                'student_profiles.pob as pob',
                'student_profiles.dob as dob',
                'student_profiles.phone as phone',
                'student_profiles.address as address',
                'student_profiles.image as image',
                'student_profiles.register_date as register_date',
                'student_profiles.initial as initial',
                'student_profiles.city as city',
                'student_profiles.postal_code as postal_code',
                'student_profiles.undergraduate as undergraduate',
                'student_profiles.graduated_at as graduated_at',
                'student_profiles.lecture_id as lecture_id'
            )
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
        $this->saveProfile($student->id, $request);

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
        $this->saveProfile($student->id, $request);

        return response()->json([
            'success' => true,
            'text' => 'Update Student Success',
            'result' => $student,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:active,nonactive',
            'send_email' => 'nullable|boolean',
        ]);

        $student = Student::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $student->status = $request->status;

        if ($request->status === 'active' && !$student->link_token) {
            $student->link_token = $this->generateRandomString(17);
        }

        $student->save();

        if ($request->status === 'active' && $request->boolean('send_email') && $student->email) {
            $student->reset_password_token = $student->link_token;
            $student->save();

            $link = env('APP_URL') . "/blu/login-email?token={$student->link_token}";

            Mail::send('mails.login_email', ['link' => $link], function ($message) use ($student) {
                $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
                $fromName = config('mail.from.name') ?: config('app.name');

                if ($fromAddress) {
                    $message->from($fromAddress, $fromName);
                }

                $message->to($student->email)
                    ->subject('Login Link');
            });
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Student Status Success',
            'result' => $student,
        ]);
    }

    protected function saveProfile($studentId, Request $request)
    {
        $fields = [
            'code', 'degree', 'pob', 'dob', 'phone', 'address', 'image', 'register_date',
            'initial', 'city', 'postal_code', 'undergraduate', 'graduated_at', 'lecture_id',
        ];
        if (!$request->hasAny($fields)) {
            return;
        }

        $data = collect($request->only($fields))
            ->map(function ($value) {
                return $value === '' ? null : $value;
            })
            ->toArray();

        StudentProfile::updateOrCreate(['student_id' => $studentId], $data);
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

    public function statusCounts(Request $request)
    {
        $dataContent = Student::query();

        if ($request->year !== null && $request->year !== '') {
            $dataContent = $dataContent->where('year', $request->year);
        }

        if ($request->study_program_code != null) {
            $dataContent = $dataContent->where('study_program_code', $request->study_program_code);
        }

        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        $counts = $dataContent->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Status Counts Success',
            'result' => [
                'active' => (int) ($counts['active'] ?? 0),
                'nonactive' => (int) ($counts['nonactive'] ?? 0),
                'graduate' => (int) ($counts['graduate'] ?? 0),
                'all' => (int) $counts->sum(),
            ],
        ]);
    }

    public function oldestYear()
    {
        $oldestYear = Student::whereNotNull('year')
            ->where('year', '!=', '')
            ->min('year');

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Oldest Student Year Success',
            'result' => [
                'year' => $oldestYear,
            ],
        ]);
    }

    public function studentList()
    {
        $students = Student::where('status', 'active')
            ->select('id', 'name', 'year')
            ->orderBy('year')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student List Success',
            'result' => $students,
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
            ->where('point_average', '>', 0)
            ->get();

        $openStaseTasks = OpenStaseTask::where('student_id', $student_id)
            ->withTrashed()
            ->with(['files'])
            ->leftJoin('stase_tasks', 'stase_tasks.id', '=', 'open_stase_tasks.stase_task_id')
            ->leftJoin('lectures', 'lectures.id', '=', 'open_stase_tasks.lecture_id')
            ->select('open_stase_tasks.*', 'stase_tasks.task_id as task_id', 'lectures.name as lecture_name')
            ->whereIn('open_stase_tasks.stase_task_id', $staseTasks->pluck('id')->toArray())
            ->get();

        $openStaseTasksByTask = $openStaseTasks->groupBy('stase_task_id');
        $staseTaskLogsByTask = $staseTaskLogs->groupBy('stase_task_id');

        foreach ($staseTasks as $task) {
            $taskId = $task->id;
            $openTasks = $openStaseTasksByTask->get($taskId, collect())->values();
            $logs = $staseTaskLogsByTask->get($taskId, collect())->values();

            if ($openTasks->count() && $logs->count()) {
                $usedOpenTaskIds = collect();
                foreach ($logs as $log) {
                    if (!$log->lecture_id) {
                        $log->setAttribute('openStaseTasks', collect());
                        continue;
                    }
                    $matchedOpenTasks = $openTasks->where('lecture_id', $log->lecture_id)
                        ->whereNotIn('id', $usedOpenTaskIds)
                        ->values();
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
            'study_program_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:20',
            'degree' => 'nullable|string|max:100',
            'pob' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'register_date' => 'nullable|date',
            'initial' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'undergraduate' => 'nullable|string|max:50',
            'graduated_at' => 'nullable|date',
            'lecture_id' => 'nullable|integer',
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

        if ($request->study_program_code != null) {
            $dataContent = $dataContent->where('students.study_program_code', $request->study_program_code);
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
