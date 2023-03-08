<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Document;
use App\Models\StudentReg;
use Illuminate\Http\Request;

class StudentRegController extends Controller
{
    use ImageThumbnailTrait;

    public function index(Request $request)
    {
        $dataContent = StudentReg::orderByDesc('date')
            ->with('documents');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        $response = collect($this->response);
        return $response->merge($dataContent);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        StudentReg::create($request->all());

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $reg = StudentReg::find($id);

        if ($reg) {
            $reg->update($request->all());
        }

        return $this->response;
    }

    private function validateData(Request $request)
    {
        $request->validate([
            'status' => 'required',
            'name'   => 'required',
        ]);
    }

    private function withFilter($dataContent, $request)
    {
        if($request->name){
            $dataContent = $dataContent->where('name', 'LIKE', '%'.$request->name.'%');
        }

        if($request->status){
            $dataContent = $dataContent->whereStatus($request->status);
        }

        return $dataContent;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'student_reg_id' => 'required',
            'name'           => 'required',
            'category'       => 'required',
            'file'           => 'required',
        ]);

        if ($request->hasFile('file')) {
            $link = $this->fileUploadProcessing($request, 'student_regs');

            if ($link != null) {
                Document::create([
                    "model"       => "student_reg",
                    "relation_id" => $request->student_reg_id,
                    "title"       => $request->name,
                    "category"    => $request->category,
                    "file"        => $link,
                ]);
            }
        }

        return $this->response;
    }
}
