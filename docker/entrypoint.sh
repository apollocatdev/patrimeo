#!/usr/bin/env bash
set -euo pipefail

# TZ
if [ -n "${TZ:-}" ]; then
  ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime && echo "$TZ" > /etc/timezone || true
fi

# Dossiers Laravel
mkdir -p /app/storage/framework/{cache,sessions,views} /app/storage/logs
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# DB SQLite mappée: garantir l’existence du fichier
if [ ! -f /app/storage/app/database.sqlite ]; then
  install -o www-data -g www-data -m 660 /dev/null /app/storage/app/database.sqlite
fi

# Optimisations Laravel
su -s /bin/bash -c "php artisan config:clear || true" www-data
su -s /bin/bash -c "php artisan route:clear || true" www-data
su -s /bin/bash -c "php artisan view:clear || true" www-data
su -s /bin/bash -c "php artisan migrate --force || true" www-data
su -s /bin/bash -c "php artisan config:cache || true" www-data
su -s /bin/bash -c "php artisan route:cache || true" www-data

# Préparer répertoire woob (persisté côté host via bind)
mkdir -p /home/app/.config/woob/backends
chown -R www-data:www-data /home/app/.config || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf