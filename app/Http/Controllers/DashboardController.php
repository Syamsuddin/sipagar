<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\TahunAnggaran;
use App\Queries\DashboardQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** F07: semua angka dari DashboardQuery (docs/08, docs/16). Filter tahun (default aktif, 'semua'), bidang. */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardQuery $query) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $daftarTahun = TahunAnggaran::orderByDesc('tahun')->get();

        $tahunParam = $request->input('tahun');
        $tahun = match (true) {
            $tahunParam === 'semua' => 'semua',
            is_numeric($tahunParam) => $daftarTahun->firstWhere('tahun', (int) $tahunParam)?->id,
            default => DashboardQuery::tahunDefault()?->id,
        };

        $bidangId = $request->has('bidang')
            ? ($request->input('bidang') === '' ? null : $request->integer('bidang'))
            : DashboardQuery::bidangDefault($user);

        return view('dashboard.index', [
            'hasil' => $this->query->jalankan($tahun, $bidangId),
            'daftarTahun' => $daftarTahun,
            'daftarBidang' => Bidang::where('is_active', true)->orderBy('urutan')->get(),
            'tahunTerpilih' => $tahun === 'semua' ? 'semua' : $daftarTahun->firstWhere('id', $tahun)?->tahun,
            'bidangTerpilih' => $bidangId,
        ]);
    }
}
