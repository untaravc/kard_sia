<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\StaseTaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LectureController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Lecture::withCount([
            'StaseTaskLogs',
            'activity_lectures',
            'open_stase_task',
        ]);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->orderBy('status')
            ->orderBy('name')
            ->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $request->merge([
            'password'     => Hash::make($request->password)
        ]);
//        $request = $this->parseStatus($request);

        $this->validateData($request);

        if ($request->image){
            $name = $this->imageProcessing($request->image);
            $request->merge(['image' => $name]);
        }

        Lecture::create($request->all());

        return 'success';
    }

    public function show($id){
        return Lecture::with([
            'lectureProfile',
        ])->find($id);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if($request->password){
            $request->merge([
                'password'     => Hash::make($request->password)
            ]);
        }

        if ($request->image && strlen($request->image) > 100 ){
            $name = $this->imageProcessing($request->image);
            $request->merge(['image' => $name]);
        }

        Lecture::find($id)->update($request->all());
    }

    public function destroy($id)
    {
        $dataContent = Lecture::findOrFail($id)->update([
            'deleted_at' => now()
        ]);
        return 'success';
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
            "email"         => 'required|email',
//            "image"         => 'required',
            "password"      => 'required_without:id',
        ],[
//            'name.required'     => 'Nama harus diisi',
//            'address.required'  => 'Alamat harus diisi',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->name != null){
            $dataContent = $dataContent->where('name', 'LIKE', "%".$request->name."%");
        }

        return $dataContent;
    }

    public function imageProcessing($img){
        $name = 'driver/'.time().'.' . explode('/', explode(':', substr($img, 0, strpos($img, ';')))[1])[1];

        $base64_image = $img;
        @list($type, $file_data) = explode(';', $base64_image);
        @list(, $file_data) = explode(',', $file_data);
        Storage::disk('local')->put('public/'.$name, base64_decode($file_data));

        return $name;
    }

    public function loginLecture($id){
        $lecture = Lecture::find($id);

        if(Auth::guard('lecture')->check()){
            if(Auth::guard('lecture')->user()->id !== $id){
                Auth::guard('lecture')->logout();
            }
        }

        Auth::guard('lecture')->login($lecture);
        return redirect('/dosen');
    }

    public function lectureScoring(Request $request, $id){
        return StaseTaskLog::with([
            'student',
            'stase',
            'task'
        ])->whereLectureId($id)
            ->orderByDesc('created_at')->paginate(10);
    }

    public function list(){
        $data = Lecture::whereStatus(1)
            ->orderBy('name')
            ->get();

        $this->response['result'] = $data;
        return $this->response;
    }
}
