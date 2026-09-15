<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\KonfirmasiSandi;
use App\Http\Requests\Auth\KonfirmasiSandiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * POST /konfirmasi-sandi (docs/21): benar → simpan waktu di sesi (5 menit);
 * salah → 403 dengan nomor percobaan; 3 gagal berturut → jeda 60 detik.
 */
class KonfirmasiSandiController extends Controller
{
    public function store(KonfirmasiSandiRequest $request): JsonResponse
    {
        $user = $request->user();
        $kunci = 'konfirmasi-sandi:'.$user->id;

        if (RateLimiter::tooManyAttempts($kunci, 3)) {
            return response()->json(['message' => 'Terlalu banyak percobaan, coba lagi dalam '.RateLimiter::availableIn($kunci).' detik'], 429);
        }

        if (! Hash::check($request->string('password'), $user->password)) {
            $percobaan = RateLimiter::hit($kunci, 60);
            if ($percobaan >= 3) {
                Log::warning('Konfirmasi sandi gagal 3x', ['user_id' => $user->id, 'ip' => $request->ip()]);
            }

            return response()->json(['message' => "Kata sandi salah! Percobaan {$percobaan} dari 3", 'percobaan' => $percobaan], 403);
        }

        RateLimiter::clear($kunci);
        $request->session()->put(KonfirmasiSandi::KUNCI_SESI, time());

        return response()->json(['ok' => true]);
    }
}
