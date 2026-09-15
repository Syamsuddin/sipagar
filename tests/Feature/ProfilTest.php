<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('ubah sandi dengan sandi lama benar & baru ≥ 8 huruf+angka → sukses + toast', function () {
    $user = User::factory()->create(['password' => 'Lama1234']);

    $this->actingAs($user)->from(route('dashboard'))->post(route('profil.sandi'), [
        'sandi_lama' => 'Lama1234', 'sandi_baru' => 'Baru5678', 'sandi_baru_confirmation' => 'Baru5678',
    ])->assertRedirect(route('dashboard'))->assertSessionHas('sukses', 'Kata sandi berhasil diubah!');

    expect(Hash::check('Baru5678', $user->fresh()->password))->toBeTrue();
});

test('sandi lama salah → error field sandi_lama, sandi tidak berubah', function () {
    $user = User::factory()->create(['password' => 'Lama1234']);

    $this->actingAs($user)->post(route('profil.sandi'), [
        'sandi_lama' => 'salah', 'sandi_baru' => 'Baru5678', 'sandi_baru_confirmation' => 'Baru5678',
    ])->assertSessionHasErrors(['sandi_lama' => 'Kata sandi lama salah']);

    expect(Hash::check('Lama1234', $user->fresh()->password))->toBeTrue();
});

test('sandi baru lemah (tanpa angka / < 8) → 422', function (string $baru) {
    $user = User::factory()->create(['password' => 'Lama1234']);

    $this->actingAs($user)->post(route('profil.sandi'), [
        'sandi_lama' => 'Lama1234', 'sandi_baru' => $baru, 'sandi_baru_confirmation' => $baru,
    ])->assertSessionHasErrors('sandi_baru');
})->with(['hanyahuruf', 'Ab1']);
