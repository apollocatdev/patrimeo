#!/usr/bin/env bash
set -euo pipefail

echo "🌍 Starting Patrimeo container..."

mkdir -p /tmp/caddy/{caddy,locks,certificates} && chmod 1777 /tmp/caddy


# Ensure /app is working directory
cd /app

# ───────────────────────────────────────────────
# .env handling
# ───────────────────────────────────────────────
if [ ! -f .env ]; then
  echo "⚙️  No .env found, creating it..."
  cp .env.example .env
  ls -l |grep .env 
  cat .env
  rm -f bootstrap/cache/config.php 2>/dev/null || true
  php artisan key:generate --force
fi


# ───────────────────────────────────────────────
# Laravel storage
# ───────────────────────────────────────────────
echo "📁 Preparing Laravel storage directories..."
mkdir -p storage/app \
         storage/framework/{cache,sessions,views} \
         storage/logs




# ───────────────────────────────────────────────
# Database: initialize only if file does not exist
# ───────────────────────────────────────────────
DB_FILE="storage/app/database.sqlite"

if [ ! -f "$DB_FILE" ]; then
  echo "🆕 No database found → creating + migrate + seed"
  : > "$DB_FILE"
  php artisan migrate --force || true
  php artisan db:seed --force || true
else
  echo "✅ Existing database detected → skipping migrate and seed"
fi

# ───────────────────────────────────────────────
# Laravel caches
# ───────────────────────────────────────────────
echo "🧩 Rebuilding Laravel caches..."
php artisan config:clear || true
php artisan route:clear  || true
php artisan view:clear   || true
php artisan config:cache || true
php artisan route:cache  || true

# ───────────────────────────────────────────────
# Woob config directory
# ───────────────────────────────────────────────
echo "🐍 Checking Woob config directory..."
mkdir -p /home/app/.config/woob/backends

# ───────────────────────────────────────────────
# Start supervisor
# ───────────────────────────────────────────────
echo "🚀 Starting Supervisor (FrankenPHP, Queue, Scheduler)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
