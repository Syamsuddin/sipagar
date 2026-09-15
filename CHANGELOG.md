# Changelog SIPAGAR v2

Format: versi · tanggal rilis · ringkasan. Diisi pada docs/25 langkah 11.

## [Belum dirilis]
### v1.0.0 — MVP (F01–F13)
- S0 skeleton: Laravel 12, Vite + Tailwind 3.4, CSS & komponen Blade identik prototipe.
- S1 auth & master: login 3 peran (throttle, nonaktif), pengguna, bidang, sumber dana (15 kode), tahun anggaran, pengaturan, konfirmasi sandi.
- S2 anggaran & target: Program › Kegiatan › Sub Kegiatan, target triwulan kumulatif, scope bidang, tahun terkunci → 423.
- S3 realisasi & dashboard: realisasi keuangan + lampiran privat, realisasi fisik, SerapanCalculator, dashboard nyata, seeder prototipe.
- S4 laporan: Monev Triwulan, Rekap Sumber Dana & Bidang, Buku Realisasi, Tren Serapan — web/xlsx/pdf berkop.
- S5 audit & rilis: trait Auditable (created/updated/deleted/restored), kunci/buka tahun dengan modal sandi, layar Audit Log, request id, deploy.sh.
