<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SharedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            Auth::guard('lecture')->check() ||
            Auth::guard('web')->check() ||
            Auth::guard('student')->check()
        ) {
            return $next($request);
        }
        return redirect('/');
    }
}
