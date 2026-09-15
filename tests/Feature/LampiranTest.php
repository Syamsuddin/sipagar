<?php

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    $this->admin = User::factory()->admin()->create();
    $sk = SubKegiatan::factory()->create();
    $this->r = RealisasiKeuangan::factory()->for($sk)->create(['created_by' => $this->admin->id, 'lampiran_path' => 'lampiran/2025/1.pdf']);
    Storage::disk('private')->put('lampiran/2025/1.pdf', '%PDF-1.4 uji');
});

test('semua peran login boleh mengunduh lampiran', function (string $peran) {
    $user = match ($peran) {
        'admin' => $this->admin, 'operator' => User::factory()->operator()->create(), default => User::factory()->pimpinan()->create()
    };

    $this->actingAs($user)->get(route('realisasi.lampiran.show', $this->r))
        ->assertOk()->assertHeader('Content-Type', 'application/pdf');
})->with(['admin', 'operator', 'pimpinan']);

test('tamu → redirect login; user nonaktif → 403', function () {
    $this->get(route('realisasi.lampiran.show', $this->r))->assertRedirect(route('login'));

    $nonaktif = User::factory()->nonaktif()->create();
    $this->actingAs($nonaktif)->get(route('realisasi.lampiran.show', $this->r))->assertForbidden();
});

test('lampiran tidak ada → 404; berkas tidak pernah publik', function () {
    $tanpa = RealisasiKeuangan::factory()->for($this->r->subKegiatan)->create(['created_by' => $this->admin->id]);
    $this->actingAs($this->admin)->get(route('realisasi.lampiran.show', $tanpa))->assertNotFound();

    expect(config('filesystems.disks.private.serve'))->toBeFalse()
        ->and(config('filesystems.disks.private.root'))->toBe(storage_path('app/private'));
});
