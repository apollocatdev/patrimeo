# Image monolithique: FrankenPHP (Debian) + PHP 8.4 + Supervisor + Cron + Python + woob
FROM dunglas/frankenphp:1-php8.4

ARG APP_VERSION=0.0.0
LABEL org.opencontainers.image.version="${APP_VERSION}" \
      org.opencontainers.image.title="patrimeo" \
      org.opencontainers.image.description="Patrimeo - self-hosted financial portfolio management" \
      org.opencontainers.image.source="https://github.com/apollocatdev/patrimeo"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Europe/Paris \
    PHP_MEMORY_LIMIT=512M

# dépendances système & PHP extensions utiles à Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
      ca-certificates curl git unzip zip tzdata locales supervisor cron sqlite3 \
      python3 python3-venv python3-pip \
      libicu-dev libzip-dev zlib1g-dev \
      $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Activer intl + zip (compilation depuis les sources fournis par l'image)
RUN docker-php-ext-install -j$(nproc) intl zip

# Composer (depuis l'image officielle PHP fournie par FrankenPHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Activer un php.ini de prod
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo "memory_limit=${PHP_MEMORY_LIMIT}" > "$PHP_INI_DIR/conf.d/zz-memory.ini"

# Préparer l’app
WORKDIR /app
COPY . /app

# Dépendances PHP
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# woob dans un venv Python
RUN python3 -m venv /opt/woob-venv \
 && /opt/woob-venv/bin/pip install --upgrade pip \
 && /opt/woob-venv/bin/pip install --no-cache-dir woob

# Caddyfile (FrankenPHP)
# Voir: https://frankenphp.dev/docs/laravel/
COPY docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

# Cron (scheduler Laravel)
COPY docker/cron/laravel /etc/cron.d/laravel
RUN chmod 0644 /etc/cron.d/laravel && crontab /etc/cron.d/laravel

# Supervisor (multi-process: frankenphp + cron + queue worker)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
