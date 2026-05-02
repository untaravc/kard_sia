<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationDetail;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationStudentController extends Controller
{
    private function getAuthRegistrationOrFail(Request $request): array
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = data_get($payload, 'auth_type');
        $authId = data_get($payload, 'auth_id');

        if ($authType !== 'registration' || empty($authId)) {
            return [null, response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401)];
        }

        $registration = Registration::where('id', $authId)->first();
        if (! $registration) {
            return [null, response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404)];
        }

        return [$registration, null];
    }

    private function buildToken(array $baseClaims, array $overrides = [])
    {
        $issuedAt = new \DateTimeImmutable();
        $issuedAtTimestamp = $issuedAt->getTimestamp();
        $expiresAtTimestamp = $issuedAt->modify('+1 year')->getTimestamp();

        $claims = array_merge([
            'iss' => config('app.url'),
            'iat' => $issuedAtTimestamp,
            'nbf' => $issuedAtTimestamp,
            'exp' => $expiresAtTimestamp,
            'sub' => (string) ($baseClaims['auth_id'] ?? ''),
            'jti' => (string) Str::uuid(),
        ], $baseClaims, $overrides);

        return JWT::encode($claims, env('JWT_SECRET'), 'HS256');
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:registrations,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Email telah digunakan, lanjutkan untuk MASUK',
        ]);

        $period = env('REGISTRATION_PERIOD') ?: null;

        $registration = Registration::create([
            'registration_period' => $period,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 100,
        ]);

        $token = $this->buildToken([
            'email' => $registration->email,
            'auth_type' => 'registration',
            'auth_id' => $registration->id,
            'name' => $registration->name,
        ]);

        $this->response['success'] = true;
        $this->response['text'] = 'Register Success';
        $this->response['result'] = [
            'token' => $token,
            'auth_type' => 'registration',
            'auth_id' => $registration->id,
            'name' => $registration->name,
            'email' => $registration->email,
        ];

        return $this->response;
    }

    public function processCopy(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $email = data_get($payload, 'email');

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $currentPeriod = env('REGISTRATION_PERIOD');
        if (empty($currentPeriod)) {
            return response()->json([
                'success' => false,
                'text' => 'REGISTRATION_PERIOD is not configured',
                'result' => null,
            ], 422);
        }

        $archiveRegistration = Registration::whereEmail($email)
            ->orderByDesc('registration_period')
            ->first();

        $currentRegistration = Registration::whereEmail($email)
            ->whereRegistrationPeriod($currentPeriod)
            ->first();

        if (! $archiveRegistration || $currentRegistration) {
            return response()->json([
                'success' => false,
                'text' => 'Failed. Registration exist',
                'result' => null,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $copyPayload = [];
            $fillable = (new Registration())->getFillable();

            foreach ($fillable as $field) {
                if (in_array($field, ['registration_period', 'status'], true)) {
                    continue;
                }
                $copyPayload[$field] = $archiveRegistration->getAttribute($field);
            }

            $copyPayload['registration_period'] = $currentPeriod;
            $copyPayload['test_order'] = ((int) ($archiveRegistration->test_order ?? 0)) + 1;
            $copyPayload['status'] = 100;

            $newRegistration = Registration::create($copyPayload);

            $details = RegistrationDetail::whereRegistrationId($archiveRegistration->id)->get();
            foreach ($details as $detail) {
                $detailPayload = $detail->getAttributes();
                unset($detailPayload['id'], $detailPayload['created_at'], $detailPayload['updated_at']);
                $detailPayload['registration_id'] = $newRegistration->id;
                RegistrationDetail::create($detailPayload);
            }

            $archiveRegistration->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'text' => 'Copy failed',
                'result' => null,
            ], 500);
        }

        $token = $this->buildToken([
            'email' => $newRegistration->email,
            'auth_type' => 'registration',
            'auth_id' => $newRegistration->id,
            'name' => $newRegistration->name,
        ]);

        $this->response['success'] = true;
        $this->response['text'] = 'Success';
        $this->response['result'] = [
            'token' => $token,
        ];

        return $this->response;
    }

    public function show(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $registration->load('details');

        $this->response['success'] = true;
        $this->response['text'] = 'Success';
        $this->response['result'] = $registration;

        return $this->response;
    }

    public function setProfile(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required|numeric',
            'image_uri' => 'required',
        ]);

        $registration->name = $request->name;
        $registration->email = $request->email;
        $registration->phone = $request->phone;
        $registration->image_uri = $request->image_uri;
        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setIdentity(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'nik' => 'required|numeric|digits:16',
            'gender' => 'required|in:M,F',
            'birth_place' => 'required',
            'birth_date' => 'required|date',
            'origin_address' => 'required',
            'marital_status' => 'required|in:S,M',
            'religion' => 'required',
        ]);

        $registration->nik = $request->nik;
        $registration->gender = $request->gender;
        $registration->birth_place = $request->birth_place;
        $registration->birth_date = $request->birth_date;
        $registration->origin_address = $request->origin_address;
        $registration->marital_status = $request->marital_status;
        $registration->religion = $request->religion;
        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setRegistration(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'test_order' => 'required|numeric',
            'selection_path' => 'required',
            'working_status' => 'required',
        ]);

        $registration->test_order = $request->test_order;
        $registration->selection_path = $request->selection_path;
        $registration->working_status = $request->working_status;
        $registration->video_url = $request->video_url;

        $registration->graduate_place = $request->graduate_place;
        $registration->graduate_reason = $request->graduate_reason;
        $registration->graduate_url = $request->graduate_url;
        $registration->statement_letter_url = $request->statement_letter_url;

        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setEducationBackground(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'origin_university' => 'required',
            'origin_university_accreditation' => 'required',
            'origin_university_address' => 'required',
            'ip_s1' => 'required',
            'ip_profession' => 'required',
            'ip_commulative' => 'required',
        ]);

        $registration->origin_university = $request->origin_university;
        $registration->origin_university_status = $request->origin_university_status;
        $registration->origin_university_accreditation = $request->origin_university_accreditation;
        $registration->origin_university_address = $request->origin_university_address;
        $registration->ip_s1 = $request->ip_s1;
        $registration->ip_profession = $request->ip_profession;
        $registration->ip_commulative = $request->ip_commulative;

        $registration->s1_init_year = $request->s1_init_year;
        $registration->s1_finish_year = $request->s1_finish_year;
        $registration->profession_init_year = $request->profession_init_year;
        $registration->profession_finish_year = $request->profession_finish_year;

        $registration->acls_end_date = $request->acls_end_date;
        $registration->acls_url = $request->acls_url;
        $registration->str_end_date = $request->str_end_date;
        $registration->str_url = $request->str_url;

        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setFamily(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $registration->father_name = $request->father_name;
        $registration->father_religion = $request->father_religion;
        $registration->father_job = $request->father_job;
        $registration->father_address = $request->father_address;

        $registration->mother_name = $request->mother_name;
        $registration->mother_religion = $request->mother_religion;
        $registration->mother_job = $request->mother_job;

        $registration->spouse_name = $request->spouse_name;
        $registration->spouse_birth_place = $request->spouse_birth_place;
        $registration->spouse_job = $request->spouse_job;
        $registration->spouse_address = $request->spouse_address;
        $registration->spouse_birth_date = $request->spouse_birth_date;
        $registration->wedding_date = $request->wedding_date;

        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setInstitution(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'institution_user_id' => 'required',
            'institution_name' => 'required',
            'institution_address' => 'required',
            'institution_city' => 'required',
        ]);

        $registration->institution_user_id = $request->institution_user_id;
        $registration->institution_name = $request->institution_name;
        $registration->institution_address = $request->institution_address;
        $registration->institution_city = $request->institution_city;
        $registration->education_permit = $request->education_permit;
        $registration->education_permit_signer = $request->education_permit_signer;
        $registration->education_permit_url = $request->education_permit_url;

        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setScore(Request $request)
    {
        [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->validate($request, [
            'score_acept' => 'required|numeric',
            'score_tpa' => 'required|numeric',
        ]);

        $registration->score_acept = $request->score_acept;
        $registration->score_tpa = $request->score_tpa;
        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }

    public function setStatus(Request $request)
    {
        $this->validate($request, [
            'status' => 'required',
        ]);

        $registrationId = $request->get('id');
        if (! $registrationId) {
            [$registration, $errorResponse] = $this->getAuthRegistrationOrFail($request);
            if ($errorResponse) {
                return $errorResponse;
            }
        } else {
            $registration = Registration::where('id', $registrationId)->first();
            if (! $registration) {
                return response()->json([
                    'success' => false,
                    'text' => 'Registration not found',
                    'result' => null,
                ], 404);
            }
        }

        $registration->status = $request->status;
        $registration->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $registration,
        ]);
    }
}
