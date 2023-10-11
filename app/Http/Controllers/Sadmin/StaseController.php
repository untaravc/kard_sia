<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Stase;
use Illuminate\Http\Request;

class StaseController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Stase::withCount('staseTasks')->orderBy('stase_order');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        Stase::create($request->all());

        return 'success';
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Stase::find($id)->update($request->all());
    }

    public function show($id){
        return Stase::with(['staseTasks'=>function($q){
            $q->with(['lecture','task']);
        }])->find($id);
    }

    public function destroy($id)
    {
        Stase::findOrFail($id)->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "name"           => 'required',
            "alias"          => 'required',
            "color"          => 'required',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where(function ($q)use ($request){
                $q->where('name', 'LIKE', '%'.$request->keyword.'%');
                $q->orWhere('desc', 'LIKE', '%'.$request->keyword.'%');
            });
        }

        return $dataContent;
    }
}
