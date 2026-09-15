<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\RekapExport;
use App\Http\Requests\Laporan\FilterLaporanRequest;
use App\Queries\RekapQuery;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class RekapController extends LaporanController
{
    public function __construct(private readonly RekapQuery $query) {}

    public function index(FilterLaporanRequest $request): View|Response
    {
        $f = $request->filter();
        $hasil = $f ? $this->query->jalankan($f) : null;

        if ($f && $hasil && $request->export() === 'xlsx') {
            return $this->unduhXlsx(new RekapExport($hasil), $this->namaBerkas('rekap', $f, $f->periodeTw(), 'xlsx'));
        }
        if ($f && $hasil && $request->export() === 'pdf') {
            return $this->unduhPdf('pdf.rekap', ['hasil' => $hasil], $this->namaBerkas('rekap', $f, $f->periodeTw(), 'pdf'));
        }

        return view('laporan.rekap.index', $this->dataFilter($f) + ['hasil' => $hasil]);
    }
}
