<?php

namespace App\Services;

use App\Exceptions\TahunTerkunciException;
use App\Models\SubKegiatan;
use Illuminate\Support\Facades\DB;

/**
 * P2 docs/06: upsert 4 baris target kumulatif sekaligus. Validasi monoton/TW4 di FormRequest (docs/14);
 * kunci tahun dicek ulang di sini (docs/21). Audit per baris target lewat trait Auditable.
 */
class TargetService
{
    /**
     * @param  array<int, array{triwulan: int, target_keuangan: int, target_fisik: float}>  $baris
     */
    public function simpan(SubKegiatan $sk, array $baris): void
    {
        $ta = $sk->tahunAnggaran();
        if ($ta->isTerkunci()) {
            throw new TahunTerkunciException($ta);
        }

        DB::transaction(function () use ($sk, $baris) {
            foreach ($baris as $b) {
                $sk->targetTriwulan()->updateOrCreate(
                    ['triwulan' => $b['triwulan']],
                    ['target_keuangan' => $b['target_keuangan'], 'target_fisik' => $b['target_fisik']],
                );
            }
        });
    }
}
