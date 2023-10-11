<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Document::with([
            'student',
        ])->whereNotNull('student_id')
            ->orderByDesc('created_at');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        $file = null;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();

            $filename        = str_replace(' ', '_', strtolower(pathinfo($filenameWithExt, PATHINFO_FILENAME)));
            $extension       = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $file            = $request->file('file')->storeAs('documents', $fileNameToStore, ['disk' => 'public']);
        }

        $document = Document::find($request->id);
        if($document){
            $document->update([
                'file'       => $file,
            ]);
        }

        return $this->response;
    }

    public function destroy($id)
    {
        $docs = Document::find($id);
        File::delete(public_path().'/storage/'.$docs->file);
        $docs->delete();
    }

    public function validateData($request){
        $this->validate($request, [
            "file"      => 'required',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->search($request->keyword, null, true);
        }

        if ($request->title != null){
            $dataContent = $dataContent->search($request->title, null, true);
        }

        if ($request->category != null){
            $dataContent = $dataContent->where('category', 'LIKE', '%'.$request->category.'%');
        }

        if ($request->type != null){
            $dataContent = $dataContent->whereType($request->type);
        }

        if ($request->lecture_id != null){
            $dataContent = $dataContent->whereLectureId($request->lecture_id);
        }

        if ($request->student != null){
            $dataContent = $dataContent->whereHas('student', function ($q) use ($request){
                $q->where('name', 'LIKE','%'. $request->student .'%');
            });
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
