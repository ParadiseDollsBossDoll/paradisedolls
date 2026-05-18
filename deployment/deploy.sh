#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/paradisedollz}"
PHP_BIN="${PHP_BIN:-php}"
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"

cd "$APP_DIR"

mkdir -p \
  bootstrap/cache \
  storage/app/private \
  storage/app/public \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs

if [ ! -f .env ]; then
  echo "Missing .env in $APP_DIR. Create it from deployment/.env.production before deploying." >&2
  exit 1
fi

$PHP_BIN artisan down --render="errors::503" --retry=60 || true

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan migrate --force
$PHP_BIN artisan storage:link
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache
$PHP_BIN artisan queue:restart

chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache public/build || true
chmod -R ug+rwX storage bootstrap/cache || true

if command -v supervisorctl >/dev/null 2>&1; then
  supervisorctl reread || true
  supervisorctl update || true
  supervisorctl restart paradisedollz-queue:* || true
  supervisorctl restart paradisedollz-reverb:* || supervisorctl restart paradisedollz-reverb || true
fi

if command -v systemctl >/dev/null 2>&1; then
  systemctl reload nginx || true
  systemctl reload php8.3-fpm || systemctl reload php8.2-fpm || true
fi

$PHP_BIN artisan up

echo "Deployment complete."
