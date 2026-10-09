# ==============================================================================
# Multi-stage Dockerfile for Laravel Application with Supervisor (Coolify Ready)
# Optimized for high performance, fast build times, and zero-downtime reliability
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
# Uses Debian Bookworm for instant pre-compiled extension installations
# ------------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS production

ENV DEBIAN_FRONTEND=noninteractive

# Install essential system dependencies & runtime packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    bash \
    tzdata \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

# Install Docker PHP Extension Installer for instant pre-compiled extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install required PHP extensions for Laravel & database drivers
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    bcmath \
    mbstring \
    xml \
    zip \
    intl \
    gd \
    opcache \
    pcntl \
    redis \
    exif

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
