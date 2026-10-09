# ==============================================================================
# Multi-stage Dockerfile for Laravel Application with Supervisor (Coolify Ready)
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite / Tailwind CSS)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS frontend_builder

WORKDIR /app

# Copy dependency definition files
COPY package.json package-lock.json ./

# Install npm dependencies
RUN npm ci --no-audit

# Copy frontend source files & configs
COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

# Build assets with Vite
RUN npm run build


# ------------------------------------------------------------------------------
# Stage 2: Install Composer Dependencies
# ------------------------------------------------------------------------------
FROM composer:2 AS composer_builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies without dev dependencies & scripts
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


# ------------------------------------------------------------------------------
# Stage 3: Production Runtime (PHP 8.4-FPM + Nginx + Supervisor)
# ------------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS production

# Install system dependencies & utilities
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    tzdata \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    oniguruma

# Install Docker PHP Extension Installer for clean, reliable PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install required PHP extensions for Laravel & DB drivers
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

# Configure Nginx, PHP, and Supervisor
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

# Copy vendor from composer_builder
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets from frontend_builder
COPY --from=frontend_builder /app/public/build ./public/build

# Finish Composer classmap generation
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Ensure runtime directories exist with proper permissions
RUN mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/log/supervisor \
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
