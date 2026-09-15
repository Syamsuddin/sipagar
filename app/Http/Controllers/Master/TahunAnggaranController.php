<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTahunAnggaranRequest;
use App\Http\Requests\Master\UpdateTahunAnggaranRequest;
use App\Models\TahunAnggaran;
use App\Services\TahunAnggaranService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TahunAnggaranController extends Controller
{
    public function __construct(private readonly TahunAnggaranService $service) {}

    public function index(): View
    {
        return view('master.tahun-anggaran.index', [
            'daftar' => TahunAnggaran::with('lockedBy')->orderByDesc('tahun')->get(),
        ]);
    }

    public function store(StoreTahunAnggaranRequest $request): RedirectResponse
    {
        $ta = $this->service->tambah($request->integer('tahun'));

        return redirect()->route('master.tahun-anggaran.index')->with('sukses', "Tahun anggaran {$ta->tahun} ditambahkan (draft)");
    }

    public function update(UpdateTahunAnggaranRequest $request, TahunAnggaran $tahunAnggaran): RedirectResponse
    {
        $this->service->aktifkan($tahunAnggaran);

        return redirect()->route('master.tahun-anggaran.index')->with('sukses', "Tahun anggaran {$tahunAnggaran->tahun} diaktifkan");
    }
}
