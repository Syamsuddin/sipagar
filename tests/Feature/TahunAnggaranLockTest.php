<?php

use App\Enums\StatusTahun;
use App\Http\Middleware\KonfirmasiSandi;
use App\Models\AuditLog;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->sk = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran')))->create();
    $this->konfirmasi = [KonfirmasiSandi::KUNCI_SESI => time()];
});

test('kunci tahun butuh Admin + konfirmasi sandi; hasil: terkunci, locked_at/by, audit lock_tahun; semua tulis → 423', function () {
    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.kunci', $this->tahun))->assertForbidden();
    $this->actingAs(User::factory()->operator()->create())->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.kunci', $this->tahun))->assertForbidden();
    expect($this->tahun->fresh()->status)->toBe(StatusTahun::Aktif);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.kunci', $this->tahun))
        ->assertRedirect(route('master.tahun-anggaran.index'))->assertSessionHas('sukses', 'Tahun anggaran 2025 dikunci');

    $ta = $this->tahun->fresh();
    expect($ta->status)->toBe(StatusTahun::Terkunci)->and($ta->locked_at)->not->toBeNull()->and($ta->locked_by)->toBe($this->admin->id);

    $log = AuditLog::where('action', 'lock_tahun')->where('auditable_id', $ta->id)->firstOrFail();
    expect($log->user_id)->toBe($this->admin->id)->and($log->old_values['status'])->toBe('aktif')->and($log->new_values['status'])->toBe('terkunci')
        ->and(AuditLog::where('auditable_type', TahunAnggaran::class)->where('auditable_id', $ta->id)->where('action', 'updated')->count())->toBe(0);

    // tulis apa pun pada tahun itu → 423; laporan tetap 200 (docs/13 #5)
    $this->actingAs($this->admin)->post(route('anggaran.program.store'), ['tahun_anggaran_id' => $ta->id, 'kode' => '1.01.01', 'nama' => 'X'])->assertStatus(423);
    $this->actingAs($this->admin)->put(route('target.update', $this->sk), ['target' => []])->assertStatus(423);
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), ['sub_kegiatan_id' => $this->sk->id, 'tanggal' => '2025-01-01', 'jumlah' => 1, 'uraian' => 'x'])->assertStatus(423);
    $this->actingAs($this->admin)->put(route('realisasi.fisik.update', $this->sk), ['fisik' => [1 => 10]])->assertStatus(423);
    $this->actingAs($this->admin)->get(route('laporan.monev.index', ['tahun' => 2025]))->assertOk();
    $this->actingAs($this->admin)->get(route('anggaran.index', ['tahun' => 2025]))->assertOk()->assertSee('Terkunci');

    // kunci ulang → 422
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.kunci', $ta))->assertSessionHasErrors('status');
});

test('buka kunci → draft, locked_* kosong, audit unlock_tahun; lalu bisa diaktifkan lagi', function () {
    $ta = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024, 'locked_by' => $this->admin->id]);

    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.buka', $ta))->assertForbidden();
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.buka', $ta))->assertRedirect()->assertSessionHas('info');

    $ta->refresh();
    expect($ta->status)->toBe(StatusTahun::Draft)->and($ta->locked_at)->toBeNull()->and($ta->locked_by)->toBeNull()
        ->and(AuditLog::where('action', 'unlock_tahun')->where('auditable_id', $ta->id)->where('user_id', $this->admin->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.buka', $ta))->assertSessionHasErrors('status');

    // 2025 masih aktif → aktifkan 2024 ditolak; kunci 2025 lalu aktifkan 2024 → ok
    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $ta), ['status' => 'aktif'])->assertSessionHasErrors(['status' => 'Kunci tahun 2025 dulu']);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->post(route('master.tahun-anggaran.kunci', $this->tahun))->assertRedirect();
    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $ta), ['status' => 'aktif'])->assertRedirect();
    expect($ta->fresh()->status)->toBe(StatusTahun::Aktif)->and(TahunAnggaran::aktif()->count())->toBe(1);
});

test('layar Tahun Anggaran menampilkan tombol Kunci/Buka & modal sandi hanya untuk Admin', function () {
    TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $this->actingAs($this->admin)->get(route('master.tahun-anggaran.index'))->assertOk()
        ->assertSee('title="Kunci tahun"', false)->assertSee('title="Buka kunci"', false)->assertSee('id="modal-kunci-tahun"', false);
});
