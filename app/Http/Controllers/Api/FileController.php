<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
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
            'title' => 'required|string',
            'desc' => 'nullable|string',
            'link' => 'required|string',
            'open_stase_task_id' => 'required|integer',
            'stase_task_log_id' => 'nullable|integer',
            'type' => 'nullable|string',
        ]);

        $file = File::create([
            'student_id' => $authId,
            'open_stase_task_id' => $request->open_stase_task_id,
            'stase_task_log_id' => $request->stase_task_log_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'link' => $request->link,
            'type' => $request->type ?? 'document',
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Create File Success',
            'result' => $file->fresh(),
        ]);
    }
}
