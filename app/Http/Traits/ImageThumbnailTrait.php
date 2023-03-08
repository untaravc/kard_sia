<?php


namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImageThumbnailTrait {

    /**
     * Untuk membuat thumbnail gambar base64
     * @param $name string folder name
     * @param $file_data
     */
    private function makeThumbnail($name, $file_data) {
        $image = Image::make(base64_decode($file_data))
            ->resize(120, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg',80);
        Storage::disk('local')->put( 'public/thumbnails/'.$name, $image);
    }

    /**
     * Untuk upload gambar berupa base64
     * @param $img string base64 file
     * @param $folder_name string folder name to contain the files
     * @param false $make_thumbnail boolean
     * @return string image link
     */
    public function imageProcessing(String $img, String $folder_name, bool $make_thumbnail = false){
        $name = $folder_name.'/' . date('Ym') .'-'.time().'.' . explode('/', explode(':', substr($img, 0, strpos($img, ';')))[1])[1];

        $base64_image = $img;
        @list($type, $file_data) = explode(';', $base64_image);
        @list(, $file_data) = explode(',', $file_data);
        $image = Image::make(base64_decode($file_data))
            ->resize(720, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg',80);

        Storage::disk('public')->put( $name, $image);

        if($make_thumbnail){
            $this->makeThumbnail($name, $file_data);
        }
        return $name;
    }

    public function fileUploadProcessing($request, $folder_name = 'files', String $file_container = 'file'){
        $attachment = null;
        if ($request->hasFile($file_container)) {
            $filenameWithExt = $request->file($file_container)->getClientOriginalName();

            $filename        = str_replace(' ', '_', strtolower(pathinfo($filenameWithExt, PATHINFO_FILENAME)));
            $extension       = $request->file($file_container)->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $attachment      = $request->file($file_container)->storeAs($folder_name, $fileNameToStore);
        }

        return $attachment;
    }
}
