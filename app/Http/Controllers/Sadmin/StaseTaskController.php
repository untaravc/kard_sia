<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\StaseTask;
use Illuminate\Http\Request;

class StaseTaskController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = StaseTask::orderByDesc('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);
        StaseTask::create($request->all());
    }

    public function getStaseLog(){
        StaseTask::whereStudentId()->get();
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if($request->status == 'finish'){
            $request->merge([
                'end_date'    => now(),
                'duration'    => date_diff(date_create($request->start_date), now())->format('%R%a hari')
            ]);
        }
        StaseTask::find($id)->update($request->all());
    }

    public function destroy($id)
    {
        StaseTask::findOrFail($id)->delete();
    }

    public function validateData($request){
        $this->validate($request, [
//            "lecture_id"       => 'required',
            "task_id"         => 'required',
        ],[
//            'name.required'     => 'Nama harus diisi',
//            'address.required'  => 'Alamat harus diisi',
        ]);
    }
}
