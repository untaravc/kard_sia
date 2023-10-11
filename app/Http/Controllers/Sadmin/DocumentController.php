<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Document::with([
            'lecture',
        ])->whereNotNull('lecture_id')
            ->orderByDesc('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        if ($request->file){
            $name = $this->fileProcessing($request->file, $request->title);
            $request->merge(['file' => $name]);
        }

        Document::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if ($request->file && strlen($request->file) > 100 ){
            $name = $this->imageProcessing($request->file);
            $request->merge(['file' => $name]);
        }

        Document::find($id)->update($request->all());
    }

    public function destroy($id)
    {
        $docs = Document::find($id);
        File::delete(public_path().'/storage/'.$docs->file);
        $docs->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "lecture_id"          => 'required',
            "title"         => 'required',
            "category"         => 'required',
            "file"      => 'required_without:id',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->search($request->keyword, null, true);
        }

        if ($request->cat != null){
            $dataContent = $dataContent->whereCategory(strtolower($request->cat));
        }

        if ($request->lecture_id != null){
            $dataContent = $dataContent->whereLectureId($request->lecture_id);
        }

        return $dataContent;
    }

    public function fileProcessing($file, $title){
        $title = str_replace(' ', '_', strtolower($title));
        $name = 'file/' . time() .'.' . explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];

        $base64_file = $file;
        @list($type, $file_data) = explode(';', $base64_file);
        @list(, $file_data) = explode(',', $file_data);
        Storage::disk('public')->put($name, base64_decode($file_data));

        return $name;
    }

    public function loginLecture($id){
        $lecture = Lecture::find($id);

        if(Auth::guard('lecture')->check()){
            if(Auth::guard('lecture')->user()->id !== $id){
                Auth::guard('lecture')->logout();
            }
        }

        Auth::guard('lecture')->login($lecture);
        return redirect('/dosen');
    }
}
