<?php

namespace App\Http\Controllers;

use App\Http\Requests\Target\UpdateTargetTriwulanRequest;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\TargetTriwulan;
use App\Services\SerapanCalculator;
use App\Services\TahunAnggaranService;
use App\Services\TargetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Layar Target (docs/26): pilih sub kegiatan → 4 baris TW → simpan. */
class TargetTriwulanController extends Controller
{
    public function __construct(private readonly TargetService $service, private readonly SerapanCalculator $kalkulator) {}

    public function index(Request $request): View
    {
        return view('target.index', $this->dataPilihan($request, null));
    }

    public function show(Request $request, SubKegiatan $subKegiatan): View
    {
        $this->authorize('view', [TargetTriwulan::class, $subKegiatan]);

        $target = $subKegiatan->targetTriwulan()->get()->keyBy('triwulan');
        $baris = collect([1, 2, 3, 4])->map(fn (int $tw) => [
            'triwulan' => $tw,
            'keuangan' => (int) old("target.{$tw}.keuangan", $target->get($tw)?->target_keuangan ?? 0),
            'fisik' => (float) old("target.{$tw}.fisik", $target->get($tw)?->target_fisik ?? 0),
            'persen' => $this->kalkulator->targetPersen($subKegiatan->pagu, (int) ($target->get($tw)?->target_keuangan ?? 0)),
        ]);

        return view('target.show', $this->dataPilihan($request, $subKegiatan) + [
            'subKegiatan' => $subKegiatan->load(['bidang', 'sumberDana', 'kegiatan.program']),
            'baris' => $baris,
            'terkunci' => $subKegiatan->tahunAnggaran()->isTerkunci(),
            'bolehTulis' => $request->user()->can('update', [TargetTriwulan::class, $subKegiatan]),
        ]);
    }

    public function update(UpdateTargetTriwulanRequest $request, SubKegiatan $subKegiatan): RedirectResponse
    {
        $this->service->simpan($subKegiatan, $request->baris());

        return redirect()->route('target.show', $subKegiatan)->with('sukses', 'Target triwulan berhasil disimpan');
    }

    /** Select sub kegiatan tahun aktif; Operator hanya bidangnya (P2 docs/06, scope forUser). */
    private function dataPilihan(Request $request, ?SubKegiatan $terpilih): array
    {
        $tahun = TahunAnggaranService::aktif() ?? TahunAnggaran::orderByDesc('tahun')->first();

        $pilihan = $tahun
            ? SubKegiatan::forUser($request->user())
                ->whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $tahun->id))
                ->with('kegiatan')->orderBy('kode')->get()
            : collect();

        return ['tahun' => $tahun, 'pilihan' => $pilihan, 'terpilih' => $terpilih];
    }
}
