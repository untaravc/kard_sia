<?php

namespace App\Http\Middleware;

use App\Traits\AppLogTrait;
use Closure;
use Illuminate\Support\Facades\Request;

class AppLogMiddleware
{
    use AppLogTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->createAppLog('access_point', str_replace('https://sia.kardiologi-fkkmk.com', '', Request::url()));
        return $next($request);
    }
}
