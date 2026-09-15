# 15 — Observability

## Logging
| Hal | Nilai |
|---|---|
| Channel | `daily`, retensi 14 hari, `storage/logs/laravel-YYYY-MM-DD.log` |
| Format | Laravel default + konteks JSON: `request_id`, `user_id`, `route`, `ip` (middleware `AssignRequestId` menambah ke `Log::shareContext`) |
| Level produksi | `warning` ke atas + `info` untuk aksi audit (lock/unlock, login gagal) |
| Level lokal | `debug` |

Apa yang dilog: 403/423/429 (`warning`/`info`), 500 (`error` + trace), ekspor gagal, kunci/buka tahun, login gagal, reset sandi. **Jangan** log sandi, isi lampiran, atau token.

## Audit bisnis
Terpisah dari log teknis: tabel `audit_logs` (docs/07) — siapa/kapan/apa untuk semua model ✎. Dilihat Admin di layar Audit Log (docs/26).

## Metrik & monitoring
`[TERBUKA]` pasca-MVP. MVP cukup: `php artisan about` + smoke test docs/25 + uptime ping eksternal (opsional) ke `/login`.
