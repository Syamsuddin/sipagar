<?php

namespace App\Queries;

use App\Models\RealisasiKeuangan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** F10: rincian transaksi; paginasi 50 di web, penuh di ekspor. */
class BukuRealisasiQuery
{
    public const PER_HALAMAN = 50;

    public function dasar(LaporanFilter $f): Builder
    {
        return RealisasiKeuangan::query()
            ->with(['subKegiatan.bidang', 'pembuat'])
            ->whereHas('subKegiatan', fn ($q) => $q
                ->whereHas('kegiatan.program', fn ($p) => $p->where('tahun_anggaran_id', $f->tahun->id))
                ->when($f->bidangId, fn ($q2) => $q2->where('bidang_id', $f->bidangId))
                ->when($f->subKegiatanId, fn ($q2) => $q2->where('id', $f->subKegiatanId)))
            ->whereBetween('tanggal', [$f->dari->toDateString(), $f->sampai->toDateString()])
            ->orderBy('tanggal')->orderBy('id');
    }

    /** @return array{filter: LaporanFilter, halaman: LengthAwarePaginator, total: int, jumlah: int} */
    public function halaman(LaporanFilter $f, int $nomor = 1): array
    {
        $dasar = $this->dasar($f);

        return [
            'filter' => $f,
            'halaman' => (clone $dasar)->paginate(self::PER_HALAMAN, ['*'], 'halaman', $nomor)->withQueryString(),
            'total' => (int) (clone $dasar)->sum('jumlah'),
            'jumlah' => (clone $dasar)->count(),
        ];
    }

    /** @return array{filter: LaporanFilter, baris: Collection<int, array<string, mixed>>, total: int, jumlah: int} */
    public function semua(LaporanFilter $f): array
    {
        $baris = $this->dasar($f)->get()->values()->map(fn (RealisasiKeuangan $r, int $i) => self::baris($r, $i + 1));

        return ['filter' => $f, 'baris' => $baris, 'total' => (int) $baris->sum('jumlah'), 'jumlah' => $baris->count()];
    }

    /** @return array<string, mixed> */
    public static function baris(RealisasiKeuangan $r, int $no): array
    {
        return [
            'no' => $no,
            'id' => $r->id,
            'tanggal' => $r->tanggal->format('d/m/Y'),
            'kode' => $r->subKegiatan->kode,
            'nama' => $r->subKegiatan->nama,
            'bidang' => $r->subKegiatan->bidang->kode,
            'uraian' => $r->uraian,
            'no_sp2d' => $r->no_sp2d,
            'jumlah' => $r->jumlah,
            'lampiran' => (bool) $r->lampiran_path,
            'dicatat_oleh' => $r->pembuat?->name,
        ];
    }
}
