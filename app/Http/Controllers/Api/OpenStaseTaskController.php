<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\BaseTrait;
use App\Models\OpenStaseTask;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OpenStaseTaskController extends Controller
{
    use BaseTrait;
    public function create(Request $request)
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
            'stase_task_id' => 'required|integer',
            'title' => 'required|string',
            'plan' => 'required|date',
            'lecture_ids' => 'required|array',
        ]);

        $created = [];
        foreach ($request->lecture_ids as $lectureId) {
            if (!$lectureId) {
                continue;
            }
            $created[] = OpenStaseTask::create([
                'student_id' => $authId,
                'stase_task_id' => $request->stase_task_id,
                'lecture_id' => $lectureId,
                'title' => $request->title,
                'plan' => $request->plan,
                'link_token' => $this->generateRandomString(17),
            ]);
        }

        return response()->json([
            'success' => true,
            'text' => 'Create Open Stase Task Success',
            'result' => $created,
        ]);
    }

    public function openStaseTask(Request $request)
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

        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $data = OpenStaseTask::with([
            'student',
            'lecture',
            'files',
            'staseTask' => function ($query) {
                $query->with([
                    'stase',
                    'task',
                ]);
            },
        ])
            ->where(function ($query) use ($authId) {
                $query->where('lecture_id', $authId)
                    ->orWhere('lecture_id', 0);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'LIKE', '%' . $keyword . '%')
                        ->orWhereHas('student', function ($q) use ($keyword) {
                            $q->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('staseTask', function ($q) use ($keyword) {
                            $q->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhereHas('task', function ($taskQuery) use ($keyword) {
                                    $taskQuery->where('name', 'LIKE', '%' . $keyword . '%');
                                });
                        });
                });
            })
            // ->whereDate('created_at', '>', date('Y-m-d', strtotime(date('Y-m-d') . ' -3 months')))
            ->orderByDesc('created_at')
            ->get();

        foreach ($data as $item) {
            $item->setAttribute('data', StaseTaskLog::whereLectureId($authId)
                ->whereStudentId(data_get($item, 'student.id', 0))
                ->whereStaseTaskId(data_get($item, 'staseTask.id'))
                ->first());
        }

        $blank = [];
        $clicked = [];

        foreach ($data as $item) {
            if ($item['data'] && data_get($item, 'data.point_average') > 0) {
                $clicked[] = $item;
            } else {
                $blank[] = $item;
            }
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $merged = array_merge($blank, $clicked);
        $total = count($merged);
        $items = array_slice($merged, ($page - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Open Stase Tasks Success',
            'result' => $paginator,
        ]);
    }

    public function update(Request $request, $id)
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
            'title' => 'required|string',
            'plan' => 'required|date',
        ]);

        $task = OpenStaseTask::whereStudentId($authId)->find($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Open stase task not found',
                'result' => null,
            ], 404);
        }

        $task->update([
            'title' => $request->title,
            'plan' => $request->plan,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Open Stase Task Success',
            'result' => $task->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
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

        $task = OpenStaseTask::whereStudentId($authId)->find($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Open stase task not found',
                'result' => null,
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Open Stase Task Success',
            'result' => null,
        ]);
    }

    public function show(Request $request, $id)
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

        $query = OpenStaseTask::with(['files', 'lecture', 'staseTask']);

        if ($authType === 'student' && $authId) {
            $query->whereStudentId($authId);
        } elseif ($authType === 'lecture' && $authId) {
            $query->whereLectureId($authId);
        }

        $task = $query->find($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'text' => 'Open stase task not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Open Stase Task Success',
            'result' => $task,
        ]);
    }
}
