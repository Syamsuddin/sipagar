<?php

use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Queries\DashboardQuery;
use Database\Seeders\PrototipeSeeder;

test('dashboard tampil dengan komponen prototipe untuk semua peran', function (string $peran) {
    $user = match ($peran) {
        'admin' => User::factory()->admin()->create(),
        'operator' => User::factory()->operator()->create(),
        default => User::factory()->pimpinan()->create(),
    };

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()->assertSee('class="tab-btn', false)->assertSee('stat-card', false)->assertSee('chart-grid', false)->assertSee('empty-state', false)->assertSee($user->name);
})->with(['admin', 'operator', 'pimpinan']);

test('kartu, grafik, tabel = hasil DashboardQuery; seed prototipe (semua tahun) → pagu 3,9 M, realisasi 1,33 M, serapan 34%', function () {
    $this->seed(PrototipeSeeder::class);
    $admin = User::factory()->admin()->create();

    $hasil = app(DashboardQuery::class)->jalankan('semua', null);
    expect($hasil['total'])->toMatchArray(['pagu' => 3_900_000_000, 'realisasi' => 1_330_000_000, 'sisa' => 2_570_000_000, 'serapan' => 34, 'jumlah_sub' => 10, 'jumlah_transaksi' => 16])
        ->and($hasil['grafik_bar']['labels'])->toHaveCount(10)
        ->and($hasil['grafik_bar']['labels'][0])->toBe('Pembayaran TPP ASN')
        ->and($hasil['grafik_doughnut']['labels'][0])->toBe('TPP')
        ->and(array_sum($hasil['grafik_doughnut']['data']))->toBe(3_900_000_000);

    $res = $this->actingAs($admin)->get(route('dashboard', ['tahun' => 'semua']))->assertOk();
    $res->assertSee('Rp 3.9 M')->assertSee('Rp 1.3 M')->assertSee('10 sub kegiatan')->assertSee('16 transaksi')->assertSee('34%')->assertSee('66% tersisa')
        ->assertSee('Rp 3.900.000.000')->assertSee('Rp 1.330.000.000')->assertSee('data-table', false);

    // default = tahun aktif 2025: 7 sub kegiatan
    $aktif = app(DashboardQuery::class)->jalankan(TahunAnggaran::where('tahun', 2025)->value('id'), null);
    expect($aktif['total']['pagu'])->toBe(3_520_000_000)->and($aktif['total']['jumlah_sub'])->toBe(7);
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Rp 3.5 M')->assertSee('7 sub kegiatan');
});

test('tabel ringkasan memuat status docs/04 dan kolom lengkap', function () {
    $this->seed(PrototipeSeeder::class);
    $html = $this->actingAs(User::factory()->admin()->create())->get(route('dashboard', ['tahun' => 'semua']))->getContent();

    foreach (['<th>No</th>', '<th>Kode</th>', '<th>Sub Kegiatan</th>', '>Bidang</th>', '>Sumber Dana</th>', '<th>Pagu</th>', '<th>Realisasi</th>', '<th>Sisa</th>', '<th>Serapan</th>', '<th>Status</th>'] as $kolom) {
        expect($html)->toContain($kolom);
    }
    // p5 SIMPEG 205/320 = 64% → Sedang ; p1 145/285 = 51% → Aman
    expect($html)->toContain('Sedang')->toContain('Aman')->not->toContain('Hampir Habis');
});

test('filter bidang; Operator default bidangnya', function () {
    $tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $bidangA = Bidang::factory()->create();
    $bidangB = Bidang::factory()->create();
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($tahun, 'tahunAnggaran'))->create();
    $skA = SubKegiatan::factory()->for($kegiatan)->untukBidang($bidangA)->pagu(100)->create(['nama' => 'Sub Bidang A']);
    $skB = SubKegiatan::factory()->for($kegiatan)->untukBidang($bidangB)->pagu(50)->create(['nama' => 'Sub Bidang B']);
    RealisasiKeuangan::factory()->for($skB)->create(['jumlah' => 50, 'created_by' => User::factory()->admin()->create()->id]);

    $operator = User::factory()->operator($bidangA)->create();
    $this->actingAs($operator)->get(route('dashboard'))->assertOk()->assertSee('Sub Bidang A')->assertDontSee('Sub Bidang B')->assertSee('1 sub kegiatan');
    $this->actingAs($operator)->get(route('dashboard', ['bidang' => '']))->assertOk()->assertSee('Sub Bidang B');

    $hasil = app(DashboardQuery::class)->jalankan($tahun->id, $bidangB->id);
    expect($hasil['total'])->toMatchArray(['pagu' => 50, 'realisasi' => 50, 'serapan' => 100])
        ->and($hasil['baris'][0]['status']->value)->toBe('habis');
});

test('dashboard memuat aset lewat Vite, bukan CDN; root → login', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('dashboard'))->getContent();
    expect($html)->toContain('/build/assets/')->not->toContain('cdn.tailwindcss.com')->not->toContain('cdn.jsdelivr.net')->not->toContain('cdnjs.cloudflare.com');
    $this->get('/')->assertRedirect('/login');
});
