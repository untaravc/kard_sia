<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Setting::query()->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Settings Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $setting = Setting::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Setting Success',
            'result' => $setting,
        ]);
    }

    public function show($id)
    {
        $setting = Setting::find($id);
        if (!$setting) {
            return response()->json([
                'success' => false,
                'text' => 'Setting not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Setting Success',
            'result' => $setting,
        ]);
    }

    public function showByLabel($label)
    {
        $setting = Setting::where('label', $label)->first();
        if (!$setting) {
            return response()->json([
                'success' => false,
                'text' => 'Setting not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Setting Success',
            'result' => $setting,
        ]);
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);
        if (!$setting) {
            return response()->json([
                'success' => false,
                'text' => 'Setting not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $setting->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Setting Success',
            'result' => $setting,
        ]);
    }

    public function destroy($id)
    {
        $setting = Setting::find($id);
        if (!$setting) {
            return response()->json([
                'success' => false,
                'text' => 'Setting not found',
                'result' => null,
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Setting Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'name' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:100',
            'value' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('label', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('value', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        return $dataContent;
    }
}
