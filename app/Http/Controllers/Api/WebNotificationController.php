<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\WebNotificationService;
use Illuminate\Http\Request;

class WebNotificationController extends Controller
{
    public function index(Request $request)
    {
        [$authType, $authId] = $this->currentAuth($request);

        $limit = (int) $request->get('limit', 20);
        $limit = $limit > 0 ? min($limit, 100) : 20;

        $service = app(WebNotificationService::class);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Notifications Success',
            'result' => [
                'notifications' => $service->listForUser($authType, $authId, $limit),
                'unread_count' => $service->unreadCount($authType, $authId),
            ],
        ]);
    }

    public function markAllRead(Request $request)
    {
        [$authType, $authId] = $this->currentAuth($request);

        $updated = app(WebNotificationService::class)->markAllRead($authType, $authId);

        return response()->json([
            'success' => true,
            'text' => 'Notifications Marked As Read',
            'result' => ['updated' => $updated],
        ]);
    }

    public function markRead(Request $request, $id)
    {
        [$authType, $authId] = $this->currentAuth($request);

        $ok = app(WebNotificationService::class)->markRead($authType, $authId, $id);

        if (!$ok) {
            return response()->json([
                'success' => false,
                'text' => 'Notification not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Notification Marked As Read',
            'result' => null,
        ]);
    }

    private function currentAuth(Request $request): array
    {
        $payload = $request->attributes->get('jwt_payload');

        return [
            $payload ? data_get($payload, 'auth_type') : null,
            $payload ? (int) data_get($payload, 'auth_id') : null,
        ];
    }
}
