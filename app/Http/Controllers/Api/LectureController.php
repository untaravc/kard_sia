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
        $dataContent = Lecture::leftJoin('lecture_profiles', 'lecture_profiles.lecture_id', '=', 'lectures.id')
            ->select(
                'lectures.*',
                'lecture_profiles.code as code',
                'lecture_profiles.degree as degree',
                'lecture_profiles.pob as pob',
                'lecture_profiles.dob as dob',
                'lecture_profiles.phone as phone',
                'lecture_profiles.address as address',
                'lecture_profiles.image as image',
                'lecture_profiles.register_date as register_date'
            )
            ->orderBy('lectures.name');
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
        $this->saveProfile($lecture->id, $request);

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
        $this->saveProfile($lecture->id, $request);

        return response()->json([
            'success' => true,
            'text' => 'Update Lecture Success',
            'result' => $lecture,
        ]);
    }

    protected function saveProfile($lectureId, Request $request)
    {
        $fields = ['code', 'degree', 'pob', 'dob', 'phone', 'address', 'image', 'register_date'];
        if (!$request->hasAny($fields)) {
            return;
        }

        $data = collect($request->only($fields))
            ->map(function ($value) {
                return $value === '' ? null : $value;
            })
            ->toArray();

        LectureProfile::updateOrCreate(['lecture_id' => $lectureId], $data);
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

    public function list(Request $request)
    {
        $dataContent = Lecture::whereStatus(1);

        if ($request->study_program_code != null) {
            $code = $request->study_program_code;
            $dataContent = $dataContent->where(function ($q) use ($code) {
                $q->whereJsonContains('study_program_codes', $code)
                    ->orWhereNull('study_program_codes');
            });
        }

        $lectures = $dataContent->select('id', 'name')
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
            'number' => 'nullable|string|max:50',
            'email' => 'required|email',
            'password' => 'required_without:id',
            'name_alt' => 'nullable',
            'last_act' => 'nullable',
            'status' => 'nullable',
            'is_in_house' => 'nullable|boolean',
            'study_program_codes' => 'nullable|array',
            'code' => 'nullable|string',
            'degree' => 'nullable|string|max:100',
            'pob' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'register_date' => 'nullable|date',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('lectures.name', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('lectures.email', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('lectures.number', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->study_program_code != null) {
            $dataContent = $dataContent->whereJsonContains('lectures.study_program_codes', $request->study_program_code);
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

        if ($request->image) {
            if (str_starts_with($request->image, 'http')) {
                $pathName = $request->image;
            } elseif (strlen($request->image) > 100) {
                $pathName = $this->imageProcessing($request->image, 'lectures', false);
            }
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
