<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            $cookieToken = $request->cookie('board_yet_auth_token');
            // Ensure we get a string value, not an array
            $token = is_array($cookieToken) ? $cookieToken[0] ?? null : $cookieToken;
        }

        if (!$token || !is_string($token)) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });

        return $next($request);
    }
}
