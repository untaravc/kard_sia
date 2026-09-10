<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\ActivityPresenceImport;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Activity::orderByDesc('start_date');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        $this->attachAttendeeCounts($dataContent->getCollection());

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Activities Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $payload = $request->all();

        // created_by decides who may edit or delete the activity later, and
        // the column points at a student. A student always gets stamped from
        // their own token so the field cannot be forged; admins may still
        // name the owner explicitly when filing on someone's behalf.
        $jwt = $request->attributes->get('jwt_payload');
        $authType = $jwt ? (data_get($jwt, 'log_as_auth_type') ?: data_get($jwt, 'auth_type')) : null;
        $authId = $jwt ? (data_get($jwt, 'log_as_auth_id') ?: data_get($jwt, 'auth_id')) : null;

        if ($authType === 'student' && $authId) {
            $payload['created_by'] = $authId;
        } elseif (empty($payload['created_by'])) {
            $payload['created_by'] = 0;
        }
        if (!array_key_exists('status', $payload)) {
            $payload['status'] = 'active';
        }

        $activity = Activity::create($payload);

        return response()->json([
            'success' => true,
            'text' => 'Create Activity Success',
            'result' => $activity,
        ]);
    }

    public function show($id)
    {
        $activity = Activity::with([
            'activity_lectures' => fn ($query) => $query->with('lecture')->orderBy('created_at'),
            'activity_students' => fn ($query) => $query->with('student')->orderBy('created_at'),
        ])->find($id);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Activity Success',
            'result' => $activity,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $activity = Activity::find($id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $activity->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Activity Success',
            'result' => $activity,
        ]);
    }

    public function destroy($id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Activity Success',
            'result' => null,
        ]);
    }

    public function activitiesToday(Request $request)
    {
        $dateParam = $request->query('date');
        try {
            $today = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        } catch (\Exception $e) {
            $today = Carbon::today();
        }

        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        $activities = Activity::whereDate('start_date', '=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->orderBy('start_date')
            ->get();

        if ($authType && $authId && $activities->count()) {
            $activityIds = $activities->pluck('id')->all();
            if ($authType === 'lecture') {
                $absences = ActivityLecture::whereIn('activity_id', $activityIds)
                    ->where('lecture_id', $authId)
                    ->get()
                    ->keyBy('activity_id');
            } else if ($authType === 'student') {
                $absences = ActivityStudent::whereIn('activity_id', $activityIds)
                    ->where('student_id', $authId)
                    ->get()
                    ->keyBy('activity_id');
            } else {
                $absences = collect();
            }

            $activities->transform(function ($activity) use ($absences) {
                $activity->setAttribute('absence', $absences->get($activity->id));
                return $activity;
            });
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Today Activities Success',
            'result' => $activities,
        ]);
    }

    public function presence(Request $request, $activity_id)
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

        if (!$authType || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $activity = Activity::find($activity_id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $presence = null;
        if ($authType === 'student') {
            $presence = ActivityStudent::firstOrCreate([
                'activity_id' => $activity_id,
                'student_id' => $authId,
            ]);
        } else if ($authType === 'lecture') {
            $presence = ActivityLecture::firstOrCreate([
                'activity_id' => $activity_id,
                'lecture_id' => $authId,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'text' => 'Presence recorded',
            'result' => $presence,
        ]);
    }

    public function previewImportPresence(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xls,xlsx',
        ]);

        $activeRows = $this->parsePresenceRows($request->file('file'));
        $indexes = $this->loadPresenceMatchIndexes();

        $result = array_map(fn ($cols) => $this->buildPresenceRow($cols, $indexes), $activeRows);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Presence Import Preview Success',
            'result' => $result,
        ]);
    }

    public function importPresence(Request $request, $activity_id)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xls,xlsx',
        ]);

        $activity = Activity::find($activity_id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'text' => 'Activity not found',
                'result' => null,
            ], 404);
        }

        $activeRows = $this->parsePresenceRows($request->file('file'));
        $indexes = $this->loadPresenceMatchIndexes();

        $summary = [
            'total' => count($activeRows),
            'matched' => 0,
            'unmatched' => 0,
            'students_created' => 0,
            'students_existing' => 0,
            'lectures_created' => 0,
            'lectures_existing' => 0,
        ];

        $rows = [];
        foreach ($activeRows as $cols) {
            $row = $this->buildPresenceRow($cols, $indexes);

            if (!$row['matched']) {
                $summary['unmatched']++;
                $rows[] = $row;
                continue;
            }

            $summary['matched']++;
            $identityNumber = $row['identity_number'] ? trim((string) $row['identity_number']) : null;
            $presenceTime = $this->parsePresenceDateTime($row['presence_time']);

            if ($row['student_matched']) {
                $student = Student::find($row['student_id']);
                if ($student && !$student->univ_number && $identityNumber) {
                    $student->update(['univ_number' => $identityNumber]);
                }

                $activityStudent = ActivityStudent::firstOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'student_id' => $row['student_id'],
                    ],
                    $presenceTime ? ['created_at' => $presenceTime] : []
                );
                $summary[$activityStudent->wasRecentlyCreated ? 'students_created' : 'students_existing']++;
            }

            if ($row['lecture_matched']) {
                $lecture = Lecture::find($row['lecture_id']);
                if ($lecture && !$lecture->univ_number && $identityNumber) {
                    $lecture->update(['univ_number' => $identityNumber]);
                }

                $activityLecture = ActivityLecture::firstOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'lecture_id' => $row['lecture_id'],
                    ],
                    $presenceTime ? ['created_at' => $presenceTime] : []
                );
                $summary[$activityLecture->wasRecentlyCreated ? 'lectures_created' : 'lectures_existing']++;
            }

            $rows[] = $row;
        }

        return response()->json([
            'success' => true,
            'text' => 'Import Presence Success',
            'result' => array_merge($summary, ['rows' => $rows]),
        ]);
    }

    private function parsePresenceDateTime($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parsePresenceRows($file)
    {
        $tabs = Excel::toArray(new ActivityPresenceImport, $file);
        $rows = $tabs[0] ?? [];

        $startIndex = null;
        foreach ($rows as $index => $cols) {
            $number = $cols[0] ?? null;
            if ($number !== null && trim((string) $number) === '1') {
                $startIndex = $index;
                break;
            }
        }

        return $startIndex === null ? [] : array_values(array_slice($rows, $startIndex));
    }

    private function loadPresenceMatchIndexes()
    {
        $students = Student::where('status', 'active')->get(['id', 'name', 'univ_number']);
        $lectures = Lecture::get(['id', 'name_alt', 'univ_number']);

        return [
            'students_by_number' => $students->filter(fn ($student) => filled($student->univ_number))
                ->keyBy(fn ($student) => trim($student->univ_number)),
            'students_by_name' => $students->keyBy(fn ($student) => $this->normalizeName($student->name)),
            'lectures_by_number' => $lectures->filter(fn ($lecture) => filled($lecture->univ_number))
                ->keyBy(fn ($lecture) => trim($lecture->univ_number)),
            'lectures_by_name' => $lectures->filter(fn ($lecture) => filled($lecture->name_alt))
                ->keyBy(fn ($lecture) => $this->normalizeName($lecture->name_alt)),
        ];
    }

    private function buildPresenceRow($cols, $indexes)
    {
        $name = $cols[1] ?? null;
        $identityNumber = $cols[3] ?? null;
        $normalizedName = $this->normalizeName($name);
        $normalizedNumber = $identityNumber !== null && $identityNumber !== ''
            ? trim((string) $identityNumber)
            : null;

        $student = ($normalizedNumber ? $indexes['students_by_number']->get($normalizedNumber) : null)
            ?? $indexes['students_by_name']->get($normalizedName);

        $lecture = ($normalizedNumber ? $indexes['lectures_by_number']->get($normalizedNumber) : null)
            ?? $indexes['lectures_by_name']->get($normalizedName);

        return [
            'no' => $cols[0] ?? null,
            'name' => $name,
            'identity_type' => $cols[2] ?? null,
            'identity_number' => $identityNumber,
            'presence_time' => $cols[4] ?? null,
            'unit' => $cols[5] ?? null,
            'matched' => (bool) ($student || $lecture),
            'student_matched' => (bool) $student,
            'student_id' => $student ? $student->id : null,
            'lecture_matched' => (bool) $lecture,
            'lecture_id' => $lecture ? $lecture->id : null,
        ];
    }

    private function normalizeName($name)
    {
        $name = (string) $name;
        $name = preg_replace('/\s+/', ' ', trim($name));

        return strtolower($name);
    }

    private function validateData(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'study_program_codes' => 'nullable|array',
        ]);
    }

    private function attachAttendeeCounts($activities)
    {
        $activityIds = $activities->pluck('id');
        if ($activityIds->isEmpty()) {
            return;
        }

        $studentCounts = ActivityStudent::whereIn('activity_id', $activityIds)
            ->selectRaw('activity_id, COUNT(*) as total')
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');

        $lectureCounts = ActivityLecture::whereIn('activity_id', $activityIds)
            ->selectRaw('activity_id, COUNT(*) as total')
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');

        $activities->each(function ($activity) use ($studentCounts, $lectureCounts) {
            $activity->setAttribute('activity_students_count', $studentCounts->get($activity->id, 0));
            $activity->setAttribute('activity_lectures_count', $lectureCounts->get($activity->id, 0));
        });
    }

    private function withFilter($dataContent, Request $request)
    {
        if ($request->keyword != null) {
            $keyword = $request->keyword;
            $dataContent = $dataContent->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('speaker', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('title', 'LIKE', '%' . $keyword . '%');
            });
        }

        if ($request->date_from != null) {
            $dataContent = $dataContent->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->date_to != null) {
            $dataContent = $dataContent->whereDate('start_date', '<=', $request->date_to);
        }

        return $dataContent;
    }
}
