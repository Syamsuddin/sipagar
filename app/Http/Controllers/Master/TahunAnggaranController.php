<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTahunAnggaranRequest;
use App\Http\Requests\Master\UpdateTahunAnggaranRequest;
use App\Models\TahunAnggaran;
use App\Services\TahunAnggaranService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    /** P5: kunci — route dilindungi konfirmasi-sandi (modal sandi, docs/22). */
    public function kunci(Request $request, TahunAnggaran $tahunAnggaran): RedirectResponse
    {
        $this->authorize('kunci', $tahunAnggaran);
        $this->service->kunci($tahunAnggaran, $request->user());

        return redirect()->route('master.tahun-anggaran.index')->with('sukses', "Tahun anggaran {$tahunAnggaran->tahun} dikunci");
    }

    public function buka(Request $request, TahunAnggaran $tahunAnggaran): RedirectResponse
    {
        $this->authorize('kunci', $tahunAnggaran);
        $this->service->buka($tahunAnggaran, $request->user());

        return redirect()->route('master.tahun-anggaran.index')->with('info', "Kunci tahun anggaran {$tahunAnggaran->tahun} dibuka (status draft)");
    }
}
