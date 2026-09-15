<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('halaman login tampil dengan layout auth prototipe', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('auth-box', false)
        ->assertSee('inputBox', false)
        ->assertSee('btn-login', false)
        ->assertSee('name="_token"', false)
        ->assertDontSee('Daftar Akun')
        ->assertDontSee('Lupa Kata Sandi');
});

test('login benar → redirect dashboard, last_login_at terisi, audit login', function () {
    $user = User::factory()->admin()->create(['username' => 'admin1', 'password' => 'Sandi1234']);

    $this->post(route('login.store'), ['username' => 'admin1', 'password' => 'Sandi1234'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
    expect(AuditLog::where('action', 'login')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('username tidak peka huruf besar-kecil', function () {
    User::factory()->create(['username' => 'siti', 'password' => 'Sandi1234']);

    $this->post(route('login.store'), ['username' => 'SITI', 'password' => 'Sandi1234'])->assertRedirect(route('dashboard'));
});

test('sandi salah → kembali dengan error, tidak login', function () {
    User::factory()->create(['username' => 'siti', 'password' => 'Sandi1234']);

    $this->from(route('login'))->post(route('login.store'), ['username' => 'siti', 'password' => 'salah'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('sandi salah 5x dalam 1 menit → 429 dengan pesan waktu tunggu', function () {
    RateLimiter::clear('siti|127.0.0.1');
    User::factory()->create(['username' => 'siti', 'password' => 'Sandi1234']);

    foreach (range(1, 5) as $i) {
        $this->post(route('login.store'), ['username' => 'siti', 'password' => 'salah'])->assertRedirect();
    }

    $this->post(route('login.store'), ['username' => 'siti', 'password' => 'Sandi1234'])
        ->assertStatus(429)
        ->assertSee('Terlalu banyak percobaan, coba lagi dalam')
        ->assertHeader('Retry-After');
    $this->assertGuest();
});

test('user nonaktif → login ditolak "Akun nonaktif"', function () {
    User::factory()->nonaktif()->create(['username' => 'mati', 'password' => 'Sandi1234']);

    $this->post(route('login.store'), ['username' => 'mati', 'password' => 'Sandi1234'])
        ->assertSessionHasErrors(['username' => 'Akun nonaktif']);
    $this->assertGuest();
});

test('logout mengakhiri sesi dan dicatat audit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
    expect(AuditLog::where('action', 'logout')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('tamu diarahkan ke login; user login diarahkan dari /login ke dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('login'))->assertRedirect(route('dashboard'));
});

test('header keamanan docs/21 terpasang', function () {
    $this->get(route('login'))
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'same-origin');
    expect($this->get(route('login'))->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");
});

test('konfirmasi sandi: benar → ok; salah 3x → 403 lalu 429', function () {
    $user = User::factory()->create(['password' => 'Sandi1234']);
    RateLimiter::clear('konfirmasi-sandi:'.$user->id);

    $this->actingAs($user)->postJson(route('konfirmasi-sandi'), ['password' => 'Sandi1234'])
        ->assertOk()->assertJson(['ok' => true]);

    foreach (range(1, 3) as $i) {
        $this->actingAs($user)->postJson(route('konfirmasi-sandi'), ['password' => 'salah'])
            ->assertForbidden()->assertJson(['message' => "Kata sandi salah! Percobaan {$i} dari 3"]);
    }
    $this->actingAs($user)->postJson(route('konfirmasi-sandi'), ['password' => 'Sandi1234'])->assertStatus(429);
});

test('user nonaktif dengan sesi tersisa → 403 pada route ber-auth', function () {
    $nonaktif = User::factory()->nonaktif()->create();
    $this->actingAs($nonaktif)->get(route('dashboard'))->assertForbidden();
    $this->actingAs($nonaktif)->get(route('anggaran.index'))->assertForbidden();
});
