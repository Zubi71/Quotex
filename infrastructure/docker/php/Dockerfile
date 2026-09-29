# ──────────────────────────────────────────────────────────────────────────────
# OTC Signal Intelligence — PHP 8.3 FPM Dockerfile
# Multi-stage build: builder -> production
# ──────────────────────────────────────────────────────────────────────────────

# ══════════════════════════════════════════════════════════════════════════════
# Stage 1: builder — installs all dependencies including dev tools
# ══════════════════════════════════════════════════════════════════════════════
FROM php:8.3-fpm-alpine AS builder

LABEL maintainer="OTC Signal Intelligence Team"

# ── System build dependencies ─────────────────────────────────────────────────
RUN apk add --no-cache \
    autoconf \
    bash \
    curl \
    g++ \
    gcc \
    git \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    make \
    mysql-client \
    oniguruma-dev \
    openssl-dev \
    postgresql-dev \
    unzip \
    zip \
    zlib-dev

# ── PHP extensions ─────────────────────────────────────────────────────────────
# Core extensions from source
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        calendar \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pcntl \
        sockets \
        xml \
        zip

# Install PECL extensions
RUN pecl install redis-6.0.2 \
    && docker-php-ext-enable redis

# ── Composer 2 ────────────────────────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/local/bin/composer

# ── Working directory ─────────────────────────────────────────────────────────
WORKDIR /var/www/api

# ── Install PHP dependencies (cached layer) ───────────────────────────────────
# Copy only composer files first to leverage Docker layer cache.
# If composer.json/lock haven't changed, this layer is reused.
COPY apps/api/composer.json apps/api/composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ── Copy application source ───────────────────────────────────────────────────
COPY apps/api/ .

# ── Run post-install scripts now that all code is present ────────────────────
RUN composer run-script post-autoload-dump --no-interaction || true

# ── Set permissions ───────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/api \
    && chmod -R 755 /var/www/api/storage \
    && chmod -R 755 /var/www/api/bootstrap/cache

# ══════════════════════════════════════════════════════════════════════════════
# Stage 2: production — lean runtime image
# ══════════════════════════════════════════════════════════════════════════════
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="OTC Signal Intelligence Team"
LABEL description="PHP 8.3 FPM runtime for OTC Signal Intelligence API"

# ── Runtime dependencies only ─────────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    icu-libs \
    libpng \
    libjpeg-turbo \
    libwebp \
    libxml2 \
    libzip \
    mysql-client \
    oniguruma \
    openssl \
    postgresql-libs \
    unzip \
    zip

# ── PHP extensions (copy compiled extensions from builder) ─────────────────────
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Re-enable extensions in production stage
RUN docker-php-ext-enable \
    bcmath \
    calendar \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pcntl \
    redis \
    sockets \
    xml \
    zip

# ── PHP configuration ─────────────────────────────────────────────────────────
COPY infrastructure/docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# ── Composer (needed for artisan commands in entrypoint) ──────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/local/bin/composer

# ── Copy application from builder stage ──────────────────────────────────────
WORKDIR /var/www/api
COPY --from=builder --chown=www-data:www-data /var/www/api .

# ── Create required directories ───────────────────────────────────────────────
RUN mkdir -p storage/app/public \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/testing \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── PHP-FPM pool configuration ────────────────────────────────────────────────
RUN sed -i 's/^user = .*/user = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^;listen.owner = .*/listen.owner = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^;listen.group = .*/listen.group = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm = .*/pm = dynamic/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.max_children = .*/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.start_servers = .*/pm.start_servers = 4/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 2/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 8/' /usr/local/etc/php-fpm.d/www.conf

# ── Security: remove composer from production if desired ──────────────────────
# Uncomment the line below for hardened production images:
# RUN rm /usr/local/bin/composer

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
