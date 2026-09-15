<?php

namespace App\Http\Controllers\Realisasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Realisasi\UpdateRealisasiFisikRequest;
use App\Models\RealisasiFisik;
use App\Models\SubKegiatan;
use App\Queries\DashboardQuery;
use App\Services\RealisasiService;
use App\Services\SerapanCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Layar Realisasi › Fisik (docs/26): pilih sub kegiatan → grid 12 bulan. */
class RealisasiFisikController extends Controller
{
    public function __construct(private readonly RealisasiService $service, private readonly SerapanCalculator $kalkulator) {}

    public function index(Request $request): View
    {
        return view('realisasi.fisik.index', $this->dataPilihan($request, null));
    }

    public function show(Request $request, SubKegiatan $subKegiatan): View
    {
        $this->authorize('view', [RealisasiFisik::class, $subKegiatan]);

        $fisik = $this->kalkulator->fisikPerBulan($subKegiatan);
        $target = $subKegiatan->targetTriwulan()->pluck('target_fisik', 'triwulan')->map(fn ($v) => (float) $v);

        return view('realisasi.fisik.show', $this->dataPilihan($request, $subKegiatan) + [
            'subKegiatan' => $subKegiatan->load(['bidang', 'sumberDana']),
            'fisik' => $fisik,
            'target' => $target,
            'deviasi' => $target->map(fn (float $t, int $tw) => $this->kalkulator->deviasiFisik($fisik, $tw, $t)),
            'terkunci' => $subKegiatan->tahunAnggaran()->isTerkunci(),
            'bolehTulis' => $request->user()->can('update', [RealisasiFisik::class, $subKegiatan]),
        ]);
    }

    public function update(UpdateRealisasiFisikRequest $request, SubKegiatan $subKegiatan): RedirectResponse
    {
        $this->service->simpanFisik($subKegiatan, $request->persenPerBulan(), $request->user());

        return redirect()->route('realisasi.fisik.show', $subKegiatan)->with('sukses', 'Realisasi fisik disimpan');
    }

    private function dataPilihan(Request $request, ?SubKegiatan $terpilih): array
    {
        $tahun = DashboardQuery::tahunDefault();
        $pilihan = $tahun
            ? SubKegiatan::forUser($request->user())
                ->whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $tahun->id))->orderBy('kode')->get()
            : collect();

        return ['tahun' => $tahun, 'pilihan' => $pilihan, 'terpilih' => $terpilih, 'basis' => url('/realisasi/fisik')];
    }
}
