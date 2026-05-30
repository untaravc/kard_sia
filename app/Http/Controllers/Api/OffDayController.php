<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OffDay;
use Illuminate\Http\Request;

class OffDayController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = OffDay::query()->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Off Days Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $offDay = OffDay::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Off Day Success',
            'result' => $offDay,
        ]);
    }

    public function show($id)
    {
        $offDay = OffDay::find($id);
        if (!$offDay) {
            return response()->json([
                'success' => false,
                'text' => 'Off Day not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Off Day Success',
            'result' => $offDay,
        ]);
    }

    public function update(Request $request, $id)
    {
        $offDay = OffDay::find($id);
        if (!$offDay) {
            return response()->json([
                'success' => false,
                'text' => 'Off Day not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $offDay->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Off Day Success',
            'result' => $offDay,
        ]);
    }

    public function destroy($id)
    {
        $offDay = OffDay::find($id);
        if (!$offDay) {
            return response()->json([
                'success' => false,
                'text' => 'Off Day not found',
                'result' => null,
            ], 404);
        }

        $offDay->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Off Day Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'name' => 'nullable|string',
            'date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('status', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $dataContent = $dataContent->whereDate('date', $request->date);
        }

        if ($request->filled('date_gte')) {
            $dataContent = $dataContent->whereDate('date', '>=', $request->date_gte);
        }

        if ($request->filled('date_lte')) {
            $dataContent = $dataContent->whereDate('date', '<=', $request->date_lte);
        }

        return $dataContent;
    }
}
