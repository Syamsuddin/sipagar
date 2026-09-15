<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\TrenSerapanExport;
use App\Http\Requests\Laporan\FilterLaporanRequest;
use App\Queries\TrenSerapanQuery;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class TrenSerapanController extends LaporanController
{
    public function __construct(private readonly TrenSerapanQuery $query) {}

    public function index(FilterLaporanRequest $request): View|Response
    {
        $f = $request->filter();
        $hasil = $f ? $this->query->jalankan($f) : null;

        if ($f && $hasil && $request->export() === 'xlsx') {
            return $this->unduhXlsx(new TrenSerapanExport($hasil), $this->namaBerkas('tren', $f, 'bulanan', 'xlsx'));
        }
        if ($f && $hasil && $request->export() === 'pdf') {
            return $this->unduhPdf('pdf.tren', ['hasil' => $hasil], $this->namaBerkas('tren', $f, 'bulanan', 'pdf'));
        }

        return view('laporan.tren.index', $this->dataFilter($f) + ['hasil' => $hasil]);
    }
}
