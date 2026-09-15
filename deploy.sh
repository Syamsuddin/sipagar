#!/usr/bin/env bash
#
# SIPAGAR — deploy/update ke server produksi (docs/25 langkah 3–9, pola SIMURU).
# Jalankan SEBAGAI USER DEPLOY (bukan root) di server:
#     cd /var/www/sipagar && bash deploy.sh [branch|tag]
#
# Alur: backup DB → maintenance → git pull --ff-only → composer/npm build → migrate --force
#       → optimize → izin → reload php-fpm & nginx → up → smoke test. Gagal migrasi → petunjuk rollback (docs/25 §R).
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/sipagar}"
REF="${1:-main}"
BACKUP_DIR="${HOME}/sipagar_backups"
DEPLOY_USER="$(id -un)"
cd "$APP_DIR"

if [ "$DEPLOY_USER" = "root" ]; then
  echo "❌ Jangan jalankan sebagai root (sudo dipakai per-perintah)."; exit 1
fi
if grep -qE '^APP_ENV=production' .env && grep -qE '^APP_DEBUG=true' .env; then
  echo "❌ APP_DEBUG=true pada produksi (docs/10). Perbaiki .env dulu."; exit 1
fi

TS="$(date +%F_%H%M%S)"
mkdir -p "$BACKUP_DIR"
env_val() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"'"'"' '; }
DB_DATABASE="$(env_val DB_DATABASE)"; DB_USERNAME="$(env_val DB_USERNAME)"; DB_PASSWORD="$(env_val DB_PASSWORD)"; DB_HOST="$(env_val DB_HOST)"
ERR_SEBELUM="$(grep -c ERROR "storage/logs/laravel-$(date +%F).log" 2>/dev/null || echo 0)"

echo "==> [3] Backup database ${DB_DATABASE}"
BACKUP="${BACKUP_DIR}/pre_${REF//\//_}_${TS}.sql"
MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --no-tablespaces -h "${DB_HOST:-127.0.0.1}" -u "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP"
[ -s "$BACKUP" ] && tail -1 "$BACKUP" | grep -q "Dump completed" || { echo "❌ Backup gagal/kosong: $BACKUP"; exit 1; }
echo "    -> $BACKUP ($(du -h "$BACKUP" | cut -f1))"

echo "==> [4] Maintenance mode"
SECRET="$(php -r 'echo bin2hex(random_bytes(8));')"
php artisan down --secret="$SECRET" --render="errors::503" >/dev/null || php artisan down --secret="$SECRET"
echo "    bypass: https://<host>/${SECRET}"
trap 'echo "⚠️  Deploy terhenti — aplikasi masih maintenance. Ikuti docs/25 §R lalu: php artisan up"; exit 1' ERR

echo "==> [5] Tarik kode (${REF})"
git fetch --tags origin
git checkout -q "$REF"
git rev-parse --verify -q "origin/$REF" >/dev/null && git pull --ff-only origin "$REF"
echo "    Commit aktif: $(git log --oneline -1)"

echo "==> [6] Dependensi & aset"
composer install --no-dev --optimize-autoloader --no-interaction --quiet
npm ci --silent && npm run build --silent
[ -f public/build/manifest.json ] || { echo "❌ public/build/manifest.json tidak ada"; exit 1; }

echo "==> [7] Migrasi (--force)"
if ! php artisan migrate --force; then
  echo "❌ Migrasi gagal. Rollback (docs/25 §R): php artisan migrate:rollback --step=<n> bila down() teruji,"
  echo "   atau restore: MYSQL_PWD=... mysql -u $DB_USERNAME $DB_DATABASE < $BACKUP"
  exit 1
fi

echo "==> [8] Optimize, izin, reload"
php artisan optimize --quiet
sudo chown -R "${DEPLOY_USER}:www-data" "$APP_DIR"
sudo chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
PHP_FPM="$(systemctl list-units --type=service --no-legend 'php*-fpm*' | awk '{print $1}' | head -1)"
[ -n "$PHP_FPM" ] && sudo systemctl reload "$PHP_FPM" && echo "    reloaded $PHP_FPM"
sudo nginx -t -q && sudo systemctl reload nginx && echo "    reloaded nginx"

echo "==> [9] Up"
trap - ERR
php artisan up

echo "==> [10] Smoke test"
APP_URL="$(env_val APP_URL)"
STATUS="$(curl -sI -o /dev/null -w '%{http_code}' "${APP_URL%/}/login" || echo 000)"
ERR_SESUDAH="$(grep -c ERROR "storage/logs/laravel-$(date +%F).log" 2>/dev/null || echo 0)"
echo "    GET /login → $STATUS · ERROR log: $ERR_SEBELUM → $ERR_SESUDAH"
if [ "$STATUS" = "200" ] && [ "$ERR_SESUDAH" -le "$ERR_SEBELUM" ]; then
  echo "DEPLOY OK"
else
  echo "❌ Smoke test gagal — periksa storage/logs dan pertimbangkan docs/25 §R"; exit 1
fi
