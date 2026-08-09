<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use App\Models\Menu;
use App\Models\MenuRole;
use App\Models\Setting;
use App\Models\StudentLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Menu::orderBy('order')->orderBy('name');
        $dataContent = $this->withFilterMenu($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Menus Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMenuData($request);

        $menuItem = Menu::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Menu Success',
            'result' => $menuItem,
        ]);
    }

    public function update(Request $request, $id)
    {
        $menuItem = Menu::find($id);
        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'text' => 'Menu not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateMenuData($request, $id);
        $menuItem->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Menu Success',
            'result' => $menuItem,
        ]);
    }

    public function show($id)
    {
        $menuItem = Menu::find($id);

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'text' => 'Menu not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Menu Success',
            'result' => $menuItem,
        ]);
    }

    public function destroy($id)
    {
        $menuItem = Menu::find($id);
        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'text' => 'Menu not found',
                'result' => null,
            ], 404);
        }

        $menuItem->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Menu Success',
            'result' => null,
        ]);
    }

    public function list()
    {
        $menus = Menu::where('is_active', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'title', 'parent_id']);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Menu List Success',
            'result' => $menus,
        ]);
    }

    protected function validateMenuData(Request $request, $id = null)
    {
        return $this->validate($request, [
            'parent_id' => 'nullable|integer',
            'order' => 'nullable|integer',
            'type' => 'required|in:menu,title,submenu',
            'url' => 'required|string|max:255',
            'name' => ['required', 'string', 'max:255', Rule::unique('menus', 'name')->ignore($id)],
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function withFilterMenu($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('title', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('url', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('parent_id')) {
            $dataContent = $dataContent->where('parent_id', $request->parent_id);
        }

        return $dataContent;
    }
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

        $appName = Setting::where('label', 'app.name')->value('value');
        $logbooksUrl = $appName === 'IPD' ? "{$basePath}/logbook-student-daily" : "{$basePath}/logbook-student";

        $menuByType = [
            'student' => [
                ['label' => 'Scoring', 'icon' => 'mdi:clipboard-check-outline', 'to' => "{$basePath}/dashboard-student/scoring"],
                ['label' => 'Agenda', 'icon' => 'mdi:calendar-month-outline', 'to' => "{$basePath}/dashboard-student/agenda", 'counter' => $todayAgendaCount],
                ['label' => 'Logbooks', 'icon' => 'mdi:notebook-outline', 'to' => $logbooksUrl],
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

        $menu = $authType === 'user'
            ? $this->buildUserMenu($authId)
            : ($menuByType[$authType] ?? []);

        $this->response['success'] = true;
        $this->response['text'] = 'Retrieve Menu Success';
        $this->response['result'] = [
            'menu' => $menu,
        ];

        return $this->response;
    }

    /**
     * Builds the admin sidebar from the menus the user's role has been
     * granted the INDEX (method = GET) permission for on, via menu_role.
     */
    protected function buildUserMenu($authId)
    {
        $user = $authId ? User::find($authId) : null;
        $roleId = $user ? $user->role_id : null;

        if (!$roleId) {
            return [];
        }

        $menuIds = MenuRole::where('role_id', $roleId)
            ->where('method', 'GET')
            ->pluck('menu_id');

        if ($menuIds->isEmpty()) {
            return [];
        }

        $menus = Menu::whereIn('id', $menuIds)
            ->where('is_active', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $topLevel = $menus->whereNull('parent_id')->values();
        $childrenByParent = $menus->whereNotNull('parent_id')->groupBy('parent_id');

        return $topLevel->map(function ($menuItem) use ($childrenByParent) {
            $children = $childrenByParent->get($menuItem->id, collect())
                ->map(function ($child) {
                    return [
                        'label' => $child->title,
                        'to' => $child->url,
                    ];
                })
                ->values()
                ->all();

            $item = [
                'label' => $menuItem->title,
                'icon' => $menuItem->icon,
            ];

            if (count($children)) {
                $item['children'] = $children;
            } else {
                $item['to'] = $menuItem->url;
            }

            return $item;
        })->values()->all();
    }
}
