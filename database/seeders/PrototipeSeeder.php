<?php

namespace Database\Seeders;

use App\Enums\StatusTahun;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * F13: 10 program & 16 realisasi `SEED.sql` prototipe → struktur v2 (landmine #4: SEED.sql hanya sumber data).
 * Tiap tahun (2024 terkunci, 2025 aktif, 2026 draft) mendapat Program 5.03.01 / Kegiatan "Migrasi Prototipe".
 * Idempoten: updateOrCreate berdasar kode & (sub kegiatan, tanggal, uraian). Butuh MasterSeeder & satu admin.
 */
class PrototipeSeeder extends Seeder
{
    private const PROGRAM = [
        // [id prototipe, nama, pagu, tahun, kode sumber, kode bidang]
        ['p1', 'Pelatihan Dasar CPNS Gol. III', 285_000_000, 2025, 'APBD II', 'PK'],
        ['p2', 'Diklat Kepemimpinan Pengawas', 195_000_000, 2025, 'APBD II', 'PK'],
        ['p3', 'Pelatihan Teknis Fungsional', 150_000_000, 2025, 'DAK', 'PK'],
        ['p4', 'Penyusunan Analisis Jabatan', 95_000_000, 2025, 'DAU', 'SEK'],
        ['p5', 'Sistem Informasi Kepegawaian (SIMPEG)', 320_000_000, 2025, 'Dana Transfer', 'PPIK'],
        ['p6', 'Mutasi dan Promosi Jabatan', 75_000_000, 2025, 'APBD II', 'MP'],
        ['p7', 'Pengelolaan Database Kepegawaian', 120_000_000, 2024, 'APBD II', 'PPIK'],
        ['p8', 'Diklat PIM IV Tingkat Dasar', 175_000_000, 2024, 'DAK', 'PK'],
        ['p9', 'Reviu Jabatan Fungsional', 85_000_000, 2026, 'APBN', 'MP'],
        ['p10', 'Pembayaran TPP ASN', 2_400_000_000, 2025, 'TPP', 'SEK'],
    ];

    private const REALISASI = [
        ['p1', '2025-01-15', 45_000_000, 'Akomodasi peserta batch 1'],
        ['p1', '2025-02-20', 62_000_000, 'Honor narasumber batch 2'],
        ['p1', '2025-03-10', 38_000_000, 'Pengadaan materi dan ATK'],
        ['p2', '2025-02-05', 55_000_000, 'Penyelenggaraan diklat'],
        ['p2', '2025-04-12', 48_000_000, 'Akomodasi peserta'],
        ['p3', '2025-01-25', 35_000_000, 'Pelatihan teknis perencanaan'],
        ['p3', '2025-03-18', 42_000_000, 'Pelatihan teknis keuangan'],
        ['p4', '2025-02-28', 28_000_000, 'Konsultan analisis jabatan'],
        ['p5', '2025-01-10', 120_000_000, 'Pengadaan server'],
        ['p5', '2025-03-22', 85_000_000, 'Pengembangan SIMPEG fase 1'],
        ['p6', '2025-04-01', 15_000_000, 'Naskah keputusan mutasi'],
        ['p10', '2025-01-31', 200_000_000, 'TPP Januari 2025'],
        ['p10', '2025-02-28', 200_000_000, 'TPP Februari 2025'],
        ['p10', '2025-03-31', 200_000_000, 'TPP Maret 2025'],
        ['p7', '2024-06-15', 65_000_000, 'Lisensi software'],
        ['p8', '2024-08-20', 92_000_000, 'Diklat PIM IV angkatan 1'],
    ];

    private const STATUS_TAHUN = [2024 => StatusTahun::Terkunci, 2025 => StatusTahun::Aktif, 2026 => StatusTahun::Draft];

    public function run(): void
    {
        $this->call(MasterSeeder::class);
        $admin = User::where('role', 'admin')->orderBy('id')->first() ?? User::factory()->admin()->create(['username' => 'admin', 'name' => 'Administrator']);

        DB::transaction(function () use ($admin) {
            $kegiatanPerTahun = [];
            foreach (self::STATUS_TAHUN as $tahun => $status) {
                $ta = TahunAnggaran::firstOrCreate(['tahun' => $tahun], ['status' => $status]);
                if ($status === StatusTahun::Aktif && $ta->status === StatusTahun::Draft) {
                    $ta->update(['status' => $status]);
                }
                $program = Program::withTrashed()->updateOrCreate(
                    ['tahun_anggaran_id' => $ta->id, 'kode' => '5.03.01'],
                    ['nama' => 'Program Penunjang Urusan Pemerintahan Daerah Kabupaten/Kota', 'urutan' => 1, 'deleted_at' => null],
                );
                $kegiatanPerTahun[$tahun] = Kegiatan::withTrashed()->updateOrCreate(
                    ['program_id' => $program->id, 'kode' => '5.03.01.2.01'],
                    ['nama' => 'Migrasi Prototipe', 'urutan' => 1, 'deleted_at' => null],
                );
            }

            $sumber = SumberDana::pluck('id', 'kode');
            $bidang = Bidang::pluck('id', 'kode');
            $subPerId = [];
            foreach (self::PROGRAM as $i => [$pid, $nama, $pagu, $tahun, $kodeSumber, $kodeBidang]) {
                $subPerId[$pid] = SubKegiatan::withTrashed()->updateOrCreate(
                    ['kegiatan_id' => $kegiatanPerTahun[$tahun]->id, 'kode' => sprintf('5.03.01.2.01.%04d', $i + 1)],
                    ['nama' => $nama, 'pagu' => $pagu, 'sumber_dana_id' => $sumber[$kodeSumber], 'bidang_id' => $bidang[$kodeBidang], 'urutan' => $i + 1, 'deleted_at' => null],
                );
            }

            foreach (self::REALISASI as [$pid, $tanggal, $jumlah, $uraian]) {
                RealisasiKeuangan::withTrashed()->updateOrCreate(
                    ['sub_kegiatan_id' => $subPerId[$pid]->id, 'tanggal' => $tanggal, 'uraian' => $uraian],
                    ['jumlah' => $jumlah, 'created_by' => $admin->id, 'deleted_at' => null],
                );
            }
        });
    }
}
