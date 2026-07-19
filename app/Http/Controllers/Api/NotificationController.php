<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\CalculatesAttendance;
use App\Models\Activity;
use App\Models\ActivityStudent;
use App\Models\DeviceToken;
use App\Models\Lecture;
use App\Models\Notification;
use App\Models\Presence;
use App\Models\Stase;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentLog;
use App\Services\Firebase\NotificationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    use CalculatesAttendance;

    public function index(Request $request)
    {
        $dataContent = Notification::orderBy('id', 'desc');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Notifications Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $notification = Notification::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Notification Success',
            'result' => $notification,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request, $id);

        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json([
                'success' => false,
                'text' => 'Notification not found',
                'result' => null,
            ], 404);
        }

        $notification->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Notification Success',
            'result' => $notification,
        ]);
    }

    public function show($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'text' => 'Notification not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Notification Success',
            'result' => $notification,
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json([
                'success' => false,
                'text' => 'Notification not found',
                'result' => null,
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Notification Success',
            'result' => null,
        ]);
    }

    public function pushNotif(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $email = $request->email;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        $authUser = null;
        $authType = null;

        foreach ($providers as $type => $model) {
            $candidate = $model::whereEmail($email)->first();
            if ($candidate) {
                $authUser = $candidate;
                $authType = $type;
                break;
            }
        }

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'text' => 'Email not found',
                'result' => null,
            ], 404);
        }

        $notification = Notification::create([
            'auth_type' => $authType,
            'auth_id' => $authUser->id,
            'title' => $request->title,
            'content' => $request->content,
            'link' => null,
            'is_read' => 0,
        ]);

        $tokens = DeviceToken::where('auth_type', $authType)
            ->where('auth_id', $authUser->id)
            ->pluck('token')
            ->all();

        $pushResult = null;
        if (!empty($tokens)) {
            $pushResult = app(NotificationService::class)->sendToTokens(
                $tokens,
                $notification->title,
                $notification->content,
                [
                    'notification_id' => (string) $notification->id,
                    'type' => 'custom',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'text' => 'Push Notification Success',
            'result' => [
                'notification' => $notification,
                'push_result' => $pushResult,
            ],
        ]);
    }

    /**
     * Preview (render without sending) the insufficient-logbook reminder.
     */
    public function insufficientLogbookPreview(Request $request)
    {
        $data = $this->buildInsufficientLogbookData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Insufficient Logbook Preview Success',
            'result' => [
                'to' => $this->notificationRecipient($data['student']->email),
                'subject' => 'Pengingat Pengisian Logbook',
                'html' => view('mails.notifications.Insufficient_logbook', $data)->render(),
            ],
        ]);
    }

    /**
     * Email a student who has logged an insufficient number of logbook
     * entries relative to weekdays in the given (default: trailing 30 day)
     * range. Triggered from the Student Monitoring Logbook screen.
     */
    public function insufficientLogbook(Request $request)
    {
        $data = $this->buildInsufficientLogbookData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email not found',
                'result' => null,
            ], 404);
        }

        $student = $data['student'];

        Mail::send('mails.notifications.Insufficient_logbook', $data, function ($message) use ($student) {
            $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
            $fromName = config('mail.from.name') ?: config('app.name');

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            $message->to($this->notificationRecipient($student->email), $student->name)
                ->subject('Pengingat Pengisian Logbook');
        });

        return response()->json([
            'success' => true,
            'text' => 'Insufficient Logbook Reminder Sent',
            'result' => [
                'student_id' => $student->id,
                'email' => $student->email,
                'logbook_count' => $data['logbookCount'],
                'weekdays' => $data['weekdays'],
            ],
        ]);
    }

    /**
     * Preview (render without sending) the insufficient-score reminder.
     */
    public function insufficientScorePreview(Request $request)
    {
        $data = $this->buildInsufficientScoreData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email or stase not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Insufficient Score Preview Success',
            'result' => [
                'to' => $this->notificationRecipient($data['student']->email),
                'subject' => 'Pengingat Penyelesaian Tugas Stase',
                'html' => view('mails.notifications.insufficient_score', $data)->render(),
            ],
        ]);
    }

    /**
     * Email a student who has not completed enough scored tasks in a stase.
     * Triggered from the Student Monitoring task-detail modal.
     */
    public function insufficientScore(Request $request)
    {
        $data = $this->buildInsufficientScoreData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email or stase not found',
                'result' => null,
            ], 404);
        }

        $student = $data['student'];
        $stase = $data['stase'];

        Mail::send('mails.notifications.insufficient_score', $data, function ($message) use ($student) {
            $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
            $fromName = config('mail.from.name') ?: config('app.name');

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            $message->to($this->notificationRecipient($student->email), $student->name)
                ->subject('Pengingat Penyelesaian Tugas Stase');
        });

        return response()->json([
            'success' => true,
            'text' => 'Insufficient Score Reminder Sent',
            'result' => [
                'student_id' => $student->id,
                'stase_id' => $stase->id,
                'email' => $student->email,
                'done_tasks' => $data['doneTasks'],
                'total_tasks' => $data['totalTasks'],
            ],
        ]);
    }

    /**
     * Shared view data for the insufficient-logbook reminder (used by both
     * the preview and the actual send, so what you preview is what is sent).
     */
    private function buildInsufficientLogbookData(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required|integer',
        ]);

        $student = Student::find($request->student_id);
        if (!$student || !$student->email) {
            return null;
        }

        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

        $logbookCount = StudentLog::where('student_id', $student->id)
            ->whereDate('date', '>=', $dateFrom->toDateString())
            ->whereDate('date', '<=', $dateTo->toDateString())
            ->count();

        $weekdays = 0;
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            if ($cursor->isWeekday()) {
                $weekdays++;
            }
            $cursor->addDay();
        }

        return [
            'student' => $student,
            'logbookCount' => $logbookCount,
            'weekdays' => $weekdays,
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
        ];
    }

    /**
     * Shared view data for the insufficient-score reminder (used by both the
     * preview and the actual send, so what you preview is what is sent).
     */
    private function buildInsufficientScoreData(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required|integer',
            'stase_id' => 'required|integer',
        ]);

        $student = Student::find($request->student_id);
        $stase = Stase::find($request->stase_id);

        if (!$student || !$student->email || !$stase) {
            return null;
        }

        $totalTasks = StaseTask::where('stase_id', $stase->id)
            ->where('status', 1)
            ->count();

        $doneTasks = StaseTaskLog::where('student_id', $student->id)
            ->where('stase_id', $stase->id)
            ->where('point_average', '>', 0)
            ->whereNotNull('stase_task_id')
            ->distinct('stase_task_id')
            ->count('stase_task_id');
        $doneTasks = min($doneTasks, $totalTasks);

        return [
            'student' => $student,
            'stase' => $stase,
            'doneTasks' => $doneTasks,
            'totalTasks' => $totalTasks,
        ];
    }

    /**
     * Preview (render without sending) the insufficient-presence reminder.
     */
    public function insufficientPresencePreview(Request $request)
    {
        $data = $this->buildInsufficientPresenceData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Insufficient Presence Preview Success',
            'result' => [
                'to' => $this->notificationRecipient($data['student']->email),
                'subject' => 'Pengingat Presensi dan Kehadiran Kegiatan',
                'html' => view('mails.notifications.insufficient_presence', $data)->render(),
            ],
        ]);
    }

    /**
     * Email a student whose check-ins and activity attendance are behind the
     * weekdays / activities offered in the given (default: trailing 30 day)
     * range. Triggered from the Student Monitoring Presence screen.
     */
    public function insufficientPresence(Request $request)
    {
        $data = $this->buildInsufficientPresenceData($request);
        if (!$data) {
            return response()->json([
                'success' => false,
                'text' => 'Student email not found',
                'result' => null,
            ], 404);
        }

        $student = $data['student'];

        Mail::send('mails.notifications.insufficient_presence', $data, function ($message) use ($student) {
            $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
            $fromName = config('mail.from.name') ?: config('app.name');

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            $message->to($this->notificationRecipient($student->email), $student->name)
                ->subject('Pengingat Presensi dan Kehadiran Kegiatan');
        });

        return response()->json([
            'success' => true,
            'text' => 'Insufficient Presence Reminder Sent',
            'result' => [
                'student_id' => $student->id,
                'email' => $student->email,
                'presence_count' => $data['presenceCount'],
                'weekdays' => $data['weekdays'],
                'activity_student_total' => $data['activityStudentTotal'],
                'activity_total' => $data['activityTotal'],
            ],
        ]);
    }

    /**
     * Shared view data for the insufficient-presence reminder (used by both
     * the preview and the actual send, so what you preview is what is sent).
     */
    private function buildInsufficientPresenceData(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required|integer',
        ]);

        $student = Student::find($request->student_id);
        if (!$student || !$student->email) {
            return null;
        }

        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $dateTo->copy()->subDays(29);

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

        // Activities overlapping the range, bucketed per day (multi-day
        // activities are counted under every day they span).
        $activities = Activity::whereDate('start_date', '<=', $dateTo->toDateString())
            ->where(function ($query) use ($dateFrom) {
                $query->whereDate('end_date', '>=', $dateFrom->toDateString())
                    ->orWhereNull('end_date');
            })
            ->get(['id', 'start_date', 'end_date']);

        $activityIdsByDate = [];
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
                $activityIdsByDate[$cursor->toDateString()][] = $activity->id;
                $cursor->addDay();
            }
        }

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

        return [
            'student' => $student,
            'presenceCount' => $presenceCount,
            'weekdays' => $weekdays,
            'activityStudentTotal' => $activityStudentTotal,
            'activityTotal' => $activityTotal,
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
        ];
    }

    /**
     * In the local environment, reminder emails are redirected to a fixed
     * testing inbox instead of the real recipient, so local development
     * never sends mail to actual students.
     */
    private function notificationRecipient($email)
    {
        if (config('app.env') === 'local') {
            return 'vyvy1777@gmail.com';
        }

        return $email;
    }

    public function validateData($request, $id = null)
    {
        $this->validate($request, [
            'auth_type' => 'required|string',
            'auth_id' => 'required|integer',
            'title' => 'required|string',
            'content' => 'required|string',
            'link' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('content', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->auth_type != null) {
            $dataContent = $dataContent->where('auth_type', $request->auth_type);
        }

        if ($request->auth_id != null) {
            $dataContent = $dataContent->where('auth_id', $request->auth_id);
        }

        if ($request->is_read != null) {
            $dataContent = $dataContent->where('is_read', $request->is_read);
        }

        return $dataContent;
    }
}
