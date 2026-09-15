<?php

namespace App\Queries;

use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Services\SerapanCalculator;
use Illuminate\Support\Collection;

/**
 * F07: satu sumber angka untuk kartu, grafik, dan tabel ringkasan (docs/08, docs/16).
 * Filter: tahun (id | 'semua'), bidang (id | null). Operator default bidangnya.
 */
class DashboardQuery
{
    public function __construct(private readonly SerapanCalculator $kalkulator) {}

    /**
     * @return array{
     *   baris: Collection<int, array<string, mixed>>,
     *   total: array{pagu: int, realisasi: int, sisa: int, serapan: int, sisa_persen: int, jumlah_sub: int, jumlah_transaksi: int},
     *   grafik_bar: array{labels: array<int, string>, pagu: array<int, int>, realisasi: array<int, int>},
     *   grafik_doughnut: array{labels: array<int, string>, data: array<int, int>}
     * }
     */
    public function jalankan(int|string|null $tahunId, ?int $bidangId): array
    {
        $sub = SubKegiatan::query()
            ->with(['bidang', 'sumberDana'])
            ->withSum('realisasiKeuangan as realisasi_total', 'jumlah')
            ->withCount('realisasiKeuangan as jumlah_transaksi')
            ->when($tahunId !== 'semua' && $tahunId !== null, fn ($q) => $q->whereHas('kegiatan.program', fn ($p) => $p->where('tahun_anggaran_id', $tahunId)))
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
            ->orderBy('kode')
            ->get();

        $baris = $sub->values()->map(function (SubKegiatan $sk, int $i) {
            $realisasi = (int) $sk->realisasi_total;

            return [
                'no' => $i + 1,
                'id' => $sk->id,
                'kode' => $sk->kode,
                'nama' => $sk->nama,
                'bidang' => $sk->bidang->kode,
                'sumber' => $sk->sumberDana,
                'pagu' => $sk->pagu,
                'realisasi' => $realisasi,
                'sisa' => $this->kalkulator->sisa($sk->pagu, $realisasi),
                'serapan' => $this->kalkulator->serapanPersen($sk->pagu, $realisasi),
                'status' => $this->kalkulator->status($sk->pagu, $realisasi),
                'transaksi' => (int) $sk->jumlah_transaksi,
            ];
        });

        $agregat = $this->kalkulator->agregat($baris->all());

        $top = $baris->sortByDesc('pagu')->take(10)->values();
        $perSumber = $baris->groupBy(fn ($b) => $b['sumber']->kode)->map(fn ($g) => $g->sum('pagu'))->sortDesc();

        return [
            'baris' => $baris,
            'total' => [
                'pagu' => $agregat['pagu'],
                'realisasi' => $agregat['realisasi'],
                'sisa' => $agregat['sisa'],
                'serapan' => $agregat['serapan'],
                'sisa_persen' => $this->kalkulator->sisaPersen($agregat['pagu'], $agregat['realisasi']),
                'jumlah_sub' => $baris->count(),
                'jumlah_transaksi' => (int) $baris->sum('transaksi'),
            ],
            'grafik_bar' => [
                'labels' => $top->pluck('nama')->all(),
                'pagu' => $top->pluck('pagu')->all(),
                'realisasi' => $top->pluck('realisasi')->all(),
            ],
            'grafik_doughnut' => [
                'labels' => $perSumber->keys()->all(),
                'data' => $perSumber->values()->all(),
            ],
        ];
    }

    /** Tahun default: aktif, lalu terbaru. */
    public static function tahunDefault(): ?TahunAnggaran
    {
        return TahunAnggaran::aktif()->first() ?? TahunAnggaran::orderByDesc('tahun')->first();
    }

    public static function bidangDefault(User $user): ?int
    {
        return $user->isOperator() ? $user->bidang_id : null;
    }
}
