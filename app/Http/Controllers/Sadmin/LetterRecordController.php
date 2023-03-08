<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LetterRecordController extends Controller
{
    use ImageThumbnailTrait;

    public function index(Request $request)
    {
        $dataContent = Letter::with([
            'students',
            'lectures',
            'user',
        ])->orderByDesc('created_at');

        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $file_path = $this->fileUploadProcessing($request, 'letters');
        Letter::create([
            "number"   => $request->number,
            "user_id"  => Auth::guard()->id(),
            "name"     => $request->name,
            "desc"     => $request->desc,
            "category" => $request->category,
            "file"     => $file_path,
            "link"     => $request->link,
            "status"   => $request->status,
            "date"     => $request->date,
        ]);

        return $this->response;
    }

    public function update(Request $request)
    {
        $this->validateData($request);

        $document = Letter::find($request->id);

        if ($document) {
            $document->update([
                "number"   => $request->number,
                "name"     => $request->name,
                "desc"     => $request->desc,
                "category" => $request->category,
                "link"     => $request->link,
                "status"   => $request->status,
                "date"     => $request->date,
            ]);
        }

        if ($request->hasFile('file')) {
            $file_path = $this->fileUploadProcessing($request, 'letters');
            $request->merge(['link', $file_path]);

            if ($document) {
                $document->update([
                    'file' => $file_path,
                ]);
            }
        }

        return $this->response;
    }

    public function destroy($id)
    {
        $letter = Letter::find($id);
        $auth_id = Auth::guard()->id();

        if($letter->user_id != $auth_id ){
            $this->response['status'] = false;
            $this->response['message'] = 'File bukan punya anda.';
            return $this->response;
        }

        File::delete(public_path() . '/storage/' . $letter->file);
        $letter->delete();

        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            "number"   => 'required',
            "name"     => 'required',
            "category" => 'required',
            "status"   => 'required',
            "date"     => 'required',
        ], [
            "number.required"   => 'Nomor surat harus diisi',
            "name.required"     => 'Nama surat harus diisi',
            "category.required" => 'Kategori harus diisi',
            "status.required"   => 'Status harus diisi',
            "date.required"     => 'Tanggal harus diisi',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->category != null) {
            $dataContent = $dataContent->where('category', 'LIKE', '%' . $request->category . '%');
        }

        if ($request->number != null) {
            $dataContent = $dataContent->where('number', 'LIKE', '%' . $request->number . '%');
        }

        if ($request->link != null) {
            $dataContent = $dataContent->where('link', 'LIKE', '%' . $request->link . '%');
        }

        return $dataContent;
    }
}
