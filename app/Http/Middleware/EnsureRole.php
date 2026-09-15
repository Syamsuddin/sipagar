<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `role:admin` / `role:admin,operator` (docs/21); tanpa argumen (`role`) = user aktif peran apa pun.
 * User nonaktif (is_active=0) selalu 403 walau sesinya masih ada (docs/05, F05).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403);
        }

        if ($roles === []) {
            return $next($request);
        }

        $izin = array_map(fn (string $r) => Role::from($r), $roles);
        if (! in_array($user->role, $izin, true)) {
            abort(403);
        }

        return $next($request);
    }
}
