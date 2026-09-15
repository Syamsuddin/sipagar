<?php

namespace App\Http\Controllers\Anggaran;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggaran\StoreKegiatanRequest;
use App\Http\Requests\Anggaran\UpdateKegiatanRequest;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Services\AnggaranService;
use Illuminate\Http\RedirectResponse;

class KegiatanController extends Controller
{
    public function __construct(private readonly AnggaranService $service) {}

    public function store(StoreKegiatanRequest $request): RedirectResponse
    {
        $program = Program::findOrFail($request->integer('program_id'));
        $this->service->tambahKegiatan($program, $request->safe()->except('program_id'));

        return redirect()->route('anggaran.index', ['tahun' => $program->tahunAnggaran->tahun])->with('sukses', 'Kegiatan baru berhasil ditambahkan');
    }

    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan): RedirectResponse
    {
        $this->service->ubahKegiatan($kegiatan, $request->validated());

        return redirect()->route('anggaran.index', ['tahun' => $kegiatan->tahunAnggaran()->tahun])->with('sukses', 'Kegiatan berhasil diperbarui');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        $this->authorize('delete', new SubKegiatan);
        $this->service->hapusKegiatan($kegiatan);

        return redirect()->route('anggaran.index', ['tahun' => $kegiatan->tahunAnggaran()->tahun])->with('info', 'Berhasil dihapus');
    }
}
