<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Models\Object\Quiz;
use App\Models\Object\QuizSection;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Quiz::orderByDesc('id');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);

        $stats = [
            'stats' => [
                'draft'     => Quiz::whereStatus(100)->count(),
                'img_null'  => Quiz::whereStatus(110)->count(),
                'draft_img' => Quiz::whereStatus(111)->count(),
                'active'    => Quiz::whereStatus(200)->count(),
            ],
        ];

        $stat_collect = collect($stats);
        $data = $stat_collect->merge($dataContent);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        Quiz::create($request->all());

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Quiz::find($id)->update($request->all());

        return $this->response;
    }

    public function show($id)
    {
        $this->response['result'] = Quiz::find($id);

        return $this->response;
    }

    public function destroy($id)
    {
        Quiz::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
//            'category_id' => 'required',
            'question' => 'required',
            'option_1' => 'required',
            'option_2' => 'required',
//            'option_3'    => '',
//            'option_4'    => '',
//            'option_5'    => '',
            'answer'   => 'required',
            'score'    => 'required',
//            'note'        => '',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->question != null) {
            $dataContent = $dataContent->where('question', 'LIKE', '%' . $request->question . '%');
        }

        if ($request->category != null) {
            $dataContent = $dataContent->where('category', '=', $request->category);
        }

        if ($request->status != null) {
            $dataContent = $dataContent->where('status', '=', $request->status);
        }

        return $dataContent;
    }

    public function list(Request $request)
    {
        $quizzes = Quiz::orderBy('category')
            ->when($request->category, function ($q) use ($request) {
                $q->where('category', 'like', $request->category);
            })->when($request->name, function ($q) use ($request) {
                $q->where('question', 'LIKE', '%' . $request->name . '%');
            })
            ->whereIn('status', [100,200])
            ->limit(100)
            ->get();
        $selected = QuizSection::whereSectionId($request->section_id)->pluck('quiz_id');

        foreach ($quizzes as $quiz) {
            $quiz->setAttribute('selected', in_array($quiz->id, $selected->toArray()));
        }

        $this->response['result'] = $quizzes;
        return $this->response;
    }
}
