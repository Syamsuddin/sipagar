<?php

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\BukuRealisasiQuery;
use App\Queries\LaporanFilter;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();
    $this->bA = Bidang::factory()->create(['kode' => 'A']);
    $this->s1 = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bA)->pagu(10_000_000_000)->create();
    $this->s2 = SubKegiatan::factory()->for($kegiatan)->untukBidang(Bidang::factory()->create())->pagu(10_000_000_000)->create();
    foreach (range(1, 60) as $i) {
        RealisasiKeuangan::factory()->for($i <= 40 ? $this->s1 : $this->s2)->create(['tanggal' => sprintf('2025-%02d-%02d', ($i % 12) + 1, ($i % 27) + 1), 'jumlah' => 1_000_000, 'created_by' => $this->admin->id, 'uraian' => "Transaksi {$i}"]);
    }
});

test('paginasi 50 di web, penuh di ekspor; filter tanggal, bidang, sub kegiatan', function () {
    $q = app(BukuRealisasiQuery::class);
    $semua = $q->semua(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]));
    expect($semua['jumlah'])->toBe(60)->and($semua['total'])->toBe(60_000_000)->and($semua['baris'][0]['no'])->toBe(1);

    $hal = $q->halaman(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]), 2);
    expect($hal['halaman']->perPage())->toBe(50)->and($hal['halaman']->count())->toBe(10)->and($hal['total'])->toBe(60_000_000);

    expect($q->semua(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4, 'bidang' => $this->bA->id]))['jumlah'])->toBe(40)
        ->and($q->semua(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4, 'sub_kegiatan' => $this->s2->id]))['jumlah'])->toBe(20)
        ->and($q->semua(LaporanFilter::dari(['tahun' => 2025, 'dari' => '2025-03-01', 'sampai' => '2025-03-31']))['jumlah'])->toBe(5)
        // preset TW1 = 1 Jan–31 Mar (bulan 1..3 → i%12 ∈ {0,1,2})
        ->and($q->semua(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 1]))['jumlah'])->toBe(15);
});

test('layar web halaman 1 & 2, empty-state, unduh xlsx/pdf dengan nama berperiode', function () {
    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['triwulan' => 4]))->assertOk()->assertSee('60 transaksi')->assertSee('Halaman 1 dari 2')->assertSee('Transaksi 1')->assertSee('name="triwulan"', false);
    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['triwulan' => 4, 'halaman' => 2]))->assertOk()->assertSee('Halaman 2 dari 2');
    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['dari' => '2025-12-30', 'sampai' => '2025-12-31']))->assertOk()->assertSee('Belum ada realisasi');
    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['dari' => '2025-05-01', 'sampai' => '2025-01-01']))->assertSessionHasErrors('sampai');

    $this->actingAs($this->admin)->get(route('laporan.buku.index', ['triwulan' => 4, 'export' => 'xlsx']))->assertOk()->assertDownload('sipagar_buku-realisasi_2025_TW4.xlsx');
    $pdf = $this->actingAs($this->admin)->get(route('laporan.buku.index', ['export' => 'pdf', 'dari' => '2025-03-01', 'sampai' => '2025-03-31']))
        ->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('sipagar_buku-realisasi_2025_2025-03-01_2025-03-31.pdf');
    expect(strlen($pdf->getContent()))->toBeGreaterThan(1024);
});
