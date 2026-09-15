# 03 — Roadmap

Fase = **vertical slice** yang bisa didemokan (migrasi → model → service → UI → tes). Kerjakan berurutan; setiap slice lulus docs/23 + docs/24 sebelum lanjut.

| Slice | Tujuan demoable | Fitur | Layar (docs/26) | Status |
|---|---|---|---|---|
| S0 | Skeleton: Laravel 12 terpasang, Vite membundel `app.css` prototipe, layout `app.blade.php` + komponen Blade dasar tampil identik prototipe (header, tab-nav, card, stat-card, table, badge, modal, toast) dengan data dummy | — | Login, Dashboard (dummy) | ✅ selesai 2026-09-15 (branch `feat/S0-skeleton`) |
| S1 | Login 3 peran; Admin kelola pengguna, bidang, sumber dana, tahun anggaran, pengaturan | F01, F02 | Login, Pengguna, Master ×3, Pengaturan, Profil (modal sandi) | ✅ selesai 2026-09-15 (branch `feat/S1-auth-master`) |
| S2 | Admin susun struktur anggaran tahun aktif; Operator isi target triwulan bidangnya; Policy scope bidang berjalan | F03, F04 | Anggaran, Target | ✅ selesai 2026-09-15 (branch `feat/S2-anggaran-target`) |
| S3 | Operator catat realisasi keuangan (dengan lampiran) & fisik; validasi sisa pagu, tahun terkunci, bidang lain → 403; dashboard hidup dengan data nyata; seeder prototipe | F05, F06, F07, F13 | Realisasi Keuangan, Realisasi Fisik, Dashboard | ✅ selesai 2026-09-15 (branch `feat/S3-realisasi-dashboard`) |
| S4 | Empat laporan tampil web + unduh xlsx & pdf berkop | F08–F11 | Laporan ×4 | — |
| S5 | Audit log lengkap, kunci/buka tahun dengan konfirmasi password, `deploy.sh`, rilis produksi pertama | F12 | Audit Log | — |

Pasca-MVP (tidak dijadwalkan): F14 Verifikator → F15 notifikasi & impor DPA → F16 multi-OPD/API.
