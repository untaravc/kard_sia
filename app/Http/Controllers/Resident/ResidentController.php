<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\OpenStaseTask;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResidentController extends Controller
{
    public function index(){
        $id = Auth::guard('student')->user()->id;

        $data = Student::with([
            'staseLogs'=>function($q){$q->with(['stase']);},
            'staseLogsActive'=>function($q){
                $q->with([
                    'stase'=>function($q2){
                        $q2->with([
                            'staseTasks'=>function($q3){
                                $q3->with([
                                    'lecture',
                                    'openStaseTask'=>function($q4){
                                        $q4->with(['lecture', 'files'])
                                            ->whereStudentId(Auth::guard('student')->user()->id);
                                    },
                                    'openStaseTasks'=>function($q4){
                                        $q4->with(['lecture', 'files'])
                                            ->whereStudentId(Auth::guard('student')->user()->id);
                                    },
                                    'staseTaskLogs' => function($q4){
                                        $q4->with(['lecture'])
                                            ->whereStudentId(Auth::guard('student')->user()->id);
                                    }
                                ]);
                            },
                        ]);
                    },
                ]);
            },
            'today_presence',
        ])->leftJoin('student_profiles', 'student_profiles.student_id', '=', 'students.id')
            ->select('student_profiles.*', 'students.id', 'students.name', 'students.email', 'students.year')
            ->find($id);


        return $data;
    }

    public function uploadFile(Request $request){
        $this->validate($request, [
            'title' => 'required',
            'file' => 'required',
        ]);
        $open = OpenStaseTask::find($request->open_stase_task_id);
        $request->merge([
            'student_id' => $open->student_id,
        ]);
        if($request->file){
            $request->merge([
                'link' => $this->fileProcessing($request->file),
            ]);
        }
        File::create($request->except('file'));
    }

    public function fileProcessing($file){
        $name = 'task/'.time().'.' . explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];

        $base64_file = $file;
        @list($type, $file_data) = explode(';', $base64_file);
        @list(, $file_data) = explode(',', $file_data);
        Storage::disk('public')->put($name, base64_decode($file_data));

        return $name;
    }

    public function deleteFile($id){
        File::find($id)->delete();
    }
}
