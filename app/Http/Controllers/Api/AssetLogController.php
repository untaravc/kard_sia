<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetLog;
use Illuminate\Http\Request;

class AssetLogController extends Controller
{
    public function index(Request $request, $asset_id)
    {
        $dataContent = AssetLog::query()
            ->where('asset_id', $asset_id)
            ->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Asset Logs Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request, $asset_id)
    {
        $data = $this->validateData($request);
        $data['asset_id'] = $asset_id;

        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }
        $data['auth_type'] = $authType;
        $data['auth_id'] = $authId;

        $assetLog = AssetLog::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Asset Log Success',
            'result' => $assetLog,
        ]);
    }

    public function show($asset_id, $id)
    {
        $assetLog = AssetLog::where('asset_id', $asset_id)->find($id);
        if (!$assetLog) {
            return response()->json([
                'success' => false,
                'text' => 'Asset Log not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Asset Log Success',
            'result' => $assetLog,
        ]);
    }

    public function update(Request $request, $asset_id, $id)
    {
        $assetLog = AssetLog::where('asset_id', $asset_id)->find($id);
        if (!$assetLog) {
            return response()->json([
                'success' => false,
                'text' => 'Asset Log not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $assetLog->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Asset Log Success',
            'result' => $assetLog,
        ]);
    }

    public function destroy($asset_id, $id)
    {
        $assetLog = AssetLog::where('asset_id', $asset_id)->find($id);
        if (!$assetLog) {
            return response()->json([
                'success' => false,
                'text' => 'Asset Log not found',
                'result' => null,
            ], 404);
        }

        $assetLog->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Asset Log Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'auth_id' => 'nullable|integer',
            'auth_type' => 'nullable|string',
            'status' => 'nullable|string',
            'note' => 'nullable|string',
            'location' => 'nullable|string',
            'photo_urls' => 'nullable',
            'study_program_code' => 'nullable|string|max:50',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('note', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('status', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('location', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->filled('auth_id')) {
            $dataContent = $dataContent->where('auth_id', $request->auth_id);
        }

        if ($request->filled('auth_type')) {
            $dataContent = $dataContent->where('auth_type', $request->auth_type);
        }

        return $dataContent;
    }
}
