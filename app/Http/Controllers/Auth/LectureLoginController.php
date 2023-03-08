<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AppLogTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureLoginController extends Controller
{
    use AuthenticatesUsers;
    use AppLogTrait;
    protected $guard = 'lecture';
    protected $redirectTo = '/cmsl';

    public function __construct()
    {
        $this->middleware('guest:lecture')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login-lecture');
    }

    public function guard()
    {
        return auth()->guard('lecture');
    }

    public function login(Request $request)
    {
        if (auth()->guard('lecture')->attempt(['email' => $request->email, 'password' => $request->password ])) {
            $this->createAppLog('login');
            return redirect('/dosen');
        }
        return back()->withErrors(['email' => 'Email or password are wrong.']);
    }

    public function logout(Request $request)
    {
        $this->guard('lecture')->logout();

        return $this->loggedOut($request) ? : redirect('/');
    }
}
