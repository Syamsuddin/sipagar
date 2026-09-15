<?php

use App\Exports\BukuRealisasiExport;
use App\Exports\MonevTriwulanExport;
use App\Exports\RekapExport;
use App\Exports\TrenSerapanExport;
use App\Models\Bidang;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\BukuRealisasiQuery;
use App\Queries\DashboardQuery;
use App\Queries\LaporanFilter;
use App\Queries\MonevTriwulanQuery;
use App\Queries\RekapQuery;
use App\Queries\TrenSerapanQuery;
use Database\Seeders\PrototipeSeeder;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->seed(PrototipeSeeder::class);
    $this->admin = User::where('username', 'admin')->firstOrFail();
});

test('xlsx: Excel::fake + kelas & jumlah baris; angka int = angka Query (docs/13 ekspor & #9)', function () {
    Excel::fake();
    $f = LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]);

    $this->actingAs($this->admin)->get(route('laporan.monev.index', ['triwulan' => 4, 'export' => 'xlsx']))->assertOk();
    $monev = app(MonevTriwulanQuery::class)->jalankan($f);
    Excel::assertDownloaded('sipagar_monev_2025_TW4.xlsx', function (MonevTriwulanExport $e) use ($monev) {
        $rows = $e->array();
        $total = end($rows);

        // per Program: 1 baris judul + n baris + 1 subtotal; lalu 1 total
        return count($rows) === $monev['jumlah'] + 2 * $monev['kelompok']->count() + 1 && $total[5] === $monev['total']['pagu'] && $total[8] === $monev['total']['realisasi'] && is_int($total[5]);
    });

    $this->actingAs($this->admin)->get(route('laporan.rekap.index', ['triwulan' => 4, 'export' => 'xlsx']))->assertOk();
    $rekap = app(RekapQuery::class)->jalankan($f);
    Excel::assertDownloaded('sipagar_rekap_2025_TW4.xlsx', function (RekapExport $e) use ($rekap) {
        [$sumber, $bidang] = $e->sheets();
        $b = $bidang->array();

        return count($sumber->array()) === $rekap['sumber_dana']->count() + 1 && end($b)[4] === $rekap['total']['pagu'] && $rekap['total']['pagu'] === 3_520_000_000;
    });

    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['triwulan' => 4, 'export' => 'xlsx']))->assertOk();
    $buku = app(BukuRealisasiQuery::class)->semua(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]));
    Excel::assertDownloaded('sipagar_buku-realisasi_2025_TW4.xlsx', function (BukuRealisasiExport $e) use ($buku) {
        $rows = $e->array();
        $total = end($rows);

        return count($rows) === $buku['jumlah'] + 1 && $total[7] === 1_173_000_000 && $buku['jumlah'] === 14;
    });

    $this->actingAs($this->admin)->get(route('laporan.tren.index', ['export' => 'xlsx']))->assertOk();
    $tren = app(TrenSerapanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025]));
    Excel::assertDownloaded('sipagar_tren_2025_bulanan.xlsx', function (TrenSerapanExport $e) use ($tren) {
        $rows = $e->array();

        return count($rows) === 12 && $rows[11][1] === $tren['baris'][12]['realisasi'] && $rows[11][1] === 1_173_000_000;
    });
});

test('pdf: application/pdf > 1 KB untuk keempat laporan; data kosong tetap berisi header + "Tidak ada data"', function () {
    foreach (['laporan.monev.index', 'laporan.rekap.index', 'laporan.buku.index', 'laporan.tren.index'] as $rute) {
        $res = $this->actingAs($this->admin)->get(route($rute, ['export' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
        expect(strlen($res->getContent()))->toBeGreaterThan(1024, $rute);
    }

    // 2026 (draft): hanya 1 sub kegiatan tanpa realisasi; monev bidang lain → kosong
    $bidangKosong = Bidang::factory()->create();
    $kosong = new MonevTriwulanExport(app(MonevTriwulanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2026, 'bidang' => $bidangKosong->id])));
    expect($kosong->array())->toBe([['Tidak ada data']])->and($kosong->headings())->toContain('Sub Kegiatan');
    $pdf = $this->actingAs($this->admin)->get(route('laporan.monev.index', ['tahun' => 2026, 'bidang' => $bidangKosong->id, 'export' => 'pdf']))->assertOk();
    expect(strlen($pdf->getContent()))->toBeGreaterThan(1024);
});

test('web = export: total pagu/realisasi Monev TW4 2025 identik dengan dashboard 2025', function () {
    $monev = app(MonevTriwulanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]));
    $dash = app(DashboardQuery::class)->jalankan(TahunAnggaran::where('tahun', 2025)->value('id'), null);

    expect($monev['total']['pagu'])->toBe($dash['total']['pagu'])->toBe(3_520_000_000)
        ->and($monev['total']['realisasi'])->toBe($dash['total']['realisasi'])->toBe(1_173_000_000)
        ->and($monev['total']['realisasi_persen'])->toBe($dash['total']['serapan']);
});
