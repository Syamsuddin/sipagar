# 02 — Scope

Gerbang anti scope-creep. Agen yang menemukan kebutuhan di luar daftar **In-scope** wajib berhenti dan bertanya.

## In-scope (MVP)
- Fitur F01–F13 sesuai docs/01.
- Satu OPD (BKPSDM), banyak Bidang, tiga peran (docs/05).
- Struktur anggaran tiga tingkat; pagu & seluruh pencatatan di Sub Kegiatan.
- Target & realisasi bersifat kumulatif (docs/04).
- Empat laporan dengan keluaran web, xlsx, pdf.
- Seed demo dari `SEED.sql` prototipe.
- Deploy ke satu VPS (docs/10, docs/25).

## Out-of-scope (eksplisit — jangan dibangun)
1. Multi-OPD dalam satu instalasi.
2. Integrasi SIPD / SIMDA / e-Kinerja atau API eksternal apa pun.
3. Penatausahaan keuangan (pembuatan SPP/SPM/SP2D) — SP2D hanya dirujuk nomornya.
4. Aplikasi mobile native / PWA offline.
5. Multi-bahasa.
6. Tanda tangan elektronik pada PDF.
7. Tema terang, pemilih tema, atau warna/font di luar token docs/26.
8. Signup publik & reset password mandiri (prototipe punya; v2 sengaja menghapus — akun dibuat Admin).
9. Alur verifikasi berjenjang, notifikasi email, impor DPA (F14–F16, pasca-MVP).

## Asumsi & batasan
- Asumsi berlabel di `docs/_MANIFEST.json` → `assumptions`; yang ditunda di `open_questions`.
- Rentang tahun anggaran 2020–2034.
- Kode rekening diinput manual; sistem hanya memvalidasi format & keunikan per tahun.
- Data prototipe yang pernah diinput pengguna di browser (localStorage) **tidak** dimigrasi otomatis; hanya data contoh `SEED.sql`.
