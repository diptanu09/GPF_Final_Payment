#!/bin/sh
set -e

# Ensure storage framework directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs

# Fix permissions for Laravel storage and bootstrap cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage link if not exists
php artisan storage:link || true

# Production optimization caching
if [ "$APP_ENV" = "production" ]; then
    echo "Running production optimization caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
else
    echo "Running in non-production mode, clearing caches..."
    php artisan optimize:clear || true
fi

# Execute CMD passed to docker container (default supervisord)
exec "$@"
