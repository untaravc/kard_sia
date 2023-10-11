<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTrait;
use App\Models\Activity;
use App\Models\AppLog;
use App\Models\FormOption;
use App\Models\Lecture;
use App\Models\OpenStaseTask;
use App\Models\StaseLog;
use App\Models\Student;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class HomeController extends Controller
{
    use BaseTrait;

    public function test(Request $request) {
        $form_options = FormOption::with('stase')
            ->whereType('stase-logbook')
            ->orderBy('relation_id')->get();

        return view('home.logbook_form_option', ['form_options'=>$form_options]);
    }

    public function test_view() {
        return view('mails.email-confirmation');
    }

    public function page() {
        return view('presensi');
    }

    public function upload(Request $request) {
        $this->validate($request, [
            'photo' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $file = $request->file('photo');
        $filenameWithExt = $file->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        $filename = preg_replace("/\s+/", '-', $filename);
        $extension = $file->getClientOriginalExtension();
        $fileNameToStore = time() .'_'.$filename.'.'.$extension;
        $resize = Image::make($file)
            ->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        })
            ->encode('jpg');

        $save = Storage::disk('public')->put("/images/{$fileNameToStore}", $resize->__toString());

        if($save) {
            return 'success';
        }
        return 'fail';
    }

    public function agenda() {
        return view('resident.agenda');
    }

    public function make_token() {
        $lectures = Lecture::whereLinkToken(null)->get();
        foreach ($lectures as $lecture){
            $token =  $this->generateRandomString(12);
            $lecture->update([
                'link_token' => $token
            ]);
        }

        $students = Student::whereLinkToken(null)->get();
        foreach ($students as $student){
            $token =  $this->generateRandomString(15);
            $student->update([
                'link_token' => $token
            ]);
        }

        $opens = OpenStaseTask::whereLinkToken(null)->get();
        foreach ($opens as $open){
            $token =  $this->generateRandomString(17);
            $open->update([
                'link_token' => $token
            ]);
        }
    }

    public function test_pdf() {
        return view('templates.pdf');
        $pdf = PDF::loadView('templates.pdf')
            ->setPaper('a4');
        return $pdf->download('test.pdf');
    }
}
