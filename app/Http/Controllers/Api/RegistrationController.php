<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::query()->orderBy('name');

        if ($request->filled('keyword')) {
            $query->where('name', 'LIKE', '%' . $request->keyword . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'LIKE', '%' . $request->email . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $period = env('REGISTRATION_PERIOD');
        if ($request->filled('registration_period')) {
            $query->where('registration_period', $request->registration_period);
        } elseif (!empty($period)) {
            $query->where('registration_period', $period);
        }

        $perPage = (int) ($request->per_page ?? 25);
        if ($perPage <= 0) {
            $perPage = 25;
        }

        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Registrations Success',
            'result' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $payload = $request->all();
        if (!empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        $registration = Registration::create($payload);

        return response()->json([
            'success' => true,
            'text' => 'Create Registration Success',
            'result' => $registration,
        ]);
    }

    public function show($id)
    {
        $registration = Registration::with(['details', 'score'])->find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Registration Success',
            'result' => $registration,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|integer',
        ]);

        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $registration->update([
            'status' => (int) $request->status,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Status Success',
            'result' => $registration,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request, $id);

        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $payload = $request->all();
        if (!empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        } else {
            unset($payload['password']);
        }

        $registration->update($payload);

        return response()->json([
            'success' => true,
            'text' => 'Update Registration Success',
            'result' => $registration,
        ]);
    }

    public function destroy($id)
    {
        $registration = Registration::find($id);
        if (!$registration) {
            return response()->json([
                'success' => false,
                'text' => 'Registration not found',
                'result' => null,
            ], 404);
        }

        $registration->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Registration Success',
            'result' => null,
        ]);
    }

    private function validateData(Request $request, $id = null)
    {
        $emailRule = 'required|email';
        if ($id) {
            $emailRule .= '|unique:registrations,email,' . $id;
        } else {
            $emailRule .= '|unique:registrations,email';
        }

        $this->validate($request, [
            'name' => 'required|string',
            'email' => $emailRule,
            'password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);
    }
}
