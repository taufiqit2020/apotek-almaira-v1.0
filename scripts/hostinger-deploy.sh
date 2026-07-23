#!/usr/bin/env bash
# Deploy Apotek Almaira on Hostinger (SSH).
# Hostinger PHP blocks proc_open by default, so Composer must run with -d disable_functions=
set -euo pipefail

APP_DIR="${APP_DIR:-$HOME/domains/ptnurmadanifarma.com/public_html}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer2}"

cd "$APP_DIR"

echo "==> Pull latest code"
git fetch origin main
git reset --hard origin/main

echo "==> Composer install (proc_open override)"
$PHP_BIN -d disable_functions= "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --no-scripts
$PHP_BIN -d disable_functions= "$COMPOSER_BIN" dump-autoload -o --no-interaction
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php bootstrap/cache/routes.php
$PHP_BIN artisan package:discover --ansi || true

echo "==> Ensure storage link"
if [ ! -L public/storage ]; then
  rm -rf public/storage
  ln -s ../storage/app/public public/storage
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache storage/app/public
chmod -R ug+rwx storage bootstrap/cache || true

if [ ! -f .env ]; then
  echo "ERROR: .env missing. Copy from .env.production.example and configure DB first."
  exit 1
fi

echo "==> Migrate + optimize"
$PHP_BIN artisan migrate --force
# Seeding is intentional/manual on production. Master data import:
#   php tools/export_master_for_hostinger.php   (local)
#   php tools/import_master_on_hostinger.php    (server)
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# Root .htaccess is tracked in git (needed when Hostinger document root = public_html)
test -f .htaccess || {
  echo "ERROR: missing root .htaccess"
  exit 1
}

echo "==> Deploy OK"
