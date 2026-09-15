# 23 — Kriteria Terima per Fitur

Terarsip (selesai): F01, F02, F03, F04 → `docs/_archive/23-F0x.md`.

Selesai = semua kriteria fitur di bawah **+** docs/24. Pola UI (states, breakpoint, komponen) → docs/26; setiap layar data wajib memenuhi "Kriteria UI umum" di akhir dokumen. Perintah → docs/11.

## F05 — Realisasi keuangan
- Simpan transaksi valid → 302 + toast "Realisasi dicatat"; sisa & serapan di layar berkurang sesuai.
- `jumlah` > sisa → 422 "Melebihi sisa! Sisa: Rp …"; tanggal di luar tahun → 422; lampiran > 2 MB / mime salah → 422.
- Lampiran tersimpan di disk private; `GET /realisasi/lampiran/{id}` mengembalikan file untuk user berhak, 403 untuk yang tidak (semua peran boleh lihat — uji user nonaktif/tamu → 401/403).
- Edit/hapus memerlukan konfirmasi sandi (sesi `password_confirmed_at` ≤ 5 menit) → tanpa itu 403.
- Hapus = soft delete; serapan tidak menghitung yang terhapus; audit `deleted`.
```
php artisan test tests/Feature/RealisasiKeuanganTest.php tests/Feature/LampiranTest.php → passed
```

## F06 — Realisasi fisik
- Upsert 12 bulan; persen menurun dari bulan terisi sebelumnya atau > 100 → 422 menyebut bulan.
- Scope bidang & tahun terkunci seperti F05.
```
php artisan test tests/Feature/RealisasiFisikTest.php → passed
```

## F07 — Dashboard
- Kartu Total Pagu / Total Realisasi / Sisa / Serapan + sub-teks (jumlah sub kegiatan, transaksi, % tersisa) sama persis dengan hasil `DashboardQuery`.
- Grafik "Pagu vs Realisasi" (bar per sub kegiatan, top 10) & "Distribusi Pagu" (doughnut per sumber dana) memakai palet docs/26.
- Tabel ringkasan: No, Kode, Sub Kegiatan, Bidang, Sumber Dana, Pagu, Realisasi, Sisa, Serapan, Status (badge sesuai docs/04).
- Filter tahun (default aktif) & bidang; Operator default bidangnya.
```
php artisan test tests/Feature/DashboardTest.php → passed ; buka /dashboard dengan seed prototipe → total pagu Rp 3.900.000.000, realisasi Rp 1.330.000.000, serapan 34%
```

## F08 — Laporan Monev Triwulan
- Filter tahun + TW + bidang + sumber dana. Kolom: No, Kode, Sub Kegiatan, Bidang, Sumber Dana, Pagu, Target Keu (Rp, %), Realisasi Keu (Rp, %), Deviasi Keu (%), Target Fisik %, Realisasi Fisik %, Deviasi Fisik, Status; baris subtotal per Program & total.
- Unduh xlsx & pdf berkop; angka identik dengan web.
## F09 — Rekap Sumber Dana & Bidang
- Dua tabel (per sumber dana, per bidang): Pagu, Realisasi, Sisa, Serapan %, jumlah sub kegiatan; total; serapan agregat dari Σ.
## F10 — Buku Realisasi
- Filter tahun, rentang tanggal, bidang, sub kegiatan; kolom: No, Tanggal, Kode, Sub Kegiatan, Uraian, No. SP2D, Jumlah, Lampiran (ikon), Dicatat oleh; total; paginasi 50 di web, penuh di ekspor.
## F11 — Tren Serapan
- Grafik garis kumulatif per bulan: realisasi tahun berjalan, target (interpolasi TW), realisasi tahun sebelumnya; tabel 12 bulan di bawahnya.
```
php artisan test tests/Feature/Laporan*Test.php → passed ; setiap laporan: GET ?export=xlsx → content-type spreadsheet, ?export=pdf → application/pdf
```

## F12 — Audit, soft delete, kunci tahun
- Setiap create/update/delete/restore model ✎ (docs/07) menghasilkan baris `audit_logs` dengan `old_values`/`new_values` tanpa `password`.
- Kunci tahun: modal sandi → status `terkunci`, `locked_at/by`; tulis apa pun pada tahun itu → 423; buka kunci dicatat.
- Layar Audit Log: filter user, aksi, model, rentang tanggal; hanya Admin.
```
php artisan test tests/Feature/AuditLogTest.php tests/Feature/TahunAnggaranLockTest.php → passed
```

## F13 — Seed prototipe
- `php artisan db:seed --class=PrototipeSeeder` memuat 10 sub kegiatan & 16 transaksi `SEED.sql` ke tahun 2024/2025/2026 sesuai kolom `tahun`; idempoten.
```
php artisan test tests/Feature/PrototipeSeederTest.php → passed
```

## Kriteria UI umum (semua layar; detail docs/26)
- Layar dibangun hanya dari komponen `x-*` docs/26; tidak ada inline style baru selain nilai dinamis (lebar progress).
- State kosong memakai `<x-empty-state>`; error validasi memakai `.input-error` + teks; sukses/gagal memakai toast.
- Layak di 768 px (grid 2 kolom / 1 kolom) dan 480 px (stat-grid 1 kolom, `.hide-mobile` tersembunyi).
- Aksi destruktif & edit data anggaran memakai `<x-modal-konfirmasi-sandi>`.
- Tangkapan layar Dashboard v2 dibandingkan dengan prototipe pada 1320 px: header, nav, kartu, tabel tidak berbeda secara kasat mata.
