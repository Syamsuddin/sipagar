<?php

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\LaporanFilter;
use App\Queries\RekapQuery;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();
    $this->bA = Bidang::factory()->create(['kode' => 'A']);
    $this->bB = Bidang::factory()->create(['kode' => 'B']);
    $this->sDak = SumberDana::factory()->create(['kode' => 'DAK']);
    $this->sApbd = SumberDana::factory()->create(['kode' => 'APBD II']);
    $s1 = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bA)->pagu(100)->create(['sumber_dana_id' => $this->sDak->id]);
    $s2 = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bA)->pagu(300)->create(['sumber_dana_id' => $this->sApbd->id]);
    $s3 = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bB)->pagu(600)->create(['sumber_dana_id' => $this->sApbd->id]);
    RealisasiKeuangan::factory()->for($s1)->create(['tanggal' => '2025-01-05', 'jumlah' => 100, 'created_by' => $this->admin->id]);
    RealisasiKeuangan::factory()->for($s3)->create(['tanggal' => '2025-08-05', 'jumlah' => 300, 'created_by' => $this->admin->id]);
});

test('rekap per sumber dana & bidang: Σ per kelompok = Σ total; serapan agregat dari Σ bukan rata-rata (docs/13 #9)', function () {
    $hasil = app(RekapQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 4]));

    $bidangA = $hasil['bidang']->firstWhere('kode', 'A');
    $bidangB = $hasil['bidang']->firstWhere('kode', 'B');
    expect($bidangA)->toMatchArray(['jumlah_sub' => 2, 'pagu' => 400, 'realisasi' => 100, 'sisa' => 300, 'serapan' => 25])
        ->and($bidangB)->toMatchArray(['jumlah_sub' => 1, 'pagu' => 600, 'realisasi' => 300, 'serapan' => 50])
        ->and($hasil['total'])->toMatchArray(['pagu' => 1000, 'realisasi' => 400, 'sisa' => 600, 'serapan' => 40, 'jumlah_sub' => 3])
        ->and($hasil['bidang']->sum('pagu'))->toBe($hasil['total']['pagu'])
        ->and($hasil['sumber_dana']->sum('realisasi'))->toBe($hasil['total']['realisasi'])
        ->and($hasil['sumber_dana']->firstWhere('kode', 'APBD II'))->toMatchArray(['pagu' => 900, 'realisasi' => 300, 'serapan' => 33]);

    // preset TW2 = 1 Jan–30 Jun: realisasi Agustus belum masuk
    $tw2 = app(RekapQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 2]));
    expect($tw2['total']['realisasi'])->toBe(100)->and($tw2['bidang']->firstWhere('kode', 'B')['status']->value)->toBe('aman');

    // rentang tanggal eksplisit menang atas TW (keputusan pemilik S4)
    $agu = LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 2, 'dari' => '2025-08-01', 'sampai' => '2025-08-31']);
    expect($agu->tanggalEksplisit)->toBeTrue()->and($agu->periodeRentang())->toBe('2025-08-01_2025-08-31')
        ->and(app(RekapQuery::class)->jalankan($agu)['total']['realisasi'])->toBe(300);
    // nilai preset yang dikirim ulang form tetap dianggap TW
    $ulang = LaporanFilter::dari(['tahun' => 2025, 'triwulan' => 2, 'dari' => '2025-01-01', 'sampai' => '2025-06-30']);
    expect($ulang->tanggalEksplisit)->toBeFalse()->and($ulang->periodeRentang())->toBe('TW2');
});

test('layar web dua tabel + unduh xlsx/pdf', function () {
    $this->actingAs(User::factory()->pimpinan()->create())->get(route('laporan.rekap.index'))->assertOk()
        ->assertSee('Per Sumber Dana')->assertSee('Per Bidang')->assertSee('data-table', false);

    $this->actingAs($this->admin)->get(route('laporan.rekap.index', ['triwulan' => 4, 'export' => 'xlsx']))->assertOk()->assertDownload('sipagar_rekap_2025_TW4.xlsx');
    $this->actingAs($this->admin)->get(route('laporan.rekap.index', ['dari' => '2025-08-01', 'sampai' => '2025-08-31', 'export' => 'xlsx']))->assertOk()->assertDownload('sipagar_rekap_2025_2025-08-01_2025-08-31.xlsx');
    $this->actingAs($this->admin)->get(route('laporan.rekap.index'))->assertOk()->assertSee('name="dari"', false)->assertSee('name="triwulan"', false);
    $pdf = $this->actingAs($this->admin)->get(route('laporan.rekap.index', ['triwulan' => 4, 'export' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(strlen($pdf->getContent()))->toBeGreaterThan(1024);
});
