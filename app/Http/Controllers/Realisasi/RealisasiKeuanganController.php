<?php

namespace App\Http\Controllers\Realisasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Realisasi\StoreRealisasiKeuanganRequest;
use App\Http\Requests\Realisasi\UpdateRealisasiKeuanganRequest;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Queries\DashboardQuery;
use App\Services\RealisasiService;
use App\Services\SerapanCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Layar Realisasi › Keuangan (docs/26): form(400) + daftar; select sub kegiatan menampilkan sisa. */
class RealisasiKeuanganController extends Controller
{
    public function __construct(private readonly RealisasiService $service, private readonly SerapanCalculator $kalkulator) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RealisasiKeuangan::class);
        $user = $request->user();
        $tahun = DashboardQuery::tahunDefault();

        $pilihan = $tahun
            ? SubKegiatan::forUser($user)
                ->whereHas('kegiatan.program', fn ($q) => $q->where('tahun_anggaran_id', $tahun->id))
                ->withSum('realisasiKeuangan as realisasi_total', 'jumlah')->orderBy('kode')->get()
                ->map(fn (SubKegiatan $sk) => ['id' => $sk->id, 'label' => "{$sk->kode} — {$sk->nama}", 'pagu' => $sk->pagu, 'sisa' => $this->kalkulator->sisa($sk->pagu, (int) $sk->realisasi_total)])
            : collect();

        $skId = (int) old('sub_kegiatan_id', $request->integer('sub_kegiatan'));
        $terpilih = $skId ? SubKegiatan::with(['bidang', 'sumberDana'])->find($skId) : null;

        $edit = $request->filled('edit') ? RealisasiKeuangan::findOrFail($request->integer('edit')) : null;

        $daftar = $tahun
            ? RealisasiKeuangan::with(['subKegiatan.bidang', 'pembuat'])
                ->whereHas('subKegiatan', fn ($q) => $q->forUser($user)->when($terpilih, fn ($q2) => $q2->where('id', $terpilih->id))
                    ->whereHas('kegiatan.program', fn ($p) => $p->where('tahun_anggaran_id', $tahun->id)))
                ->orderByDesc('tanggal')->orderByDesc('id')->limit(200)->get()
            : collect();

        return view('realisasi.keuangan.index', [
            'tahun' => $tahun,
            'pilihan' => $pilihan,
            'terpilih' => $terpilih,
            'edit' => $edit,
            'daftar' => $daftar,
            'terkunci' => $tahun?->isTerkunci() ?? false,
            'bolehTulis' => $user->isAdmin() || $user->isOperator(),
        ]);
    }

    public function store(StoreRealisasiKeuanganRequest $request): RedirectResponse
    {
        $sk = $request->subKegiatan();
        $this->service->catat($sk, $request->safe()->except(['sub_kegiatan_id', 'lampiran']), $request->user(), $request->file('lampiran'));

        return redirect()->route('realisasi.keuangan.index', ['sub_kegiatan' => $sk->id])->with('sukses', 'Realisasi dicatat');
    }

    public function update(UpdateRealisasiKeuanganRequest $request, RealisasiKeuangan $realisasiKeuangan): RedirectResponse
    {
        $this->service->ubah($realisasiKeuangan, $request->safe()->except(['sub_kegiatan_id', 'lampiran']), $request->user(), $request->file('lampiran'));

        return redirect()->route('realisasi.keuangan.index', ['sub_kegiatan' => $realisasiKeuangan->sub_kegiatan_id])->with('sukses', 'Realisasi diperbarui');
    }

    public function destroy(RealisasiKeuangan $realisasiKeuangan): RedirectResponse
    {
        $this->authorize('delete', $realisasiKeuangan);
        $this->service->hapus($realisasiKeuangan);

        return redirect()->route('realisasi.keuangan.index', ['sub_kegiatan' => $realisasiKeuangan->sub_kegiatan_id])->with('info', 'Berhasil dihapus');
    }
}
