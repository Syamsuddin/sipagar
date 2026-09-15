<?php

namespace App\Services;

use App\Enums\StatusTahun;
use App\Models\TahunAnggaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * P1 langkah 3 & P5 docs/06. Kunci/buka (S5) memakai kelas ini juga.
 */
class TahunAnggaranService
{
    public function __construct(private readonly AuditService $audit) {}

    public function tambah(int $tahun): TahunAnggaran
    {
        return TahunAnggaran::create(['tahun' => $tahun, 'status' => StatusTahun::Draft]);
    }

    /**
     * Hanya satu tahun aktif — dijaga di sini dengan transaksi + lockForUpdate (docs/16 #9),
     * bukan constraint DB. Tahun terkunci tidak bisa diaktifkan kembali lewat jalur ini.
     */
    public function aktifkan(TahunAnggaran $ta): TahunAnggaran
    {
        return DB::transaction(function () use ($ta) {
            $ta = TahunAnggaran::lockForUpdate()->findOrFail($ta->id);

            if ($ta->status === StatusTahun::Terkunci) {
                throw ValidationException::withMessages(['status' => "Tahun {$ta->tahun} terkunci; buka kunci dulu."]);
            }

            $aktifLain = TahunAnggaran::lockForUpdate()->aktif()->where('id', '!=', $ta->id)->first();
            if ($aktifLain) {
                throw ValidationException::withMessages(['status' => "Kunci tahun {$aktifLain->tahun} dulu"]);
            }

            $ta->update(['status' => StatusTahun::Aktif]);

            return $ta;
        });
    }

    /**
     * P5 docs/06: kunci → terkunci + locked_at/by, audit `lock_tahun` (trait dimatikan agar tidak ganda).
     * Hanya lewat UI Admin + modal sandi (docs/22) — controller memakai middleware konfirmasi-sandi.
     */
    public function kunci(TahunAnggaran $ta, User $oleh): TahunAnggaran
    {
        if ($ta->status === StatusTahun::Terkunci) {
            throw ValidationException::withMessages(['status' => "Tahun {$ta->tahun} sudah terkunci"]);
        }
        $lama = ['status' => $ta->status->value, 'locked_at' => null, 'locked_by' => null];

        TahunAnggaran::tanpaAudit(fn () => $ta->update(['status' => StatusTahun::Terkunci, 'locked_at' => now(), 'locked_by' => $oleh->id]));
        $this->audit->catat('lock_tahun', $ta, $lama, ['status' => StatusTahun::Terkunci->value, 'locked_at' => $ta->locked_at->format('Y-m-d H:i:s'), 'locked_by' => $oleh->id], $oleh->id);
        Log::info('Tahun anggaran dikunci', ['tahun' => $ta->tahun, 'user_id' => $oleh->id]);

        return $ta;
    }

    /** Buka kunci → kembali `draft` (aktifkan ulang lewat aturan satu tahun aktif); audit `unlock_tahun`. */
    public function buka(TahunAnggaran $ta, User $oleh): TahunAnggaran
    {
        if ($ta->status !== StatusTahun::Terkunci) {
            throw ValidationException::withMessages(['status' => "Tahun {$ta->tahun} tidak terkunci"]);
        }
        $lama = ['status' => $ta->status->value, 'locked_at' => $ta->locked_at?->format('Y-m-d H:i:s'), 'locked_by' => $ta->locked_by];

        TahunAnggaran::tanpaAudit(fn () => $ta->update(['status' => StatusTahun::Draft, 'locked_at' => null, 'locked_by' => null]));
        $this->audit->catat('unlock_tahun', $ta, $lama, ['status' => StatusTahun::Draft->value, 'locked_at' => null, 'locked_by' => null], $oleh->id);
        Log::info('Kunci tahun anggaran dibuka', ['tahun' => $ta->tahun, 'user_id' => $oleh->id]);

        return $ta;
    }

    public static function aktif(): ?TahunAnggaran
    {
        return TahunAnggaran::aktif()->first();
    }
}
