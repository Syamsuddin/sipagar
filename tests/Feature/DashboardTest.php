<?php

// S0: dashboard dummy tanpa auth. S1 (F01) menambahkan skenario tamu → redirect login.

test('dashboard tampil dengan komponen prototipe', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('class="tab-btn', false)
        ->assertSee('stat-card', false)
        ->assertSee('data-table', false)
        ->assertSee('chart-grid', false)
        ->assertSee('Rp 2.8 M');
});

test('dashboard memuat nav delapan menu docs/26 dan Dashboard aktif', function () {
    $html = $this->get(route('dashboard'))->getContent();

    foreach (['Dashboard', 'Anggaran', 'Target', 'Realisasi', 'Laporan', 'Master', 'Pengguna', 'Audit'] as $menu) {
        expect($html)->toContain($menu);
    }
    expect(substr_count($html, 'role="tab"'))->toBe(8)
        ->and(substr_count($html, 'aria-selected="true"'))->toBe(1);
});

test('dashboard memuat aset lewat Vite, bukan CDN', function () {
    $html = $this->get(route('dashboard'))->getContent();

    expect($html)->toContain('/build/assets/')
        ->not->toContain('cdn.tailwindcss.com')
        ->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('cdnjs.cloudflare.com');
});

test('root mengarah ke halaman login', function () {
    $this->get('/')->assertRedirect('/login');
});
