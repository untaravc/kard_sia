<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AppLogTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class StudentLoginController extends Controller
{
    use AuthenticatesUsers;
    use AppLogTrait;
    protected $guard = 'student';

    public function __construct()
    {
        $this->middleware('guest:student')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login-student');
    }

    public function guard()
    {
        return auth()->guard('student');
    }

    public function login(Request $request)
    {
        if (auth()->guard('student')->attempt(['email' => $request->email, 'password' => $request->password ])) {
            $this->createAppLog('login');
            return redirect('/resident');
        }
        return back()->withErrors(['email' => 'Email or password are wrong.']);
    }

    public function logout(Request $request)
    {
        $this->guard('student')->logout();

        return $this->loggedOut($request) ? : redirect('/');
    }
}
