<?php

namespace App\Http\Middleware;

use Closure;

class SiteAvailabilityByDate
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
        if (now()->toDateString() > '2026-02-17') {
            return response('This site is temporarily unavailable', 503);
        }

        return $next($request);
    }
}
