<?php

namespace App\Http\Middleware;

use App\Services\ActionLogStore;
use App\Traits\AppLogTrait;
use Closure;
use Illuminate\Http\Request;

class ActionLogMiddleware
{
    use AppLogTrait;

    protected $loggedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Runs after the response has been fully rendered (including exceptions
     * turned into 422/500 responses by the app's exception handler), so the
     * logged status code always reflects what the client actually received.
     */
    public function terminate($request, $response)
    {
        if (!in_array($request->method(), $this->loggedMethods, true)) {
            return;
        }

        $identity = $this->resolveAppLogIdentity();

        app(ActionLogStore::class)->write([
            'time' => now()->toDateTimeString(),
            'route' => '/' . ltrim($request->path(), '/'),
            'method' => $request->method(),
            'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'actor' => [
                'id' => $identity['id'],
                'email' => $identity['email'] ?? null,
                'name' => $identity['name'],
            ],
        ]);
    }
}
