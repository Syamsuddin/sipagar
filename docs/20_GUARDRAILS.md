# 20 — Guardrail Operasional

Daftar **JANGAN** keras. Keamanan detail → docs/21; kebijakan perubahan → docs/22.

1. JANGAN hard-code rahasia, kredensial, atau `APP_KEY`; semuanya di `.env`.
2. JANGAN menonaktifkan/ melewati CSRF, Policy, FormRequest, middleware `EnsureTahunTerbuka`, atau throttle — meski "sementara untuk tes".
3. JANGAN menulis rumus serapan/deviasi/status di luar `SerapanCalculator`.
4. JANGAN menyimpan nilai turunan (sisa, serapan, status) di tabel.
5. JANGAN menambah paket Composer/NPM, komponen UI, warna, atau font baru tanpa alasan tertulis (docs/09, docs/26).
6. JANGAN memakai CDN untuk aset runtime (satu-satunya pengecualian tertulis: Google Fonts `@import` — docs/09).
7. JANGAN `forceDelete`, `migrate:fresh`, `db:wipe`, atau `TRUNCATE` di luar lokal.
8. JANGAN melonggarkan tes agar hijau (docs/18).
9. JANGAN mengubah keputusan yang disengaja (docs/08 tabel keputusan, docs/26 desain) tanpa konfirmasi.
10. JANGAN melampaui scope docs/02 atau "berinisiatif" membangun F14–F16.
11. JANGAN log sandi/token/isi lampiran; JANGAN bocorkan stack trace ke pengguna.
12. JANGAN commit langsung ke `main` (docs/22).
