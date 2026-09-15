<?php

use App\Models\User;

test('dashboard tampil dengan komponen prototipe untuk semua peran', function (string $peran) {
    $user = match ($peran) {
        'admin' => User::factory()->admin()->create(),
        'operator' => User::factory()->operator()->create(),
        default => User::factory()->pimpinan()->create(),
    };

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('class="tab-btn', false)
        ->assertSee('stat-card', false)
        ->assertSee('data-table', false)
        ->assertSee('chart-grid', false)
        ->assertSee($user->name);
})->with(['admin', 'operator', 'pimpinan']);

test('menu Master/Pengguna/Audit hanya tampil untuk Admin', function () {
    $adminHtml = $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->getContent();
    $opHtml = $this->actingAs(User::factory()->operator()->create())->get(route('dashboard'))->getContent();

    expect(substr_count($adminHtml, 'role="tab"'))->toBe(8)
        ->and(substr_count($opHtml, 'role="tab"'))->toBe(5)
        ->and($opHtml)->not->toContain('>Pengguna<');
});

test('dashboard memuat aset lewat Vite, bukan CDN', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('dashboard'))->getContent();

    expect($html)->toContain('/build/assets/')
        ->not->toContain('cdn.tailwindcss.com')
        ->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('cdnjs.cloudflare.com');
});

test('root mengarah ke halaman login', function () {
    $this->get('/')->assertRedirect('/login');
});
