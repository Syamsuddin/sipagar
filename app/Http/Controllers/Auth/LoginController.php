<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * F01: throttle 5/menit per username+IP (429), user nonaktif ditolak,
     * last_login_at dicatat, audit `login`, sesi diregenerasi (docs/21).
     */
    public function store(LoginRequest $request, AuditService $audit): RedirectResponse|Response
    {
        $kunci = $request->throttleKey();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);
            Log::warning('Login throttle', ['username' => $request->string('username'), 'ip' => $request->ip()]);

            return response()->view('auth.login', [
                'throttle' => "Terlalu banyak percobaan, coba lagi dalam {$detik} detik",
            ], 429)->header('Retry-After', (string) $detik);
        }

        $user = User::where('username', mb_strtolower($request->string('username')))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            RateLimiter::hit($kunci, 60);
            throw ValidationException::withMessages(['username' => 'Username atau kata sandi salah']);
        }

        if (! $user->is_active) {
            RateLimiter::hit($kunci, 60);
            throw ValidationException::withMessages(['username' => 'Akun nonaktif']);
        }

        RateLimiter::clear($kunci);
        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        $audit->catat('login', $user, userId: $user->id);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuditService $audit): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $audit->catat('logout', $user, userId: $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
