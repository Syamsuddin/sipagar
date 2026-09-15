<?php

namespace App\Http\Controllers\Anggaran;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggaran\StoreSubKegiatanRequest;
use App\Http\Requests\Anggaran\UpdateSubKegiatanRequest;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Services\AnggaranService;
use Illuminate\Http\RedirectResponse;

class SubKegiatanController extends Controller
{
    public function __construct(private readonly AnggaranService $service) {}

    public function store(StoreSubKegiatanRequest $request): RedirectResponse
    {
        $kegiatan = Kegiatan::findOrFail($request->integer('kegiatan_id'));
        $this->service->tambahSubKegiatan($kegiatan, $request->safe()->except('kegiatan_id'));

        return redirect()->route('anggaran.index', ['tahun' => $kegiatan->tahunAnggaran()->tahun])->with('sukses', 'Sub kegiatan baru berhasil ditambahkan');
    }

    public function update(UpdateSubKegiatanRequest $request, SubKegiatan $subKegiatan): RedirectResponse
    {
        $this->service->ubahSubKegiatan($subKegiatan, $request->validated());

        return redirect()->route('anggaran.index', ['tahun' => $subKegiatan->tahunAnggaran()->tahun])->with('sukses', 'Sub kegiatan berhasil diperbarui');
    }

    public function destroy(SubKegiatan $subKegiatan): RedirectResponse
    {
        $this->authorize('delete', $subKegiatan);
        $this->service->hapusSubKegiatan($subKegiatan);

        return redirect()->route('anggaran.index', ['tahun' => $subKegiatan->tahunAnggaran()->tahun])->with('info', 'Berhasil dihapus');
    }
}
