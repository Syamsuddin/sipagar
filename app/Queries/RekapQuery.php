<?php

namespace App\Queries;

use App\Models\SubKegiatan;
use App\Services\SerapanCalculator;
use Illuminate\Support\Collection;

/** F09: rekap per Sumber Dana & per Bidang s.d. akhir TW n; serapan agregat dari Σ (docs/04). */
class RekapQuery
{
    public function __construct(private readonly SerapanCalculator $k) {}

    /**
     * @return array{filter: LaporanFilter, sumber_dana: Collection, bidang: Collection, total: array}
     */
    public function jalankan(LaporanFilter $f): array
    {
        $akhir = $this->k->akhirTriwulan($f->tahun->tahun, $f->triwulan);

        $sub = SubKegiatan::query()->with(['bidang', 'sumberDana'])
            ->withSum(['realisasiKeuangan as realisasi_sd_tw' => fn ($q) => $q->whereDate('tanggal', '<=', $akhir)], 'jumlah')
            ->whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $f->tahun->id))
            ->get();

        $kelompokkan = fn (string $relasi) => $sub
            ->groupBy(fn (SubKegiatan $sk) => $sk->{$relasi}->id)
            ->map(function (Collection $g) use ($relasi) {
                $agregat = $this->k->agregat($g->map(fn ($sk) => ['pagu' => $sk->pagu, 'realisasi' => (int) $sk->realisasi_sd_tw])->all());

                return ['kode' => $g->first()->{$relasi}->kode, 'nama' => $g->first()->{$relasi}->nama, 'jumlah_sub' => $g->count()] + $agregat;
            })
            ->sortByDesc('pagu')->values()
            ->map(function (array $r, int $i) {
                $r['no'] = $i + 1;

                return $r;
            });

        $total = $this->k->agregat($sub->map(fn ($sk) => ['pagu' => $sk->pagu, 'realisasi' => (int) $sk->realisasi_sd_tw])->all()) + ['jumlah_sub' => $sub->count()];

        return ['filter' => $f, 'sumber_dana' => $kelompokkan('sumberDana'), 'bidang' => $kelompokkan('bidang'), 'total' => $total];
    }
}
