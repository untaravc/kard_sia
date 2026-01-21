<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Lecture;
use App\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request) {
        $this->validate($request, [
           'email' => 'required|email',
           'password' => 'required',
        ]);

        $authUser = null;
        $authType = null;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        foreach ($providers as $type => $model) {
            $candidate = $model::whereEmail($request->email)->first();
            if ($candidate && Hash::check($request->password, $candidate->password)) {
                $authUser = $candidate;
                $authType = $type;
                break;
            }
        }

        if (!$authUser) {
            $this->response['status'] = false;
            $this->response['text'] = 'Wrong email or password';
            $this->response['data'] = null;
            return $this->response;
        }

        $name = data_get($authUser, 'name')
            ?? data_get($authUser, 'lectureProfile.name')
            ?? data_get($authUser, 'studentProfile.name')
            ?? $authUser->email;

        $issuedAt = new \DateTimeImmutable();
        $issuedAtTimestamp = $issuedAt->getTimestamp();
        $expiresAtTimestamp = $issuedAt->modify('+1 year')->getTimestamp();

        $claims = [
            'iss' => config('app.url'),
            'iat' => $issuedAtTimestamp,
            'nbf' => $issuedAtTimestamp,
            'exp' => $expiresAtTimestamp,
            'sub' => (string) $authUser->id,
            'jti' => (string) Str::uuid(),
            'email' => $authUser->email,
            'auth_type' => $authType,
            'auth_id' => $authUser->id,
            'name' => $name,
        ];

        $token = JWT::encode($claims, env('JWT_SECRET'), 'HS256');

        $this->response['success'] = true;
        $this->response['text'] = 'Login Success';
        $this->response['result'] = [
            'token' => $token,
            'auth_type' => $authType,
            'auth_id' => $authUser->id,
            'name' => $name,
            'email' => $authUser->email,
        ];

        return $this->response;
    }

    public function profile() {
        $dosen = auth_dosen();
        if($dosen){
            $this->response['status'] = true;
            $this->response['text'] = 'Retrieve Profile Success';
            $this->response['data'] = [
                'profile' => $dosen,
            ];;
        }
        return $this->response;
    }

    public function logout() {
        $this->response['status'] = true;
        $this->response['text'] = 'Logged Out';
        return $this->response;
    }

    public function forgotPassword(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $authUser = null;
        $authType = null;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        foreach ($providers as $type => $model) {
            $candidate = $model::whereEmail($request->email)->first();
            if ($candidate) {
                $authUser = $candidate;
                $authType = $type;
                break;
            }
        }

        if (!$authUser) {
            $this->response['status'] = false;
            $this->response['text'] = 'No email registered';
            $this->response['data'] = null;
            return $this->response;
        }

        $token = Str::random(100);
        $authUser->reset_password_token = $token;
        $authUser->save();

        Mail::raw("Your reset password token: {$token}", function ($message) use ($authUser) {
            $message->to($authUser->email)
                ->subject('Reset Password');
        });

        $this->response['status'] = true;
        $this->response['text'] = 'Reset token sent';
        $this->response['data'] = [
            'email' => $authUser->email,
            'auth_type' => $authType,
        ];

        return $this->response;
    }

    public function menu(Request $request) {
        $basePath = $request->get('basePath', '/cblu');

        $menu = [
            [ 'label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard" ],
            [
                'label' => 'Dosen',
                'icon' => 'dosen',
                'children' => [
                    [ 'label' => 'Data', 'to' => "{$basePath}/lecture" ],
                    [ 'label' => 'Dokumen', 'to' => "{$basePath}/lecture-file" ],
                ],
            ],
            [
                'label' => 'Residen',
                'icon' => 'resident',
                'children' => [
                    [ 'label' => 'Data', 'to' => "{$basePath}/resident" ],
                    [ 'label' => 'Presensi', 'to' => "{$basePath}/presences" ],
                    [ 'label' => 'Presensi Harian', 'to' => "{$basePath}/presence-resume" ],
                    [ 'label' => 'Resume Presensi', 'to' => "{$basePath}/presence-ilmiah-resume" ],
                    [ 'label' => 'Stase Residen', 'to' => "{$basePath}/stase-plots" ],
                    [ 'label' => 'Ujian', 'to' => "{$basePath}/exams" ],
                    [ 'label' => 'Dokumen', 'to' => "{$basePath}/letters" ],
                    [ 'label' => 'Log Book', 'to' => "{$basePath}/student-logs" ],
                    [ 'label' => 'Download', 'to' => "{$basePath}/download" ],
                    [ 'label' => 'Calon PPDS', 'to' => "{$basePath}/student-regs" ],
                    [ 'label' => 'Pengabdian Masyarakat', 'to' => "{$basePath}/document-review" ],
                    [ 'label' => 'Laporan Harian', 'to' => "{$basePath}/daily-report" ],
                ],
            ],
            [
                'label' => 'CBT',
                'icon' => 'cbt',
                'children' => [
                    [ 'label' => 'Kategori', 'to' => '/object/categories' ],
                    [ 'label' => 'Bank Soal', 'to' => '/object/quizzes' ],
                    [ 'label' => 'Paket Ujian', 'to' => '/object/quiz-sections' ],
                ],
            ],
            [ 'label' => 'Ujian Terbuka', 'icon' => 'open-exam', 'to' => "{$basePath}/open-exam" ],
            [ 'label' => 'Agenda', 'icon' => 'agenda', 'to' => "{$basePath}/activity" ],
            [
                'label' => 'Data Master',
                'icon' => 'data-master',
                'children' => [
                    [ 'label' => 'Surat', 'to' => "{$basePath}/letter-records" ],
                    [ 'label' => 'Stase', 'to' => "{$basePath}/stase" ],
                    [ 'label' => 'Task', 'to' => "{$basePath}/task" ],
                    [ 'label' => 'Admin', 'to' => "{$basePath}/user" ],
                ],
            ],
        ];

        $this->response['status'] = true;
        $this->response['text'] = 'Retrieve Menu Success';
        $this->response['data'] = [
            'menu' => $menu,
        ];

        return $this->response;
    }
}
