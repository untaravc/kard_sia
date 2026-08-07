<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuRole;
use Illuminate\Http\Request;

class MenuRoleController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = MenuRole::with(['role', 'menu'])->orderBy('id', 'desc');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Menu Roles Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $menuRole = MenuRole::create($data);
        $menuRole->load(['role', 'menu']);

        return response()->json([
            'success' => true,
            'text' => 'Create Menu Role Success',
            'result' => $menuRole,
        ]);
    }

    public function update(Request $request, $id)
    {
        $menuRole = MenuRole::find($id);
        if (!$menuRole) {
            return response()->json([
                'success' => false,
                'text' => 'Menu role not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        $menuRole->update($data);
        $menuRole->load(['role', 'menu']);

        return response()->json([
            'success' => true,
            'text' => 'Update Menu Role Success',
            'result' => $menuRole,
        ]);
    }

    public function show($id)
    {
        $menuRole = MenuRole::with(['role', 'menu'])->find($id);

        if (!$menuRole) {
            return response()->json([
                'success' => false,
                'text' => 'Menu role not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Menu Role Success',
            'result' => $menuRole,
        ]);
    }

    public function destroy($id)
    {
        $menuRole = MenuRole::find($id);
        if (!$menuRole) {
            return response()->json([
                'success' => false,
                'text' => 'Menu role not found',
                'result' => null,
            ], 404);
        }

        $menuRole->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Menu Role Success',
            'result' => null,
        ]);
    }

    public function sync(Request $request)
    {
        $this->validate($request, [
            'role_id' => 'required|integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*.menu_id' => 'required|integer|exists:menus,id',
            'permissions.*.method' => 'required|in:GET,SHOW,POST,PUT,DEL',
        ]);

        $roleId = $request->role_id;

        $rows = collect($request->permissions ?? [])
            ->unique(function ($item) {
                return $item['menu_id'] . '-' . $item['method'];
            })
            ->map(function ($item) use ($roleId) {
                return [
                    'role_id' => $roleId,
                    'menu_id' => $item['menu_id'],
                    'method' => $item['method'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        MenuRole::where('role_id', $roleId)->delete();

        if (!empty($rows)) {
            MenuRole::insert($rows);
        }

        $menuRoles = MenuRole::with(['role', 'menu'])->where('role_id', $roleId)->get();

        return response()->json([
            'success' => true,
            'text' => 'Update Menu Roles Success',
            'result' => $menuRoles,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'role_id' => 'required|integer|exists:roles,id',
            'menu_id' => 'required|integer|exists:menus,id',
            'method' => 'required|in:GET,SHOW,POST,PUT,DEL',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('role_id')) {
            $dataContent = $dataContent->where('role_id', $request->role_id);
        }

        if ($request->filled('menu_id')) {
            $dataContent = $dataContent->where('menu_id', $request->menu_id);
        }

        return $dataContent;
    }
}
