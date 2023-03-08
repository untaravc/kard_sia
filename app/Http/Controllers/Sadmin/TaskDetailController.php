<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\TaskDetail;
use Illuminate\Http\Request;

class TaskDetailController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = TaskDetail::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);
        TaskDetail::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        TaskDetail::find($id)->update($request->all());
    }

    public function destroy($id)
    {
         TaskDetail::findOrFail($id)->delete();
    }

    public function taskDetail($id){
        return TaskDetail::whereTaskId($id)->orderBy('order')->paginate(30);
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
            "order"          => 'required',
        ],[
//            'name.required'     => 'Nama harus diisi',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where('name', 'LIKE', "%".$request->keyword."%");
        }

        return $dataContent;
    }
}
