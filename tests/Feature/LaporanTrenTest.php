<?php

use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\LaporanFilter;
use App\Queries\TrenSerapanQuery;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $lalu = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $this->sk = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran')))->pagu(1_200_000_000)->create();
    foreach ([1 => 300_000_000, 2 => 600_000_000, 3 => 900_000_000, 4 => 1_200_000_000] as $tw => $t) {
        $this->sk->targetTriwulan()->create(['triwulan' => $tw, 'target_keuangan' => $t, 'target_fisik' => $tw * 25]);
    }
    RealisasiKeuangan::factory()->for($this->sk)->create(['tanggal' => '2025-01-15', 'jumlah' => 120_000_000, 'created_by' => $this->admin->id]);
    RealisasiKeuangan::factory()->for($this->sk)->create(['tanggal' => '2025-03-15', 'jumlah' => 240_000_000, 'created_by' => $this->admin->id]);
    $skLalu = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($lalu, 'tahunAnggaran')))->pagu(1_000_000_000)->create();
    RealisasiKeuangan::factory()->for($skLalu)->create(['tanggal' => '2024-02-01', 'jumlah' => 500_000_000, 'created_by' => $this->admin->id]);
});

test('kurva kumulatif: realisasi, target interpolasi TW, tahun lalu', function () {
    $hasil = app(TrenSerapanQuery::class)->jalankan(LaporanFilter::dari(['tahun' => 2025]));
    $b = $hasil['baris'];

    expect($hasil['pagu'])->toBe(1_200_000_000)->and($hasil['tahun_lalu'])->toBe(2024)
        ->and($b[1]['realisasi'])->toBe(120_000_000)->and($b[2]['realisasi'])->toBe(120_000_000)->and($b[3]['realisasi'])->toBe(360_000_000)->and($b[12]['realisasi'])->toBe(360_000_000)
        ->and($b[1]['target'])->toBe(100_000_000)->and($b[2]['target'])->toBe(200_000_000)->and($b[3]['target'])->toBe(300_000_000)->and($b[6]['target'])->toBe(600_000_000)->and($b[12]['target'])->toBe(1_200_000_000)
        ->and($b[3]['realisasi_persen'])->toBe(30)->and($b[3]['target_persen'])->toBe(25)->and($b[3]['deviasi'])->toBe(5)
        ->and($b[1]['realisasi_lalu'])->toBe(0)->and($b[2]['realisasi_lalu'])->toBe(500_000_000)->and($b[2]['realisasi_lalu_persen'])->toBe(50)
        ->and($hasil['grafik']['labels'])->toHaveCount(12)->and($hasil['grafik']['realisasi'][11])->toBe(30);
});

test('layar web grafik + tabel 12 bulan; tanpa data → empty-state; unduh xlsx/pdf', function () {
    $this->actingAs(User::factory()->operator()->create())->get(route('laporan.tren.index'))->assertOk()
        ->assertSee('grafikGaris', false)->assertSee('data-table', false)->assertSee('Des')->assertSee('Rp 360.000.000');

    TahunAnggaran::factory()->create(['tahun' => 2026]);
    $this->actingAs($this->admin)->get(route('laporan.tren.index', ['tahun' => 2026]))->assertOk()->assertSee('empty-state', false);

    $this->actingAs($this->admin)->get(route('laporan.tren.index', ['export' => 'xlsx']))->assertOk()->assertDownload('sipagar_tren_2025_bulanan.xlsx');
    $pdf = $this->actingAs($this->admin)->get(route('laporan.tren.index', ['export' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(strlen($pdf->getContent()))->toBeGreaterThan(1024);
});
