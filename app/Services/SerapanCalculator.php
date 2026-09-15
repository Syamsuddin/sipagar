<?php

namespace App\Services;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use Carbon\CarbonInterface;

/**
 * SATU-SATUNYA rumah rumus docs/04. S2: realisasi/sisa/serapan (dipakai validasi pagu);
 * deviasi, fisik, status, agregat dilengkapi di S3 (F07).
 */
class SerapanCalculator
{
    /** Σ jumlah realisasi keuangan s.d. tanggal T (soft-deleted dikecualikan). */
    public function realisasi(SubKegiatan $sk, ?CarbonInterface $sampai = null): int
    {
        return (int) RealisasiKeuangan::query()
            ->where('sub_kegiatan_id', $sk->id)
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->sum('jumlah');
    }

    public function sisa(int $pagu, int $realisasi): int
    {
        return $pagu - $realisasi;
    }

    /** serapan % = pagu > 0 ? round(realisasi / pagu × 100) : 0 */
    public function serapanPersen(int $pagu, int $realisasi): int
    {
        return $pagu > 0 ? (int) round($realisasi / $pagu * 100) : 0;
    }

    public function sisaPersen(int $pagu, int $realisasi): int
    {
        return $pagu > 0 ? (int) round(($pagu - $realisasi) / $pagu * 100) : 0;
    }

    /** target keuangan % TW n = round(target / pagu × 100) */
    public function targetPersen(int $pagu, int $targetKeuangan): int
    {
        return $pagu > 0 ? (int) round($targetKeuangan / $pagu * 100) : 0;
    }
}
