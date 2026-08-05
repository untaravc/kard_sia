<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class JwtQueryAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            $header = $request->header('Authorization', '');
            if (preg_match('/^Bearer\\s+(.*)$/i', $header, $matches)) {
                $token = trim($matches[1]);
            }
        }

        if ($token === '') {
            return response('false', 401)->header('Content-Type', 'text/plain');
        }

        try {
            $payload = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        } catch (Throwable $e) {
            return response('false', 401)->header('Content-Type', 'text/plain');
        }

        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}

