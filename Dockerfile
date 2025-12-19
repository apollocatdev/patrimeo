# Monolith: Nginx + PHP-FPM 8.4 + Supervisor + Python + woob
FROM php:8.4-fpm

ARG APP_VERSION=0.0.0
LABEL org.opencontainers.image.version="${APP_VERSION}" \
      org.opencontainers.image.title="patrimeo" \
      org.opencontainers.image.description="Patrimeo - self-hosted financial portfolio management" \
      org.opencontainers.image.source="https://github.com/apollocatdev/patrimeo"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Europe/Paris \
    PHP_MEMORY_LIMIT=512M \
    APP_DIR=/var/www/patrimeo \
    XDG_CONFIG_HOME=/var/www/.config \
    WOOB_DIR=/var/www/.config/woob



# --- System deps (root) ---
RUN apt-get update && apt-get install -y --no-install-recommends \
      ca-certificates sudo curl git gpgv unzip zip tzdata locales supervisor sqlite3 \
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

RUN mkdir -p /home/www-data/.local/share/woob/keyrings

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
RUN mkdir -p ${APP_DIR} ${WOOB_DIR} \
    && chown -R www-data:www-data /var/www \
    && chmod 775 ${WOOB_DIR}

USER www-data
WORKDIR ${APP_DIR}

# Install vendors (no scripts) with only composer files for better caching
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Copy the rest of the application
COPY --chown=www-data:www-data . ${APP_DIR}

RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 755 public \
    && chmod 644 public/index.php

USER root

# Optional: run composer post-autoload scripts
RUN composer run-script post-autoload-dump || true

# No cron here: scheduler runs via Supervisor (`php artisan schedule:work`)

EXPOSE 80
CMD ["/entrypoint.sh"]
