<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Models\Object\QuizSection;
use App\Models\Object\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Section::orderByDesc('id')->with([
            'lecture',
        ]);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        Section::create($request->all());

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Section::find($id)->update($request->all());

        return $this->response;
    }

    public function show($id)
    {
        $this->response['result'] = Section::find($id);
        $this->response['quizzes'] = QuizSection::whereSectionId($id)
            ->with([
                'quiz',
            ])
            ->orderBy('order')
            ->get();

        return $this->response;
    }

    public function destroy($id)
    {
        Section::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->name . '%');
        }

        return $dataContent;
    }

    public function sections_quiz(Request $request, $id)
    {
        $quiz_ids = $request->quiz_ids;
        // delete unselected
        QuizSection::whereSectionId($id)
            ->whereNotIn('quiz_id', $quiz_ids)
            ->delete();

        foreach ($quiz_ids as $quiz_id) {
            $quiz_section = QuizSection::whereSectionId($id)
                ->whereQuizId($quiz_id)
                ->first();

            $max_order = QuizSection::whereSectionId($id)->max('order');
            if (!$quiz_section) {
                QuizSection::create([
                    'section_id' => $id,
                    'quiz_id'    => $quiz_id,
                    'order'      => $max_order + 1,
                ]);
            }
        }

        $this->reorder($id);
        return $this->response;
    }

    private function reorder($section_id)
    {
        $quiz_sections = QuizSection::whereSectionId($section_id)
            ->orderBy('order')
            ->get();

        foreach ($quiz_sections as $key => $quiz_section) {
            $quiz_section->update([
                'order' => $key + 1
            ]);
        }
    }

    public function ordering_quiz(Request $request)
    {
        if ($request->type == 'up') {
            $quiz_section = QuizSection::whereSectionId($request->section_id)
                ->whereIn('order', [$request->order, ($request->order - 1)])
                ->get();

            $up_id = $quiz_section->where('order', $request->order)->first()['id'];
            $down_id = $quiz_section->where('order', $request->order - 1)->first()['id'];

            QuizSection::find($up_id)->update([
                'order' => ($request->order - 1)
            ]);

            QuizSection::find($down_id)->update([
                'order' => ($request->order)
            ]);
        }

        if ($request->type == 'down') {
            $quiz_section = QuizSection::whereSectionId($request->section_id)
                ->whereIn('order', [$request->order, ($request->order + 1)])
                ->get();

            $down_id = $quiz_section->where('order', $request->order)->first()['id'];
            $up_id = $quiz_section->where('order', $request->order + 1)->first()['id'];

            QuizSection::find($down_id)->update([
                'order' => ($request->order + 1)
            ]);

            QuizSection::find($up_id)->update([
                'order' => ($request->order)
            ]);
        }
    }

    public function delete_quiz(Request $request){
        QuizSection::find($request->id)->delete();
        return $this->response;
    }
}
