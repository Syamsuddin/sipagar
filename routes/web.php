<?php

use App\Http\Controllers\Anggaran\KegiatanController;
use App\Http\Controllers\Anggaran\ProgramController;
use App\Http\Controllers\Anggaran\SubKegiatanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\KonfirmasiSandiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Laporan\BukuRealisasiController;
use App\Http\Controllers\Laporan\MonevTriwulanController;
use App\Http\Controllers\Laporan\RekapController;
use App\Http\Controllers\Laporan\TrenSerapanController;
use App\Http\Controllers\Master\BidangController;
use App\Http\Controllers\Master\PengaturanController;
use App\Http\Controllers\Master\SumberDanaController;
use App\Http\Controllers\Master\TahunAnggaranController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Realisasi\LampiranController;
use App\Http\Controllers\Realisasi\RealisasiFisikController;
use App\Http\Controllers\Realisasi\RealisasiKeuanganController;
use App\Http\Controllers\TargetTriwulanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// `role` tanpa argumen: user harus aktif (nonaktif → 403 walau sesi ada)
Route::middleware(['auth', 'role'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/konfirmasi-sandi', [KonfirmasiSandiController::class, 'store'])->name('konfirmasi-sandi');
    Route::post('/profil/sandi', [ProfilController::class, 'ubahSandi'])->name('profil.sandi');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Anggaran: dibaca semua peran; tulis Admin + tahun terbuka; ubah/hapus + konfirmasi sandi (docs/21, docs/26)
    Route::get('/anggaran', [ProgramController::class, 'index'])->name('anggaran.index');
    Route::middleware(['role:admin', 'tahun-terbuka'])->prefix('anggaran')->name('anggaran.')->group(function () {
        Route::post('/program', [ProgramController::class, 'store'])->name('program.store');
        Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
        Route::post('/sub-kegiatan', [SubKegiatanController::class, 'store'])->name('sub-kegiatan.store');

        Route::middleware('konfirmasi-sandi')->group(function () {
            Route::put('/program/{program}', [ProgramController::class, 'update'])->name('program.update');
            Route::delete('/program/{program}', [ProgramController::class, 'destroy'])->name('program.destroy');
            Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
            Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
            Route::put('/sub-kegiatan/{subKegiatan}', [SubKegiatanController::class, 'update'])->name('sub-kegiatan.update');
            Route::delete('/sub-kegiatan/{subKegiatan}', [SubKegiatanController::class, 'destroy'])->name('sub-kegiatan.destroy');
        });
    });

    // Realisasi: baca semua peran; tulis Admin/Operator bidangnya (Policy) + tahun terbuka; ubah/hapus + konfirmasi sandi
    Route::prefix('realisasi')->name('realisasi.')->group(function () {
        Route::get('/keuangan', [RealisasiKeuanganController::class, 'index'])->name('keuangan.index');
        Route::post('/keuangan', [RealisasiKeuanganController::class, 'store'])->middleware('tahun-terbuka')->name('keuangan.store');
        Route::middleware(['tahun-terbuka', 'konfirmasi-sandi'])->group(function () {
            Route::put('/keuangan/{realisasiKeuangan}', [RealisasiKeuanganController::class, 'update'])->name('keuangan.update');
            Route::delete('/keuangan/{realisasiKeuangan}', [RealisasiKeuanganController::class, 'destroy'])->name('keuangan.destroy');
        });
        Route::get('/lampiran/{realisasiKeuangan}', [LampiranController::class, 'show'])->name('lampiran.show');

        Route::get('/fisik', [RealisasiFisikController::class, 'index'])->name('fisik.index');
        Route::get('/fisik/{subKegiatan}', [RealisasiFisikController::class, 'show'])->name('fisik.show');
        Route::put('/fisik/{subKegiatan}', [RealisasiFisikController::class, 'update'])->middleware('tahun-terbuka')->name('fisik.update');
    });

    // Laporan: semua peran (docs/05); ?export=xlsx|pdf
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/monev', [MonevTriwulanController::class, 'index'])->name('monev.index');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/buku-realisasi', [BukuRealisasiController::class, 'index'])->name('buku.index');
        Route::get('/tren', [TrenSerapanController::class, 'index'])->name('tren.index');
    });

    // Target triwulan: Admin semua bidang, Operator bidangnya (Policy); tahun terbuka
    Route::get('/target', [TargetTriwulanController::class, 'index'])->name('target.index');
    Route::get('/target/{subKegiatan}', [TargetTriwulanController::class, 'show'])->name('target.show');
    Route::put('/target/{subKegiatan}', [TargetTriwulanController::class, 'update'])->middleware('tahun-terbuka')->name('target.update');

    // Master & pengguna: hanya Admin (docs/21 route grup role:admin + Policy)
    Route::middleware('role:admin')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::patch('/pengguna/{pengguna}/aktif', [PenggunaController::class, 'toggleAktif'])->name('pengguna.aktif');
        Route::post('/pengguna/{pengguna}/reset-sandi', [PenggunaController::class, 'resetSandi'])
            ->middleware('konfirmasi-sandi')->name('pengguna.reset-sandi');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

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
            // Kunci/buka tahun: irreversibel (docs/22) → konfirmasi sandi
            Route::post('/tahun-anggaran/{tahun_anggaran}/kunci', [TahunAnggaranController::class, 'kunci'])->middleware('konfirmasi-sandi')->name('tahun-anggaran.kunci');
            Route::post('/tahun-anggaran/{tahun_anggaran}/buka', [TahunAnggaranController::class, 'buka'])->middleware('konfirmasi-sandi')->name('tahun-anggaran.buka');

            Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
            Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        });
    });
});
