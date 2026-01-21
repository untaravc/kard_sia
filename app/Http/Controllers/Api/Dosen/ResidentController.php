<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(Request $request){
        $dataContent = Student::with([
            'staseLogsActive'=>function($q){
                $q->with('stase');
            }
        ])->orderBy('name')
            ->when($request->s, function ($q) use ($request){
                $q->where('name', 'LIKE', '%'. $request->s .'%');
            })
            ->paginate(10);

        $this->response['status'] = true;
        $this->response['data'] = [
            'residents' => $dataContent,
        ];

        return $this->response;
    }
}
