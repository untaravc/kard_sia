<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request) {
        $dataContent = Document::myOwnLecture()
            ->with([
                'lecture',
            ]);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->orderByDesc('created_at')
            ->paginate(25);
        return $dataContent;
    }

    public function store(Request $request) {
        $this->validateData($request);
        $lecture_id = Auth::guard('lecture')->id();

        $file = null;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();

            $filename        = str_replace(' ', '_', strtolower(pathinfo($filenameWithExt, PATHINFO_FILENAME)));
            $extension       = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $lecture_id . '-' . $filename . '_' . time() . '.' . $extension;
            $file            = $request->file('file')
                ->storeAs('lecture-documents/' . date('Ym'), $fileNameToStore, ['disk' => 'public']);
        }

        Document::create([
            'lecture_id' => $lecture_id,
            'type'       => $request->type,
            'category'   => $request->category,
            'title'      => $request->title,
            'desc'       => $request->desc,
            'file'       => $file,
        ]);

        return $this->response;
    }

    public function update(Request $request, $id) {
        $this->validateData($request);

        Document::find($id)->update([
            'title'    => $request->title,
            'desc'     => $request->desc,
            'category' => $request->category,
        ]);
        return $this->response;
    }

    public function destroy($id) {
        Document::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request) {
        $this->validate($request, [
            "title"    => 'required',
            "type"     => 'required',
            "category" => 'required',
        ]);
    }

    public function withFilter($dataContent, $request) {
        if ($request->keyword != null) {
            $dataContent = $dataContent->search($request->keyword);
        }

        return $dataContent;
    }

    public function properties() {
        $file              = new File();
        $data['type_list'] = $file->type_list();

        $this->response['data'] = $data;
        return $this->response;
    }

    public function document_categories(Request $request) {
        $send = [
            [
                'name' => 'certificate',
                'desc' => 'certificate',
            ],
        ];

        $req = [
            [
                'name' => 'cuti',
                'desc' => 'cuti',
            ],
            [
                'name' => 'isoman',
                'desc' => 'isoman',
            ],
        ];

        if ($request->type == 'request') {
            $this->response['data'] = $req;
        } else {
            $this->response['data'] = $send;
        }
        return $this->response;
    }
}
