<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sadmin\DownloadController;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\FormOption;
use App\Models\Stase;
use App\Models\StaseLog;
use App\Models\StaseTaskLog;
use App\Models\Student;
use App\Models\StudentLog;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentLogController extends Controller
{
    use ImageThumbnailTrait;

    public function index(Request $request)
    {
        $dataContent = StudentLog::studentHas()
            ->whereStaseId($request->stase_id)
            ->with([
                'lecture',
            ]);

        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(20);

        $collect = collect($this->response);

        return $collect->merge($dataContent);
    }

    public function store(Request $request)
    {
        $request->merge([
            'student_id' => Auth::guard('student')->id(),
            'status'     => 0,
            'date'       => $request->date ?? date('Y-m-d')
        ]);

        $this->validateData($request);

        if ($request->file != null) {
            $name = $this->fileUploadProcessing($request, 'logbooks', 'file');
            $request->merge(['photo' => $name]);
        }
        StudentLog::create($request->except('file'));

        return $this->response;
    }

    public function show($id, $json = true)
    {
        $group = StudentLog::studentHas()
            ->whereStaseId($id)
            ->when($json, function ($q) {
                $q->orderByDesc('date');
            })->when(!$json, function ($q) {
                $q->orderBy('date');
            })->with([
                'lecture',
            ])->get();

        $form_data = FormOption::whereRelationId($id)
            ->whereType('stase-logbook')
            ->get();

        $categories = FormOption::whereRelationId($id)
            ->whereType('logbook-cat')
            ->get();

        foreach ($form_data as $data) {
            $data->setAttribute('logbook', $group->where('type', $data->value)->flatten());
        }
//        return $form_data;
        $result = [];
        foreach ($form_data as $data) {
            if (count($data['logbook']) > 0 || $data['status'] == 1) {
                $result[] = $data;
            }
        }

        $this->response['result'] = $result;
        $this->response['categories'] = $categories;
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'student_id' => 'required',
            'stase_id'   => 'required',
            'type'       => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request) {

        }

        return $dataContent;
    }

    public function update(Request $request, $id)
    {
        $log = StudentLog::studentHas()->find($id);

        if ($log == null) {
            $this->res['text'] = 'data tidak ditemukan';
            return $this->res;
        }

        $request->merge([
            'student_id' => Auth::guard('student')->id(),
            'status'     => 0,
            'date'       => $request->date ?? date('Y-m-d')
        ]);

        $this->validateData($request);
        if ($request->file != null) {
            $name = $this->fileUploadProcessing($request, 'logbooks', 'file');
            $request->merge(['photo' => $name]);
        }

        $log->update($request->except('file'));

        return $this->response;
    }

    public function delete($id)
    {
        $log = StudentLog::studentHas()->find($id);
        if ($log == null) {
            $this->res['text'] = 'data tidak ditemukan';
            return $this->res;
        }

        $log->delete();

        return $this->response;
    }

    public function log_category($id)
    {
        $check_stase = StaseTaskLog::myOwn()
            ->whereStaseId($id)
            ->get();
//        if(count($check_stase) < 1){
//            $this->res['text'] = 'no stase';
//            return $this->res;
//        }

        $options = FormOption::whereRelationId($id)
            ->whereType('stase-logbook')
            ->get();

        $this->response['data'] = $options;
        return $this->response;
    }

    public function staseList(Request $request)
    {
        $id = Auth::guard('student')->id();
        $stase = StaseLog::with([
            'stase'
        ])->whereHas('stase')
            ->whereStudentId($id)
            ->orderByDesc('created_at')
            ->when($request->name, function ($q) use ($request) {
                $q->whereHas('stase', function ($q2) use ($request) {
                    $q2->where('name', 'LIKE', '%' . $request->name . '%');
                });
            })
            ->get();

        $stase_ids = $stase->pluck('stase_id');

        $form_options = FormOption::whereIn('relation_id', $stase_ids)->get();

        foreach ($stase as $st) {
            $st->setAttribute('has_log', count($form_options->where('relation_id', $st->stase_id)));
        }

        return $stase;
    }

    public function destroy($id)
    {
        $log = StudentLog::studentHas()
            ->find($id);
        if ($log != null) {
            $log->delete();
            return $this->response;
        }

        return $this->res;
    }

    public function pdf($id)
    {
        $data = $this->show($id, false);
        $student = Auth::guard('student')->user();
        $stase = Stase::find($id);
//        $pdf = PDF::loadView('templates.pdf.logbook', [
//            'data'    => $data['result'],
//            'student' => $student,
//            'stase'   => $stase
//        ])
//            ->setPaper('a4');

        $name = 'Logbook - ' . $student['name'] . ' - ' . $stase['name'];
        return view('templates.pdf.logbook', [
            'data'    => $data['result'],
            'student' => $student,
            'stase'   => $stase,
            'name'    => $name,
        ]);
    }

    public function create_bulk(Request $request)
    {
        $request->merge([
            'student_id' => Auth::guard('student')->id(),
            'status'     => 0,
            'date'       => $request->date ?? date('Y-m-d')
        ]);

        $this->validateData($request);

        if (count($request->data) > 0) {
            foreach ($request->data as $datum) {
                StudentLog::create([
                    'student_id' => $request->student_id,
                    'status'     => $request->status,
                    'stase_id'   => $request->stase_id,
                    'type'       => $request->type,
                    'lecture_id' => $request->lecture_id,
                    'date'       => $request->date,
                    'category'   => $request->category,
                    'field_1'    => $datum['field_1'],
                    'field_2'    => $datum['field_2'],
                    'field_3'    => $datum['field_3'],
                    'field_4'    => $datum['field_4'],
                ]);
            }
        }

        return $this->response;
    }

    public function identity($id = null)
    {
        $student = $id ?? Auth::guard('student')->user();
        $student_id = $id ?? $student->id;
        $data = Student::with([
            'studentProfile' => function ($q) {
                $q->with('lecture');
            }
        ])->find($student_id);
        $file = public_path('storage/') . $data->studentProfile->image;
        $img = base64_encode(file_get_contents($file));

//        return view('templates.pdf.identity', compact('data', 'img'));

        $pdf = PDF::loadView('templates.pdf.identity', compact('data', 'img'))
            ->setPaper('a4');

        return $pdf->download('Identitas - ' . $student['name'] . '.pdf');
    }

    public function compile_pdf(Request $request)
    {
        $auth = Auth::guard('student')->user();
        $data['student'] = Student::with('studentProfile')->find($request->student_id ?? $auth['id']);

        $stases = Stase::whereIn('desc', ['tahap_1', 'tahap_2', 'tahap_3'])
            ->select('desc', 'name', 'id')
//            ->whereIn('id', [1])
            ->orderByRaw("FIELD(id, 26,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,25,21,22,23,31,33,36,20,24)")
            ->get();

        $stase_log = StaseLog::whereStudentId($data['student']['id'])->get();

        foreach ($stases as $stase) {
            $stase['stase_log'] = $stase_log->where('stase_id', $stase['id'])->first();
            $stase['log_book'] = $this->show($stase->id, false)['result'];
        }

        $student_profile = $data['student']['studentProfile'] ? $data['student']['studentProfile'] : null;
        $photo = $student_profile && $student_profile['image'] ? $student_profile['image'] : null;

        $img = '';
        if ($photo) {
            $file = public_path("/storage/" . $photo);
            $img = base64_encode(file_get_contents($file)) ?? null;
        }

        $pdf = PDF::loadView('templates.logbook.layout', [
            'photo'   => $img ?? null,
            'stases'  => $stases,
            'student' => $data['student']
        ]);

        return $pdf->download('Logbook ' . $data['student']['name'] . '-'. date('d M y') . '.pdf');

        return view('templates.logbook.layout', [
            'photo'   => $img ?? null,
            'stases'  => $stases,
            'student' => $data['student']
        ]);
    }

    private function student_logs($stase_id, $student_id)
    {
        $data['stase'] = Stase::find($stase_id);
        $data['stase_log'] = StaseLog::whereStaseId($stase_id)->whereStudentId($student_id)->first();

        $data['student_logs'] = StudentLog::whereStaseId($stase_id)
            ->whereStudentId($student_id)
            ->orderBy('date')
            ->leftJoin('lectures', 'lectures.id', '=', 'student_logs.lecture_id')
            ->select(
                'student_logs.*',
                'lectures.name_alt as lecture_name'
            )->get();

        return $data;
    }
}
