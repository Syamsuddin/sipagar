<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\MonevTriwulanExport;
use App\Http\Requests\Laporan\FilterLaporanRequest;
use App\Queries\MonevTriwulanQuery;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class MonevTriwulanController extends LaporanController
{
    public function __construct(private readonly MonevTriwulanQuery $query) {}

    public function index(FilterLaporanRequest $request): View|Response
    {
        $f = $request->filter();
        $hasil = $f ? $this->query->jalankan($f) : null;

        if ($f && $hasil && $request->export() === 'xlsx') {
            return $this->unduhXlsx(new MonevTriwulanExport($hasil), $this->namaBerkas('monev', $f, $f->periodeTw(), 'xlsx'));
        }
        if ($f && $hasil && $request->export() === 'pdf') {
            return $this->unduhPdf('pdf.monev', ['hasil' => $hasil], $this->namaBerkas('monev', $f, $f->periodeTw(), 'pdf'), 'landscape');
        }

        return view('laporan.monev.index', $this->dataFilter($f) + ['hasil' => $hasil]);
    }
}
