<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request){
        return auth_dosen('token');
    }

    public function products(){
        $data = [];
        for($i = 1; $i < 30; $i++){
            $data[] = [
                'id'    => $i,
                'name'  => 'Data ke-' . ($i + 5),
            ];
        }

        $response = [
            'status'    => true,
            'result'    => $data,
        ];

        return response()->json($response, 200);
    }

    public function store(Request $request){
        return response()->json('ok', 201);
    }

//    function generateRandomString($length = 10) {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $charactersLength = strlen($characters);
//        $randomString = '';
//        for ($i = 0; $i < $length; $i++) {
//            $randomString .= $characters[rand(0, $charactersLength - 1)];
//        }
//        return $randomString;
//    }
}
