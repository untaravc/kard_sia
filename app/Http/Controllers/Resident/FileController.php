<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FileController extends Controller {
    use ImageThumbnailTrait;

    public function index(Request $request) {
        $dataContent = File::myOwn()->with([
            'student',
        ]);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->orderByDesc('created_at')
            ->paginate(25);
        return $dataContent;
    }

    public function store(Request $request) {
        $this->validateData($request);
        File::create([
            'student_id' => Auth::guard('student')->id(),
            'title'      => $request->title,
            'type'       => $request->type,
            'desc'       => $request->desc,
            'link'       => $this->fileUploadProcessing($request),
        ]);

        return $this->response;
    }

    public function show($id) {

    }

    public function update(Request $request, $id) {
        $this->validateData($request);

        File::find($id)->update([
            'title'      => $request->title,
            'type'       => $request->type,
            'desc'       => $request->desc,
        ]);
        return $this->response;
    }

    public function destroy($id) {
        File::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request) {
        $this->validate($request, [
            "title" => 'required',
            "type"  => 'required',
        ]);
    }

    public function withFilter($dataContent, $request) {
        if ($request->title != null) {
            $dataContent = $dataContent->where('title', 'LIKE', "%" . $request->title . "%");
        }

        return $dataContent;
    }

    public function properties(){
        $file = new File();
        $data['type_list'] = $file->type_list();

        $this->response['data']     = $data;
        return $this->response;
    }
}
