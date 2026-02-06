<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\LectureProfile;
use App\Models\Student;
use App\Models\StudentProfile;
use App\Models\Activity;
use App\Models\ActivityLecture;
use App\Models\ActivityStudent;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

    private function buildLoginRedirectUrl(array $params = [])
    {
        $baseUrl = rtrim(env('APP_URL'), '/');
        $url = $baseUrl . '/blu/login';

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    private function isAllowedSsoDomain(?string $email)
    {
        return !empty($email);
    }

    public function loginGoogleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function loginGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect($this->buildLoginRedirectUrl([
                'error' => 'Google authentication failed',
            ]));
        }

        $email = $googleUser ? $googleUser->getEmail() : null;
        if (!$this->isAllowedSsoDomain($email)) {
            return redirect($this->buildLoginRedirectUrl([
                'error' => 'Email domain not allowed',
            ]));
        }

        $authUser = null;
        $authType = null;

        $providers = [
            'user' => User::class,
            'lecture' => Lecture::class,
            'student' => Student::class,
        ];

        foreach ($providers as $type => $model) {
            $candidate = $model::whereEmail($email)->first();
            if ($candidate) {
                $authUser = $candidate;
                $authType = $type;
                break;
            }
        }

        if (!$authUser) {
            return redirect($this->buildLoginRedirectUrl([
                'error' => 'No account registered for this email',
            ]));
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

        return redirect($this->buildLoginRedirectUrl([
            'token' => $token,
        ]));
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

    public function firebaseConfig()
    {
        $this->response['success'] = true;
        $this->response['text'] = 'Retrieve Firebase Config Success';
        $this->response['result'] = [
            'apiKey' => env('FB_API_KEY'),
            'authDomain' => env('FB_AUTH_DOMAIN'),
            'databaseURL' => env('FB_DATABASE_URL'),
            'projectId' => env('FB_PROJECT_ID'),
            'storageBucket' => env('FB_STORAGE_BUCKET'),
            'messagingSenderId' => env('FB_MESSAGING_SENDER_ID'),
            'appId' => env('FB_APP_ID'),
            'measurementId' => env('FB_MEASUREMENT_ID'),
            'vapidKey' => env('FB_VAPID_KEY'),
        ];

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

        $user = User::find(data_get($payload, 'auth_id'));

        $token = $this->buildToken([
            'email' => $user->email,
            'auth_type' => $authType,
            'auth_id' => data_get($payload, 'auth_id'),
            'name' => $user->name,
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

        $link = env('APP_URL') . "/blu/reset-password?token={$token}";

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

    public function loginEmailRequest(Request $request)
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

        $link = env('APP_URL') . "/blu/login-email?token={$token}";

        Mail::send('mails.login_email', ['link' => $link], function ($message) use ($authUser) {
            $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
            $fromName = config('mail.from.name') ?: config('app.name');

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            $message->to($authUser->email)
                ->subject('Login Link');
        });

        $this->response['success'] = true;
        $this->response['text'] = 'Login link sent';
        $this->response['result'] = [
            'email' => $authUser->email,
            'auth_type' => $authType,
        ];

        return $this->response;
    }

    public function loginPhoneRequest(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|string',
        ]);

        $rawPhone = preg_replace('/\D+/', '', $request->phone);
        if (!$rawPhone) {
            $this->response['success'] = false;
            $this->response['text'] = 'Invalid phone number';
            $this->response['result'] = null;
            return $this->response;
        }

        $normalizedPhones = [$rawPhone];
        $sendPhone = $rawPhone;
        if (str_starts_with($rawPhone, '0')) {
            $sendPhone = '62' . substr($rawPhone, 1);
            $normalizedPhones[] = $sendPhone;
        } elseif (str_starts_with($rawPhone, '62')) {
            $normalizedPhones[] = '0' . substr($rawPhone, 2);
        }
        $normalizedPhones = array_values(array_unique($normalizedPhones));

        $authUser = null;
        $authType = null;

        $candidate = User::whereIn('phone', $normalizedPhones)->first();
        if ($candidate) {
            $authUser = $candidate;
            $authType = 'user';
        } else {
            $lectureProfile = LectureProfile::whereIn('phone', $normalizedPhones)->first();
            if ($lectureProfile) {
                $lecture = Lecture::find($lectureProfile->lecture_id);
                if ($lecture) {
                    $authUser = $lecture;
                    $authType = 'lecture';
                }
            }
        }

        if (!$authUser) {
            $studentProfile = StudentProfile::whereIn('phone', $normalizedPhones)->first();
            if ($studentProfile) {
                $student = Student::find($studentProfile->student_id);
                if ($student) {
                    $authUser = $student;
                    $authType = 'student';
                }
            }
        }

        if (!$authUser) {
            $this->response['success'] = false;
            $this->response['text'] = 'No phone registered';
            $this->response['result'] = null;
            return $this->response;
        }

        $token = Str::random(100);
        $authUser->reset_password_token = $token;
        $authUser->save();

        $link = env('APP_URL') . "/blu/login-phone?token={$token}";
        // $message = "Halo";
        $message = "Login link: \n {$link} \nHarap simpan nomor ini, jika link tidak dapat di klik";

        app(WhatsAppController::class)->sendMessage($sendPhone, $message);

        $this->response['success'] = true;
        $this->response['text'] = 'Login link sent';
        $this->response['result'] = [
            'auth_type' => $authType,
        ];

        return $this->response;
    }

    public function checkLoginEmailToken(Request $request)
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
                $name = data_get($candidate, 'name')
                    ?? data_get($candidate, 'lectureProfile.name')
                    ?? data_get($candidate, 'studentProfile.name')
                    ?? $candidate->email;

                $jwtToken = $this->buildToken([
                    'email' => $candidate->email,
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'name' => $name,
                ]);

                $candidate->reset_password_token = null;
                $candidate->save();

                $this->response['success'] = true;
                $this->response['text'] = 'Login Success';
                $this->response['result'] = [
                    'token' => $jwtToken,
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'name' => $name,
                    'email' => $candidate->email,
                ];

                return $this->response;
            }
        }

        $this->response['success'] = false;
        $this->response['text'] = 'Token invalid';
        $this->response['result'] = null;

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

    public function checkLoginPhoneToken(Request $request)
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
                $name = data_get($candidate, 'name')
                    ?? data_get($candidate, 'lectureProfile.name')
                    ?? data_get($candidate, 'studentProfile.name')
                    ?? $candidate->email;

                $jwtToken = $this->buildToken([
                    'email' => $candidate->email,
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'name' => $name,
                ]);

                $candidate->reset_password_token = null;
                $candidate->save();

                $this->response['success'] = true;
                $this->response['text'] = 'Login Success';
                $this->response['result'] = [
                    'token' => $jwtToken,
                    'auth_type' => $type,
                    'auth_id' => $candidate->id,
                    'name' => $name,
                    'email' => $candidate->email,
                ];

                return $this->response;
            }
        }

        $this->response['success'] = false;
        $this->response['text'] = 'Token invalid';
        $this->response['result'] = null;

        return $this->response;
    }

    public function menu(Request $request)
    {
        $basePath = $request->get('basePath', '/blu');
        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        $todayAgendaCount = 0;
        if (in_array($authType, ['student', 'lecture'], true)) {
            $today = Carbon::today();
            $activities = Activity::whereDate('start_date', '=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->orderBy('start_date')
                ->get();

            if ($authId && $activities->count()) {
                $activityIds = $activities->pluck('id')->all();
                if ($authType === 'lecture') {
                    $absences = ActivityLecture::whereIn('activity_id', $activityIds)
                        ->where('lecture_id', $authId)
                        ->get()
                        ->keyBy('activity_id');
                } else {
                    $absences = ActivityStudent::whereIn('activity_id', $activityIds)
                        ->where('student_id', $authId)
                        ->get()
                        ->keyBy('activity_id');
                }

                $activities->transform(function ($activity) use ($absences) {
                    $activity->setAttribute('absence', $absences->get($activity->id));
                    return $activity;
                });
            }

            $todayAgendaCount = $activities->count();
        }

        $menuByType = [
            'user' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'to' => "{$basePath}/dashboard"],
                [
                    'label' => 'Lectures',
                    'icon' => 'dosen',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/lectures"],
                        // [ 'label' => 'Dokumen', 'to' => "{$basePath}/lecture-file" ],
                    ],
                ],
                [
                    'label' => 'Students',
                    'icon' => 'resident',
                    'children' => [
                        ['label' => 'Data', 'to' => "{$basePath}/students"],
                        ['label' => 'Presences', 'to' => "{$basePath}/presences"],
                        ['label' => 'Presences Daily', 'to' => "{$basePath}/presences/daily"],
                        ['label' => 'Presences Monthly', 'to' => "{$basePath}/presences/monthly"],
                        // [ 'label' => 'Stase Residen', 'to' => "{$basePath}/stase-plots" ],
                        // [ 'label' => 'Ujian', 'to' => "{$basePath}/exams" ],
                        // [ 'label' => 'Dokumen', 'to' => "{$basePath}/letters" ],
                        [ 'label' => 'Log Book', 'to' => "{$basePath}/logbooks" ],
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
                [ 'label' => 'Activities', 'icon' => 'agenda', 'to' => "{$basePath}/activities" ],
                [ 'label' => 'Accreditations', 'icon' => 'agenda', 'to' => "{$basePath}/accreditations" ],
                [
                    'label' => 'Data Master',
                    'icon' => 'data-master',
                    'children' => [
                        // ['label' => 'Surat', 'to' => "{$basePath}/letter-records"],
                        ['label' => 'Stase', 'to' => "{$basePath}/stases"],
                        ['label' => 'Task', 'to' => "{$basePath}/tasks"],
                        ['label' => 'Admin', 'to' => "{$basePath}/users"],
                    ],
                ],
            ],
            'student' => [
                ['label' => 'Scoring', 'icon' => 'mdi:clipboard-check-outline', 'to' => "{$basePath}/dashboard-student/scoring"],
                ['label' => 'Agenda', 'icon' => 'mdi:calendar-month-outline', 'to' => "{$basePath}/dashboard-student/agenda", 'counter' => $todayAgendaCount],
                ['label' => 'Logbooks', 'icon' => 'mdi:notebook-outline', 'to' => "{$basePath}/logbook-student"],
                ['label' => 'Accreditations', 'icon' => 'mdi:certificate-outline', 'to' => "{$basePath}/accreditations"],
                ['label' => 'Profile', 'icon' => 'mdi:account-outline', 'to' => "{$basePath}/dashboard-student/profile"],
            ],
            'lecture' => [
                ['label' => 'Scoring', 'icon' => 'mdi:clipboard-check-outline', 'to' => "{$basePath}/dashboard-lecture/scoring"],
                ['label' => 'Agenda', 'icon' => 'mdi:calendar-month-outline', 'to' => "{$basePath}/dashboard-lecture/agenda"],
                ['label' => 'Report', 'icon' => 'mdi:file-chart-outline', 'to' => "{$basePath}/dashboard-lecture/report"],
                ['label' => 'Accreditations', 'icon' => 'mdi:certificate-outline', 'to' => "{$basePath}/accreditations"],
                ['label' => 'Profile', 'icon' => 'mdi:account-outline', 'to' => "{$basePath}/dashboard-lecture/profile"],
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
