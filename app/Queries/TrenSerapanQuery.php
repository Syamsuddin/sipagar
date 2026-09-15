<?php

namespace App\Queries;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\TargetTriwulan;
use App\Services\SerapanCalculator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * F11: kurva kumulatif per bulan — realisasi tahun berjalan, target (TW diinterpolasi linear ke bulan),
 * realisasi tahun sebelumnya. Persen terhadap pagu tahun masing-masing.
 */
class TrenSerapanQuery
{
    public const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function __construct(private readonly SerapanCalculator $k) {}

    /**
     * @return array{filter: LaporanFilter, pagu: int, pagu_lalu: int, tahun_lalu: int|null, baris: array<int, array<string, mixed>>, grafik: array<string, array<int, int|float>>}
     */
    public function jalankan(LaporanFilter $f): array
    {
        $subIds = $this->subIds($f->tahun, $f->bidangId);
        $pagu = (int) SubKegiatan::whereIn('id', $subIds)->sum('pagu');
        $realisasi = $this->kumulatifPerBulan($subIds, $f->tahun->tahun);

        $targetTw = TargetTriwulan::whereIn('sub_kegiatan_id', $subIds)->selectRaw('triwulan, SUM(target_keuangan) as t')->groupBy('triwulan')->pluck('t', 'triwulan')->map(fn ($v) => (int) $v);
        $target = $this->interpolasiTarget($targetTw);

        $tahunLalu = TahunAnggaran::where('tahun', $f->tahun->tahun - 1)->first();
        $subLalu = $tahunLalu ? $this->subIds($tahunLalu, $f->bidangId) : [];
        $paguLalu = $tahunLalu ? (int) SubKegiatan::whereIn('id', $subLalu)->sum('pagu') : 0;
        $realisasiLalu = $tahunLalu ? $this->kumulatifPerBulan($subLalu, $tahunLalu->tahun) : array_fill(1, 12, 0);

        $baris = [];
        foreach (range(1, 12) as $b) {
            $baris[$b] = [
                'bulan' => self::BULAN[$b - 1],
                'realisasi' => $realisasi[$b],
                'realisasi_persen' => $this->k->serapanPersen($pagu, $realisasi[$b]),
                'target' => $target[$b],
                'target_persen' => $this->k->targetPersen($pagu, $target[$b]),
                'deviasi' => $this->k->deviasiKeuanganPoin($pagu, $realisasi[$b], $target[$b]),
                'realisasi_lalu' => $realisasiLalu[$b],
                'realisasi_lalu_persen' => $this->k->serapanPersen($paguLalu, $realisasiLalu[$b]),
            ];
        }

        return [
            'filter' => $f,
            'pagu' => $pagu,
            'pagu_lalu' => $paguLalu,
            'tahun_lalu' => $tahunLalu?->tahun,
            'baris' => $baris,
            'grafik' => [
                'labels' => self::BULAN,
                'realisasi' => array_values(array_map(fn ($r) => $r['realisasi_persen'], $baris)),
                'target' => array_values(array_map(fn ($r) => $r['target_persen'], $baris)),
                'lalu' => array_values(array_map(fn ($r) => $r['realisasi_lalu_persen'], $baris)),
            ],
        ];
    }

    /** @return array<int, int> */
    private function subIds(TahunAnggaran $ta, ?int $bidangId): array
    {
        return SubKegiatan::whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $ta->id))
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))->pluck('id')->all();
    }

    /** @return array<int, int> bulan => Σ realisasi s.d. akhir bulan */
    private function kumulatifPerBulan(array $subIds, int $tahun): array
    {
        $perBulan = RealisasiKeuangan::whereIn('sub_kegiatan_id', $subIds)->whereYear('tanggal', $tahun)
            ->selectRaw('MONTH(tanggal) as b, SUM(jumlah) as j')->groupBy('b')->pluck('j', 'b')->map(fn ($v) => (int) $v);
        $hasil = [];
        $kumulatif = 0;
        foreach (range(1, 12) as $b) {
            $kumulatif += $perBulan->get($b, 0);
            $hasil[$b] = $kumulatif;
        }

        return $hasil;
    }

    /**
     * Target kumulatif TW n berlaku di bulan 3n; bulan di antaranya diinterpolasi linear dari titik sebelumnya (bulan 0 = 0).
     *
     * @param  Collection<int, int>  $targetTw
     * @return array<int, int>
     */
    private function interpolasiTarget($targetTw): array
    {
        $titik = [0 => 0];
        foreach ([1, 2, 3, 4] as $tw) {
            $titik[$tw * 3] = (int) ($targetTw->get($tw) ?? $titik[($tw - 1) * 3]);
        }
        $hasil = [];
        foreach (range(1, 12) as $b) {
            $awal = intdiv($b - 1, 3) * 3;
            $akhir = $awal + 3;
            $hasil[$b] = (int) round($titik[$awal] + ($titik[$akhir] - $titik[$awal]) * ($b - $awal) / 3);
        }

        return $hasil;
    }

    public static function namaBulan(Carbon $c): string
    {
        return self::BULAN[$c->month - 1];
    }
}
