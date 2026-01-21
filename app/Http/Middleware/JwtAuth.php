<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class JwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');
        if (! preg_match('/^Bearer\\s+(.*)$/i', $header, $matches)) {
            return response()->json([
                'status' => false,
                'text' => 'Missing bearer token',
                'data' => null,
            ], 401);
        }

        $token = trim($matches[1]);

        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'text' => 'Invalid token',
                'result' => null,
            ], 401);
        }

        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
