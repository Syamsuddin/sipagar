# 11 — Perintah

Semua dokumen merujuk ke sini. Jalankan dari root proyek.

| Tujuan | Perintah | Sinyal lulus |
|---|---|---|
| Install dependensi | `composer install && npm install` | tanpa error |
| Jalankan lokal | `php artisan serve` + `npm run dev` | `http://localhost:8000` tampil layar login |
| Migrasi + seed (lokal) | `php artisan migrate --seed` | `INFO Seeding database.` tanpa exception |
| Migrasi ulang bersih ⚠️ lokal saja | `php artisan migrate:fresh --seed` | — (DILARANG di produksi, docs/22) |
| Migrasi produksi ⚠️ | `php artisan migrate --force` | `DONE` tiap migrasi; tidak ada `Rolling back` |
| Rollback 1 batch ⚠️ | `php artisan migrate:rollback --step=1` | |
| **Tes semua** | `php artisan test` | `Tests: N passed` , 0 failed |
| Tes satu file | `php artisan test tests/Feature/RealisasiKeuanganTest.php` | |
| Tes + coverage (opsional) | `php artisan test --coverage --min=70` | ≥ 70 % |
| Lint/format | `vendor/bin/pint --test` (cek) · `vendor/bin/pint` (perbaiki) | `PASS` |
| Build aset produksi | `npm run build` | `public/build/manifest.json` ada |
| Cache konfigurasi (produksi) | `php artisan optimize` | |
| Bersihkan cache | `php artisan optimize:clear` | |
| Seed ulang data demo prototipe | `php artisan db:seed --class=PrototipeSeeder` | |
| Buat admin awal (produksi) | `php artisan sipagar:buat-admin` | prompt username/sandi; command custom |
| Backup DB | `mysqldump --single-transaction $DB_DATABASE > backup_$(date +%F_%H%M).sql` | file > 0 byte |
| Smoke test produksi | `curl -sI https://<host>/login \| head -1` | `HTTP/2 200` |
| Deploy ⚠️ | `bash deploy.sh main` (di server, sebagai user deploy) | keluaran `DEPLOY OK` |
| Log aplikasi | `tail -f storage/logs/laravel-$(date +%F).log` | |
