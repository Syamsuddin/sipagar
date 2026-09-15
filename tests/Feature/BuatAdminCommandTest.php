<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/** TEM-012 (review-vcbd): `sipagar:buat-admin` = langkah pertama produksi (docs/25, docs/11). */
test('sipagar:buat-admin membuat admin aktif dengan sandi bcrypt & username lower-case', function () {
    $this->artisan('sipagar:buat-admin')
        ->expectsQuestion('Nama tampil', 'Kepala BKPSDM')
        ->expectsQuestion('Username', 'Kaban.HSS')
        ->expectsQuestion('Kata sandi (min 8, huruf+angka)', 'Rahasia2026')
        ->expectsOutput("Admin 'kaban.hss' dibuat.")
        ->assertSuccessful();

    $user = User::where('username', 'kaban.hss')->firstOrFail();
    expect($user->role)->toBe(Role::Admin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->bidang_id)->toBeNull()
        ->and(Hash::check('Rahasia2026', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('Rahasia2026');
});

test('sipagar:buat-admin menolak sandi lemah, username tidak valid, dan username duplikat (docs/21)', function () {
    User::factory()->admin()->create(['username' => 'admin']);

    $this->artisan('sipagar:buat-admin')
        ->expectsQuestion('Nama tampil', 'A')
        ->expectsQuestion('Username', 'admin')
        ->expectsQuestion('Kata sandi (min 8, huruf+angka)', 'hanyahuruf')
        ->assertFailed();

    $this->artisan('sipagar:buat-admin')
        ->expectsQuestion('Nama tampil', 'B')
        ->expectsQuestion('Username', 'ab')
        ->expectsQuestion('Kata sandi (min 8, huruf+angka)', 'Sandi1234')
        ->assertFailed();

    $this->artisan('sipagar:buat-admin')
        ->expectsQuestion('Nama tampil', 'C')
        ->expectsQuestion('Username', 'kaban')
        ->expectsQuestion('Kata sandi (min 8, huruf+angka)', '1234567')
        ->assertFailed();

    expect(User::count())->toBe(1);
});
