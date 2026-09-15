<?php

use App\Enums\Role;
use App\Http\Middleware\KonfirmasiSandi;
use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('Admin melihat daftar pengguna dengan komponen prototipe', function () {
    User::factory()->count(2)->create();

    $this->actingAs($this->admin)->get(route('pengguna.index'))
        ->assertOk()->assertSee('data-table', false)->assertSee('class="tab-btn', false)->assertSee('3 pengguna');
});

test('Operator dan Pimpinan → 403 pada /pengguna', function (string $peran) {
    $user = $peran === 'operator' ? User::factory()->operator()->create() : User::factory()->pimpinan()->create();

    $this->actingAs($user)->get(route('pengguna.index'))->assertForbidden();
    $this->actingAs($user)->post(route('pengguna.store'), [])->assertForbidden();
})->with(['operator', 'pimpinan']);

test('Admin tambah operator dengan bidang → tersimpan, username lower-case, sandi bcrypt', function () {
    $bidang = Bidang::factory()->create();

    $this->actingAs($this->admin)->post(route('pengguna.store'), [
        'name' => 'Siti', 'username' => 'Siti.A', 'password' => 'Sandi1234', 'role' => 'operator', 'bidang_id' => $bidang->id,
    ])->assertRedirect(route('pengguna.index'))->assertSessionHas('sukses');

    $u = User::where('username', 'siti.a')->first();
    expect($u)->not->toBeNull()
        ->and($u->role)->toBe(Role::Operator)
        ->and($u->bidang_id)->toBe($bidang->id)
        ->and(Hash::check('Sandi1234', $u->password))->toBeTrue();
});

test('tambah operator tanpa bidang → 422 "Bidang wajib untuk Operator"', function () {
    $this->actingAs($this->admin)->post(route('pengguna.store'), [
        'name' => 'Siti', 'username' => 'siti', 'password' => 'Sandi1234', 'role' => 'operator',
    ])->assertSessionHasErrors(['bidang_id' => 'Bidang wajib untuk Operator']);

    expect(User::where('username', 'siti')->exists())->toBeFalse();
});

test('username duplikat / sandi lemah → 422', function () {
    User::factory()->create(['username' => 'siti']);

    $this->actingAs($this->admin)->post(route('pengguna.store'), [
        'name' => 'X', 'username' => 'siti', 'password' => 'lemah', 'role' => 'pimpinan',
    ])->assertSessionHasErrors(['username', 'password']);
});

test('Admin ubah pengguna: pimpinan → operator wajib bidang; bidang dihapus bila bukan operator', function () {
    $bidang = Bidang::factory()->create();
    $u = User::factory()->operator($bidang)->create();

    $this->actingAs($this->admin)->put(route('pengguna.update', $u), [
        'name' => 'Baru', 'username' => $u->username, 'role' => 'pimpinan',
    ])->assertRedirect(route('pengguna.index'));
    expect($u->fresh())->name->toBe('Baru')->role->toBe(Role::Pimpinan)->bidang_id->toBeNull();

    $this->actingAs($this->admin)->put(route('pengguna.update', $u), [
        'name' => 'Baru', 'username' => $u->username, 'role' => 'operator',
    ])->assertSessionHasErrors('bidang_id');
});

test('nonaktifkan pengguna memutus sesinya; Admin tidak bisa menonaktifkan diri sendiri', function () {
    $u = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'sesi-u', 'user_id' => $u->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($this->admin)->patch(route('pengguna.aktif', $u))->assertRedirect(route('pengguna.index'));
    expect($u->fresh()->is_active)->toBeFalse()
        ->and(DB::table('sessions')->where('user_id', $u->id)->exists())->toBeFalse();

    $this->actingAs($this->admin)->patch(route('pengguna.aktif', $this->admin))->assertForbidden();
    expect($this->admin->fresh()->is_active)->toBeTrue();
});

test('reset sandi butuh konfirmasi sandi Admin ≤ 5 menit', function () {
    $u = User::factory()->create(['password' => 'Lama1234']);

    $this->actingAs($this->admin)->post(route('pengguna.reset-sandi', $u), ['password' => 'Baru5678'])->assertForbidden();
    expect(Hash::check('Lama1234', $u->fresh()->password))->toBeTrue();

    $this->actingAs($this->admin)->withSession([KonfirmasiSandi::KUNCI_SESI => time()])
        ->post(route('pengguna.reset-sandi', $u), ['password' => 'Baru5678'])
        ->assertRedirect(route('pengguna.index'))->assertSessionHas('sukses');
    expect(Hash::check('Baru5678', $u->fresh()->password))->toBeTrue()
        ->and(AuditLog::where('action', 'reset_password')->where('auditable_id', $u->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)->withSession([KonfirmasiSandi::KUNCI_SESI => time() - 6 * 60])
        ->post(route('pengguna.reset-sandi', $u), ['password' => 'Lain9999'])->assertForbidden();
});

test('audit log pengguna tidak memuat password', function () {
    $this->actingAs($this->admin)->post(route('pengguna.store'), [
        'name' => 'Siti', 'username' => 'siti', 'password' => 'Sandi1234', 'role' => 'pimpinan',
    ]);

    $log = AuditLog::where('action', 'created')->latest('id')->first();
    expect($log->new_values)->not->toHaveKey('password')->toHaveKey('username');
});
