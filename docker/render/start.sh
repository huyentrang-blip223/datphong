#!/bin/sh
set -eu
cd /var/www/html
# Render provides this origin after service creation; no guessed deployment URL.
if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then export APP_URL="$RENDER_EXTERNAL_URL"; fi
if [ -z "${APP_KEY:-}" ]; then echo 'APP_KEY must be configured and kept stable.' >&2; exit 1; fi
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan dt07:prepare-deployment
if [ ! -L public/storage ]; then php artisan storage:link; fi
php artisan config:cache
exec supervisord -c /etc/supervisord.conf
