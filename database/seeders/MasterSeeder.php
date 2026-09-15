<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Setting;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;

/**
 * Master awal: 5 bidang (_MANIFEST assumptions), 15 sumber dana prototipe, kunci settings.
 * Idempoten: updateOrCreate berdasar kode/key.
 */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $bidang = [
            ['SEK', 'Sekretariat'],
            ['PPIK', 'Bidang Pengadaan, Pemberhentian & Informasi Kepegawaian'],
            ['MP', 'Bidang Mutasi & Promosi'],
            ['PK', 'Bidang Pengembangan Kompetensi'],
            ['PKP', 'Bidang Penilaian Kinerja & Penghargaan'],
        ];
        foreach ($bidang as $i => [$kode, $nama]) {
            Bidang::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'urutan' => $i + 1]);
        }

        // 15 kode SUMBER_DANA prototipe (value, label, cssClass)
        $sumber = [
            ['APBD I', 'APBD I (Provinsi)', 'sd-apbd'],
            ['APBD II', 'APBD II (Kabupaten/Kota)', 'sd-apbd'],
            ['APBN', 'APBN', 'sd-apbn'],
            ['Dana Transfer', 'Dana Transfer Umum', 'sd-dt'],
            ['DAU', 'Dana Alokasi Umum (DAU)', 'sd-dau'],
            ['DAK', 'Dana Alokasi Khusus (DAK)', 'sd-dak'],
            ['DBHPT', 'DBH Pajak & Retribusi', 'sd-dbhp'],
            ['DBHCHT', 'DBH Cukai Hasil Tembakau', 'sd-dbhp'],
            ['DBHSDA', 'DBH Sumber Daya Alam', 'sd-dbhp'],
            ['BAN', 'Bantuan Keuangan (BAN)', 'sd-ban'],
            ['BLUD', 'Badan Layanan Umum Daerah', 'sd-blud'],
            ['TPP', 'Tunjangan Kinerja (TPP)', 'sd-tpp'],
            ['Hibah', 'Hibah Pemerintah Pusat', 'sd-ban'],
            ['Pinjaman Daerah', 'Pinjaman Daerah', 'sd-lainnya'],
            ['Lainnya', 'Lainnya', 'sd-lainnya'],
        ];
        foreach ($sumber as $i => [$kode, $nama, $css]) {
            SumberDana::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'css_class' => $css, 'urutan' => $i + 1]);
        }

        $bawaan = [
            'kop_nama_instansi' => 'BKPSDM Kabupaten Hulu Sungai Selatan',
            'ttd_kota' => 'Kandangan',
        ];
        foreach (Setting::KUNCI as $key) {
            Setting::firstOrCreate(['key' => $key], ['value' => $bawaan[$key] ?? null]);
        }
    }
}
