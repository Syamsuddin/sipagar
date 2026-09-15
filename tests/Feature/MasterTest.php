<?php

use App\Enums\StatusTahun;
use App\Models\Bidang;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use Database\Seeders\MasterSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('semua layar master hanya untuk Admin (operator/pimpinan → 403)', function (string $rute) {
    $this->actingAs(User::factory()->operator()->create())->get(route($rute))->assertForbidden();
    $this->actingAs(User::factory()->pimpinan()->create())->get(route($rute))->assertForbidden();
    $this->actingAs($this->admin)->get(route($rute))->assertOk()->assertSee('class="tab-btn', false);
})->with(['master.bidang.index', 'master.sumber-dana.index', 'master.tahun-anggaran.index', 'master.pengaturan.index']);

test('seed master: 5 bidang, 15 sumber dana prototipe, idempoten', function () {
    $this->seed(MasterSeeder::class);
    $this->seed(MasterSeeder::class);

    expect(Bidang::count())->toBe(5)
        ->and(SumberDana::count())->toBe(15)
        ->and(SumberDana::where('kode', 'APBD II')->value('css_class'))->toBe('sd-apbd')
        ->and(SumberDana::where('kode', 'TPP')->value('nama'))->toBe('Tunjangan Kinerja (TPP)')
        ->and(Setting::count())->toBe(count(Setting::KUNCI));
});

test('CRUD bidang: tambah, ubah, kode duplikat → 422', function () {
    $this->actingAs($this->admin)->post(route('master.bidang.store'), ['kode' => 'SEK', 'nama' => 'Sekretariat', 'urutan' => 1])
        ->assertRedirect(route('master.bidang.index'))->assertSessionHas('sukses');
    $b = Bidang::where('kode', 'SEK')->firstOrFail();

    $this->actingAs($this->admin)->post(route('master.bidang.store'), ['kode' => 'SEK', 'nama' => 'Lain'])
        ->assertSessionHasErrors(['kode' => 'Kode sudah dipakai']);

    $this->actingAs($this->admin)->put(route('master.bidang.update', $b), ['kode' => 'SEK', 'nama' => 'Sekretariat Badan', 'urutan' => 2, 'is_active' => 0])
        ->assertRedirect(route('master.bidang.index'));
    expect($b->fresh())->nama->toBe('Sekretariat Badan')->is_active->toBeFalse();

    $this->actingAs($this->admin)->get(route('master.bidang.index'))->assertSee('Sekretariat Badan')->assertSee('1 bidang');
});

test('CRUD sumber dana: kode unik, css_class harus sd-*', function () {
    $this->actingAs($this->admin)->post(route('master.sumber-dana.store'), ['kode' => 'DAK', 'nama' => 'Dana Alokasi Khusus', 'css_class' => 'sd-dak'])
        ->assertRedirect(route('master.sumber-dana.index'));
    $s = SumberDana::where('kode', 'DAK')->firstOrFail();

    $this->actingAs($this->admin)->post(route('master.sumber-dana.store'), ['kode' => 'DAK', 'nama' => 'X', 'css_class' => 'sd-dak'])
        ->assertSessionHasErrors('kode');
    $this->actingAs($this->admin)->post(route('master.sumber-dana.store'), ['kode' => 'BARU', 'nama' => 'X', 'css_class' => 'merah'])
        ->assertSessionHasErrors('css_class');

    $this->actingAs($this->admin)->put(route('master.sumber-dana.update', $s), ['kode' => 'DAK', 'nama' => 'DAK Fisik', 'css_class' => 'sd-dak', 'is_active' => 1])
        ->assertRedirect(route('master.sumber-dana.index'));
    expect($s->fresh()->nama)->toBe('DAK Fisik');
    $this->actingAs($this->admin)->get(route('master.sumber-dana.index'))->assertSee('badge sd-dak', false);
});

test('tahun anggaran: tambah draft, di luar rentang / duplikat → 422', function () {
    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.store'), ['tahun' => 2025])
        ->assertRedirect(route('master.tahun-anggaran.index'))->assertSessionHas('sukses');
    expect(TahunAnggaran::where('tahun', 2025)->value('status'))->toBe(StatusTahun::Draft);

    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.store'), ['tahun' => 2025])->assertSessionHasErrors(['tahun' => 'Tahun anggaran sudah ada']);
    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.store'), ['tahun' => 2019])->assertSessionHasErrors('tahun');
    $this->actingAs($this->admin)->post(route('master.tahun-anggaran.store'), ['tahun' => 2035])->assertSessionHasErrors('tahun');
});

test('hanya satu tahun aktif: aktifkan saat ada tahun aktif → 422 "Kunci tahun N dulu"', function () {
    $aktif = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $draft = TahunAnggaran::factory()->create(['tahun' => 2026]);

    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $draft), ['status' => 'aktif'])
        ->assertSessionHasErrors(['status' => 'Kunci tahun 2025 dulu']);
    expect($draft->fresh()->status)->toBe(StatusTahun::Draft);

    $aktif->update(['status' => StatusTahun::Terkunci]);
    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $draft), ['status' => 'aktif'])
        ->assertRedirect(route('master.tahun-anggaran.index'));
    expect($draft->fresh()->status)->toBe(StatusTahun::Aktif)
        ->and(TahunAnggaran::aktif()->count())->toBe(1);
});

test('tahun terkunci tidak bisa diaktifkan lewat form; status selain aktif ditolak', function () {
    $kunci = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);

    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $kunci), ['status' => 'aktif'])->assertSessionHasErrors('status');
    $this->actingAs($this->admin)->put(route('master.tahun-anggaran.update', $kunci), ['status' => 'draft'])->assertSessionHasErrors('status');
    expect($kunci->fresh()->status)->toBe(StatusTahun::Terkunci);
});

test('pengaturan kop/ttd tersimpan di settings dan tampil kembali', function () {
    $this->actingAs($this->admin)->put(route('master.pengaturan.update'), [
        'kop_nama_instansi' => 'BKPSDM HSS', 'kop_alamat' => 'Jl. Contoh 1', 'ttd_nama' => 'Budi', 'ttd_nip' => '19700101', 'ttd_jabatan' => 'Kepala', 'ttd_kota' => 'Kandangan',
    ])->assertRedirect(route('master.pengaturan.index'))->assertSessionHas('sukses');

    expect(Setting::nilai('ttd_nama'))->toBe('Budi')->and(Setting::semua()['kop_nama_instansi'])->toBe('BKPSDM HSS');
    $this->actingAs($this->admin)->get(route('master.pengaturan.index'))->assertSee('Jl. Contoh 1');

    $this->actingAs($this->admin)->put(route('master.pengaturan.update'), ['kop_nama_instansi' => ''])->assertSessionHasErrors('kop_nama_instansi');
});
