# 01 — PRD (Product Requirements)

Pemilik daftar fitur & user story. Kriteria terima per fitur → docs/23. Batas scope → docs/02. UI → docs/26.

## Daftar fitur
| ID | Fitur | Deskripsi singkat | Prioritas |
|---|---|---|---|
| F01 | Auth & pengguna | Login username+password, 3 peran (docs/05), Admin CRUD pengguna, ubah sandi sendiri via modal | MVP |
| F02 | Master & pengaturan | Bidang, Sumber Dana (15 kode prototipe), Tahun Anggaran (draft/aktif/terkunci), Pengaturan kop & penandatangan laporan | MVP |
| F03 | Struktur anggaran | Program → Kegiatan → Sub Kegiatan per tahun; kode rekening, nama, pagu, bidang PJ, sumber dana, PPTK | MVP |
| F04 | Target triwulan | Target kumulatif TW1–TW4 keuangan (Rp) & fisik (%) per Sub Kegiatan | MVP |
| F05 | Realisasi keuangan | Transaksi: tanggal, jumlah, uraian, no. SP2D, lampiran; validasi ≤ sisa pagu | MVP |
| F06 | Realisasi fisik | Persen kumulatif per bulan per Sub Kegiatan | MVP |
| F07 | Dashboard | 4 kartu (pagu, realisasi, sisa, serapan), grafik Pagu vs Realisasi & distribusi, tabel ringkasan status; filter tahun & bidang | MVP |
| F08 | Laporan Monev Triwulan | Per Sub Kegiatan: pagu, target & realisasi keuangan+fisik s.d. TW n, deviasi, status | MVP |
| F09 | Rekap Sumber Dana & Bidang | Agregat pagu/realisasi/sisa/serapan per sumber dana dan per bidang | MVP |
| F10 | Buku Realisasi | Rincian transaksi per periode dengan filter | MVP |
| F11 | Tren Serapan | Kurva kumulatif bulanan realisasi vs target, tahun berjalan vs tahun lalu | MVP |
| F12 | Audit, soft delete, kunci tahun | Log setiap create/update/delete; soft delete entitas anggaran; kunci tahun memblokir tulis | MVP |
| F13 | Seed demo | Data prototipe (`SEED.sql`) dimuat ke struktur v2 lewat seeder | MVP |
| F14 | Verifikasi realisasi | Peran Verifikator setujui/tolak transaksi | Nanti |
| F15 | Notifikasi & impor DPA | Email pengingat, impor Excel DPA | Nanti |
| F16 | Multi-OPD & API | Beberapa OPD, API publik | Nanti |

Setiap laporan F08–F11: tampil web (tabel/grafik) + tombol **Unduh Excel** + **Unduh PDF**.

## User story (MVP)
| ID | Sebagai | Saya ingin | Agar |
|---|---|---|---|
| F01 | Admin | membuat akun Operator dan menetapkan bidangnya | tiap bidang hanya menyentuh datanya sendiri |
| F01 | Semua | login dan mengubah sandi sendiri lewat modal | akun tetap aman tanpa bantuan Admin |
| F02 | Admin | mengelola Bidang, Sumber Dana, Tahun Anggaran, kop laporan | struktur organisasi & referensi bisa berubah tanpa ubah kode |
| F03 | Admin | menginput struktur anggaran tahun N lengkap dengan pagu & bidang PJ | Operator punya wadah pencatatan |
| F04 | Operator | mengisi target kumulatif TW1–TW4 sub kegiatan bidang saya | deviasi realisasi terhadap rencana terukur |
| F05 | Operator | mencatat tiap transaksi realisasi dengan nomor SP2D & bukti | buku realisasi dapat direkonsiliasi |
| F06 | Operator | mengisi capaian fisik % per bulan | monev tidak hanya keuangan |
| F07 | Pimpinan | melihat serapan & status tiap sub kegiatan dalam satu layar | keputusan percepatan bisa diambil cepat |
| F08–F11 | Pimpinan/Admin | mengunduh laporan Excel/PDF dengan filter | laporan ke Bappeda/Inspektorat tanpa rekap manual |
| F12 | Admin | mengunci tahun anggaran & melihat siapa mengubah apa | data akhir tahun tidak berubah diam-diam |
| F13 | Developer | memuat data demo prototipe | UI bisa diuji dengan data realistis |

## Kebutuhan non-fungsional
| Aspek | Kebutuhan |
|---|---|
| Tampilan | Identik prototipe — docs/26 |
| Kinerja | Halaman laporan tahun aktif (≤ 300 sub kegiatan, ≤ 5.000 transaksi) < 2 detik; ekspor < 10 detik |
| Keamanan | docs/21 |
| Ketersediaan | Satu VPS; backup DB harian lewat `deploy.sh`/cron (docs/25) |
| Bahasa | id-ID |
