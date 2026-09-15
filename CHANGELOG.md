# Changelog

Semua perubahan penting proyek ini dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/) dan versi mengikuti [Semantic Versioning](https://semver.org/lang/id/).
Entri versi final diisi pada docs/25 langkah 11.

## [Unreleased]

## [1.0.0-rc2] - 2026-09-15

Hasil audit `review-vcbd` terhadap blueprint (dosir: `docs/_TEMUAN.json`, `docs/_LAPORAN_TEMUAN.md`) — 20 temuan, 16 ditambal, 4 ditolak sebagai salah tangkap pemindai.

### Fixed
- **Filter Dashboard, Anggaran, Audit Log, dan 4 Laporan mati di browser** (TEM-008, KRITIS): CSP `script-src 'self' 'unsafe-eval'` (docs/21) memblokir atribut inline `onchange="this.form.submit()"`; diganti Alpine `@change` tanpa melonggarkan CSP. Tes penjaga `tests/Feature/CspInlineHandlerTest.php`.
- `coding-vcbd/scripts/gerbang.sh` gagal parse di bash 3.2 macOS (TEM-020) — `case … )` di dalam `$( )` diganti `[[ ]]`.

### Added
- Tes `sipagar:buat-admin` — langkah pertama produksi (TEM-012).
- Tes ubah Program & Kegiatan: konfirmasi sandi, audit `updated`, operator 403, tahun terkunci 423 (TEM-013).
- `.env.example`: `SESSION_SECURE_COOKIE` (true di produksi) (TEM-019).
- docs/26: token latar toast dan palet PDF (`print.css`) (TEM-017).

### Changed
- `composer.json`: `php ^8.4` sesuai docs/09 (TEM-019).
- Keputusan domain dicatat di dokumen pemiliknya: tahun `draft` dapat ditulis & buka kunci → `draft` (docs/04, docs/06 P5.3; TEM-009, TEM-011); scope bidang = scope lokal `forUser()` + Policy, bukan global scope (docs/08, docs/21; TEM-010); Google Fonts sebagai satu-satunya pengecualian tertulis aset CDN (docs/09, docs/20; TEM-018).
- Asumsi build dinaikkan ke dokumen pemilik: hapus Program/Kegiatan beranak → 422, bulan fisik kosong = dihapus, filter rentang tanggal Rekap/Buku, deviasi Monev dalam poin %, interpolasi Tren (docs/06); konfirmasi sandi Program/Kegiatan & lampiran soft delete (docs/21); aksi audit eksplisit (docs/15); Policy Program/Kegiatan (docs/12) (TEM-004, TEM-016).
- docs/10 kredensial seeder lokal `admin / Admin12345` (TEM-014); docs/21 mekanisme pemutusan sesi user nonaktif (TEM-015); docs/13 #6 counter RateLimiter (TEM-019); docs/03 utang non-fitur pasca-MVP (TEM-007).

## [1.0.0-rc1] - 2026-09-15

MVP F01–F13 — kandidat rilis pertama. Rilis produksi (docs/25 langkah 2–11) menunggu pemilik.

### Added
- S0 skeleton: Laravel 12, Vite + Tailwind 3.4, CSS & komponen Blade identik prototipe.
- S1 auth & master: login 3 peran (throttle, nonaktif), pengguna, bidang, sumber dana (15 kode), tahun anggaran, pengaturan, konfirmasi sandi.
- S2 anggaran & target: Program › Kegiatan › Sub Kegiatan, target triwulan kumulatif, scope bidang, tahun terkunci → 423.
- S3 realisasi & dashboard: realisasi keuangan + lampiran privat, realisasi fisik, SerapanCalculator, dashboard nyata, seeder prototipe.
- S4 laporan: Monev Triwulan, Rekap Sumber Dana & Bidang, Buku Realisasi, Tren Serapan — web/xlsx/pdf berkop; filter TW + rentang tanggal (keputusan pemilik).
- S5 audit & rilis: trait Auditable (created/updated/deleted/restored), kunci/buka tahun dengan modal sandi, layar Audit Log, request id, `deploy.sh`.

[Unreleased]: https://github.com/Syamsuddin/sipagar/compare/v1.0.0-rc2...HEAD
[1.0.0-rc2]: https://github.com/Syamsuddin/sipagar/compare/v1.0.0-rc1...v1.0.0-rc2
[1.0.0-rc1]: https://github.com/Syamsuddin/sipagar/releases/tag/v1.0.0-rc1
