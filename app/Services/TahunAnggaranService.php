<?php

namespace App\Services;

use App\Enums\StatusTahun;
use App\Models\TahunAnggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * P1 langkah 3 & P5 docs/06. Kunci/buka (S5) memakai kelas ini juga.
 */
class TahunAnggaranService
{
    public function __construct(private readonly AuditService $audit) {}

    public function tambah(int $tahun): TahunAnggaran
    {
        $ta = TahunAnggaran::create(['tahun' => $tahun, 'status' => StatusTahun::Draft]);
        $this->audit->catat('created', $ta, null, ['tahun' => $tahun, 'status' => StatusTahun::Draft->value]);

        return $ta;
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

            $lama = $ta->status->value;
            $ta->update(['status' => StatusTahun::Aktif]);
            $this->audit->catat('updated', $ta, ['status' => $lama], ['status' => StatusTahun::Aktif->value]);

            return $ta;
        });
    }

    public static function aktif(): ?TahunAnggaran
    {
        return TahunAnggaran::aktif()->first();
    }
}
