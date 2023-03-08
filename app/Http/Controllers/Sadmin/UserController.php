<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = User::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $request->merge([
            'password'     => Hash::make($request->password)
        ]);

        $this->validateData($request);

        User::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if($request->password){
            $request->merge([
                'password'     => Hash::make($request->password)
            ]);
        }

        User::find($id)->update($request->all());
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
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
