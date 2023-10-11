<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Task::withCount('taskDetails')->orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        Task::create($request->all());

        return 'success';
    }

    public function show($id){
        return Task::find($id);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Task::find($id)->update($request->all());
    }

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
//            "desc"          => 'required',
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
