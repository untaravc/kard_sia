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

    public function login(Request $request)
    {
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
            $this->response['success'] = false;
            $this->response['text'] = 'Wrong email or password';
            $this->response['result'] = null;
            return $this->response;
        }

        $name = data_get($authUser, 'name')
            ?? data_get($authUser, 'lectureProfile.name')
            ?? data_get($authUser, 'studentProfile.name')
            ?? $authUser->email;

        $token = $this->buildToken([
            'email' => $authUser->email,
            'auth_type' => $authType,
            'auth_id' => $authUser->id,
            'name' => $name,
        ]);

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

    public function profile()
    {
        $dosen = auth_dosen();
        if ($dosen) {
            $this->response['success'] = true;
            $this->response['text'] = 'Retrieve Profile Success';
            $this->response['result'] = [
                'profile' => $dosen,
            ];;
        }
        return $this->response;
    }

    public function logout()
    {
        $this->response['success'] = true;
        $this->response['text'] = 'Logged Out';
        return $this->response;
    }

    public function auth(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');

        $this->response['success'] = true;
        $this->response['text'] = 'Auth payload';
        $this->response['result'] = $payload ? (array) $payload : null;

        return $this->response;
    }

    public function logAs(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }

        if ($authType !== 'user') {
            $this->response['success'] = false;
            $this->response['text'] = 'Unauthorized';
            $this->response['result'] = null;
            return $this->response;
        }

        $this->validate($request, [
            'auth_type' => 'required',
            'auth_id' => 'required',
        ]);

        $name = null;
        $email = null;
        if ($request->auth_type == 'student') {
            $student = Student::find($request->auth_id);
            if ($student) {
                $name = $student->name;
                $email = $student->email;
            }
        } else if ($request->auth_type == 'lecture') {
            $lecture = Lecture::find($request->auth_id);
            if ($lecture) {
                $name = $lecture->name;
                $email = $lecture->email;
            }
        }

        $token = $this->buildToken([
            'auth_type' => $authType,
            'auth_id' => data_get($payload, 'auth_id'),
            'log_as_auth_type' => $request->auth_type,
            'log_as_auth_id' => $request->auth_id,
            'name' => $name ? $name : data_get($payload, 'name'),
            'email' => $email ? $email : data_get($payload, 'email'),
        ]);

        $this->response['success'] = true;
        $this->response['text'] = 'Log as success';
        $this->response['result'] = [
            'token' => $token,
        ];

        return $this->response;
    }

    public function logoutAs(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'auth_type') : null;

        if ($authType !== 'user') {
            $this->response['success'] = false;
            $this->response['text'] = 'Unauthorized';
            $this->response['result'] = null;
            return $this->response;
        }

        $token = $this->buildToken([
            'email' => data_get($payload, 'email'),
            'auth_type' => $authType,
            'auth_id' => data_get($payload, 'auth_id'),
            'name' => data_get($payload, 'name'),
        ]);

        $this->response['success'] = true;
        $this->response['text'] = 'Logout as success';
        $this->response['result'] = [
            'token' => $token,
        ];

        return $this->response;
    }

    public function forgotPassword(Request $request)
    {
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
            $this->response['success'] = false;
            $this->response['text'] = 'No email registered';
            $this->response['result'] = null;
            return $this->response;
        }

        $token = Str::random(100);
        $authUser->reset_password_token = $token;
        $authUser->save();

        $link = env('APP_URL') . "/cblu/reset-password?token={$token}";

        Mail::send('mails.reset_password', ['token' => $link], function ($message) use ($authUser) {
            $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
            $fromName = config('mail.from.name') ?: config('app.name');

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            $message->to($authUser->email)
                ->subject('Reset Password');
        });

        $this->response['success'] = true;
        $this->response['text'] = 'Reset token sent';
        $this->response['result'] = [
            'email' => $authUser->email,
            'auth_type' => $authType,
        ];

        return $this->response;
    }

    public function checkResetPasswordToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        foreach ($providers as $type => $model) {
            $candidate = $model::where('reset_password_token', $token)->first();
            if ($candidate) {
                $this->response['status'] = true;
                $this->response['text'] = 'Token valid';
                $this->response['data'] = [
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'email' => $candidate->email,
                ];
                return $this->response;
            }
        }

        $this->response['status'] = false;
        $this->response['text'] = 'Token invalid';
        $this->response['data'] = null;

        return $this->response;
    }

    public function resetPasswordWithToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $token = $request->token;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        foreach ($providers as $type => $model) {
            $candidate = $model::where('reset_password_token', $token)->first();
            if ($candidate) {
                $candidate->password = Hash::make($request->password);
                $candidate->reset_password_token = null;
                $candidate->save();

                $this->response['status'] = true;
                $this->response['text'] = 'Password updated';
                $this->response['data'] = [
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'email' => $candidate->email,
                ];
                return $this->response;
            }
        }

        $this->response['status'] = false;
        $this->response['text'] = 'Token invalid';
        $this->response['data'] = null;

        return $this->response;
    }

    public function menu(Request $request)
    {
        $basePath = $request->get('basePath', '/cblu');
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }

        $menuByType = [
            'user' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard"],
                [
                    'label' => 'Dosen',
                    'icon' => 'dosen',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/lectures"],
                        // [ 'label' => 'Dokumen', 'to' => "{$basePath}/lecture-file" ],
                    ],
                ],
                [
                    'label' => 'Residen',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/students"],
                        ['label' => 'Presensi', 'to' => "{$basePath}/presences"],
                        ['label' => 'Presensi Harian', 'to' => "{$basePath}/presences/daily"],
                        ['label' => 'Presensi Bulanan', 'to' => "{$basePath}/presences/montly"],
                        // [ 'label' => 'Stase Residen', 'to' => "{$basePath}/stase-plots" ],
                        // [ 'label' => 'Ujian', 'to' => "{$basePath}/exams" ],
                        // [ 'label' => 'Dokumen', 'to' => "{$basePath}/letters" ],
                        // [ 'label' => 'Log Book', 'to' => "{$basePath}/student-logs" ],
                        // [ 'label' => 'Download', 'to' => "{$basePath}/download" ],
                        // [ 'label' => 'Calon PPDS', 'to' => "{$basePath}/student-regs" ],
                        // [ 'label' => 'Pengabdian Masyarakat', 'to' => "{$basePath}/document-review" ],
                        // [ 'label' => 'Laporan Harian', 'to' => "{$basePath}/daily-report" ],
                    ],
                ],
                // [
                //     'label' => 'CBT',
                //     'icon' => 'cbt',
                //     'children' => [
                //         [ 'label' => 'Kategori', 'to' => '/object/categories' ],
                //         [ 'label' => 'Bank Soal', 'to' => '/object/quizzes' ],
                //         [ 'label' => 'Paket Ujian', 'to' => '/object/quiz-sections' ],
                //     ],
                // ],
                // [ 'label' => 'Ujian Terbuka', 'icon' => 'open-exam', 'to' => "{$basePath}/open-exam" ],
                // [ 'label' => 'Agenda', 'icon' => 'agenda', 'to' => "{$basePath}/activity" ],
                [
                    'label' => 'Data Master',
                    'icon' => 'data-master',
                    'children' => [
                        ['label' => 'Surat', 'to' => "{$basePath}/letter-records"],
                        ['label' => 'Stase', 'to' => "{$basePath}/stases"],
                        ['label' => 'Task', 'to' => "{$basePath}/tasks"],
                        ['label' => 'Admin', 'to' => "{$basePath}/users"],
                    ],
                ],
            ],
            'student' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard"],
                [
                    'label' => 'Presensi',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Presensi', 'to' => "{$basePath}/presences"],
                        ['label' => 'Presensi Harian', 'to' => "{$basePath}/presences/daily"],
                        ['label' => 'Presensi Bulanan', 'to' => "{$basePath}/presences/montly"],
                    ],
                ],
            ],
            'lecture' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard"],
                [
                    'label' => 'Residen',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/students"],
                        ['label' => 'Presensi', 'to' => "{$basePath}/presences"],
                        ['label' => 'Presensi Harian', 'to' => "{$basePath}/presences/daily"],
                        ['label' => 'Presensi Bulanan', 'to' => "{$basePath}/presences/montly"],
                    ],
                ],
            ],
        ];

        $menu = $menuByType[$authType] ?? $menuByType['user'];

        $this->response['success'] = true;
        $this->response['text'] = 'Retrieve Menu Success';
        $this->response['result'] = [
            'menu' => $menu,
        ];

        return $this->response;
    }
}
