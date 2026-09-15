<?php

namespace App\Services;

use App\Enums\StatusSerapan;
use App\Models\RealisasiFisik;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * SATU-SATUNYA rumah rumus docs/04. Nilai turunan tidak disimpan (docs/07).
 * Target & realisasi bersifat KUMULATIF (landmine #7): deviasi = kumulatif − kumulatif.
 */
class SerapanCalculator
{
    /** Σ jumlah realisasi keuangan s.d. tanggal T (soft-deleted dikecualikan oleh Eloquent). */
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

    /** Akhir TW n: 31 Mar / 30 Jun / 30 Sep / 31 Des. */
    public function akhirTriwulan(int $tahun, int $triwulan): Carbon
    {
        return Carbon::create($tahun, $triwulan * 3, 1)->endOfMonth()->startOfDay();
    }

    /** deviasi keuangan TW n (Rp) = realisasi(s.d. akhir TW n) − target_keuangan_n */
    public function deviasiKeuangan(int $realisasiSdTw, int $targetKeuangan): int
    {
        return $realisasiSdTw - $targetKeuangan;
    }

    /** deviasi keuangan dalam poin = serapan% − target% */
    public function deviasiKeuanganPoin(int $pagu, int $realisasiSdTw, int $targetKeuangan): int
    {
        return $this->serapanPersen($pagu, $realisasiSdTw) - $this->targetPersen($pagu, $targetKeuangan);
    }

    /**
     * fisik s.d. bulan m: persen bulan m; bila kosong → bulan terisi terakhir < m; tidak ada → 0.
     *
     * @param  array<int, float|int|string>|Collection<int, float|int|string>  $fisikPerBulan  bulan => persen
     */
    public function fisikSampaiBulan(array|Collection $fisikPerBulan, int $bulan): float
    {
        $map = collect($fisikPerBulan);
        for ($m = $bulan; $m >= 1; $m--) {
            if ($map->has($m)) {
                return (float) $map->get($m);
            }
        }

        return 0.0;
    }

    /** @return array<int, float> bulan => persen dari DB */
    public function fisikPerBulan(SubKegiatan $sk): array
    {
        return RealisasiFisik::query()->where('sub_kegiatan_id', $sk->id)->pluck('persen', 'bulan')
            ->map(fn ($p) => (float) $p)->all();
    }

    /** deviasi fisik TW n = fisik(s.d. bulan 3n) − target_fisik_n */
    public function deviasiFisik(array|Collection $fisikPerBulan, int $triwulan, float $targetFisik): float
    {
        return round($this->fisikSampaiBulan($fisikPerBulan, $triwulan * 3) - $targetFisik, 2);
    }

    public function status(int $pagu, int $realisasi): StatusSerapan
    {
        return StatusSerapan::dariPersen($this->serapanPersen($pagu, $realisasi));
    }

    /**
     * Agregat: Σ pagu, Σ realisasi; serapan agregat dari Σ, bukan rata-rata %.
     *
     * @param  iterable<array{pagu: int, realisasi: int}>  $anggota
     * @return array{pagu: int, realisasi: int, sisa: int, serapan: int, status: StatusSerapan}
     */
    public function agregat(iterable $anggota): array
    {
        $pagu = 0;
        $realisasi = 0;
        foreach ($anggota as $a) {
            $pagu += (int) $a['pagu'];
            $realisasi += (int) $a['realisasi'];
        }

        return [
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'sisa' => $this->sisa($pagu, $realisasi),
            'serapan' => $this->serapanPersen($pagu, $realisasi),
            'status' => $this->status($pagu, $realisasi),
        ];
    }
}
