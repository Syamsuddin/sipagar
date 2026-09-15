# 10 — Lingkungan Pengembangan

Perintah → docs/11. Versi → docs/09.

## Prasyarat lokal (macOS/Linux)
PHP 8.4 + ekstensi (docs/09), Composer 2, Node 20, MySQL 8 lokal (atau Docker `mysql:8`), Git.

## Setup lokal (urut)
1. `git clone …` → `cd sipagar`
2. `composer install` · `npm install`
3. `cp .env.example .env` → isi variabel di bawah → `php artisan key:generate`
4. Buat database `sipagar` (utf8mb4) → `php artisan migrate --seed`
5. `php artisan storage:link` (hanya utk logo kop; lampiran memakai disk private, tidak di-link)
6. Terminal A `php artisan serve` · Terminal B `npm run dev` → buka `http://localhost:8000`, login `admin` / `admin123` (seeder lokal saja).

## Variabel `.env` (nama saja)
| Variabel | Catatan |
|---|---|
| APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, APP_TIMEZONE=`Asia/Makassar`, APP_LOCALE=`id` | |
| DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD | |
| SESSION_DRIVER=database, SESSION_LIFETIME=120, SESSION_SECURE_COOKIE (true di produksi) | |
| CACHE_STORE=database, QUEUE_CONNECTION=sync | |
| FILESYSTEM_DISK=local; disk `private` didefinisikan di `config/filesystems.php` → `storage/app/private` | lampiran |
| LOG_CHANNEL=daily, LOG_LEVEL | docs/15 |
| SIPAGAR_TAHUN_MIN=2020, SIPAGAR_TAHUN_MAX=2034 | rentang tahun |

## Produksi (ringkas; langkah rilis → docs/25)
Ubuntu + nginx (root `public/`) + php8.4-fpm + MySQL 8; aplikasi di `/var/www/sipagar` milik user deploy, grup `www-data`; hanya `storage/` & `bootstrap/cache/` group-writable. `.env` produksi: `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_SECURE_COOKIE=true`. Deploy lewat `deploy.sh` (pola SIMURU) atau ODIN.
