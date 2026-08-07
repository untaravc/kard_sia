<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Role::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Roles Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $role = Role::create($data);

        return response()->json([
            'success' => true,
            'text' => 'Create Role Success',
            'result' => $role,
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => false,
                'text' => 'Role not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request, $id);
        $role->update($data);

        return response()->json([
            'success' => true,
            'text' => 'Update Role Success',
            'result' => $role,
        ]);
    }

    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'text' => 'Role not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Role Success',
            'result' => $role,
        ]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => false,
                'text' => 'Role not found',
                'result' => null,
            ], 404);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Role Success',
            'result' => null,
        ]);
    }

    public function list()
    {
        $roles = Role::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Role List Success',
            'result' => $roles,
        ]);
    }

    protected function validateData(Request $request, $id = null)
    {
        return $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($id)],
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }
}
