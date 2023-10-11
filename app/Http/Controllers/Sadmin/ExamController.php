<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentExams;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Exam::with([
            'lecture',
            'stase',
            'stase_task',
        ])->orderByDesc('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $request->merge([
            'token' => Str::random(10),
        ]);

        Exam::create($request->all());

        return $this->response;
    }

    public function show($id)
    {
        $this->response['data'] = Exam::with([
            'lecture',
            'stase',
            'stase_task',
        ])->find($id);

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $exam = Exam::find($id);
        if ($exam->token == null) {
            $request->merge([
                'token' => Str::random(10),
            ]);
        }

        $exam->update($request->all());
    }

    public function destroy($id)
    {
        Exam::findOrFail($id)->delete();
        return 'success';
    }

    public function validateData($request)
    {
        $this->validate($request, [
            "name" => 'required',
            "link" => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', "%" . $request->name . "%");
        }

        return $dataContent;
    }

    public function exam_available_students(Request $request, $id)
    {
        $taken = StudentExams::whereExamId($id)->pluck('student_id');

        $students = Student::whereStatus('active')
            ->whereNotIn('id', $taken)
            ->when($request->name, function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            })
            ->limit(10)
            ->get();

        $this->response['data'] = $students;
        $this->response['count'] = Student::whereStatus('active')
            ->whereNotIn('id', $taken)->count();
        return $this->response;
    }

    public function exam_active_students(Request $request, $id)
    {
        $taken = StudentExams::whereExamId($id)
            ->whereStatus(1)
            ->pluck('student_id');

        $students = Student::whereStatus('active')
            ->whereIn('id', $taken)
            ->when($request->name, function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            })
            ->limit(10)
            ->get();

        $this->response['data'] = $students;
        $this->response['count'] = Student::whereStatus('active')
            ->whereIn('id', $taken)->count();
        return $this->response;
    }

    public function exam_done_students(Request $request, $id)
    {
        $taken = StudentExams::whereExamId($id)
            ->whereStatus(2)
            ->get();

        $students = Student::whereStatus('active')
            ->whereIn('id', $taken->pluck('student_id'))
            ->when($request->name, function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            })
            ->limit(10)
            ->get();

        foreach ($students as $student) {
            $student->setAttribute('score', $taken->where('student_id', $student->id)->first()['score']);
        }

        $this->response['data'] = $students;
        $this->response['count'] =  StudentExams::whereExamId($id)
            ->whereStatus(2)->count();
        return $this->response;
    }

    public function activate_student(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required',
            'exam_id'    => 'required',
        ]);

        $student_exam = StudentExams::whereStudentId($request->student_id)
            ->whereExamId($request->exam_id)
            ->first();

        if ($student_exam == null) {
            StudentExams::create([
                'student_id' => $request->student_id,
                'exam_id'    => $request->exam_id,
                'status'     => 1,
            ]);
        } else {
            $student_exam->update([
                'status' => 1,
            ]);
        }

        return $this->response;
    }

    public function cancel_student(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required',
            'exam_id'    => 'required',
        ]);

        $student_exam = StudentExams::whereStudentId($request->student_id)
            ->whereExamId($request->exam_id)
            ->first();

        if ($student_exam) {
            $student_exam->delete();
        }

        return $this->response;
    }

    public function finish_student(Request $request)
    {
        $this->validate($request, [
            'student_id' => 'required',
            'exam_id'    => 'required',
        ]);

        $student_exam = StudentExams::whereStudentId($request->student_id)
            ->whereStatus(1)
            ->whereExamId($request->exam_id)
            ->first();

        $student_exam->update([
            'status' => 2,
        ]);

        return $this->response;
    }

    public function add_score(Request $request)
    {
        $this->validate($request, [
            'exam_id'         => 'required',
            'student_id' => 'required',
            'score'      => 'required',
        ]);

        $student_exam = StudentExams::whereStudentId($request->student_id)
        ->whereExamId($request->exam_id)
        ->first();

        $student_exam->update([
            'score' => $request->score,
        ]);

        return $this->response;
    }
}
