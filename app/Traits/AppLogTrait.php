<?php

namespace App\Traits;

use App\Models\Lecture;
use App\Models\Student;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Facades\Agent;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Throwable;

trait AppLogTrait {

    public function createAppLog($name, $note = null) {
        $identity = $this->resolveAppLogIdentity();

        $data = [
            'type' => $identity['type'],
            'name' => $identity['name'],
        ];

        if ($identity['type'] === 'dosen' && $identity['id']) {
            Lecture::where('id', $identity['id'])->update(['last_act' => now()]);
        } else if ($identity['type'] === 'residen' && $identity['id']) {
            Student::where('id', $identity['id'])->update(['last_act' => now()]);
        }

        $data['url'] = $note;
        $browser     = Agent::browser();
        $platform    = Agent::platform();
        $device      = Agent::device();
        if ($data['name'] != 'Untara' && !strpos($note, 'get-') && !strpos($note, 'cmsd') && !strpos($note, 'cmsr') ) {
            $data['desc']  = $device . ' | ' . $platform . Agent::version($platform) . ' | ' . $browser . Agent::version($browser);
            $data['agent'] = $_SERVER['HTTP_USER_AGENT'];
            config(['logging.channels.access_point.path' => storage_path('logs/access_point/'. date('ymd') .'.log')]);
            Log::channel('access_point')->info(json_encode($data));
        }
    }

    /**
     * Resolve the acting identity for the log entry.
     *
     * Web routes still rely on session guards; API routes are stateless and
     * carry the identity inside the JWT bearer token instead.
     */
    private function resolveAppLogIdentity() {
        if (auth()->guard('web')->check()) {
            return [
                'type' => 'admin',
                'name' => Auth::guard()->user()->name,
                'id' => Auth::guard()->id(),
            ];
        }

        if (auth()->guard('lecture')->check()) {
            return [
                'type' => 'dosen',
                'name' => Auth::guard('lecture')->user()->name,
                'id' => Auth::guard('lecture')->id(),
            ];
        }

        if (auth()->guard('student')->check()) {
            return [
                'type' => 'residen',
                'name' => Auth::guard('student')->user()->name,
                'id' => Auth::guard('student')->id(),
            ];
        }

        $payload = $this->resolveJwtPayload();
        if ($payload) {
            // Honour "log as" impersonation, falling back to the real identity.
            $authType = data_get($payload, 'log_as_auth_type') ?: data_get($payload, 'auth_type');
            $authId = data_get($payload, 'log_as_auth_id') ?: data_get($payload, 'auth_id');

            $typeMap = [
                'user' => 'admin',
                'lecture' => 'dosen',
                'student' => 'residen',
            ];

            return [
                'type' => $typeMap[$authType] ?? 'guest',
                'name' => data_get($payload, 'name') ?: 'anonymous',
                'id' => $authId,
            ];
        }

        return [
            'type' => 'guest',
            'name' => 'anonymous',
            'id' => null,
        ];
    }

    /**
     * Pull the JWT payload from the request, decoding the bearer token directly
     * when the jwt.auth middleware has not run yet (e.g. global/group order).
     */
    private function resolveJwtPayload() {
        $request = request();

        $payload = $request->attributes->get('jwt_payload');
        if ($payload) {
            return $payload;
        }

        $header = $request->header('Authorization', '');
        if (!preg_match('/^Bearer\s+(.*)$/i', $header, $matches)) {
            return null;
        }

        try {
            return JWT::decode(trim($matches[1]), new Key(config('jwt.secret'), 'HS256'));
        } catch (Throwable $e) {
            return null;
        }
    }
}
