<?php

use App\Http\Middleware\KonfirmasiSandi;
use App\Models\AuditLog;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Services\SerapanCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    $this->tahun = TahunAnggaran::factory()->aktif()->create(['tahun' => 2025]);
    $this->bidangA = Bidang::factory()->create(['kode' => 'A']);
    $this->bidangB = Bidang::factory()->create(['kode' => 'B']);
    $kegiatan = Kegiatan::factory()->for(Program::factory()->for($this->tahun, 'tahunAnggaran'))->create();
    $this->skA = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangA)->pagu(100_000_000)->create();
    $this->skB = SubKegiatan::factory()->for($kegiatan)->untukBidang($this->bidangB)->pagu(50_000_000)->create();
    $this->operatorA = User::factory()->operator($this->bidangA)->create();
    $this->admin = User::factory()->admin()->create();
    $this->konfirmasi = [KonfirmasiSandi::KUNCI_SESI => time()];
});

function payloadRealisasi(SubKegiatan $sk, array $ubah = []): array
{
    return array_merge(['sub_kegiatan_id' => $sk->id, 'tanggal' => '2025-03-10', 'jumlah' => 25_000_000, 'uraian' => 'Honor narasumber', 'no_sp2d' => 'SP2D-001'], $ubah);
}

test('transaksi valid → 302 + toast "Realisasi dicatat"; sisa & serapan berkurang', function () {
    $this->actingAs($this->operatorA)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['jumlah' => 'Rp 25.000.000']))
        ->assertRedirect(route('realisasi.keuangan.index', ['sub_kegiatan' => $this->skA->id]))->assertSessionHas('sukses', 'Realisasi dicatat');

    $k = app(SerapanCalculator::class);
    expect($k->realisasi($this->skA))->toBe(25_000_000)
        ->and($k->sisa($this->skA->pagu, $k->realisasi($this->skA)))->toBe(75_000_000)
        ->and($k->serapanPersen($this->skA->pagu, 25_000_000))->toBe(25);

    $this->actingAs($this->operatorA)->get(route('realisasi.keuangan.index', ['sub_kegiatan' => $this->skA->id]))
        ->assertOk()->assertSee('Rp 25.000.000')->assertSee('1 transaksi')->assertSee('data-table', false);
});

test('jumlah > sisa → 422 "Melebihi sisa! Sisa: Rp …"; tepat = sisa → sukses', function () {
    RealisasiKeuangan::factory()->for($this->skA)->create(['jumlah' => 88_000_000, 'created_by' => $this->admin->id]);

    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['jumlah' => 12_000_001]))
        ->assertSessionHasErrors(['jumlah' => 'Melebihi sisa! Sisa: Rp 12.000.000']);
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['jumlah' => 12_000_000]))
        ->assertRedirect()->assertSessionHas('sukses');
    expect(app(SerapanCalculator::class)->realisasi($this->skA))->toBe(100_000_000);
});

test('tanggal di luar tahun anggaran → 422; jumlah ≤ 0 → 422', function () {
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['tanggal' => '2024-12-31']))
        ->assertSessionHasErrors(['tanggal' => 'Tanggal harus dalam tahun anggaran 2025']);
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['jumlah' => 0]))->assertSessionHasErrors('jumlah');
    expect(RealisasiKeuangan::count())->toBe(0);
});

test('lampiran > 2 MB atau mime salah → 422; valid → tersimpan di disk private lampiran/{tahun}/{id}.{ext}', function () {
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['lampiran' => UploadedFile::fake()->create('bukti.pdf', 2049, 'application/pdf')]))
        ->assertSessionHasErrors(['lampiran' => 'Lampiran maksimal 2 MB']);
    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['lampiran' => UploadedFile::fake()->create('bukti.exe', 10, 'application/octet-stream')]))
        ->assertSessionHasErrors('lampiran');

    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA, ['lampiran' => UploadedFile::fake()->image('bukti.png')]))
        ->assertRedirect()->assertSessionHas('sukses');
    $r = RealisasiKeuangan::firstOrFail();
    expect($r->lampiran_path)->toBe("lampiran/2025/{$r->id}.png");
    Storage::disk('private')->assertExists($r->lampiran_path);
});

test('Operator bidang lain → 403 tulis, 200 baca; Pimpinan 403 tulis', function () {
    $this->actingAs($this->operatorA)->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skB, ['jumlah' => 1_000_000]))->assertForbidden();
    $this->actingAs($this->operatorA)->get(route('realisasi.keuangan.index'))->assertOk()->assertSee($this->skA->kode)->assertDontSee($this->skB->kode);
    $this->actingAs(User::factory()->pimpinan()->create())->post(route('realisasi.keuangan.store'), payloadRealisasi($this->skA))->assertForbidden();
    $this->actingAs(User::factory()->pimpinan()->create())->get(route('realisasi.keuangan.index'))->assertOk();
    expect(RealisasiKeuangan::count())->toBe(0);
});

test('edit/hapus butuh konfirmasi sandi ≤ 5 menit; edit tidak menghitung dirinya sebagai terpakai', function () {
    $r = RealisasiKeuangan::factory()->for($this->skA)->create(['jumlah' => 90_000_000, 'tanggal' => '2025-02-01', 'created_by' => $this->admin->id]);
    $data = payloadRealisasi($this->skA, ['jumlah' => 100_000_000, 'uraian' => 'Diubah']);

    $this->actingAs($this->admin)->put(route('realisasi.keuangan.update', $r), $data)->assertForbidden();
    $this->actingAs($this->admin)->delete(route('realisasi.keuangan.destroy', $r))->assertForbidden();
    $this->actingAs($this->admin)->withSession([KonfirmasiSandi::KUNCI_SESI => time() - 6 * 60])->put(route('realisasi.keuangan.update', $r), $data)->assertForbidden();

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('realisasi.keuangan.update', $r), $data)
        ->assertRedirect()->assertSessionHas('sukses', 'Realisasi diperbarui');
    expect($r->fresh())->jumlah->toBe(100_000_000)->uraian->toBe('Diubah')->updated_by->toBe($this->admin->id);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('realisasi.keuangan.update', $r), payloadRealisasi($this->skA, ['jumlah' => 100_000_001]))
        ->assertSessionHasErrors(['jumlah' => 'Melebihi sisa! Sisa: Rp 100.000.000']);

    $this->actingAs($this->operatorA)->withSession($this->konfirmasi)->put(route('realisasi.keuangan.update', $r), $data)->assertRedirect();
    $rB = RealisasiKeuangan::factory()->for($this->skB)->create(['created_by' => $this->admin->id]);
    $this->actingAs($this->operatorA)->withSession($this->konfirmasi)->put(route('realisasi.keuangan.update', $rB), payloadRealisasi($this->skB, ['jumlah' => 1]))->assertForbidden();
});

test('hapus = soft delete; serapan tidak menghitung yang terhapus; audit deleted', function () {
    $r = RealisasiKeuangan::factory()->for($this->skA)->create(['jumlah' => 40_000_000, 'created_by' => $this->admin->id]);
    RealisasiKeuangan::factory()->for($this->skA)->create(['jumlah' => 10_000_000, 'created_by' => $this->admin->id]);

    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('realisasi.keuangan.destroy', $r))
        ->assertRedirect()->assertSessionHas('info', 'Berhasil dihapus');

    expect(RealisasiKeuangan::find($r->id))->toBeNull()
        ->and(RealisasiKeuangan::withTrashed()->find($r->id)->deleted_at)->not->toBeNull()
        ->and(app(SerapanCalculator::class)->realisasi($this->skA))->toBe(10_000_000)
        ->and(AuditLog::where('action', 'deleted')->where('auditable_type', RealisasiKeuangan::class)->where('auditable_id', $r->id)->exists())->toBeTrue();

    // pagu sub kegiatan boleh diturunkan sampai realisasi aktif (yang terhapus tak dihitung)
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('anggaran.sub-kegiatan.update', $this->skA), [
        'kode' => $this->skA->kode, 'nama' => $this->skA->nama, 'pagu' => 10_000_000, 'bidang_id' => $this->skA->bidang_id, 'sumber_dana_id' => $this->skA->sumber_dana_id,
    ])->assertRedirect()->assertSessionHas('sukses');
});

test('tahun terkunci → 423 pada tulis realisasi', function () {
    $terkunci = TahunAnggaran::factory()->terkunci()->create(['tahun' => 2024]);
    $sk = SubKegiatan::factory()->for(Kegiatan::factory()->for(Program::factory()->for($terkunci, 'tahunAnggaran')))->untukBidang($this->bidangA)->create();
    $r = RealisasiKeuangan::factory()->for($sk)->create(['tanggal' => '2024-05-05', 'created_by' => $this->admin->id]);

    $this->actingAs($this->admin)->post(route('realisasi.keuangan.store'), payloadRealisasi($sk, ['tanggal' => '2024-05-01']))->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->put(route('realisasi.keuangan.update', $r), payloadRealisasi($sk, ['tanggal' => '2024-05-01']))->assertStatus(423);
    $this->actingAs($this->admin)->withSession($this->konfirmasi)->delete(route('realisasi.keuangan.destroy', $r))->assertStatus(423);
    expect(RealisasiKeuangan::count())->toBe(1);
});
