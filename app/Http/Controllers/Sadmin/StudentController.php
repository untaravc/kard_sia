<?php

namespace App\Http\Controllers\Sadmin;

use App\Exports\ResidentScoresExport;
use App\Http\Controllers\Controller;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTask;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller {
    public function index(Request $request) {
        $dataContent = Student::with([
            'staseLogsActive' => function ($q) {
                $q->with('stase');
            },
            'today_presence',
        ])->leftJoin('student_profiles', 'student_profiles.student_id', 'students.id')
            ->select('students.*', 'student_profiles.code');
//            ->withCount(['open_stase_task', 'today_activity', 'studentLogPending']);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->orderBy('status')
            ->orderBy('year')
            ->orderBy('name')
            ->paginate(10);
        return $dataContent;
    }

    public function store(Request $request) {
        $request->merge([
            'password' => Hash::make($request->password)
        ]);

        $this->validateData($request);

        if ($request->image) {
            $name = $this->imageProcessing($request->image);
            $request->merge(['image' => $name]);
        }

        $student = Student::create($request->except('code'));

        StudentProfile::create([
            'student_id' => $student->id,
            'code' => $request->code,
        ]);

        return 'success';
    }

    public function show($id) {
        if ($id == 'undefined') {
            $id = Auth::guard('student')->id();
        }
        return Student::with([
            'studentProfile',
            'staseLogs'       => function ($q) {
                $q->with(['stase'])->orderBy('start_date');
            },
            'staseLogsActive' => function ($q) {
                $q->with([
                    'stase' => function ($q2) {
                        $q2->with([
                            'staseTasks' => function ($q3) {
                                $q3->with(['lecture', 'task']);
                            },
                        ]);
                    },
                ]);
            },
        ])->find($id);
    }

    public function profile($id) {

    }

    public function score1(Request $request, $id) {
        if ($id == 'undefined') {
            $id = Auth::guard('student')->user()->id;
        }
        if ($request->keyword !== null) {
            return Student::with([
                'staseLogs' => function ($q) use ($request) {
                    $q->with([
                        'stase',
                        'staseTaskLogs' => function ($q2) {
                            $q2->with([
                                'staseTask',
                                'lecture',
                                'staseTaskLogPoint' => function ($q3) {
                                    $q3->with(['taskDetail']);
                                },
                            ]);
                        },
                    ])->whereHas('stase', function ($q4) use ($request) {
                        $q4->where('name', 'LIKE', '%' . $request->keyword . '%');
                    });
                },
            ])->find($id);
        }

        return Student::with([
            'staseLogs' => function ($q) {
                $q->with([
                    'stase',
                    'staseTaskLogs' => function ($q2) {
                        $q2->with([
                            'staseTask',
                            'lecture',
                            'files',
                            'staseTaskLogPoint' => function ($q3) {
                                $q3->with(['taskDetail']);
                            },
                        ]);
                    },
                ]);
            },
        ])->find($id);
    }

    public function score(Request $request, $id) {
        $student_id = $id == 'undefined' ? Auth::guard('student')->user()->id : $id;

        $stase_log_id = $request->stase_log_id ?? StaseLog::whereStudentId($student_id)
                                                      ->whereStatus('ongoing')
                                                      ->first()['id'];
        if (!$stase_log_id) {
            return [];
        }
        $group = StaseTaskLog::whereStudentId($student_id)
            ->whereStaseLogId($stase_log_id)
            ->groupBy('stase_task_id')
            ->with([
                'staseTask',
                'stase',
                'files',
            ])
            ->get();

        $data = StaseTaskLog::whereStudentId($student_id)
            ->whereStaseLogId($stase_log_id)
            ->with([
                'lecture',
                'staseTaskLogPoint' => function ($q2) {
                    $q2->with(['taskDetail']);
                },
            ])
            ->get();

        foreach ($group as $item) {
            $item->setAttribute('scores', $data->where('stase_task_id', $item['stase_task_id'])->flatten());
        }

        return $group;
    }

    public function addScore(Request $request) {
        $stase_task_log = StaseTaskLog::find($request->id);
        $stase_task_log->update([
            'lecture_id'    => $request->lecture_id,
            'date'          => $request->date,
            'point_average' => $request->point_average,
            'admin'         => true,
            'status'        => 'publish'
        ]);
    }

    public function resetScore($stase_task_log_id) {
        StaseTaskLog::find($stase_task_log_id)->update([
            'lecture_id'    => null,
            'date'          => null,
            'point_average' => null,
            'admin'         => false,
        ]);
    }

    public function update(Request $request, $id) {
        $this->validateData($request);
        if ($request->password) {
            $request->merge([
                'password' => Hash::make($request->password)
            ]);
        }

        if ($request->image && strlen($request->image) > 100) {
            $name = $this->imageProcessing($request->image);
            $request->merge(['image' => $name]);
        }

        $student = Student::find($id);
        $student->update($request->except('code'));

        $profiles = StudentProfile::whereStudentId($id)->first();
        if($profiles){
            $profiles->update([
               'code'   => $request->code,
            ]);
        }else{
            StudentProfile::create([
               'student_id' => $student->id,
               'code' => $request->code,
            ]);
        }
    }

    public function destroy($id) {
        $dataContent = Student::findOrFail($id)->update([
            'deleted_at' => now()
        ]);
        return 'success';
    }

    public function validateData($request) {
        $this->validate($request, [
            "name"     => 'required',
            "email"    => 'required|email',
            //            "image"         => 'required',
            "password" => 'required_without:id',
        ], [
//            'name.required'     => 'Nama harus diisi',
//            'address.required'  => 'Alamat harus diisi',
        ]);
    }

    public function withFilter($dataContent, $request) {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', "%" . $request->name . "%");
        }
        return $dataContent;
    }

    public function imageProcessing($img) {
        $name = 'driver/' . time() . '.' . explode('/', explode(':', substr($img, 0, strpos($img, ';')))[1])[1];

        $base64_image = $img;
        @list($type, $file_data) = explode(';', $base64_image);
        @list(, $file_data) = explode(',', $file_data);
        Storage::disk('local')->put('public/' . $name, base64_decode($file_data));

        return $name;
    }

    public function loginStudent($id) {
        $student = Student::find($id);
        if (Auth::guard('student')->check()) {
            if (Auth::guard('student')->user()->id !== $id) {
                Auth::guard('student')->logout();
            }
        }

        Auth::guard('student')->login($student);
        return redirect('/resident');
    }

    public function score_export(Request $request) {
        $logs = StaseTaskLog::with([
            'student',
            'lecture',
            'stase',
        ])->orderBy('stase_task_id')
            ->whereStudentId($request->student_id)
            ->when($request->status, function ($q) use ($request) {
                $q->where('point_average', '>', 0);
            })->when($request->has_lecture == true, function ($q) use ($request) {
                $q->where('lecture_id', '>', 0);
            })->get();

        $stase_task = StaseTask::get();


        for ($s = 0; $s < count($stase_task); $s++) {
            $stase_task[$s]['logs'] = $logs
                ->where('stase_task_id', $stase_task[$s]['id'])
                ->flatten();
        }

        $stase = Stase::orderBy('desc')->get();

        for ($i = 0; $i < count($stase); $i++) {
            $stase[$i]['task'] = $stase_task->where('stase_id', $stase[$i]['id'])->flatten();
        }

        $data = [
            [
                'tahap' => 'Tahap 1',
                'stase' => $stase->where('desc', 'tahap_1')->flatten(),
            ],
            [
                'tahap' => 'Tahap 2',
                'stase' => $stase->where('desc', 'tahap_2')->flatten(),
            ],
            [
                'tahap' => 'Tahap 3',
                'stase' => $stase->where('desc', 'tahap_3')->flatten(),
            ],
        ];

        $content = [
            'status'  => true,
            'data'    => $data,
            'student' => Student::find($request->student_id),
        ];

        $student = Student::find($request->student_id);

        return Excel::download(new ResidentScoresExport($content), 'Penilaian ' . $student->name . '.xls');

    }

    public function list(){
        $students = Student::whereStatus(1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $this->response['result'] = $students;
        return $this->response;
    }
}
