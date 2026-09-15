<?php

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiFisik;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\LaporanFilter;
use App\Queries\MonevTriwulanQuery;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->bidangA = Bidang::factory()->create(['kode' => 'A']);
    $this->sumber = SumberDana::factory()->create(['kode' => 'DAK']);
    $program = Program::factory()->for($this->tahun, 'tahunAnggaran')->create(['kode' => '5.03.01']);
    $kegiatan = Kegiatan::factory()->for($program)->create();
    $this->sk = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangA)->pagu(200_000_000)->create(['sumber_dana_id' => $this->sumber->id, 'kode' => '5.03.01.2.01.0001']);
    foreach ([1 => [50_000_000, 25], 2 => [100_000_000, 50], 3 => [150_000_000, 75], 4 => [200_000_000, 100]] as $tw => [$keu, $fisik]) {
        $this->sk->targetTriwulan()->create(['triwulan' => $tw, 'target_keuangan' => $keu, 'target_fisik' => $fisik]);
    }
    RealisasiKeuangan::factory()->for($this->sk)->create(['tanggal' => '2025-02-10', 'jumlah' => 40_000_000, 'created_by' => $this->admin->id]);
    RealisasiKeuangan::factory()->for($this->sk)->create(['tanggal' => '2025-05-20', 'jumlah' => 80_000_000, 'created_by' => $this->admin->id]);
    RealisasiFisik::factory()->for($this->sk)->create(['bulan' => 2, 'persen' => 20]);
    RealisasiFisik::factory()->for($this->sk)->create(['bulan' => 5, 'persen' => 55]);
});

test('Monev TW2: kolom lengkap, kumulatif s.d. 30 Jun, subtotal per Program & total (docs/04)', function () {
    $hasil = app(MonevTriwulanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 2]));
    $b = $hasil['kelompok'][0]['baris'][0];

    expect($hasil['jumlah'])->toBe(1)
        ->and($hasil['kelompok'][0]['program']['kode'])->toBe('5.03.01')
        ->and($b)->toMatchArray(['pagu' => 200_000_000, 'target_keu' => 100_000_000, 'target_keu_persen' => 50, 'realisasi' => 120_000_000, 'realisasi_persen' => 60, 'deviasi_keu' => 10, 'target_fisik' => 50.0, 'realisasi_fisik' => 55.0, 'deviasi_fisik' => 5.0])
        ->and($b['status']->value)->toBe('sedang')
        ->and($hasil['kelompok'][0]['subtotal']['realisasi'])->toBe(120_000_000)
        ->and($hasil['total'])->toMatchArray(['pagu' => 200_000_000, 'realisasi' => 120_000_000, 'realisasi_persen' => 60]);

    // TW1: realisasi s.d. 31 Mar = 40 jt; fisik s.d. bulan 3 = bulan terisi terakhir (Feb) = 20
    $tw1 = app(MonevTriwulanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 1]))['kelompok'][0]['baris'][0];
    expect($tw1)->toMatchArray(['realisasi' => 40_000_000, 'realisasi_persen' => 20, 'deviasi_keu' => -5, 'realisasi_fisik' => 20.0, 'deviasi_fisik' => -5.0]);
});

test('layar web menampilkan tabel & filter; semua peran boleh; filter bidang/sumber dana bekerja', function () {
    foreach ([$this->admin, User::factory()->operator()->create(), User::factory()->pimpinan()->create()] as $u) {
        $this->actingAs($u)->get(route('laporan.monev.index', ['triwulan' => 2]))->assertOk()
            ->assertSee('data-table', false)->assertSee('class="tab-btn', false)->assertSee('Subtotal 5.03.01')->assertSee('Rp 120.000.000')->assertSee('Unduh Excel')->assertSee('Unduh PDF');
    }
    $lain = Bidang::factory()->create();
    $this->actingAs($this->admin)->get(route('laporan.monev.index', ['bidang' => $lain->id]))->assertOk()->assertSee('empty-state', false);
    $this->actingAs($this->admin)->get(route('laporan.monev.index', ['sumber_dana' => $this->sumber->id]))->assertOk()->assertSee('5.03.01.2.01.0001');
});

test('filter tanpa tahun → tahun aktif & TW berjalan; tanpa tahun anggaran sama sekali → empty-state', function () {
    $f = LaporanFilter::dari([]);
    expect($f->tahun->tahun)->toBe(2025)->and($f->triwulan)->toBe(LaporanFilter::triwulanBerjalan(2025));
    expect(LaporanFilter::triwulanBerjalan(2000))->toBe(4);

    // hapus berurutan (FK RESTRICT docs/07) agar tidak ada tahun anggaran sama sekali
    RealisasiFisik::query()->delete();
    RealisasiKeuangan::query()->forceDelete();
    $this->sk->targetTriwulan()->delete();
    SubKegiatan::query()->forceDelete();
    Kegiatan::query()->forceDelete();
    Program::query()->forceDelete();
    TahunAnggaran::query()->delete();
    $this->actingAs($this->admin)->get(route('laporan.monev.index'))->assertOk()->assertSee('Belum ada tahun anggaran');
});

test('unduh xlsx → spreadsheet, pdf → application/pdf > 1 KB, nama berkas sipagar_monev_<tahun>_TWn', function () {
    $this->actingAs($this->admin)->get(route('laporan.monev.index', ['triwulan' => 2, 'export' => 'xlsx']))
        ->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload('sipagar_monev_2025_TW2.xlsx');

    $pdf = $this->actingAs($this->admin)->get(route('laporan.monev.index', ['triwulan' => 2, 'export' => 'pdf']))
        ->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('sipagar_monev_2025_TW2.pdf');
    expect(strlen($pdf->getContent()))->toBeGreaterThan(1024);
});
