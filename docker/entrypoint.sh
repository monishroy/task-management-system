#!/bin/bash
set -e

# Default environment variables for supervisor workers and automation
export ENABLE_WORKER=${ENABLE_WORKER:-true}
export ENABLE_CRON=${ENABLE_CRON:-true}
export AUTORUN_LARAVEL_MIGRATION=${AUTORUN_LARAVEL_MIGRATION:-false}
export AUTORUN_LARAVEL_OPTIMIZE=${AUTORUN_LARAVEL_OPTIMIZE:-true}
export AUTORUN_STORAGE_LINK=${AUTORUN_STORAGE_LINK:-true}

# Ensure runtime directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix permissions on storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
if [ "$AUTORUN_STORAGE_LINK" = "true" ]; then
    echo ">> Linking storage..."
    php /var/www/html/artisan storage:link --force || true
fi

# Run database migrations if enabled
if [ "$AUTORUN_LARAVEL_MIGRATION" = "true" ] || [ "$RUN_MIGRATIONS" = "true" ]; then
    echo ">> Running database migrations..."
    php /var/www/html/artisan migrate --force || true
fi

# Cache config, routes, views, and events in production
if [ "$AUTORUN_LARAVEL_OPTIMIZE" = "true" ] && [ "$APP_ENV" = "production" ]; then
    echo ">> Optimizing Laravel cache for production..."
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
    php /var/www/html/artisan event:cache || true
fi

# Execute passed command (if any), otherwise start Supervisor
if [ $# -gt 0 ]; then
    exec "$@"
else
    echo ">> Starting Supervisord processes..."
    exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
fi
