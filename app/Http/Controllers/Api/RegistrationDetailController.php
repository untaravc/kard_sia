<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationDetail;
use Illuminate\Http\Request;

class RegistrationDetailController extends Controller
{
    private function getAuthRegistrationId(Request $request): ?int
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'auth_type') : null;
        $authId = $payload ? data_get($payload, 'auth_id') : null;

        if ($authType !== 'registration' || empty($authId)) {
            return null;
        }

        return (int) $authId;
    }

    public function store(Request $request)
    {
        $registrationId = $this->getAuthRegistrationId($request);
        if (! $registrationId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        if (! Registration::whereKey($registrationId)->exists()) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $this->validate($request, [
            'label' => 'required|string',
            'name' => 'required|string',
        ]);

        $detail = RegistrationDetail::create([
            'registration_id' => $registrationId,
            'label' => $request->label,
            'name' => $request->name,
            'desc' => $request->desc,
            'date' => $request->date,
            'file_url' => $request->file_url,
            'desc_1' => $request->desc_1,
            'desc_2' => $request->desc_2,
            'contact' => $request->contact,
            'duration' => $request->duration,
            'place' => $request->place,
            'year' => $request->year,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $detail,
        ]);
    }

    public function update($id, Request $request)
    {
        $registrationId = $this->getAuthRegistrationId($request);
        if (! $registrationId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $detail = RegistrationDetail::whereKey($id)
            ->where('registration_id', $registrationId)
            ->first();

        if (! $detail) {
            return response()->json([
                'success' => false,
                'text' => 'Detail not found',
                'result' => null,
            ], 404);
        }

        $this->validate($request, [
            'label' => 'required|string',
            'name' => 'required|string',
        ]);

        $detail->label = $request->label;
        $detail->name = $request->name;
        $detail->desc = $request->desc;
        $detail->date = $request->date;
        $detail->file_url = $request->file_url;
        $detail->desc_1 = $request->desc_1;
        $detail->desc_2 = $request->desc_2;
        $detail->contact = $request->contact;
        $detail->duration = $request->duration;
        $detail->place = $request->place;
        $detail->year = $request->year;
        $detail->save();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => $detail,
        ]);
    }

    public function destroy($id, Request $request)
    {
        $registrationId = $this->getAuthRegistrationId($request);
        if (! $registrationId) {
            return response()->json([
                'success' => false,
                'text' => 'Unauthorized',
                'result' => null,
            ], 401);
        }

        $detail = RegistrationDetail::whereKey($id)
            ->where('registration_id', $registrationId)
            ->first();

        if (! $detail) {
            return response()->json([
                'success' => false,
                'text' => 'Detail not found',
                'result' => null,
            ], 404);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'text' => 'Sucess',
            'result' => true,
        ]);
    }
}

