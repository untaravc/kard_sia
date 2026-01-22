<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = User::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return response()->json([
            'success' => true,
            'text' => 'Retrieve Users Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'password'     => Hash::make($request->password)
        ]);

        $this->validateData($request);

        $user = User::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create User Success',
            'result' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if($request->password){
            $request->merge([
                'password'     => Hash::make($request->password)
            ]);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'text' => 'User not found',
                'result' => null,
            ], 404);
        }

        $user->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update User Success',
            'result' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'text' => 'User not found',
                'result' => null,
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete User Success',
            'result' => null,
        ]);
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
            "email"         => 'required|email',
            "password"      => 'required_without:id',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where('name', 'LIKE', "%".$request->keyword."%");
        }
        return $dataContent;
    }
}
