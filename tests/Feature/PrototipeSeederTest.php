<?php

use App\Enums\StatusTahun;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\TahunAnggaran;
use Database\Seeders\PrototipeSeeder;

test('PrototipeSeeder memuat 10 sub kegiatan & 16 transaksi ke tahun 2024/2025/2026, idempoten', function () {
    $this->seed(PrototipeSeeder::class);
    $this->seed(PrototipeSeeder::class);

    expect(SubKegiatan::count())->toBe(10)
        ->and(RealisasiKeuangan::count())->toBe(16)
        ->and((int) SubKegiatan::sum('pagu'))->toBe(3_900_000_000)
        ->and((int) RealisasiKeuangan::sum('jumlah'))->toBe(1_330_000_000)
        ->and(TahunAnggaran::where('tahun', 2024)->value('status'))->toBe(StatusTahun::Terkunci)
        ->and(TahunAnggaran::where('tahun', 2025)->value('status'))->toBe(StatusTahun::Aktif)
        ->and(TahunAnggaran::where('tahun', 2026)->value('status'))->toBe(StatusTahun::Draft);

    $perTahun = SubKegiatan::with('kegiatan.program.tahunAnggaran')->get()->groupBy(fn ($sk) => $sk->tahunAnggaran()->tahun)->map->count();
    expect($perTahun->all())->toBe([2025 => 7, 2024 => 2, 2026 => 1]);

    $tpp = SubKegiatan::where('nama', 'Pembayaran TPP ASN')->firstOrFail();
    expect($tpp->pagu)->toBe(2_400_000_000)->and($tpp->sumberDana->kode)->toBe('TPP')->and((int) $tpp->realisasiKeuangan()->sum('jumlah'))->toBe(600_000_000);
});
