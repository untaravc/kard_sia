<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $response = [
      'status'  => false,
      'text'    => 'Unauthorized',
      'data'    => null,
    ];

    public function login(Request $request) {
        $this->validate($request, [
           'email' => 'required|email',
           'password' => 'required',
        ]);

        $dosen = Lecture::whereEmail($request->email)->first();
        if($dosen){
            if(Hash::check($request->password, $dosen->password)){
                $this->response['status'] = true;
                $this->response['text'] = 'Login Success';
                $this->response['data'] = [
                    'token' => $dosen->token,
                ];
            }
        }
        return $this->response;
    }

    public function profile() {
        $dosen = auth_dosen();
        if($dosen){
            $this->response['status'] = true;
            $this->response['text'] = 'Retrieve Profile Success';
            $this->response['data'] = [
                'profile' => $dosen,
            ];;
        }
        return $this->response;
    }

    public function logout() {
        $this->response['status'] = true;
        $this->response['text'] = 'Logged Out';
        return $this->response;
    }
}
