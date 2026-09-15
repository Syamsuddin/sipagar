<?php

use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use Database\Seeders\PrototipeSeeder;

/**
 * Regresi TEM-008: CSP docs/21 (`script-src 'self' 'unsafe-eval'`) memblokir atribut handler inline
 * (`onchange=`, `onclick=`, …) — filter auto-submit harus lewat Alpine (`@change`), bukan atribut on*.
 */
test('tidak ada atribut event handler inline (on*=) pada halaman ber-auth — dilarang oleh CSP docs/21', function () {
    $login = $this->get(route('login'))->assertOk()->getContent();
    expect(preg_match('/\son[a-z]+=["\']/i', $login))->toBe(0);

    $this->seed(PrototipeSeeder::class);
    $admin = User::factory()->admin()->create();
    $tahun = TahunAnggaran::aktif()->firstOrFail();
    $sk = SubKegiatan::query()->firstOrFail();

    $halaman = [
        route('dashboard'),
        route('anggaran.index', ['tahun' => $tahun->tahun]),
        route('target.index'),
        route('target.show', $sk),
        route('realisasi.keuangan.index'),
        route('realisasi.fisik.show', $sk),
        route('laporan.monev.index'),
        route('laporan.rekap.index'),
        route('laporan.buku.index'),
        route('laporan.tren.index'),
        route('master.bidang.index'),
        route('master.sumber-dana.index'),
        route('master.tahun-anggaran.index'),
        route('master.pengaturan.index'),
        route('pengguna.index'),
        route('audit-log.index'),
    ];

    foreach ($halaman as $url) {
        $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();
        preg_match_all('/\s(on[a-z]+)=["\']/i', $html, $m);
        expect($m[1])->toBe([], "Handler inline ditemukan di {$url}: ".implode(', ', array_unique($m[1])));
    }
});
