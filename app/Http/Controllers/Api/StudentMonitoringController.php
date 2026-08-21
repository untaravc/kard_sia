<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\CalculatesAttendance;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\FormOption;
use App\Models\Presence;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentMonitoringController extends Controller
{
    use CalculatesAttendance;

    public function index(Request $request)
    {
        // Columns: stases (optionally filtered by phase = stases.section), ordered as on the board.
        $staseQuery = Stase::orderByDesc('stase_order')->orderBy('name');
        if ($request->stase_section !== null && $request->stase_section !== '') {
            $staseQuery->where('section', $request->stase_section);
        }
        $stases = $staseQuery->get(['id', 'name', 'alias']);

        // Denominator per stase = number of its active tasks.
        $activeTasks = StaseTask::where('status', 1)->get(['id', 'stase_id']);
        $totalByStase = $activeTasks->groupBy('stase_id')->map->count();
        $activeTaskIds = $activeTasks->pluck('id')->flip();

        // Students matching the current filters (year / name); this page only
        // ever shows active students, regardless of any status param passed.
        $studentQuery = Student::query()->orderBy('name');
        $studentQuery = $this->withFilter($studentQuery, $request);
        $studentQuery->where('status', 'active');

        if ($request->filled('current_stase_id')) {
            $candidateIds = (clone $studentQuery)->pluck('id')->all();
            $matchingIds = $this->studentsCurrentlyInStase($candidateIds, (int) $request->current_stase_id);
            $studentQuery->whereIn('id', $matchingIds);
        }

        // All filtered ids drive the overall aggregate; the page drives the table.
        $allStudentIds = (clone $studentQuery)->pluck('id')->all();

        $students = $studentQuery->paginate($request->get('per_page', 20));

        $studentIds = collect($students->items())->pluck('id')->all();

        $overall = $this->overallFulfilment($allStudentIds, $stases->pluck('id')->all(), $totalByStase, $activeTaskIds);

        // stase_logs of the current page's students (enrollment / progress context).
        $staseLogs = StaseLog::whereIn('student_id', $studentIds)
            ->get(['student_id', 'stase_id', 'status', 'start_date', 'end_date'])
            ->groupBy('student_id');

        // stase_task_logs of the current page's students that have been scored.
        $taskLogs = StaseTaskLog::whereIn('student_id', $studentIds)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->get(['id','student_id', 'stase_id', 'stase_task_id']);

        // completed[student_id][stase_id] = set of distinct completed active task ids.
        $completed = [];
        foreach ($taskLogs as $log) {
            if (!$activeTaskIds->has($log->stase_task_id)) {
                continue;
            }
            $completed[$log->student_id][$log->stase_id][$log->stase_task_id] = true;
        }

        $students->getCollection()->transform(function ($student) use ($stases, $totalByStase, $completed, $staseLogs) {
            $logsForStudent = $staseLogs->get($student->id);
            // Stases the student actually has a stase_log for; the rest render grey.
            $loggedStaseIds = $logsForStudent
                ? $logsForStudent->pluck('stase_id')->all()
                : [];
            // The student's latest / currently ongoing stase, by start_date & end_date.
            $currentStaseId = $this->currentStaseId($logsForStudent);

            $cells = [];
            // Fulfilment counts only stases the student has actually taken.
            $takenDone = 0;
            $takenTotal = 0;
            foreach ($stases as $stase) {
                $total = (int) ($totalByStase[$stase->id] ?? 0);
                $done = isset($completed[$student->id][$stase->id])
                    ? count($completed[$student->id][$stase->id])
                    : 0;
                $done = min($done, $total);

                $hasLog = in_array($stase->id, $loggedStaseIds, true);

                if ($hasLog) {
                    $takenDone += $done;
                    $takenTotal += $total;
                }

                $cells[$stase->id] = [
                    'done' => $done,
                    'total' => $total,
                    'status' => $this->cellStatus($done, $total, $hasLog),
                    'ongoing' => $stase->id === $currentStaseId,
                ];
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'year' => $student->year,
                'link_token' => $student->link_token,
                'fulfilment' => $takenTotal > 0 ? (int) round($takenDone / $takenTotal * 100) : null,
                'cells' => $cells,
            ];
        });

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Success',
            'result' => $students,
            'stases' => $stases,
            'overall' => $overall,
        ]);
    }

    /**
     * Aggregate fulfilment across every filtered student (not just the current
     * page), counting taken stases only, over the displayed stase columns.
     */
    private function overallFulfilment($studentIds, $staseIds, $totalByStase, $activeTaskIds)
    {
        if (empty($studentIds) || empty($staseIds)) {
            return ['percentage' => null, 'done' => 0, 'total' => 0, 'students' => count($studentIds)];
        }

        // Distinct taken (student, stase) pairs within the displayed columns.
        $takenPairs = [];
        StaseLog::whereIn('student_id', $studentIds)
            ->whereIn('stase_id', $staseIds)
            ->get(['student_id', 'stase_id'])
            ->each(function ($log) use (&$takenPairs) {
                $takenPairs[$log->student_id][$log->stase_id] = true;
            });

        // Distinct completed active tasks per (student, stase).
        $completed = [];
        StaseTaskLog::whereIn('student_id', $studentIds)
            ->whereIn('stase_id', $staseIds)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->get(['student_id', 'stase_id', 'stase_task_id'])
            ->each(function ($log) use (&$completed, $activeTaskIds) {
                if ($activeTaskIds->has($log->stase_task_id)) {
                    $completed[$log->student_id][$log->stase_id][$log->stase_task_id] = true;
                }
            });

        $takenDone = 0;
        $takenTotal = 0;
        foreach ($takenPairs as $studentId => $staseSet) {
            foreach ($staseSet as $staseId => $_) {
                $total = (int) ($totalByStase[$staseId] ?? 0);
                $done = isset($completed[$studentId][$staseId])
                    ? count($completed[$studentId][$staseId])
                    : 0;
                $takenTotal += $total;
                $takenDone += min($done, $total);
            }
        }

        return [
            'percentage' => $takenTotal > 0 ? (int) round($takenDone / $takenTotal * 100) : null,
            'done' => $takenDone,
            'total' => $takenTotal,
            'students' => count($studentIds),
        ];
    }

    /**
     * Per-student daily logbook counts over a date range, for the monitoring
     * calendar table (columns = dates, cell = count of student_logs that day).
     */
    public function logbook(Request $request)
    {
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

        $studentQuery = Student::query()->orderBy('name');
        $studentQuery = $this->withFilter($studentQuery, $request);

        $students = $studentQuery->paginate($request->get('per_page', 14));

        $studentIds = collect($students->items())->pluck('id')->all();

        $staseId = $request->filled('stase_id') ? (int) $request->stase_id : null;
        $competenceIds = array_filter((array) $request->input('competence_ids', []));

        $logsQuery = StudentLog::whereIn('student_id', $studentIds)
            ->whereDate('date', '>=', $dateFrom->toDateString())
            ->whereDate('date', '<=', $dateTo->toDateString());

        // When one or more competencies are checked, only count entries that
        // have a matching student_log_skills row (form_options type
        // 'sp1ipd-logbook-competence').
        if (!empty($competenceIds)) {
            $logsQuery->whereHas('stase_log_skills', function ($query) use ($competenceIds) {
                $query->whereIn('form_option_id', $competenceIds);
            });
        }

        // When a stase is selected, only count entries logged under that
        // stase and whose date falls within the student's own enrollment
        // window (stase_logs.start_date - end_date) for it.
        $staseLogRangesByStudent = collect();
        if ($staseId) {
            $logsQuery->where('stase_id', $staseId);
            $staseLogRangesByStudent = StaseLog::whereIn('student_id', $studentIds)
                ->where('stase_id', $staseId)
                ->get(['student_id', 'start_date', 'end_date'])
                ->groupBy('student_id');
        }

        $logs = $logsQuery->get(['id', 'student_id', 'date']);

        if ($staseId) {
            $logs = $logs->filter(function ($log) use ($staseLogRangesByStudent) {
                $ranges = $staseLogRangesByStudent->get($log->student_id);
                if (!$ranges) {
                    return false;
                }

                $date = substr($log->date, 0, 10);

                return $ranges->contains(function ($range) use ($date) {
                    if ($range->start_date && $date < $range->start_date) {
                        return false;
                    }
                    if ($range->end_date && $date > $range->end_date) {
                        return false;
                    }
                    return true;
                });
            })->values();
        }

        $logsByStudentDate = $logs->groupBy(function ($log) {
            return $log->student_id . '_' . substr($log->date, 0, 10);
        });

        $dates = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        // Denominator for the "sufficient logging" label: weekdays in range.
        $weekdays = $this->countWeekdays($dateFrom->toDateString(), $dateTo->toDateString());

        $students->getCollection()->transform(function ($student) use ($dates, $logsByStudentDate, $weekdays) {
            $cells = [];
            $totalLogbook = 0;
            foreach ($dates as $date) {
                $count = $logsByStudentDate->get($student->id . '_' . $date, collect())->count();
                $cells[$date] = $count;
                $totalLogbook += $count;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'year' => $student->year,
                'cells' => $cells,
                'total_logbook' => $totalLogbook,
                'weekdays' => $weekdays,
                'sufficient' => $totalLogbook >= $weekdays,
            ];
        });

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Logbook Success',
            'result' => $students,
            'dates' => $dates,
            'weekdays' => $weekdays,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'stases' => Stase::orderByDesc('stase_order')->orderBy('name')->get(['id', 'name', 'alias']),
            'competence_options' => FormOption::whereStatus(1)
                ->whereType('sp1ipd-logbook-competence')
                ->orderBy('name')
                ->get(['id', 'name', 'desc']),
        ]);
    }

    /**
     * Student summary for the logbook monitoring modal: identity, current
     * (or most recent) stase, and logbook count over the same date range
     * used by logbook(). Backs the "Kirim notifikasi Email" action.
     */
    public function summary(Request $request)
    {
        $student = Student::find($request->student_id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

        $staseLogs = StaseLog::where('student_id', $student->id)
            ->get(['stase_id', 'start_date', 'end_date']);

        $staseId = $this->currentStaseId($staseLogs);
        if (!$staseId) {
            $latest = $staseLogs->sortByDesc('start_date')->first();
            $staseId = $latest ? $latest->stase_id : null;
        }

        $stase = $staseId ? Stase::find($staseId, ['id', 'name', 'alias']) : null;

        $logbookCount = StudentLog::where('student_id', $student->id)
            ->whereDate('date', '>=', $dateFrom->toDateString())
            ->whereDate('date', '<=', $dateTo->toDateString())
            ->count();

        $weekdays = $this->countWeekdays($dateFrom->toDateString(), $dateTo->toDateString());

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Logbook Summary Success',
            'result' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'year' => $student->year,
                    'email' => $student->email,
                    'phone' => optional($student->studentProfile)->phone,
                ],
                'stase' => $stase ? [
                    'id' => $stase->id,
                    'name' => $stase->name,
                    'alias' => $stase->alias,
                ] : null,
                'logbook_count' => $logbookCount,
                'weekdays' => $weekdays,
                'sufficient' => $logbookCount >= $weekdays,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    /**
     * Per-student daily presence + activity attendance, for the monitoring
     * calendar table (columns = dates). A cell is green when the student both
     * checked in and attended at least one of that day's activities, yellow
     * when only one of the two happened, grey when neither did.
     */
    public function presence(Request $request)
    {
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

        $studentQuery = Student::query()->orderBy('name');
        $studentQuery = $this->withFilter($studentQuery, $request);

        $students = $studentQuery->paginate($request->get('per_page', 10));
        $studentIds = collect($students->items())->pluck('id')->all();

        $dates = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $activityIdsByDate = $this->activityIdsByDate($dateFrom, $dateTo);
        $allActivityIds = collect($activityIdsByDate)->flatten()->unique()->values()->all();

        $attendedByStudent = ActivityStudent::whereIn('student_id', $studentIds)
            ->whereIn('activity_id', $allActivityIds)
            ->get(['student_id', 'activity_id'])
            ->groupBy('student_id')
            ->map(function ($rows) {
                return $rows->pluck('activity_id')->unique()->all();
            });

        $presenceDatesByStudent = Presence::whereIn('student_id', $studentIds)
            ->whereDate('checkin', '>=', $dateFrom->toDateString())
            ->whereDate('checkin', '<=', $dateTo->toDateString())
            ->get(['student_id', 'checkin'])
            ->groupBy('student_id')
            ->map(function ($rows) {
                return $rows->map(function ($row) {
                    return substr($row->checkin, 0, 10);
                })->unique()->all();
            });

        $weekdays = $this->countWeekdays($dateFrom->toDateString(), $dateTo->toDateString());

        $students->getCollection()->transform(function ($student) use ($dates, $activityIdsByDate, $attendedByStudent, $presenceDatesByStudent, $weekdays) {
            $presenceDates = $presenceDatesByStudent->get($student->id, []);
            $attendedIds = $attendedByStudent->get($student->id, []);

            $cells = [];
            $presenceCount = 0;
            $activityStudentTotal = 0;
            $activityTotal = 0;

            foreach ($dates as $date) {
                $dayActivityIds = $activityIdsByDate[$date] ?? [];
                $dayTotal = count($dayActivityIds);
                $dayAttended = count(array_intersect($dayActivityIds, $attendedIds));
                $hasPresence = in_array($date, $presenceDates, true);
                $hasActivity = $dayAttended > 0;

                $status = 'gray';
                if ($hasPresence && $hasActivity) {
                    $status = 'green';
                } elseif ($hasPresence || $hasActivity) {
                    $status = 'yellow';
                }

                $cells[$date] = [
                    'presence' => $hasPresence,
                    'activity_done' => $dayAttended,
                    'activity_total' => $dayTotal,
                    'status' => $status,
                ];

                if ($hasPresence) {
                    $presenceCount++;
                }
                $activityStudentTotal += $dayAttended;
                $activityTotal += $dayTotal;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'year' => $student->year,
                'cells' => $cells,
                'presence_count' => $presenceCount,
                'weekdays' => $weekdays,
                'activity_student_total' => $activityStudentTotal,
                'activity_total' => $activityTotal,
            ];
        });

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Presence Success',
            'result' => $students,
            'dates' => $dates,
            'weekdays' => $weekdays,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ]);
    }

    /**
     * Presence + activity detail for one student on one day, for the
     * monitoring calendar's cell-click modal.
     */
    public function presenceDetail(Request $request)
    {
        $student = Student::find($request->student_id);
        $date = $request->date;

        if (!$student || !$date) {
            return response()->json([
                'success' => false,
                'text' => 'Student or date not found',
                'result' => null,
            ], 404);
        }

        $presence = Presence::where('student_id', $student->id)
            ->whereDate('checkin', $date)
            ->first(['checkin', 'checkout', 'status']);

        $activities = Activity::whereDate('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereDate('end_date', '>=', $date)
                    ->orWhere(function ($subQuery) use ($date) {
                        $subQuery->whereNull('end_date')->whereDate('start_date', $date);
                    });
            })
            ->orderBy('start_date')
            ->get(['id', 'name', 'title', 'start_date', 'end_date']);

        $attended = ActivityStudent::where('student_id', $student->id)
            ->whereIn('activity_id', $activities->pluck('id')->all())
            ->get(['activity_id', 'note', 'desc'])
            ->keyBy('activity_id');

        $activityList = $activities->map(function ($activity) use ($attended) {
            $entry = $attended->get($activity->id);

            return [
                'id' => $activity->id,
                'name' => $activity->name ?: $activity->title,
                'start_date' => $activity->start_date,
                'end_date' => $activity->end_date,
                'attended' => (bool) $entry,
                'note' => $entry ? $entry->note : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Presence Detail Success',
            'result' => [
                'student' => ['id' => $student->id, 'name' => $student->name],
                'date' => $date,
                'presence' => $presence,
                'activities' => $activityList,
            ],
        ]);
    }

    /**
     * Student summary for the presence monitoring modal: identity, current
     * (or most recent) stase, and presence/activity totals over the same
     * date range used by presence(). Backs the reminder email/WhatsApp action.
     */
    public function presenceSummary(Request $request)
    {
        $student = Student::find($request->student_id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'text' => 'Student not found',
                'result' => null,
            ], 404);
        }

        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

        $staseLogs = StaseLog::where('student_id', $student->id)
            ->get(['stase_id', 'start_date', 'end_date']);

        $staseId = $this->currentStaseId($staseLogs);
        if (!$staseId) {
            $latest = $staseLogs->sortByDesc('start_date')->first();
            $staseId = $latest ? $latest->stase_id : null;
        }

        $stase = $staseId ? Stase::find($staseId, ['id', 'name', 'alias']) : null;

        $presenceCount = Presence::where('student_id', $student->id)
            ->whereDate('checkin', '>=', $dateFrom->toDateString())
            ->whereDate('checkin', '<=', $dateTo->toDateString())
            ->pluck('checkin')
            ->map(function ($checkin) {
                return substr($checkin, 0, 10);
            })
            ->unique()
            ->count();

        $weekdays = $this->countWeekdays($dateFrom->toDateString(), $dateTo->toDateString());

        $activityIdsByDate = $this->activityIdsByDate($dateFrom, $dateTo);
        $allActivityIds = collect($activityIdsByDate)->flatten()->unique()->values()->all();

        $attendedIds = ActivityStudent::where('student_id', $student->id)
            ->whereIn('activity_id', $allActivityIds)
            ->pluck('activity_id')
            ->unique()
            ->all();

        $activityStudentTotal = 0;
        $activityTotal = 0;
        foreach ($activityIdsByDate as $dayActivityIds) {
            $activityTotal += count($dayActivityIds);
            $activityStudentTotal += count(array_intersect($dayActivityIds, $attendedIds));
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Presence Summary Success',
            'result' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'year' => $student->year,
                    'email' => $student->email,
                    'phone' => optional($student->studentProfile)->phone,
                ],
                'stase' => $stase ? [
                    'id' => $stase->id,
                    'name' => $stase->name,
                    'alias' => $stase->alias,
                ] : null,
                'presence_count' => $presenceCount,
                'weekdays' => $weekdays,
                'activity_student_total' => $activityStudentTotal,
                'activity_total' => $activityTotal,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    /**
     * Activity ids that fall on each day of [dateFrom, dateTo], keyed by
     * 'Y-m-d'. A null end_date is treated as a single-day event on
     * start_date; multi-day activities appear under every day they span.
     */
    private function activityIdsByDate(Carbon $dateFrom, Carbon $dateTo)
    {
        $activities = Activity::whereDate('start_date', '<=', $dateTo->toDateString())
            ->where(function ($query) use ($dateFrom) {
                $query->whereDate('end_date', '>=', $dateFrom->toDateString())
                    ->orWhereNull('end_date');
            })
            ->get(['id', 'start_date', 'end_date']);

        $byDate = [];
        foreach ($activities as $activity) {
            $start = substr($activity->start_date, 0, 10);
            $end = $activity->end_date ? substr($activity->end_date, 0, 10) : $start;
            $start = max($start, $dateFrom->toDateString());
            $end = min($end, $dateTo->toDateString());

            if ($start > $end) {
                continue;
            }

            $cursor = Carbon::parse($start);
            $endCursor = Carbon::parse($end);
            while ($cursor->lte($endCursor)) {
                $byDate[$cursor->toDateString()][] = $activity->id;
                $cursor->addDay();
            }
        }

        return $byDate;
    }

    public function detail(Request $request)
    {
        $studentId = $request->student_id;
        $staseId = $request->stase_id;

        $student = Student::find($studentId);
        $stase = Stase::find($staseId);

        if (!$student || !$stase) {
            return response()->json([
                'success' => false,
                'text' => 'Student or Stase not found',
                'result' => null,
            ], 404);
        }

        // Enrollment window for this student/stase, if any.
        $staseLog = StaseLog::where('student_id', $studentId)
            ->where('stase_id', $staseId)
            ->orderByDesc('start_date')
            ->first(['start_date', 'end_date']);

        // Active tasks that make up this stase.
        $tasks = StaseTask::where('stase_id', $staseId)
            ->where('status', 1)
            ->orderBy('id')
            ->get(['id', 'task_id', 'name']);

        // Scored logs of this student within this stase, best score kept per task.
        $logs = StaseTaskLog::where('student_id', $studentId)
            ->where('stase_id', $staseId)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->orderByDesc('point_average')
            ->get(['stase_task_id', 'point_average', 'date', 'title']);

        $logByTask = $logs->keyBy('stase_task_id');

        $items = $tasks->map(function ($task) use ($logByTask) {
            $log = $logByTask->get($task->id);

            return [
                'stase_task_id' => $task->id,
                'name' => $task->name,
                'done' => (bool) $log,
                'point_average' => $log ? $log->point_average : null,
                'date' => $log ? $log->date : null,
            ];
        })->values();

        // Attendance (Kehadiran) over the stase's date range.
        $attendance = null;
        if ($staseLog) {
            $attendance = $this->attendanceItem(
                $staseLog->start_date ? substr($staseLog->start_date, 0, 10) : null,
                $staseLog->end_date ? substr($staseLog->end_date, 0, 10) : null,
                date('Y-m-d'),
                $this->studentPresenceDates($studentId)
            );
        }

        $doneCount = $items->where('done', true)->count();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Student Monitoring Detail Success',
            'result' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'year' => $student->year,
                    'phone' => optional($student->studentProfile)->phone,
                ],
                'stase' => ['id' => $stase->id, 'name' => $stase->name, 'alias' => $stase->alias],
                'attendance' => $attendance,
                'stase_log' => $staseLog ? [
                    'start_date' => $staseLog->start_date,
                    'end_date' => $staseLog->end_date,
                ] : null,
                'summary' => ['done' => $doneCount, 'total' => $items->count()],
                'tasks' => $items,
            ],
        ]);
    }

    /**
     * The student's current stase = the stase_log whose date range contains
     * today; if none is active, the most recently started one. Returns the
     * stase_id to mark, or null when the student has no started stase.
     */
    private function currentStaseId($logsForStudent)
    {
        if (!$logsForStudent || $logsForStudent->isEmpty()) {
            return null;
        }

        $today = date('Y-m-d');

        // Only stases that have already started.
        $started = $logsForStudent->filter(function ($log) use ($today) {
            return $log->start_date && substr($log->start_date, 0, 10) <= $today;
        });

        if ($started->isEmpty()) {
            return null;
        }

        // Prefer the one currently in range (today <= end_date, or open-ended).
        $ongoing = $started->filter(function ($log) use ($today) {
            return !$log->end_date || substr($log->end_date, 0, 10) >= $today;
        });

        $pool = $ongoing->isNotEmpty() ? $ongoing : $started;

        $current = $pool->sortByDesc('start_date')->first();

        return $current ? $current->stase_id : null;
    }

    /**
     * Of the given student ids, which ones are currently in the given
     * stase (per the same "current stase" rule as currentStaseId()).
     */
    private function studentsCurrentlyInStase(array $studentIds, int $staseId): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $logsByStudent = StaseLog::whereIn('student_id', $studentIds)
            ->get(['student_id', 'stase_id', 'start_date', 'end_date'])
            ->groupBy('student_id');

        $matching = [];
        foreach ($logsByStudent as $studentId => $logs) {
            if ($this->currentStaseId($logs) === $staseId) {
                $matching[] = $studentId;
            }
        }

        return $matching;
    }

    private function cellStatus($done, $total, $hasLog = true)
    {
        // No stase_log for this student/stase → no data, render grey.
        if (!$hasLog) {
            return 'empty';
        }

        if ($total <= 0) {
            return 'empty';
        }

        if ($done >= $total) {
            return 'green';
        }

        return ($done / $total) >= 0.5 ? 'yellow' : 'red';
    }

    private function withFilter($query, $request)
    {
        // Defaults to active when the status param is not supplied at all.
        $status = $request->has('status') ? $request->status : 'active';
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($request->year !== null && $request->year !== '') {
            $query->where('year', $request->year);
        }

        $name = $request->name ?? $request->keyword;
        if ($name !== null && $name !== '') {
            $query->where('students.name', 'LIKE', '%' . $name . '%');
        }

        return $query;
    }
}
