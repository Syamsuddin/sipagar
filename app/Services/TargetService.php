<?php

namespace App\Services;

use App\Exceptions\TahunTerkunciException;
use App\Models\SubKegiatan;
use Illuminate\Support\Facades\DB;

/**
 * P2 docs/06: upsert 4 baris target kumulatif sekaligus. Validasi monoton/TW4 di FormRequest (docs/14);
 * kunci tahun dicek ulang di sini (docs/21).
 */
class TargetService
{
    public function __construct(private readonly AuditService $audit) {}

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
            $lama = $sk->targetTriwulan()->get()->keyBy('triwulan')
                ->map(fn ($t) => ['keu' => $t->target_keuangan, 'fisik' => (float) $t->target_fisik])->all();

            foreach ($baris as $b) {
                $sk->targetTriwulan()->updateOrCreate(
                    ['triwulan' => $b['triwulan']],
                    ['target_keuangan' => $b['target_keuangan'], 'target_fisik' => $b['target_fisik']],
                );
            }

            $baru = collect($baris)->keyBy('triwulan')
                ->map(fn ($b) => ['keu' => (int) $b['target_keuangan'], 'fisik' => (float) $b['target_fisik']])->all();

            $this->audit->catat('updated', $sk, ['target' => $lama], ['target' => $baru]);
        });
    }
}
