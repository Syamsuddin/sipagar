<?php

use App\Http\Controllers\Auth\KonfirmasiSandiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\BidangController;
use App\Http\Controllers\Master\PengaturanController;
use App\Http\Controllers\Master\SumberDanaController;
use App\Http\Controllers\Master\TahunAnggaranController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/konfirmasi-sandi', [KonfirmasiSandiController::class, 'store'])->name('konfirmasi-sandi');
    Route::post('/profil/sandi', [ProfilController::class, 'ubahSandi'])->name('profil.sandi');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master & pengguna: hanya Admin (docs/21 route grup role:admin + Policy)
    Route::middleware('role:admin')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::patch('/pengguna/{pengguna}/aktif', [PenggunaController::class, 'toggleAktif'])->name('pengguna.aktif');
        Route::post('/pengguna/{pengguna}/reset-sandi', [PenggunaController::class, 'resetSandi'])
            ->middleware('konfirmasi-sandi')->name('pengguna.reset-sandi');

        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/bidang', [BidangController::class, 'index'])->name('bidang.index');
            Route::post('/bidang', [BidangController::class, 'store'])->name('bidang.store');
            Route::put('/bidang/{bidang}', [BidangController::class, 'update'])->name('bidang.update');

            Route::get('/sumber-dana', [SumberDanaController::class, 'index'])->name('sumber-dana.index');
            Route::post('/sumber-dana', [SumberDanaController::class, 'store'])->name('sumber-dana.store');
            Route::put('/sumber-dana/{sumber_dana}', [SumberDanaController::class, 'update'])->name('sumber-dana.update');

            Route::get('/tahun-anggaran', [TahunAnggaranController::class, 'index'])->name('tahun-anggaran.index');
            Route::post('/tahun-anggaran', [TahunAnggaranController::class, 'store'])->name('tahun-anggaran.store');
            Route::put('/tahun-anggaran/{tahun_anggaran}', [TahunAnggaranController::class, 'update'])->name('tahun-anggaran.update');

            Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
            Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        });
    });
});
