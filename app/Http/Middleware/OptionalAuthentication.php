<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Only attempt authentication if a Bearer Token is present
        if ($request->bearerToken()) {
            try {
                // Attempt to authenticate the user using Sanctum
                auth()->shouldUse('sanctum');
                auth()->authenticate();

            } catch (AuthenticationException $e) {
                // Ignore the exception to allow unauthenticated access
            }
        }

        return $next($request);
    }
}
