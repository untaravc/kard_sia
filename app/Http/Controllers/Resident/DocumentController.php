<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Document;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DocumentController extends Controller
{
    use ImageThumbnailTrait;

    public function index(Request $request)
    {
        $dataContent = Document::myOwn()
            ->with([
                'student',
            ]);
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent
            ->orderByDesc('created_at')
            ->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);
        $student_id = Auth::guard('student')->id();

        $file = null;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();

            $filename = str_replace(' ', '_', strtolower(pathinfo($filenameWithExt, PATHINFO_FILENAME)));
            $extension = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $student_id . '-' . $filename . '_' . time() . '.' . $extension;
            $file = $request->file('file')
                ->storeAs('documents/' . date('Ym'), $fileNameToStore, ['disk' => 'public']);
        }

        Document::create([
            'student_id' => $student_id,
            'type'       => $request->type,
            'category'   => $request->category,
            'title'      => $request->title,
            'desc'       => $request->desc,
            'date'       => $request->date,
            'file'       => $file,
        ]);

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Document::find($id)->update([
            'title'    => $request->title,
            'desc'     => $request->desc,
            'category' => $request->category,
            'date'     => $request->date,
        ]);
        return $this->response;
    }

    public function destroy($id)
    {
        Document::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            "title"    => 'required',
            "type"     => 'required',
            "category" => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->title != null) {
            $dataContent = $dataContent->where('title', 'LIKE', "%" . $request->title . "%");
        }

        return $dataContent;
    }

    public function properties()
    {
        $file = new File();
        $data['type_list'] = $file->type_list();

        $this->response['data'] = $data;
        return $this->response;
    }

    public function document_categories(Request $request)
    {
        $send = [
            [
                'name' => 'Sertifikat',
                'desc' => 'certificate',
                'note' => []
            ],
            [
                'name' => 'Ijazah',
                'desc' => 'ijazah',
                'note' => []
            ],
            [
                'name' => 'Surat Izin',
                'desc' => 'surat_izin',
                'note' => []
            ],
            [
                'name' => 'Surat Keterangan',
                'desc' => 'surat_keterangan',
                'note' => []
            ],
            [
                'name' => 'Prestasi PPDS',
                'desc' => 'prestasi_ppds',
                'note' => []
            ],
            [
                'name' => 'Case Report',
                'desc' => 'case_report',
                'note' => []
            ],
            [
                'name' => 'Karya Nasional Wajib',
                'desc' => 'karnas_wajib',
                'note' => [
                    'Judul makalah',
                    'Tempat presentasi (pertemuan ilmiah)',
                    'Tahun',
                    'Pembimbing Utama',
                ],
            ],
            [
                'name' => 'Karya Nasional Tambahan',
                'desc' => 'karnas_tambahan',
                'note' => [
                    'Judul makalah',
                    'Tempat presentasi (pertemuan ilmiah)',
                    'Tahun',
                    'Pembimbing Utama',
                ],
            ],
            [
                'name' => 'Pengabdian Masyarakat',
                'desc' => 'pengabdian_masyarakat',
                'note' => []
            ],
            [
                'name' => 'Lain lain',
                'desc' => 'lain_lain',
                'note' => []
            ],
        ];

        $req = [
            [
                'name' => 'Surat Izin Cuti',
                'desc' => 'cuti',
                'note' => []
            ],
            [
                'name' => 'Izin Isoman',
                'desc' => 'isoman',
                'note' => []
            ],
            [
                'name' => 'Surat Aktif',
                'desc' => 'surat_aktif',
                'note' => []
            ],
            [
                'name' => 'Surat Permintaan Undangan Ilmiah',
                'desc' => 'undangan_ilmiah',
                'note' => []
            ],
            [
                'name' => 'Surat permohonan stase',
                'desc' => 'permohonan_stase',
                'note' => []
            ],
            [
                'name' => 'Surat Permohonan KHS',
                'desc' => 'permohonan_khs',
                'note' => []
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
