#!/bin/sh
set -e

echo "=== GPF Final Payment Portal Entrypoint Starting ==="

# 1. Ensure storage framework directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# 2. Fix permissions for Laravel storage and bootstrap cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Create storage symlink if not exists
php artisan storage:link || true

# 4. Ensure SSL Certificate exists, is non-empty, and is valid, otherwise generate self-signed SAN certificate
if [ ! -s /etc/nginx/ssl/server.crt ] || [ ! -s /etc/nginx/ssl/server.key ] || ! openssl rsa -in /etc/nginx/ssl/server.key -check -noout >/dev/null 2>&1; then
    echo "Generating self-signed SAN SSL Certificate..."
    mkdir -p /etc/nginx/ssl
    openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/nginx/ssl/server.key \
        -out /etc/nginx/ssl/server.crt \
        -subj "/C=IN/ST=Tripura/L=Agartala/O=Office of the Accountant General (A&E) Tripura/OU=Fund Section/CN=gpffp.local" \
        -addext "subjectAltName=DNS:gpffp.local,DNS:gpf-final-payment.local,DNS:gpf_final_payment.local,DNS:gpf.tripura.local,DNS:localhost,IP:10.47.240.169,IP:127.0.0.1"
    chmod 600 /etc/nginx/ssl/server.key
    chmod 644 /etc/nginx/ssl/server.crt
fi

# 5. Run database migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Warning: Migration failed or database not ready, continuing startup..."
fi

# 6. Production optimization caching
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

echo "=== GPF Final Payment Portal Initialized Successfully ==="

# Execute CMD passed to docker container (default supervisord)
exec "$@"
