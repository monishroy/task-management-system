# ==============================================================================
# Multi-stage Dockerfile for Laravel Application with Supervisor (Coolify Ready)
# Optimized for ultra-fast builds, minimal network overhead, and high performance
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite / Tailwind CSS)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS frontend_builder

WORKDIR /app

# Cache package dependencies layer
COPY package.json package-lock.json ./
RUN npm ci --no-audit

# Copy source files required for asset compilation & Tailwind class scanning
COPY . .

# Compile production assets
RUN npm run build


# ------------------------------------------------------------------------------
# Stage 2: Install Composer Dependencies
# ------------------------------------------------------------------------------
FROM composer:2 AS composer_builder

WORKDIR /app

# Cache composer dependency layer
COPY composer.json composer.lock ./

# Install PHP dependencies without dev packages or scripts
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


# ------------------------------------------------------------------------------
# Stage 3: Production Runtime (PHP 8.4-FPM + Nginx + Supervisor)
# Streamlined single-layer dependency install with Fastly CDN mirror
# ------------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS production

ENV DEBIAN_FRONTEND=noninteractive

# 1. Switch to Fastly CDN mirror for fast downloads
# 2. Install runtime & PHP extension libraries in a SINGLE step
# 3. Clean up build artifacts to keep image lean
RUN sed -i 's|deb.debian.org|cdn-fastly.deb.debian.org|g' /etc/apt/sources.list.d/debian.sources 2>/dev/null \
    || sed -i 's|deb.debian.org|cdn-fastly.deb.debian.org|g' /etc/apt/sources.list 2>/dev/null \
    || true \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        bash \
        tzdata \
        ca-certificates \
        libpq5 \
        libzip4 \
        libicu72 \
        libpng16-16 \
        libjpeg62-turbo \
        libfreetype6 \
        libwebp7 \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        bcmath \
        pcntl \
        opcache \
        exif \
        zip \
        intl \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
    && rm -rf /var/lib/apt/lists/* /tmp/pear \
    && rm -f /etc/nginx/sites-enabled/default

# Copy Composer binary into runtime for artisan/cli operations
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configure Nginx, PHP-FPM, and Supervisor
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy vendor packages from composer_builder
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets from frontend_builder
COPY --from=frontend_builder /app/public/build ./public/build

# Generate optimized authoritative Composer classmap
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Ensure runtime and storage directories exist with correct permissions
RUN mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/log/supervisor \
    /var/log/nginx \
    /run/nginx \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Setup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose HTTP port for Coolify / reverse proxy
EXPOSE 80

# Health check using Laravel's /up endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
