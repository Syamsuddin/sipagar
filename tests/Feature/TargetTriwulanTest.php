<?php

use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\TargetTriwulan;
use App\Models\User;

beforeEach(function () {
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->bidangA = Bidang::factory()->create(['kode' => 'A']);
    $this->bidangB = Bidang::factory()->create(['kode' => 'B']);
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();
    $this->skA = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangA)->pagu(100_000_000)->create();
    $this->skB = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangB)->pagu(50_000_000)->create();
    $this->operatorA = User::factory()->operator($this->bidangA)->create();
    $this->admin = User::factory()->admin()->create();
});

function payloadTarget(array $keu, array $fisik = [25, 50, 75, 100]): array
{
    $t = [];
    foreach ([1, 2, 3, 4] as $i) {
        $t[$i] = ['keuangan' => $keu[$i - 1], 'fisik' => $fisik[$i - 1]];
    }

    return ['target' => $t];
}

test('Operator bidangnya menyimpan 4 baris sekaligus (upsert) + audit', function () {
    $this->actingAs($this->operatorA)->put(route('target.update', $this->skA), payloadTarget([20_000_000, '50.000.000', 80_000_000, 100_000_000]))
        ->assertRedirect(route('target.show', $this->skA))->assertSessionHas('sukses');

    $target = TargetTriwulan::where('sub_kegiatan_id', $this->skA->id)->orderBy('triwulan')->get();
    expect($target)->toHaveCount(4)
        ->and($target[1]->target_keuangan)->toBe(50_000_000)
        ->and((float) $target[3]->target_fisik)->toBe(100.0);

    // simpan ulang → tetap 4 baris (upsert)
    $this->actingAs($this->operatorA)->put(route('target.update', $this->skA), payloadTarget([30_000_000, 50_000_000, 80_000_000, 100_000_000]))->assertRedirect();
    expect(TargetTriwulan::where('sub_kegiatan_id', $this->skA->id)->count())->toBe(4)
        ->and(TargetTriwulan::where('sub_kegiatan_id', $this->skA->id)->where('triwulan', 1)->value('target_keuangan'))->toBe(30_000_000)
        // audit per baris (TargetTriwulan ✎): 4 created saat pertama, 1 updated (TW1 berubah) saat kedua
        ->and(AuditLog::where('auditable_type', TargetTriwulan::class)->where('action', 'created')->count())->toBe(4)
        ->and(AuditLog::where('auditable_type', TargetTriwulan::class)->where('action', 'updated')->count())->toBe(1)
        ->and(AuditLog::where('auditable_type', TargetTriwulan::class)->where('action', 'updated')->first()->new_values)->toMatchArray(['target_keuangan' => 30_000_000]);
});

test('tidak monoton → 422 menyebut TW yang salah', function () {
    $this->actingAs($this->admin)->put(route('target.update', $this->skA), payloadTarget([50_000_000, 40_000_000, 80_000_000, 100_000_000]))
        ->assertSessionHasErrors(['target.2.keuangan' => 'Target keuangan TW2 tidak boleh lebih kecil dari TW1']);

    $this->actingAs($this->admin)->put(route('target.update', $this->skA), payloadTarget([10_000_000, 40_000_000, 80_000_000, 100_000_000], [25, 50, 40, 100]))
        ->assertSessionHasErrors(['target.3.fisik' => 'Target fisik TW3 tidak boleh lebih kecil dari TW2']);
    expect(TargetTriwulan::count())->toBe(0);
});

test('TW4 keuangan ≠ pagu atau fisik ≠ 100 → 422', function () {
    $this->actingAs($this->admin)->put(route('target.update', $this->skA), payloadTarget([10_000_000, 40_000_000, 80_000_000, 90_000_000]))
        ->assertSessionHasErrors(['target.4.keuangan' => 'Target keuangan TW4 harus sama dengan pagu Rp 100.000.000']);
    $this->actingAs($this->admin)->put(route('target.update', $this->skA), payloadTarget([10_000_000, 40_000_000, 80_000_000, 100_000_000], [25, 50, 75, 90]))
        ->assertSessionHasErrors(['target.4.fisik' => 'Target fisik TW4 harus 100 %']);
    $this->actingAs($this->admin)->put(route('target.update', $this->skA), ['target' => [1 => ['keuangan' => 1, 'fisik' => 1]]])
        ->assertSessionHasErrors('target');
});

test('Operator bidang lain → 403 (tulis) tetapi baca 200; Admin semua bidang ✓; Pimpinan 403', function () {
    $this->actingAs($this->operatorA)->put(route('target.update', $this->skB), payloadTarget([10_000_000, 20_000_000, 30_000_000, 50_000_000]))->assertForbidden();
    $this->actingAs($this->operatorA)->get(route('target.show', $this->skB))->assertOk()->assertDontSee('Simpan Target');

    $this->actingAs($this->admin)->put(route('target.update', $this->skB), payloadTarget([10_000_000, 20_000_000, 30_000_000, 50_000_000]))->assertRedirect()->assertSessionHas('sukses');
    $this->actingAs(User::factory()->pimpinan()->create())->put(route('target.update', $this->skA), payloadTarget([10, 20, 30, 100_000_000]))->assertForbidden();
});

test('select Target: Operator hanya melihat sub kegiatan bidangnya; Admin semua', function () {
    $this->actingAs($this->operatorA)->get(route('target.index'))->assertOk()->assertSee($this->skA->kode)->assertDontSee($this->skB->kode);
    $this->actingAs($this->admin)->get(route('target.index'))->assertOk()->assertSee($this->skA->kode)->assertSee($this->skB->kode);
    $this->actingAs($this->admin)->get(route('target.show', $this->skA))->assertOk()->assertSee('data-table', false)->assertSee('TW4');
});

test('tahun terkunci → 423', function () {
    $terkunci = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $sk = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($terkunci, 'tahunAnggaran')))->untukBidang($this->bidangA)->pagu(100)->create();

    $this->actingAs($this->admin)->put(route('target.update', $sk), payloadTarget([10, 20, 30, 100]))->assertStatus(423);
    expect(TargetTriwulan::count())->toBe(0);
});
