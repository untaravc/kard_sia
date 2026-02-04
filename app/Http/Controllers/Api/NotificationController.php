<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Lecture;
use App\Models\Notification;
use App\Models\Student;
use App\Services\Firebase\NotificationService;
use App\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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
