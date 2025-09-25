<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleApiTokenCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie('api_token')) {
            $token = $request->cookie('api_token');

            /** @phpstan-ignore-next-line */
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
