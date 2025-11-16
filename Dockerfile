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

# --- Create non-root app user (fixed UID/GID for predictability) ---
# (Tu peux changer 1000:1000 si besoin)
RUN addgroup --gid 1000 app \
 && adduser  --uid 1000 --gid 1000 --disabled-password --gecos "" app

# --- Python woob (root) ---
RUN python3 -m venv /opt/woob-venv \
 && /opt/woob-venv/bin/pip install --upgrade pip \
 && /opt/woob-venv/bin/pip install --no-cache-dir woob

RUN ln -s /opt/woob-venv/bin/woob /usr/bin/woob

# --- System configs (root) ---
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && mkdir -p /run/nginx \
         /tmp/nginx-{client-body,proxy,fastcgi,uwsgi,scgi} \
    && chown -R root:root /run/nginx \
    && chmod 1777 /tmp/nginx-*

# --- App layer ---
WORKDIR /app

# Make sure /app is owned by app before switching user
RUN mkdir -p /app && chown app:app /app


# ⚡ Switch to non-root BEFORE copying code to avoid any chown -R later
USER app

# Install vendors (no scripts) with only composer files for better caching
COPY --chown=app:app composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Copy the rest of the application as app user
COPY --chown=app:app . /app

# Optional: run composer post-autoload scripts (still as app)
RUN composer run-script post-autoload-dump || true

# No cron here: scheduler runs via Supervisor (`php artisan schedule:work`)

EXPOSE 80
CMD ["/entrypoint.sh"]
