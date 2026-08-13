<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\BaseTrait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    use BaseTrait;

    public function list()
    {
        $users = User::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve User List Success',
            'result' => $users,
        ]);
    }

    public function index(Request $request)
    {
        $dataContent = User::orderBy('name');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return response()->json([
            'success' => true,
            'text' => 'Retrieve Users Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'password'     => Hash::make($request->password)
        ]);

        $this->validateData($request);

        $user = User::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create User Success',
            'result' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);
        if($request->password){
            $request->merge([
                'password'     => Hash::make($request->password)
            ]);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'text' => 'User not found',
                'result' => null,
            ], 404);
        }

        $user->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update User Success',
            'result' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'text' => 'User not found',
                'result' => null,
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete User Success',
            'result' => null,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:active,nonactive',
            'send_email' => 'nullable|boolean',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'text' => 'User not found',
                'result' => null,
            ], 404);
        }

        $user->status = $request->status;
        $user->save();

        if ($request->status === 'active' && $request->boolean('send_email') && $user->email) {
            $user->reset_password_token = $this->generateRandomString(17);
            $user->save();

            $link = env('APP_URL') . "/blu/login-email?token={$user->reset_password_token}";

            Mail::send('mails.login_email', ['link' => $link], function ($message) use ($user) {
                $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
                $fromName = config('mail.from.name') ?: config('app.name');

                if ($fromAddress) {
                    $message->from($fromAddress, $fromName);
                }

                $message->to($user->email)
                    ->subject('Login Link');
            });
        }

        return response()->json([
            'success' => true,
            'text' => 'Update User Status Success',
            'result' => $user,
        ]);
    }

    public function validateData($request){
        $this->validate($request, [
            "name"          => 'required',
            "email"         => 'required|email',
            "password"      => 'required_without:id',
            "study_program_codes" => 'nullable|array',
            "role_id"       => 'nullable|integer|exists:roles,id',
        ]);
    }

    public function withFilter($dataContent, $request){
        if ($request->keyword != null){
            $dataContent = $dataContent->where('name', 'LIKE', "%".$request->keyword."%");
        }
        return $dataContent;
    }
}
