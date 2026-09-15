<?php

use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiFisik;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;

beforeEach(function () {
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->bidangA = Bidang::factory()->create();
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();
    $this->skA = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangA)->create();
    $this->skB = SubKegiatan::factory()->for($kegiatan)->untukBidang(Bidang::factory()->create())->create();
    $this->operatorA = User::factory()->operator($this->bidangA)->create();
    $this->admin = User::factory()->admin()->create();
});

test('upsert 12 bulan: bulan kosong dilewati/dihapus, terisi disimpan; audit tercatat', function () {
    $this->actingAs($this->operatorA)->put(route('realisasi.fisik.update', $this->skA), ['fisik' => [1 => 10, 2 => '', 3 => 25.5, 6 => 40]])
        ->assertRedirect(route('realisasi.fisik.show', $this->skA))->assertSessionHas('sukses', 'Realisasi fisik disimpan');

    expect(RealisasiFisik::where('sub_kegiatan_id', $this->skA->id)->pluck('persen', 'bulan')->map(fn ($v) => (float) $v)->all())
        ->toBe([1 => 10.0, 3 => 25.5, 6 => 40.0]);

    $this->actingAs($this->operatorA)->put(route('realisasi.fisik.update', $this->skA), ['fisik' => [1 => 10, 3 => '', 6 => 45, 7 => 50]])->assertRedirect();
    expect(RealisasiFisik::where('sub_kegiatan_id', $this->skA->id)->pluck('persen', 'bulan')->map(fn ($v) => (float) $v)->all())
        ->toBe([1 => 10.0, 6 => 45.0, 7 => 50.0])
        ->and(AuditLog::where('auditable_id', $this->skA->id)->where('action', 'updated')->count())->toBe(2);
});

test('menurun dari bulan terisi sebelumnya → 422 menyebut bulan; > 100 → 422', function () {
    $this->actingAs($this->admin)->put(route('realisasi.fisik.update', $this->skA), ['fisik' => [1 => 30, 2 => '', 4 => 20]])
        ->assertSessionHasErrors(['fisik.4' => 'Fisik bulan 4 (20 %) tidak boleh menurun dari bulan 1 (30 %)']);
    $this->actingAs($this->admin)->put(route('realisasi.fisik.update', $this->skA), ['fisik' => [1 => 101]])
        ->assertSessionHasErrors(['fisik.1' => 'Persen maksimal 100']);
    expect(RealisasiFisik::count())->toBe(0);
});

test('scope bidang: operator lain 403 tulis / 200 baca; pimpinan 403 tulis', function () {
    $this->actingAs($this->operatorA)->put(route('realisasi.fisik.update', $this->skB), ['fisik' => [1 => 10]])->assertForbidden();
    $this->actingAs($this->operatorA)->get(route('realisasi.fisik.show', $this->skB))->assertOk()->assertDontSee('Simpan Realisasi Fisik');
    $this->actingAs($this->operatorA)->get(route('realisasi.fisik.index'))->assertOk()->assertSee($this->skA->kode)->assertDontSee($this->skB->kode);
    $this->actingAs(User::factory()->pimpinan()->create())->put(route('realisasi.fisik.update', $this->skA), ['fisik' => [1 => 10]])->assertForbidden();
    $this->actingAs($this->admin)->put(route('realisasi.fisik.update', $this->skB), ['fisik' => [1 => 10]])->assertRedirect()->assertSessionHas('sukses');
});

test('tahun terkunci → 423', function () {
    $terkunci = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $sk = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($terkunci, 'tahunAnggaran')))->create();

    $this->actingAs($this->admin)->put(route('realisasi.fisik.update', $sk), ['fisik' => [1 => 10]])->assertStatus(423);
    $this->actingAs($this->admin)->get(route('realisasi.fisik.show', $sk))->assertOk()->assertSee('Terkunci');
});
