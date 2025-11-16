#!/usr/bin/env bash
set -euo pipefail

echo "🌍 Starting Patrimeo container..."

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


# Generate APP_KEY if empty in .env
if grep -q '^APP_KEY=$' .env; then
    echo "Generating APP_KEY..."
    NEW_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s|^APP_KEY=$|APP_KEY=${NEW_KEY}|" .env
fi


# ───────────────────────────────────────────────
# Laravel storage
# ───────────────────────────────────────────────
echo "📁 Preparing Laravel storage directories..."
mkdir -p storage/app \
         storage/framework/{cache,sessions,views} \
         storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache




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
php artisan view:cache || true
php artisan event:cache || true

# ───────────────────────────────────────────────
# Woob config directory
# ───────────────────────────────────────────────
echo "🐍 Checking Woob config directory..."
mkdir -p /root/.config/woob/backends

# ───────────────────────────────────────────────
# Start supervisor
# ───────────────────────────────────────────────
echo "🚀 Starting Supervisor (Nginx, PHP-FPM, Queue, Scheduler)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
