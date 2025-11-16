# Monolith: Nginx + PHP-FPM 8.4 + Supervisor + Python + woob
FROM php:8.4-fpm

ARG APP_VERSION=0.0.0
LABEL org.opencontainers.image.version="${APP_VERSION}" \
      org.opencontainers.image.title="patrimeo" \
      org.opencontainers.image.description="Patrimeo - self-hosted financial portfolio management" \
      org.opencontainers.image.source="https://github.com/apollocatdev/patrimeo"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Europe/Paris \
    PHP_MEMORY_LIMIT=512M



# --- System deps (root) ---
RUN apt-get update && apt-get install -y --no-install-recommends \
      ca-certificates curl git unzip zip tzdata locales supervisor sqlite3 \
      python3 python3-venv python3-pip \
      nginx \
      vim-tiny procps \
      libicu-dev libzip-dev zlib1g-dev \
      $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install -j"$(nproc)" intl zip

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# php.ini prod
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo "memory_limit=${PHP_MEMORY_LIMIT}" > "$PHP_INI_DIR/conf.d/zz-memory.ini"

# --- Python woob (root) ---
RUN python3 -m venv /opt/woob-venv \
 && /opt/woob-venv/bin/pip install --upgrade pip \
 && /opt/woob-venv/bin/pip install --no-cache-dir woob

RUN ln -s /opt/woob-venv/bin/woob /usr/bin/woob

# --- System configs (root) ---
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -f /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/www.conf

# --- App layer ---
WORKDIR /app

# Create /app with proper permissions (root:root, 755)
RUN mkdir -p /app && chown root:root /app && chmod 755 /app

# Install vendors (no scripts) with only composer files for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Copy the rest of the application
COPY . /app

# Set proper permissions for /app (readable by www-data, writable by root)
RUN chown -R root:root /app && \
    find /app -type d -exec chmod 755 {} \; && \
    find /app -type f -exec chmod 644 {} \; && \
    chmod -R 775 /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Optional: run composer post-autoload scripts
RUN composer run-script post-autoload-dump || true

# No cron here: scheduler runs via Supervisor (`php artisan schedule:work`)

EXPOSE 80
CMD ["/entrypoint.sh"]
