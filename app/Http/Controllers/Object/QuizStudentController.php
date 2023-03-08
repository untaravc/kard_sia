<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Models\Object\QuizSection;
use App\Models\Object\QuizStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizStudentController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = QuizStudent::with([
            'section',
            'student',
        ])->whereSectionId($request->section_id);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $request->merge([
            'quiz_qty' => QuizSection::whereSectionId($request->section_id)->count() ?? 0,
            'token'    => $this->create_token(),
        ]);

        QuizStudent::create($request->all());

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        QuizStudent::find($id)->update($request->all());

        return $this->response;
    }

    public function show($id)
    {
        $this->response['result'] = QuizStudent::find($id);

        return $this->response;
    }

    public function destroy($id)
    {
        QuizStudent::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'student_id' => 'required',
            'section_id' => 'required',
            'date_at'    => 'required',
        ]);
    }

    private function create_token(){
        $token = Str::random(20);
        if(QuizStudent::whereToken($token)->first()){
            $this->create_token();
        }
        return $token;
    }
}
