<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aksi berisiko hanya lewat bila `POST /konfirmasi-sandi` sukses ≤ 5 menit lalu (docs/21).
 */
class KonfirmasiSandi
{
    public const KUNCI_SESI = 'password_confirmed_at';

    public function handle(Request $request, Closure $next): Response
    {
        $batas = config('sipagar.konfirmasi_sandi_menit') * 60;
        $terakhir = (int) $request->session()->get(self::KUNCI_SESI, 0);

        if (time() - $terakhir > $batas) {
            abort(403, 'Konfirmasi kata sandi diperlukan.');
        }

        return $next($request);
    }
}
