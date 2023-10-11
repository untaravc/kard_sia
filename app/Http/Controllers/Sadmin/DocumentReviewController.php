<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Document::where('category', 'pengabdian_masyarakat')->with('student');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);
        return $dataContent;
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->search($request->keyword, null, true);
        }

        return $dataContent;
    }

    public function add_comments(Request $request, $id)
    {
        Document::find($id)->update([
            "comment" => $request->comment
        ]);

        return $this->response;
    }

}
