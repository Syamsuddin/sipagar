<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * docs/15: tiap request punya `request_id` (header X-Request-Id) + konteks log user_id/route/ip.
 * docs/14: kode referensi pada halaman 500.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = (string) Str::ulid();
        $request->attributes->set('request_id', $id);
        Log::shareContext([
            'request_id' => $id,
            'user_id' => $request->user()?->id,
            'route' => $request->path(),
            'ip' => $request->ip(),
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
