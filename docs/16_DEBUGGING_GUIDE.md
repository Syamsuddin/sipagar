# 16 — Panduan Debugging & Jebakan

Log → docs/15. Perintah → docs/11. Pola error → docs/14.

## Gejala → langkah
| Gejala | Diagnosa |
|---|---|
| Angka dashboard ≠ laporan | keduanya harus lewat `SerapanCalculator`/`Queries`; cari perhitungan manual di Blade/Controller → pindahkan |
| Operator melihat/menulis bidang lain | cek `ScopedByBidang` diterapkan di `SubKegiatan` & relasi; Policy dipanggil (`authorize`) di controller |
| 423 padahal tahun aktif | `EnsureTahunTerbuka` mengambil tahun dari route/model — pastikan resolusi `tahun_anggaran_id` lewat rantai sub_kegiatan→kegiatan→program |
| Tampilan berbeda dari prototipe | Tailwind `preflight` menyala? (`tailwind.config.js corePlugins.preflight=false`); kelas token dari `app.css` tertimpa utilitas Tailwind? |
| Grafik teks tak terbaca | opsi Chart.js belum memakai warna token (lihat landmine) |
| PDF kosong/ tanpa font | DomPDF tidak memuat Google Fonts — `pdf.blade.php` memakai `DejaVu Sans`; layout cetak terang (`print.css`) |
| Excel angka jadi teks | Export harus mengembalikan `int`, bukan string `rupiah()` |
| 500 tanpa detail | ambil `X-Request-Id` dari pengguna → `grep <id> storage/logs/laravel-*.log` |

## Jebakan spesifik proyek (sumber: `_MANIFEST.json → landmines`)
1. Prototipe menyimpan `tahun` sebagai string dan membandingkan `===` — v2 memakai `tahun_anggaran_id` INT; jangan meniru filter string.
2. Prototipe punya dua label untuk ≥ 90 % ("Hampir Habis" vs "Kritis"); v2 hanya **Kritis** (docs/04). Jangan salin label prototipe mentah-mentah.
3. Hash sandi prototipe SHA-256+salt ≠ bcrypt — akun prototipe **tidak** dimigrasi. Jangan membuat "legacy hash checker".
4. `SCHEMA.sql`/`SEED.sql` di root = skema prototipe flat. **Bukan** migrasi v2. Hanya sumber data `PrototipeSeeder` (F13).
5. Prototipe memakai Tailwind CDN + inline style; v2 semua via Vite. Saat memindahkan inline style ke utilitas, hasil visual harus identik — bandingkan tangkapan layar.
6. Rupiah bisa > 2^31 (TPP 2,4 M). BIGINT + `(int)`; jangan `float`/`intval` pada string berformat titik — pakai helper `parseRupiah()`.
7. Target & realisasi fisik/keuangan **kumulatif**. Deviasi = kumulatif − kumulatif. Jangan menjumlahkan target TW1..TW4.
8. Chart.js default terang. Set `Chart.defaults.color = '#7a9488'`, `borderColor = '#1e3029'`, font `Plus Jakarta Sans` di `resources/js/app.js` sekali.
9. Tahun aktif tunggal dijaga di `TahunAnggaranService::aktifkan()` (transaksi + `lockForUpdate`), bukan constraint DB.
