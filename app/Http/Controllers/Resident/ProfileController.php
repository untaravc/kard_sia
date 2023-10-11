<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller {
    use ImageThumbnailTrait;

    public function user() {
        $student_id = Auth::guard('student')->user()->id;
        $student    = Student::with('studentProfile')->find($student_id);

        return $student;
    }

    public function store(Request $request) {
        $profile = StudentProfile::create($request->all());
        return $profile;
    }

    public function update(Request $request, $id) {
        $profile = StudentProfile::find($id)->update($request->all());
    }

    public function updateProfile(Request $request) {
        $auth_id = Auth::guard('student')->id();

        $this->validate($request, [
            'password'      => 'confirmed',
            'email'         => 'required|email',
//            'image'         => 'required',
//            'name'          => 'required',
//            'initial'       => 'required',
//            'code'          => 'required',
//            'city'          => 'required',
//            'postal_code'   => 'required',
//            'address'       => 'required',
//            'phone'         => 'required|numeric',
//            'pob'           => 'required',
//            'dob'           => 'required',
//            'undergraduate' => 'required',
//            'graduated_at'  => 'required',
//            'degree'        => 'required',
//            'lecture_id'    => 'required',
        ]);

        if ($request->password != null) {
            Student::find($request->id)->update([
                'password' => Hash::make($request->password),
            ]);
        }

        Student::find($auth_id)->update([
            'email' => $request->email,
            'name'  => $request->name,
        ]);

        $profile   = StudentProfile::whereStudentId($auth_id)->first();
        $path_name = $profile->image ?? null;

        if ($request->image && strlen($request->image) > 100) {
            $path_name = $this->imageProcessing($request->image, 'students', false);
        }

        if ($profile != null) {
            $profile->update([
                'image'         => $path_name,
                'address'       => $request->address,
                'phone'         => $request->phone,
                'initial'       => $request->initial,
                'code'          => $request->code,
                'city'          => $request->city,
                'postal_code'   => $request->postal_code,
                'pob'           => $request->pob,
                'dob'           => $request->dob,
                'undergraduate' => $request->undergraduate,
                'graduated_at'  => $request->graduated_at,
                'degree'        => $request->degree,
                'lecture_id'    => $request->lecture_id,
            ]);
        } else {
            StudentProfile::create([
                'student_id'    => $auth_id,
                'image'         => $path_name,
                'address'       => $request->address,
                'phone'         => $request->phone,
                'initial'       => $request->initial,
                'code'          => $request->code,
                'city'          => $request->city,
                'postal_code'   => $request->postal_code,
                'pob'           => $request->pob,
                'dob'           => $request->dob,
                'undergraduate' => $request->undergraduate,
                'graduated_at'  => $request->graduated_at,
                'degree'        => $request->degree,
                'lecture_id'    => $request->lecture_id,
            ]);
        }

    }
}
