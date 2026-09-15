<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\BukuRealisasiExport;
use App\Http\Requests\Laporan\FilterLaporanRequest;
use App\Models\SubKegiatan;
use App\Queries\BukuRealisasiQuery;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class BukuRealisasiController extends LaporanController
{
    public function __construct(private readonly BukuRealisasiQuery $query) {}

    public function index(FilterLaporanRequest $request): View|Response
    {
        $f = $request->filter();
        $periode = $f ? $f->dari->toDateString().'_'.$f->sampai->toDateString() : '';

        if ($f && $request->export() === 'xlsx') {
            return $this->unduhXlsx(new BukuRealisasiExport($this->query->semua($f)), $this->namaBerkas('buku-realisasi', $f, $periode, 'xlsx'));
        }
        if ($f && $request->export() === 'pdf') {
            return $this->unduhPdf('pdf.buku', ['hasil' => $this->query->semua($f)], $this->namaBerkas('buku-realisasi', $f, $periode, 'pdf'), 'landscape');
        }

        $pilihanSub = $f
            ? SubKegiatan::whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $f->tahun->id))->orderBy('kode')->get(['id', 'kode', 'nama'])
            : collect();

        return view('laporan.buku.index', $this->dataFilter($f) + [
            'hasil' => $f ? $this->query->halaman($f, (int) $request->validated('halaman', 1)) : null,
            'pilihanSub' => $pilihanSub,
        ]);
    }
}
