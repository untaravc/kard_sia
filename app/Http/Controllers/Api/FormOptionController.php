<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormOption;
use Illuminate\Http\Request;

class FormOptionController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = FormOption::query()->orderBy('relation_id')->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Form Options Success',
            'result' => $dataContent,
        ]);
    }

    public function list(Request $request)
    {
        $dataContent = FormOption::query()->where('status', 1);
        $dataContent = $this->withFilter($dataContent, $request);

        $options = $dataContent->orderBy('name')->get(['id', 'type', 'relation_id', 'value', 'name', 'desc', 'status']);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Form Option List Success',
            'result' => $options,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->preparePayload($data);

        $formOption = FormOption::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Form Option Success',
            'result' => $formOption,
        ]);
    }

    public function show($id)
    {
        $formOption = FormOption::find($id);
        if (!$formOption) {
            return response()->json([
                'success' => false,
                'text' => 'Form Option not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Form Option Success',
            'result' => $formOption,
        ]);
    }

    public function update(Request $request, $id)
    {
        $formOption = FormOption::find($id);
        if (!$formOption) {
            return response()->json([
                'success' => false,
                'text' => 'Form Option not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $data = $this->preparePayload($data);
        $formOption->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Form Option Success',
            'result' => $formOption,
        ]);
    }

    public function destroy($id)
    {
        $formOption = FormOption::find($id);
        if (!$formOption) {
            return response()->json([
                'success' => false,
                'text' => 'Form Option not found',
                'result' => null,
            ], 404);
        }

        $formOption->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Form Option Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'type' => 'required|string',
            'relation_id' => 'nullable|integer',
            'value' => 'nullable|string',
            'name' => 'required|string',
            'desc' => 'nullable',
            'status' => 'nullable|integer',
        ]);
    }

    protected function preparePayload(array $data)
    {
        if (array_key_exists('desc', $data) && (is_array($data['desc']) || is_object($data['desc']))) {
            $data['desc'] = json_encode($data['desc']);
        }

        return $data;
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('value', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->type != null) {
            $dataContent = $dataContent->where('type', $request->type);
        }

        if ($request->relation_id != null) {
            $dataContent = $dataContent->where('relation_id', $request->relation_id);
        }

        if ($request->status != null) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        return $dataContent;
    }
}
