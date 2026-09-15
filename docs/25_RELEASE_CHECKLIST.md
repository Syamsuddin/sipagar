# 25 — Checklist Rilis (berurutan)

Kebijakan → docs/22. Perintah → docs/11. Jalankan di server sebagai user deploy (bukan root); `deploy.sh` mengotomasi langkah 3–8.

| # | Langkah | Lulus bila |
|---|---|---|
| 1 | Lokal: `php artisan test` & `pint --test` | semua hijau |
| 2 | PR di-merge ke `main`; tag `vX.Y.Z` | tag ada |
| 3 | Backup DB: `mysqldump --single-transaction … > ~/sipagar_backups/pre_vX.Y.Z.sql` | file > 0 byte, `tail -1` berisi `-- Dump completed` |
| 4 | `php artisan down --secret=<token>` | halaman maintenance tampil |
| 5 | `git pull --ff-only origin main` | commit = tag |
| 6 | `composer install --no-dev --optimize-autoloader` · `npm ci && npm run build` | `public/build/manifest.json` baru |
| 7 | `php artisan migrate --force` ⚠️ | semua `DONE`; tidak ada error; **jika gagal → langkah R** |
| 8 | `php artisan optimize` · `sudo systemctl reload php8.4-fpm nginx` | tanpa error |
| 9 | `php artisan up` | |
| 10 | Smoke test: `curl -sI https://<host>/login` → 200; login Admin; buka Dashboard, satu laporan, unduh 1 PDF & 1 xlsx | semua berhasil, log tanpa `error` baru: `grep -c ERROR storage/logs/laravel-$(date +%F).log` tidak bertambah |
| 11 | Catat versi & waktu di `CHANGELOG.md` | |

## R — Rollback
1. `php artisan down`
2. `git checkout <tag sebelumnya>` → `composer install --no-dev` → `npm ci && npm run build`
3. Bila migrasi sudah jalan: `php artisan migrate:rollback --step=<n>` jika `down()` teruji; **jika tidak**: `mysql … < ~/sipagar_backups/pre_vX.Y.Z.sql`
4. `php artisan optimize` → reload fpm/nginx → `php artisan up` → smoke test langkah 10.
