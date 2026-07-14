<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use App\Models\StudentLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function menu(Request $request)
    {
        $basePath = $request->get('basePath', '/blu');
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if (!in_array($authType, ['user', 'student', 'lecture'], true)) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $todayAgendaCount = 0;
        if (in_array($authType, ['student', 'lecture'], true)) {
            $today = Carbon::today();
            $activities = Activity::whereDate('start_date', '=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->orderBy('start_date')
                ->get();

            if ($authId && $activities->count()) {
                $activityIds = $activities->pluck('id')->all();
                if ($authType === 'lecture') {
                    $absences = ActivityLecture::whereIn('activity_id', $activityIds)
                        ->where('lecture_id', $authId)
                        ->get()
                        ->keyBy('activity_id');
                } else {
                    $absences = ActivityStudent::whereIn('activity_id', $activityIds)
                        ->where('student_id', $authId)
                        ->get()
                        ->keyBy('activity_id');
                }

                $activities->transform(function ($activity) use ($absences) {
                    $activity->setAttribute('absence', $absences->get($activity->id));
                    return $activity;
                });
            }

            $todayAgendaCount = $activities->count();
        }

        $lecturePendingLogbookCount = 0;
        if ($authType === 'lecture' && $authId) {
            $lecturePendingLogbookCount = StudentLog::where('lecture_id', $authId)
                ->where('status', 0)
                ->count();
        }

        $menuByType = [
            'user' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard"],
                [
                    'label' => 'Lectures',
                    'icon' => 'dosen',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/lectures"],
                    ],
                ],
                [
                    'label' => 'Students',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/students"],
                        ['label' => 'Monitoring', 'to' => "{$basePath}/students/monitoring"],
                        ['label' => 'Presences', 'to' => "{$basePath}/presences"],
                        ['label' => 'Presences Daily', 'to' => "{$basePath}/presences/daily"],
                        ['label' => 'Presences Monthly', 'to' => "{$basePath}/presences/monthly"],
                        ['label' => 'Log Book', 'to' => "{$basePath}/logbooks"],
                    ],
                ],
                [
                    'label' => 'Registrations',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Administrasi', 'to' => "{$basePath}/registrations"],
                        ['label' => 'Journal', 'to' => "{$basePath}/registrations?section=journal"],
                        ['label' => 'Interview', 'to' => "{$basePath}/registrations?section=interview"],
                        ['label' => 'Score', 'to' => "{$basePath}/registrations/score"],
                    ],
                ],
                ['label' => 'Activities', 'icon' => 'agenda', 'to' => "{$basePath}/activities"],
                ['label' => 'Letters', 'icon' => 'agenda', 'to' => "{$basePath}/letters"],
                ['label' => 'Accreditations', 'icon' => 'agenda', 'to' => "{$basePath}/accreditations"],
                ['label' => 'Stase Log Report', 'icon' => 'agenda', 'to' => "{$basePath}/report/stase-log"],
                [
                    'label' => 'Data Master',
                    'icon' => 'data-master',
                    'children' => [
                        ['label' => 'Form Option', 'to' => "{$basePath}/form-options"],
                        ['label' => 'Post', 'to' => "{$basePath}/posts"],
                        ['label' => 'Stase', 'to' => "{$basePath}/stases"],
                        ['label' => 'Task', 'to' => "{$basePath}/tasks"],
                        ['label' => 'Asset', 'to' => "{$basePath}/assets"],
                        ['label' => 'Admin', 'to' => "{$basePath}/users"],
                    ],
                ],
            ],
            'student' => [
                ['label' => 'Scoring', 'icon' => 'mdi:clipboard-check-outline', 'to' => "{$basePath}/dashboard-student/scoring"],
                ['label' => 'Agenda', 'icon' => 'mdi:calendar-month-outline', 'to' => "{$basePath}/dashboard-student/agenda", 'counter' => $todayAgendaCount],
                ['label' => 'Logbooks', 'icon' => 'mdi:notebook-outline', 'to' => "{$basePath}/logbook-student"],
                ['label' => 'Checklist', 'icon' => 'mdi:format-list-checks', 'to' => "{$basePath}/dashboard-student/checklist"],
                ['label' => 'Profile', 'icon' => 'mdi:account-outline', 'to' => "{$basePath}/dashboard-student/profile"],
            ],
            'lecture' => [
                ['label' => 'Scoring', 'icon' => 'mdi:clipboard-check-outline', 'to' => "{$basePath}/dashboard-lecture/scoring"],
                ['label' => 'Agenda', 'icon' => 'mdi:calendar-month-outline', 'to' => "{$basePath}/dashboard-lecture/agenda"],
                ['label' => 'Logbook', 'icon' => 'mdi:notebook-outline', 'to' => "{$basePath}/dashboard-lecture/logbook", 'counter' => $lecturePendingLogbookCount],
                ['label' => 'Accreditations', 'icon' => 'mdi:certificate-outline', 'to' => "{$basePath}/accreditations"],
                ['label' => 'Profile', 'icon' => 'mdi:account-outline', 'to' => "{$basePath}/dashboard-lecture/profile"],
            ],
        ];

        $menu = $menuByType[$authType] ?? $menuByType['user'];

        $this->response['success'] = true;
        $this->response['text'] = 'Retrieve Menu Success';
        $this->response['result'] = [
            'menu' => $menu,
        ];

        return $this->response;
    }
}
