# Dosir Serah-Terima Build — SIPAGAR v2 (MVP F01–F13)

Diterbitkan: 2026-09-15 · Kontrak mesin: `docs/_SERAH_BUILD.json` (dibaca `review-vcbd`) · Ledger: `docs/_CODING_LEDGER.json`
Git: branch `main` @ `7fdb12d`, tag `v1.0.0-rc1`, 7 commit linear (S0→S5). Belum ada remote.

## 1. Status
| Indikator selesai (coding-vcbd Fase G) | Status |
|---|---|
| Semua fase docs/03 selesai | ✅ S0–S5 kode selesai |
| docs/23 tanpa fitur aktif | ✅ F01–F13 terarsip di `docs/_archive/23-F*.md` |
| Suite penuh hijau pada context bersih | ✅ `php artisan test` → 131 passed (867 assertions); `pint --test` PASS |
| `bash scripts/validate.sh` exit 0 | ✅ LULUS BERSIH (11 PASS) — skrip dipasang di `scripts/` dari paket VCBD terbaru (eTimses, 24 Agu) |
| docs/25 dijalankan berurutan | ⏸ langkah 1 lulus; langkah 2–11 (tag final, backup, deploy, smoke produksi) **menunggu pemilik** |

Kesimpulan: **kode MVP lengkap & teruji, blueprint konsisten; aplikasi belum dinyatakan "selesai" sampai rilis produksi (docs/25) dijalankan.**

## 2. Ringkasan pembangunan
- 7 slice · 34 step · 82 menit jam dinding · ~383k token [ESTIMASI meter.py]; **[TERUKUR]** `scripts/token_ledger.py sync-cc`: 3 sesi Claude Code, total 147,25 jt token (input 990 · output 872.696 · cache 146,38 jt — sebagian besar cache read).
- Stack aktual: Laravel 12.69 · PHP 8.4.23 · MySQL 8.4 (lokal) · Tailwind 3.4.19 · Alpine 3.17 · Chart.js 4.5 · maatwebsite/excel 3.1.70 · laravel-dompdf 3.1.2 · Pest 3.
- Perintah (docs/11): `php artisan test` · `vendor/bin/pint --test` · `npm run build` · `php artisan migrate --seed` · `php artisan serve`.

## 3. Langkah selanjutnya (urutan disarankan)
1. **Audit** — ✅ dijalankan 2026-09-15 (`review-vcbd`, branch `fix/audit-review-vcbd`): 20 temuan → 4 ditolak (salah tangkap pemindai), 16 ditambal & ditutup; dosir `docs/_TEMUAN.json` + `docs/_LAPORAN_TEMUAN.md`. Penahan rilis yang ditemukan & dibereskan: **TEM-008 KRITIS** — CSP docs/21 memblokir `onchange=` inline sehingga filter Dashboard/Anggaran/Audit Log/Laporan mati di browser; diganti Alpine `@change` + tes penjaga `CspInlineHandlerTest`. Keputusan yang diambil saat menambal (ubah bila tidak setuju): tahun `draft` dapat ditulis (docs/04, TEM-009 opsi A); Google Fonts = pengecualian tertulis docs/20 #6 (TEM-018). Suite: 135 passed (942 assertions), coverage 94,4 %, pint PASS, validate LULUS BERSIH, `gerbang.sh` kini jalan di bash 3.2.
2. **Paket VCBD** — `scripts/validate.sh` & `token_ledger.py` terpasang (validate LULUS BERSIH); `coding-vcbd/scripts/gerbang.sh:115` sudah diperbaiki untuk bash 3.2 (TEM-020).
3. **Remote & PR** — `git remote add origin <url> && git push -u origin main --tags`; ke depan alur `feat/*` → PR → merge pemilik (docs/22).
4. **Persiapan server** (docs/10): Ubuntu + nginx + php8.4-fpm (ext pdo_mysql, mbstring, gd, zip, intl) + MySQL 8; `/var/www/sipagar` milik user deploy; `.env` produksi `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `APP_TIMEZONE=Asia/Makassar`, DB, `SIPAGAR_TAHUN_MIN/MAX`.
5. **Rilis v1.0.0** (docs/25): tag `v1.0.0` → di server `bash deploy.sh v1.0.0` (backup → maintenance → pull → build → `migrate --force` → optimize → reload → up → smoke `DEPLOY OK`). Lalu `php artisan sipagar:buat-admin`, `php artisan db:seed --class=MasterSeeder`, isi **Master › Pengaturan** (kop & penandatangan) agar PDF berkop lengkap. Jangan jalankan `PrototipeSeeder` di produksi kecuali ingin data demo. Catat di `CHANGELOG.md` (langkah 11).
6. **Pasca-MVP** (`[TERBUKA]` di manifest): metrik/monitoring, audit aksesibilitas, F14 verifikator, F15 notifikasi & impor DPA, F16 multi-OPD/API.

## 4. Peringatan operasional
- Akun lokal seeder: `admin` / `Admin12345` — hanya env `local|testing` (AdminLokalSeeder). Produksi memakai `sipagar:buat-admin`.
- Kunci/buka tahun, reset sandi, ubah/hapus anggaran & realisasi dilindungi `POST /konfirmasi-sandi` (5 menit, 3 gagal → 403/429).
- Buka kunci tahun mengembalikan status **draft**; aktifkan ulang tunduk aturan satu tahun aktif.
- CSP memuat `script-src 'unsafe-eval'` (wajib Alpine standar) — docs/21 sudah diperbarui.
- `deploy.sh` belum pernah dieksekusi di server nyata; uji pada staging bila ada.

### 5. Deviasi dari blueprint (21)

**S0-skeleton**
- scripts/validate.sh & token_ledger.py tidak ada di paket VCBD terpasang — gerbang kesehatan blueprint dilewati, token hanya [ESTIMASI]
- gerbang.sh gagal parse di bash 3.2 macOS (line 115: case dalam $( )) — S5/S7/S8 dijalankan manual dgn logika sama
- app/View/Components/Layout/{App,Auth}.php di luar pohon docs/12 — jembatan agar <x-layout.app> (26) menunjuk views/layouts/app.blade.php (12)
- helper rupiah_singkat() ditambah (bukan di 12 semula) agar stat-card identik fRs prototipe — 12 diperbarui
- Laravel installer default menghasilkan v13 → dibuat ulang via composer create-project laravel/laravel:^12 sesuai 09

**S1-auth-master (F01,F02)**
- CSP docs/21 ditambah script-src 'self' 'unsafe-eval' — Alpine standar memerlukannya; 21 diperbarui (alternatif @alpinejs/csp ditolak)
- app/Http/Controllers/Controller.php (scaffold) ditambah trait AuthorizesRequests
- x-modal ditambah prop :terbuka agar modal ubah sandi terbuka kembali saat validasi server gagal
- migrasi users bawaan Laravel diganti 8 migrasi per tabel (07); migrate:fresh dijalankan LOKAL (DB tanpa data nyata)

**S2-anggaran-target (F03,F04)**
- Tabel realisasi_keuangan + model/factory dibuat di S2 (bukan S3) agar kriteria F03 pagu<realisasi & hapus ber-realisasi bisa diuji; endpoint/UI realisasi tetap S3
- Partial resources/views/target/_pilih.blade.php di luar daftar rencana (select sub kegiatan dipakai index & show)
- ScopedByBidang = scope lokal forUser() bukan global scope (docs/05: semua peran melihat semua bidang)

**S3-realisasi-dashboard-seed (F05,F06,F07,F13)**
- Filter dashboard mendapat opsi tahun 'Semua' (seperti prototipe) agar angka verifikasi F07 lintas tahun (3,9 M/1,33 M/34%) terpenuhi; default tetap tahun aktif — 26 diperbarui
- EnsureRole + routes + AuthTest disentuh di luar rencana: user nonaktif bersesi bisa akses route auth (temuan tes F05) → 'role' tanpa argumen = wajib aktif pada grup auth
- Layar Realisasi dibaca semua peran (docs/05) meski 26 menulis 'admin, operator' — 26 diperbarui

**S4-laporan (F08-F11)**
- app/View/Components/Layout/Pdf.php di luar rencana (jembatan <x-layout.pdf> → layouts/pdf.blade.php, pola sama dgn App/Auth) — 12 diperbarui
- RekapExport (satu xlsx dua sheet) ditambah di atas RekapSumberDana/RekapBidang yang disebut 12

**S4b-keputusan-filter (TW+rentang tanggal Rekap/Buku)**
- resources/views/laporan/_filter.blade.php disentuh di luar rencana (TW mengosongkan tanggal saat diganti)

**S5-audit-kunci-rilis (F12)**
- config/app.php: timezone => env('APP_TIMEZONE') — bawaan Laravel 12 mengunci UTC, bertentangan docs/10
- Audit target/fisik kini per baris (TargetTriwulan/RealisasiFisik ✎) menggantikan audit ringkasan di SubKegiatan (S2/S3) — tes disesuaikan
- AssignRequestId & SecurityHeaders tidak ada di pohon 12 tetapi diwajibkan 15/21 — 12 diperbarui

### 6. WARN yang dilewati (13)

**S0-skeleton**
- S1: 23 tidak punya blok S0 (skeleton tanpa fitur F) — kriteria diambil dari baris S0 docs/03 + §Kriteria UI umum 23
- S7: 94 berkas tersentuh = scaffold laravel new; berkas rencana 43 semuanya ada
- S8: gerbang cek per-berkas 3/4; state tersebar di komponen (empty-state, form-input error, toast di layout, tombol Memproses...)

**S1-auth-master (F01,F02)**
- gerbang.sh tetap tidak bisa dijalankan (bash 3.2) — S3–S8 diverifikasi manual dengan logika sama
- S8: Pengaturan & Login tanpa state kosong (layar form, bukan daftar)

**S2-anggaran-target (F03,F04)**
- gerbang.sh tak bisa dijalankan (bash 3.2) — S3–S8 manual
- S8: target/show & target/index tanpa state kosong pada berkas yang sama (kosong ada di index, form di show)

**S3-realisasi-dashboard-seed (F05,F06,F07,F13)**
- gerbang.sh tak bisa dijalankan (bash 3.2) — S3–S8 manual
- S8 dashboard 2/4: layar baca-saja (tanpa form)

**S4-laporan (F08-F11)**
- gerbang.sh tak bisa dijalankan (bash 3.2) — S3–S8 manual
- S8 laporan: layar baca-saja (kosong/filter/layout), tanpa form tulis

**S5-audit-kunci-rilis (F12)**
- gerbang.sh tak bisa dijalankan (bash 3.2) — manual
- docs/25 langkah 2–11 (merge/tag/backup/deploy/smoke produksi) TIDAK dijalankan — gerbang manusia; deploy.sh disiapkan, belum pernah dieksekusi di server

### 7. Asumsi yang diambil (37)

**S0-skeleton**
- Migrasi users bawaan Laravel dibiarkan; penyesuaian ke skema 07 (username, role, bidang_id, is_active) milik S1/F01
- Node 22 & MySQL server 8.4 lokal (dok: 20 / 8.0) — tidak mengubah 09
- Kunci flash toast: sukses
- gagal
- info (dicatat di 26)
- Persen serapan di stat-card dibulatkan (76%) mengikuti prototipe; 2 desimal di tabel — rumus final milik SerapanCalculator S3
- Footer prototipe dipertahankan tanpa nama pembuat (Copyright by Ihwan Apriadi dihilangkan) — konfirmasi bila ingin dikembalikan

**S1-auth-master (F01,F02)**
- Audit F01 hanya login/logout/created/updated/reset_password via AuditService::catat(); trait Auditable otomatis (F12) → S5
- Kunci/buka tahun (modal sandi) → S5; S1 hanya draft→aktif dengan aturan satu tahun aktif
- Konfirmasi sandi dipakai pada reset sandi pengguna; sesi konfirmasi 5 menit (config sipagar.konfirmasi_sandi_menit)
- Nonaktif = hapus baris sessions user (SESSION_DRIVER=database) + EnsureRole abort 403
- Seeder AdminLokalSeeder (admin/Admin12345) hanya env local/testing; produksi memakai sipagar:buat-admin

**S2-anggaran-target (F03,F04)**
- Hapus Program/Kegiatan yang masih punya anak → 422 (06 tidak menyebut; cascade dianggap berbahaya)
- 423 dirender halaman errors/423 bertema + pesan 'Tahun anggaran N telah dikunci' (form biasa) / JSON (fetch); tombol tulis sudah disembunyikan saat terkunci
- Ubah/hapus Program & Kegiatan ikut konfirmasi-sandi (21 menyebut 'anggaran' secara umum)
- Program/Kegiatan memakai SubKegiatanPolicy (admin-only) — tidak ada Policy terpisah di 12
- SerapanCalculator baru memuat realisasi/sisa/serapan/targetPersen; deviasi/fisik/status/agregat di S3
- Kode rekening divalidasi regex angka+titik saja (format Kepmendagri penuh tidak dipaksa; 02: sistem hanya validasi format & keunikan)

**S3-realisasi-dashboard-seed (F05,F06,F07,F13)**
- PrototipeSeeder: 2024 terkunci / 2025 aktif / 2026 draft; program 5.03.01 + kegiatan 5.03.01.2.01 'Migrasi Prototipe' per tahun; bidang dipetakan dari nama (PK/PPIK/MP/SEK); target triwulan & fisik tidak diisi
- Realisasi fisik: bulan kosong pada payload = baris bulan dihapus
- Sisa pagu dicek di FormRequest withValidator (pesan 422 per-field) DAN di RealisasiService dalam transaksi lockForUpdate (MelebihiSisaPaguException → 422)
- Lampiran lama tidak dihapus saat soft delete transaksi (dipulihkan bila restore)
- Grafik bar = top-10 pagu; doughnut = Σ pagu per sumber dana

**S4-laporan (F08-F11)**
- Default filter: tahun aktif, TW berjalan (TW4 bila tahun ≠ tahun ini); tanpa tahun anggaran → empty-state
- Rekap memakai filter s.d. TW (realisasi ≤ akhir TW); Buku memakai rentang tanggal (default 1 Jan–31 Des)
- Tren: target kumulatif diinterpolasi linear antar titik TW (bulan 0=0, 3n=target TWn); tahun lalu = data tahun N−1 bila ada
- Deviasi keu di Monev ditampilkan dalam poin % (23 menyebut 'Deviasi Keu (%)'); Rp tersedia di SerapanCalculator
- Nama berkas: sipagar_monev_<th>_TWn, sipagar_rekap_<th>_TWn, sipagar_buku-realisasi_<th>_<dari>_<sampai>, sipagar_tren_<th>_bulanan
- PDF: print.css disisipkan inline (DomPDF tanpa Vite); logo kop dari settings.kop_logo_path pada disk private bila ada
- Lampiran di Buku Realisasi web = ikon tautan; di xlsx/pdf = 'Ada'/'-'

**S4b-keputusan-filter (TW+rentang tanggal Rekap/Buku)**
- Tanggal dianggap eksplisit hanya bila berbeda dari preset TW (form mengirim ulang nilai preset)
- Monev tetap per-TW (target per TW), Tren per-tahun — tidak diberi rentang tanggal

**S5-audit-kunci-rilis (F12)**
- Buka kunci tahun → status draft (P5 tidak menyebut); aktifkan ulang tunduk aturan satu tahun aktif
- Seeder menghasilkan audit dengan user_id null (docs/07 'aksi sistem/seeder')
- Reset sandi: perubahan password tidak diaudit sebagai updated (tanpaAudit), hanya reset_password
- Audit Log menampilkan perubahan sebagai daftar kunci lama → baru; model difilter lewat class_basename
- deploy.sh mengikuti pola SIMURU + docs/25: maintenance, backup wajib 'Dump completed', gagal migrasi → petunjuk §R, smoke /login 200 & ERROR log tidak bertambah

### 8. Ditunda (16)

**S0-skeleton**
- POST /login, logout, auth middleware, modal ubah sandi aktif → S1
- DashboardController data dummy → S3 DashboardQuery
- Endpoint /konfirmasi-sandi → S5 (komponen JS sudah siap)
- Repo belum punya branch main — pemilik membuat main dari commit 374f624 saat merge PR

**S1-auth-master (F01,F02)**
- Kunci/buka tahun + modal sandi → S5 (TahunAnggaranService siap diperluas)
- Layar Audit Log → S5
- Trait Auditable & ScopedByBidang → S3/S5
- Branch main belum ada — pemilik membuat saat merge PR

**S2-anggaran-target (F03,F04)**
- Realisasi keuangan/fisik endpoint+UI, SerapanCalculator lengkap, dashboard nyata → S3
- Kunci/buka tahun UI → S5

**S3-realisasi-dashboard-seed (F05,F06,F07,F13)**
- Laporan ×4 + ekspor → S4
- Trait Auditable otomatis, kunci/buka tahun, Audit Log, deploy.sh → S5

**S4-laporan (F08-F11)**
- Audit Log layar + trait Auditable, kunci/buka tahun (modal sandi), deploy.sh, rilis → S5

**S5-audit-kunci-rilis (F12)**
- Rilis produksi v1.0.0 (docs/25 langkah 2–11) → menunggu keputusan pemilik
- Metrik/monitoring & audit aksesibilitas [TERBUKA] pasca-MVP
- F14–F16 pasca-MVP

### 9. Jebakan (landmine) yang ditemukan saat membangun
_(tidak ada)_

## 10. Dokumen yang diperbarui selama pembangunan
- docs/03_ROADMAP.md (kolom Status)
- docs/12_PROJECT_STRUCTURE.md (Support/helpers, View/Components/Layout, RupiahHelperTest)
- docs/26_UI_DESIGN.md (mekanisme modal/toast/konfirmasi, footer, rupiah_singkat)
- docs/_MANIFEST.json (build.roadmap_status, installed, env_dev_actual)
- docs/21_SECURITY_RULES.md (CSP unsafe-eval)
- docs/23 (F01,F02 diarsipkan ke docs/_archive)
- docs/03_ROADMAP.md (S1 selesai)
- docs/12_PROJECT_STRUCTURE.md (KonfirmasiSandiController, config/sipagar.php)
- docs/_MANIFEST.json (build.roadmap_status.S1)
- docs/23 (F03,F04 diarsipkan)
- docs/03_ROADMAP.md (S2 selesai)
- docs/12_PROJECT_STRUCTURE.md (Exceptions, target views, parseRupiah)
- docs/_MANIFEST.json
- docs/23 (F05,F06,F07,F13 diarsipkan)
- docs/03_ROADMAP.md (S3 selesai)
- docs/26_UI_DESIGN.md (dashboard Semua, realisasi baca semua peran, fisik stat TW)
- docs/12_PROJECT_STRUCTURE.md (views realisasi, catatan role)
- docs/_MANIFEST.json
- docs/23 (F08–F11 diarsipkan)
- docs/03_ROADMAP.md (S4 selesai)
- docs/12_PROJECT_STRUCTURE.md (LaporanFilter, LaporanController, RekapExport, Layout/Pdf, laporan/_filter)
- docs/26_UI_DESIGN.md (detail layar laporan)
- docs/_MANIFEST.json (S4, paket Excel/DomPDF)
- docs/26 (filter Rekap/Buku)
- docs/_archive/23-F09.md, 23-F10.md (catatan keputusan)
- docs/23 (F12 diarsipkan — seluruh MVP terarsip)
- docs/03_ROADMAP.md (S5 kode selesai, rilis menunggu)
- docs/12 (Auditable, AssignRequestId/SecurityHeaders, deploy.sh, CHANGELOG)
- docs/26 (Tahun kunci/buka, Audit Log)
- docs/_MANIFEST.json
