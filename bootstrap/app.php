<?php

use App\Exceptions\TahunTerkunciException;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTahunTerbuka;
use App\Http\Middleware\KonfirmasiSandi;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SecurityHeaders::class]);
        $middleware->alias([
            'role' => EnsureRole::class,
            'konfirmasi-sandi' => KonfirmasiSandi::class,
            'tahun-terbuka' => EnsureTahunTerbuka::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tahun terkunci → 423 (docs/14): JSON utk fetch, halaman 423 bertema utk form
        $exceptions->render(function (TahunTerkunciException $e, Request $request) {
            Log::info('Tulis pada tahun terkunci ditolak', ['tahun' => $e->tahunAnggaran->tahun, 'user_id' => $request->user()?->id, 'route' => $request->path()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 423);
            }

            return response()->view('errors.423', ['pesan' => $e->getMessage()], 423);
        });
    })->create();
