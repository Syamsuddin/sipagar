<?php

namespace App\Http\Controllers\Anggaran;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggaran\StoreProgramRequest;
use App\Http\Requests\Anggaran\UpdateProgramRequest;
use App\Models\Bidang;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Services\AnggaranService;
use App\Services\TahunAnggaranService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Layar Anggaran (docs/26): pohon Program › Kegiatan › Sub Kegiatan per tahun. */
class ProgramController extends Controller
{
    public function __construct(private readonly AnggaranService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SubKegiatan::class);

        $daftarTahun = TahunAnggaran::orderByDesc('tahun')->get();
        $tahun = $request->filled('tahun')
            ? $daftarTahun->firstWhere('tahun', $request->integer('tahun'))
            : (TahunAnggaranService::aktif() ?? $daftarTahun->first());

        $program = $tahun
            ? $tahun->program()->with(['kegiatan.subKegiatan.bidang', 'kegiatan.subKegiatan.sumberDana'])->get()
            : collect();

        return view('anggaran.index', [
            'daftarTahun' => $daftarTahun,
            'tahun' => $tahun,
            'program' => $program,
            'terkunci' => $tahun?->isTerkunci() ?? false,
            'daftarBidang' => Bidang::where('is_active', true)->orderBy('urutan')->get(),
            'daftarSumber' => SumberDana::where('is_active', true)->orderBy('urutan')->get(),
        ]);
    }

    public function store(StoreProgramRequest $request): RedirectResponse
    {
        $ta = TahunAnggaran::findOrFail($request->integer('tahun_anggaran_id'));
        $this->service->tambahProgram($ta, $request->safe()->except('tahun_anggaran_id'));

        return redirect()->route('anggaran.index', ['tahun' => $ta->tahun])->with('sukses', 'Program baru berhasil ditambahkan');
    }

    public function update(UpdateProgramRequest $request, Program $program): RedirectResponse
    {
        $this->service->ubahProgram($program, $request->validated());

        return redirect()->route('anggaran.index', ['tahun' => $program->tahunAnggaran->tahun])->with('sukses', 'Program berhasil diperbarui');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorize('delete', new SubKegiatan);
        $this->service->hapusProgram($program);

        return redirect()->route('anggaran.index', ['tahun' => $program->tahunAnggaran->tahun])->with('info', 'Berhasil dihapus');
    }
}
