<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Header keamanan docs/21. */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            // Alpine mengevaluasi ekspresi inline lewat new Function → butuh 'unsafe-eval' (docs/21)
            "script-src 'self' 'unsafe-eval'",
            "font-src 'self' fonts.gstatic.com data:",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com",
            "img-src 'self' data:",
        ]));

        return $response;
    }
}
