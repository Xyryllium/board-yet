<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        
        $allowedOrigins = config('cors.allowed_origins', []);

        $allowedPatterns = config('cors.allowed_origins_patterns', []);
        
        $isAllowedOrigin = in_array($origin, $allowedOrigins);

        if (!$isAllowedOrigin) {
            foreach ($allowedPatterns as $pattern) {
                if (preg_match('#' . $pattern . '#', $origin)) {
                    $isAllowedOrigin = true;
                    break;
                }
            }
        }

        if ($isAllowedOrigin) {
            $response = $next($request);
            
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-App-Name, X-App-Version, X-CSRF-TOKEN, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');
            
            return $response;
        }

        return $next($request);
    }
}
