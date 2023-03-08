<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Object\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    use ImageThumbnailTrait;

    public function upload(Request $request)
    {
        $link = $this->fileUploadProcessing($request, 'images', 'image');

        if ($link) {
            $image = Image::create([
                'model' => $request->model,
                'name'  => $request->name,
                'link'  => '/storage/' . $link,
            ]);

            $this->response['result'] = $image;
            return $this->response;
        }

        $this->response['status'] = false;
        return $this->response;
    }
}
