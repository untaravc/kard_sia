<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Services\Firebase\NotificationService;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = DeviceToken::orderBy('id', 'desc');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Device Tokens Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'auth_type') : $request->auth_type;
        $authId = $payload ? data_get($payload, 'auth_id') : $request->auth_id;

        $this->validateData($request);
        if (!$authType || !$authId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $existingToken = DeviceToken::where('auth_type', $authType)
            ->where('auth_id', $authId)
            ->where('token', $request->token)
            ->first();

        if ($existingToken) {
            $existingToken->update([
                'platform' => $request->platform,
                'last_seen_at' => $request->last_seen_at ?? now(),
            ]);

            return response()->json([
                'success' => true,
                'text' => 'Device Token Updated',
                'result' => $existingToken,
            ]);
        }

        $deviceToken = DeviceToken::create([
            'auth_type' => $authType,
            'auth_id' => $authId,
            'token' => $request->token,
            'platform' => $request->platform,
            'last_seen_at' => $request->last_seen_at ?? now(),
        ]);

        $notification = Notification::create([
            'auth_type' => $authType,
            'auth_id' => $authId,
            'title' => 'New device login',
            'content' => 'We detected a login from a new device.',
            'link' => null,
            'is_read' => 0,
        ]);

        $otherTokens = DeviceToken::where('auth_type', $authType)
            ->where('auth_id', $authId)
            ->where('token', '!=', $request->token)
            ->pluck('token')
            ->all();

        if (!empty($otherTokens)) {
            app(NotificationService::class)->sendToTokens(
                $otherTokens,
                $notification->title,
                $notification->content,
                [
                    'type' => 'new_device_login',
                    'notification_id' => (string) $notification->id,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'text' => 'Create Device Token Success',
            'result' => [
                'device_token' => $deviceToken,
                'notification' => $notification,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request, $id);

        $deviceToken = DeviceToken::find($id);
        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'text' => 'Device Token not found',
                'result' => null,
            ], 404);
        }

        $deviceToken->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Device Token Success',
            'result' => $deviceToken,
        ]);
    }

    public function show($id)
    {
        $deviceToken = DeviceToken::find($id);

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'text' => 'Device Token not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Device Token Success',
            'result' => $deviceToken,
        ]);
    }

    public function destroy($id)
    {
        $deviceToken = DeviceToken::find($id);
        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'text' => 'Device Token not found',
                'result' => null,
            ], 404);
        }

        $deviceToken->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Device Token Success',
            'result' => null,
        ]);
    }

    public function validateData($request, $id = null)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'platform' => 'required|string',
            'last_seen_at' => 'nullable|date',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('token', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('platform', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('auth_type', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->auth_type != null) {
            $dataContent = $dataContent->where('auth_type', $request->auth_type);
        }

        if ($request->auth_id != null) {
            $dataContent = $dataContent->where('auth_id', $request->auth_id);
        }

        return $dataContent;
    }
}
