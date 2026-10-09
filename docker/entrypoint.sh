#!/bin/bash
set -e

# Default environment variables for supervisor workers and automation
export ENABLE_WORKER=${ENABLE_WORKER:-true}
export ENABLE_CRON=${ENABLE_CRON:-true}
export AUTORUN_LARAVEL_MIGRATION=${AUTORUN_LARAVEL_MIGRATION:-true}
export AUTORUN_LARAVEL_OPTIMIZE=${AUTORUN_LARAVEL_OPTIMIZE:-true}
export AUTORUN_STORAGE_LINK=${AUTORUN_STORAGE_LINK:-true}

echo "================================================="
echo "   Starting Laravel Application Container        "
echo "================================================="

# Ensure all runtime and storage directories exist (especially if volume mounted)
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/log/supervisor \
         /var/log/nginx \
         /run/nginx

# Ensure SQLite file exists if using SQLite driver
if [ "${DB_CONNECTION}" = "sqlite" ]; then
    SQLITE_DB="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ ! -f "$SQLITE_DB" ]; then
        echo ">> Initializing SQLite database file: $SQLITE_DB"
        mkdir -p "$(dirname "$SQLITE_DB")"
        touch "$SQLITE_DB"
        chown www-data:www-data "$SQLITE_DB"
    fi
fi

# Fix ownership and permissions for web user (www-data)
echo ">> Setting permissions for storage and bootstrap/cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
if [ "$AUTORUN_STORAGE_LINK" = "true" ]; then
    echo ">> Linking storage directory..."
    php /var/www/html/artisan storage:link --force || true
fi

# Run database migrations if enabled (default: true)
if [ "$AUTORUN_LARAVEL_MIGRATION" = "true" ] || [ "$RUN_MIGRATIONS" = "true" ]; then
    echo ">> Checking database connectivity and running migrations..."
    if [ "$DB_CONNECTION" != "sqlite" ] && [ -n "$DB_HOST" ]; then
        for i in {1..15}; do
            if php /var/www/html/artisan db:show > /dev/null 2>&1; then
                echo ">> Database is ready."
                break
            fi
            echo ">> Waiting for database ($DB_HOST:$DB_PORT)... ($i/15)"
            sleep 2
        done
    fi
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

# If custom command was passed to container, execute it directly
if [ $# -gt 0 ]; then
    echo ">> Executing custom command: $@"
    exec "$@"
fi

# Start Supervisor as PID 1 to handle signal forwarding and process monitoring
echo ">> Starting Supervisord (Nginx + PHP-FPM + Worker + Scheduler)..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
