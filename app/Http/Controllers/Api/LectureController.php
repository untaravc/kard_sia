<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImageThumbnailTrait;
use App\Models\Lecture;
use App\Models\LectureProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LectureController extends Controller
{
    use ImageThumbnailTrait;

    public function index(Request $request)
    {
        $dataContent = Lecture::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lectures Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'password' => Hash::make($request->password),
        ]);

        $this->validateData($request);

        $lecture = Lecture::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Lecture Success',
            'result' => $lecture,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        if ($request->password) {
            $request->merge([
                'password' => Hash::make($request->password),
            ]);
        }

        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $lecture->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Lecture Success',
            'result' => $lecture,
        ]);
    }

    public function show($id)
    {
        $lecture = Lecture::find($id);

        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lecture Success',
            'result' => $lecture,
        ]);
    }

    public function destroy($id)
    {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $lecture->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Lecture Success',
            'result' => null,
        ]);
    }

    public function list()
    {
        $lectures = Lecture::whereStatus(1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lecture List Success',
            'result' => $lectures,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required_without:id',
            'name_alt' => 'nullable',
            'last_act' => 'nullable',
            'status' => 'nullable',
            'is_in_house' => 'nullable|boolean',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }

    public function profile(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $lecture = Lecture::find($authId);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        $profile = LectureProfile::whereLectureId($authId)->first();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Lecture Profile Success',
            'result' => [
                'lecture' => $lecture,
                'profile' => $profile,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        if ($authType !== 'lecture') {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 403);
        }

        $this->validate($request, [
            'email' => 'nullable|email',
            'name' => 'nullable',
            'password' => 'nullable|confirmed',
        ]);

        $lecture = Lecture::find($authId);
        if (!$lecture) {
            return response()->json([
                'success' => false,
                'text' => 'Lecture not found',
                'result' => null,
            ], 404);
        }

        if ($request->password) {
            $lecture->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $lectureData = [];
        if ($request->has('email')) {
            $lectureData['email'] = $request->email;
        }
        if ($request->has('name')) {
            $lectureData['name'] = $request->name;
        }
        if (!empty($lectureData)) {
            $lecture->update($lectureData);
        }

        $profile = LectureProfile::whereLectureId($authId)->first();
        $pathName = $profile ? $profile->image : null;

        if ($request->image && strlen($request->image) > 100) {
            $pathName = $this->imageProcessing($request->image, 'lectures', false);
        }

        $profileData = [];
        if ($request->has('address')) {
            $profileData['address'] = $request->address;
        }
        if ($request->has('phone')) {
            $profileData['phone'] = $request->phone;
        }
        if ($pathName !== null) {
            $profileData['image'] = $pathName;
        }

        if ($profile) {
            if (!empty($profileData)) {
                $profile->update($profileData);
            }
        } else {
            $profile = LectureProfile::create(array_merge([
                'lecture_id' => $authId,
            ], $profileData));
        }

        return response()->json([
            'success' => true,
            'text' => 'Update Lecture Profile Success',
            'result' => [
                'lecture' => $lecture,
                'profile' => $profile,
            ],
        ]);
    }
}
