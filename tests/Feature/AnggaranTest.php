<?php

use App\Enums\StatusTahun;
use App\Http\Middleware\KonfirmasiSandi;
use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->bidang = Bidang::factory()->create();
    $this->sumber = SumberDana::factory()->create();
    $this->konfirmasi = [KonfirmasiSandi::KUNCI_SESI => time()];
});

function subKegiatanDasar(TahunAnggaran $ta, Bidang $bidang, SumberDana $sumber, int $pagu = 100_000_000): SubKegiatan
{
    $program = Program::factory()->for($ta, 'tahunAnggaran')->create();
    $kegiatan = Kegiatan::factory()->for($program)->create();

    return SubKegiatan::factory()->for($kegiatan)->untukBidang($bidang)->pagu($pagu)->create(['sumber_dana_id' => $sumber->id]);
}

test('semua peran melihat pohon anggaran; tulis hanya Admin', function () {
    $sk = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber);
    $operator = User::factory()->operator(Bidang::factory()->create())->create();

    foreach ([$this->admin, $operator, User::factory()->pimpinan()->create()] as $u) {
        $this->actingAs($u)->get(route('anggaran.index', ['tahun' => 2025]))
            ->assertOk()->assertSee($sk->kode)->assertSee('data-table', false);
    }
    $this->actingAs($operator)->get(route('anggaran.index'))->assertDontSee('title="Ubah"', false);

    $this->actingAs($operator)->post(route('anggaran.program.store'), ['tahun_anggaran_id' => $this->tahun->id, 'kode' => '1.01.01', 'nama' => 'X'])->assertForbidden();
    $this->actingAs($operator)->withSession($this->konfirmasi)->put(route('anggaran.sub-kegiatan.update', $sk), [])->assertForbidden();
    $this->actingAs($operator)->withSession($this->konfirmasi)->delete(route('anggaran.sub-kegiatan.destroy', $sk))->assertForbidden();
    $this->actingAs(User::factory()->pimpinan()->create())->withSession($this->konfirmasi)->delete(route('anggaran.program.destroy', $sk->kegiatan->program))->assertForbidden();
});

test('Admin membuat Program → Kegiatan → Sub Kegiatan; audit created tercatat', function () {
    $this->actingAs($this->admin)->post(route('anggaran.program.store'), ['tahun_anggaran_id' => $this->tahun->id, 'kode' => '5.01.01', 'nama' => 'Program Penunjang'])
        ->assertRedirect(route('anggaran.index', ['tahun' => 2025]))->assertSessionHas('sukses');
    $program = Program::where('kode', '5.01.01')->firstOrFail();

    $this->actingAs($this->admin)->post(route('anggaran.kegiatan.store'), ['program_id' => $program->id, 'kode' => '5.01.01.2.01', 'nama' => 'Kegiatan A'])->assertRedirect();
    $kegiatan = Kegiatan::where('kode', '5.01.01.2.01')->firstOrFail();

    $this->actingAs($this->admin)->post(route('anggaran.sub-kegiatan.store'), [
        'kegiatan_id' => $kegiatan->id, 'kode' => '5.01.01.2.01.0001', 'nama' => 'Sub A', 'pagu' => 'Rp 250.000.000',
        'bidang_id' => $this->bidang->id, 'sumber_dana_id' => $this->sumber->id, 'pptk' => 'Budi',
    ])->assertRedirect()->assertSessionHas('sukses');

    $sk = SubKegiatan::where('kode', '5.01.01.2.01.0001')->firstOrFail();
    expect($sk->pagu)->toBe(250_000_000)->and($sk->bidang_id)->toBe($this->bidang->id);
    expect(AuditLog::where('action', 'created')->where('auditable_type', SubKegiatan::class)->where('auditable_id', $sk->id)->exists())->toBeTrue();
});

test('kode duplikat pada tingkat & induk sama → 422; induk berbeda boleh', function () {
    $program = Program::factory()->for($this->tahun, 'tahunAnggaran')->create(['kode' => '5.01.01']);
    $programLain = Program::factory()->for(TahunAnggaran::factory()->create(['tahun' => 2026]), 'tahunAnggaran')->create(['kode' => '5.01.02']);

    $this->actingAs($this->admin)->post(route('anggaran.program.store'), ['tahun_anggaran_id' => $this->tahun->id, 'kode' => '5.01.01', 'nama' => 'Dup'])
        ->assertSessionHasErrors(['kode' => 'Kode sudah dipakai pada tingkat & induk yang sama']);

    $kegiatan = Kegiatan::factory()->for($program)->create(['kode' => '5.01.01.2.01']);
    $this->actingAs($this->admin)->post(route('anggaran.kegiatan.store'), ['program_id' => $program->id, 'kode' => '5.01.01.2.01', 'nama' => 'Dup'])->assertSessionHasErrors('kode');
    $this->actingAs($this->admin)->post(route('anggaran.kegiatan.store'), ['program_id' => $programLain->id, 'kode' => '5.01.01.2.01', 'nama' => 'Boleh'])->assertSessionHasNoErrors();

    SubKegiatan::factory()->for($kegiatan)->create(['kode' => '5.01.01.2.01.0001', 'bidang_id' => $this->bidang->id, 'sumber_dana_id' => $this->sumber->id]);
    $this->actingAs($this->admin)->post(route('anggaran.sub-kegiatan.store'), [
        'kegiatan_id' => $kegiatan->id, 'kode' => '5.01.01.2.01.0001', 'nama' => 'Dup', 'pagu' => 1, 'bidang_id' => $this->bidang->id, 'sumber_dana_id' => $this->sumber->id,
    ])->assertSessionHasErrors('kode');
});

test('pagu ≤ 0 atau bukan angka → 422', function (mixed $pagu) {
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();

    $this->actingAs($this->admin)->post(route('anggaran.sub-kegiatan.store'), [
        'kegiatan_id' => $kegiatan->id, 'kode' => '1.1.1.1.1.0001', 'nama' => 'X', 'pagu' => $pagu, 'bidang_id' => $this->bidang->id, 'sumber_dana_id' => $this->sumber->id,
    ])->assertSessionHasErrors('pagu');
})->with([0, -5, 'abc', '']);

test('ubah/hapus butuh konfirmasi sandi; edit pagu < realisasi → 422 dengan nominal', function () {
    $sk = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber, 100_000_000);
    RealisasiKeuangan::factory()->for($sk)->create(['jumlah' => 60_000_000, 'created_by' => $this->admin->id]);
    $data = ['kode' => $sk->kode, 'nama' => $sk->nama, 'pagu' => 50_000_000, 'bidang_id' => $sk->bidang_id, 'sumber_dana_id' => $sk->sumber_dana_id];

    $this->actingAs($this->admin)->put(route('anggaran.sub-kegiatan.update', $sk), $data)->assertForbidden();

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('anggaran.sub-kegiatan.update', $sk), $data)
        ->assertSessionHasErrors(['pagu' => 'Pagu tidak boleh kurang dari realisasi Rp 60.000.000']);
    expect($sk->fresh()->pagu)->toBe(100_000_000);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('anggaran.sub-kegiatan.update', $sk), ['pagu' => 60_000_000, 'nama' => 'Nama Baru'] + $data)
        ->assertRedirect()->assertSessionHas('sukses');
    expect($sk->fresh())->pagu->toBe(60_000_000)->nama->toBe('Nama Baru');
});

test('hapus Sub Kegiatan ber-realisasi → 422; tanpa realisasi → soft delete + audit', function () {
    $sk = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber);
    RealisasiKeuangan::factory()->for($sk)->create(['created_by' => $this->admin->id]);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.sub-kegiatan.destroy', $sk))
        ->assertSessionHasErrors('sub_kegiatan');
    expect($sk->fresh())->not->toBeNull();

    $sk2 = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.sub-kegiatan.destroy', $sk2))
        ->assertRedirect()->assertSessionHas('info', 'Berhasil dihapus');
    expect(SubKegiatan::find($sk2->id))->toBeNull()
        ->and(SubKegiatan::withTrashed()->find($sk2->id)->deleted_at)->not->toBeNull()
        ->and(AuditLog::where('action', 'deleted')->where('auditable_id', $sk2->id)->where('auditable_type', SubKegiatan::class)->exists())->toBeTrue();
});

test('hapus Program/Kegiatan yang masih punya anak → 422', function () {
    $sk = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.program.destroy', $sk->kegiatan->program))->assertSessionHasErrors('program');
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.kegiatan.destroy', $sk->kegiatan))->assertSessionHasErrors('kegiatan');
    expect(Program::count())->toBe(1)->and(Kegiatan::count())->toBe(1);
});

test('tahun terkunci: semua tulis → 423, baca tetap 200', function () {
    $terkunci = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $sk = subKegiatanDasar($terkunci, $this->bidang, $this->sumber);

    $this->actingAs($this->admin)->get(route('anggaran.index', ['tahun' => 2024]))->assertOk()->assertSee('Terkunci')->assertDontSee('title="Ubah"', false);

    $this->actingAs($this->admin)->post(route('anggaran.program.store'), ['tahun_anggaran_id' => $terkunci->id, 'kode' => '1.01.01', 'nama' => 'X'])
        ->assertStatus(423)->assertSee('Tahun anggaran 2024 telah dikunci');
    $this->actingAs($this->admin)->post(route('anggaran.kegiatan.store'), ['program_id' => $sk->kegiatan->program_id, 'kode' => '1.01.01.1.01', 'nama' => 'X'])->assertStatus(423);
    $this->actingAs($this->admin)->post(route('anggaran.sub-kegiatan.store'), ['kegiatan_id' => $sk->kegiatan_id, 'kode' => '1.1', 'nama' => 'X', 'pagu' => 1, 'bidang_id' => $this->bidang->id, 'sumber_dana_id' => $this->sumber->id])->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('anggaran.sub-kegiatan.update', $sk), [])->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.sub-kegiatan.destroy', $sk))->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('anggaran.program.destroy', $sk->kegiatan->program))->assertStatus(423);
    expect(SubKegiatan::count())->toBe(1);
});

test('layar Anggaran default ke tahun aktif dan menampilkan empty-state tanpa program', function () {
    $this->actingAs($this->admin)->get(route('anggaran.index'))->assertOk()->assertSee('Struktur Anggaran 2025')->assertSee('empty-state', false);
});

// TEM-013 (review-vcbd): jalur ubah Program & Kegiatan (docs/05 CRUD, docs/21 konfirmasi sandi, docs/07 ✎)
test('ubah Program & Kegiatan: butuh konfirmasi sandi, tersimpan + audit updated, tahun terkunci → 423', function () {
    $sk = subKegiatanDasar($this->tahun, $this->bidang, $this->sumber);
    $program = $sk->kegiatan->program;
    $kegiatan = $sk->kegiatan;

    $this->actingAs($this->admin)->put(route('anggaran.program.update', $program), ['kode' => $program->kode, 'nama' => 'Program Baru'])->assertForbidden();
    $this->actingAs($this->admin)->put(route('anggaran.kegiatan.update', $kegiatan), ['kode' => $kegiatan->kode, 'nama' => 'Kegiatan Baru'])->assertForbidden();
    expect($program->fresh()->nama)->not->toBe('Program Baru');

    $this->actingAs($this->admin)->withSession($this->konfirmasi)
        ->put(route('anggaran.program.update', $program), ['kode' => $program->kode, 'nama' => 'Program Baru', 'urutan' => 7])
        ->assertRedirect(route('anggaran.index', ['tahun' => 2025]))->assertSessionHas('sukses', 'Program berhasil diperbarui');
    $this->actingAs($this->admin)->withSession($this->konfirmasi)
        ->put(route('anggaran.kegiatan.update', $kegiatan), ['kode' => $kegiatan->kode, 'nama' => 'Kegiatan Baru'])
        ->assertRedirect()->assertSessionHas('sukses');
    expect($program->fresh())->nama->toBe('Program Baru')->urutan->toBe(7)
        ->and($kegiatan->fresh()->nama)->toBe('Kegiatan Baru');

    $logP = AuditLog::where('auditable_type', Program::class)->where('auditable_id', $program->id)->where('action', 'updated')->firstOrFail();
    expect($logP->new_values['nama'])->toBe('Program Baru')->and($logP->old_values)->toHaveKey('nama');
    expect(AuditLog::where('auditable_type', Kegiatan::class)->where('auditable_id', $kegiatan->id)->where('action', 'updated')->count())->toBe(1);

    // Operator tidak boleh, walau punya konfirmasi sandi
    $this->actingAs(User::factory()->operator()->create())->withSession($this->konfirmasi)
        ->put(route('anggaran.program.update', $program), ['kode' => $program->kode, 'nama' => 'X'])->assertForbidden();

    // tahun terkunci → 423
    $this->tahun->update(['status' => StatusTahun::Terkunci]);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)
        ->put(route('anggaran.program.update', $program), ['kode' => $program->kode, 'nama' => 'Y'])->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)
        ->put(route('anggaran.kegiatan.update', $kegiatan), ['kode' => $kegiatan->kode, 'nama' => 'Y'])->assertStatus(423);
    expect($program->fresh()->nama)->toBe('Program Baru');
});
