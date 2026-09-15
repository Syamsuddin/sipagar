<?php

namespace App\Queries;

use App\Enums\StatusSerapan;
use App\Models\SubKegiatan;
use App\Services\SerapanCalculator;
use Illuminate\Support\Collection;

/**
 * F08: per Sub Kegiatan s.d. akhir TW n, dikelompokkan per Program dengan subtotal, plus total.
 * Semua angka lewat SerapanCalculator (docs/04).
 */
class MonevTriwulanQuery
{
    public function __construct(private readonly SerapanCalculator $k) {}

    /**
     * @return array{filter: LaporanFilter, kelompok: Collection<int, array{program: array{kode: string, nama: string}, baris: Collection, subtotal: array}>, total: array, jumlah: int}
     */
    public function jalankan(LaporanFilter $f): array
    {
        $akhir = $this->k->akhirTriwulan($f->tahun->tahun, $f->triwulan);

        $sub = SubKegiatan::query()
            ->with(['bidang', 'sumberDana', 'kegiatan.program', 'targetTriwulan', 'realisasiFisik'])
            ->withSum(['realisasiKeuangan as realisasi_sd_tw' => fn ($q) => $q->whereDate('tanggal', '<=', $akhir)], 'jumlah')
            ->whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $f->tahun->id))
            ->when($f->bidangId, fn ($q) => $q->where('bidang_id', $f->bidangId))
            ->when($f->sumberDanaId, fn ($q) => $q->where('sumber_dana_id', $f->sumberDanaId))
            ->orderBy('kode')->get();

        $no = 0;
        $kelompok = $sub->groupBy(fn (SubKegiatan $sk) => $sk->kegiatan->program_id)->map(function (Collection $anggota) use ($f, &$no) {
            $program = $anggota->first()->kegiatan->program;
            $baris = $anggota->map(function (SubKegiatan $sk) use ($f, &$no) {
                $target = $sk->targetTriwulan->firstWhere('triwulan', $f->triwulan);
                $targetKeu = (int) ($target?->target_keuangan ?? 0);
                $targetFisik = (float) ($target?->target_fisik ?? 0);
                $realisasi = (int) $sk->realisasi_sd_tw;
                $fisik = $sk->realisasiFisik->pluck('persen', 'bulan')->map(fn ($p) => (float) $p)->all();
                $fisikSd = $this->k->fisikSampaiBulan($fisik, $f->triwulan * 3);

                return [
                    'no' => ++$no,
                    'kode' => $sk->kode,
                    'nama' => $sk->nama,
                    'bidang' => $sk->bidang->kode,
                    'sumber' => $sk->sumberDana->kode,
                    'pagu' => $sk->pagu,
                    'target_keu' => $targetKeu,
                    'target_keu_persen' => $this->k->targetPersen($sk->pagu, $targetKeu),
                    'realisasi' => $realisasi,
                    'realisasi_persen' => $this->k->serapanPersen($sk->pagu, $realisasi),
                    'deviasi_keu' => $this->k->deviasiKeuanganPoin($sk->pagu, $realisasi, $targetKeu),
                    'target_fisik' => $targetFisik,
                    'realisasi_fisik' => $fisikSd,
                    'deviasi_fisik' => round($fisikSd - $targetFisik, 2),
                    'status' => $this->k->status($sk->pagu, $realisasi),
                ];
            })->values();

            return [
                'program' => ['kode' => $program->kode, 'nama' => $program->nama],
                'baris' => $baris,
                'subtotal' => $this->subtotal($baris),
            ];
        })->values();

        $semua = $kelompok->flatMap(fn ($g) => $g['baris']);

        return ['filter' => $f, 'kelompok' => $kelompok, 'total' => $this->subtotal($semua), 'jumlah' => $semua->count()];
    }

    /** @return array{pagu: int, target_keu: int, target_keu_persen: int, realisasi: int, realisasi_persen: int, deviasi_keu: int, status: StatusSerapan} */
    private function subtotal(Collection $baris): array
    {
        $agregat = $this->k->agregat($baris->map(fn ($b) => ['pagu' => $b['pagu'], 'realisasi' => $b['realisasi']])->all());
        $targetKeu = (int) $baris->sum('target_keu');

        return [
            'pagu' => $agregat['pagu'],
            'target_keu' => $targetKeu,
            'target_keu_persen' => $this->k->targetPersen($agregat['pagu'], $targetKeu),
            'realisasi' => $agregat['realisasi'],
            'realisasi_persen' => $agregat['serapan'],
            'deviasi_keu' => $this->k->deviasiKeuanganPoin($agregat['pagu'], $agregat['realisasi'], $targetKeu),
            'status' => $agregat['status'],
        ];
    }
}
