# ==============================================================================
# Multi-Stage Dockerfile for GPF Final Payment Portal (Production)
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite + React 19 + Tailwind CSS)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend-builder
WORKDIR /app

# Cache package installation
COPY package*.json ./
RUN npm ci --silent || npm install --silent

# Build production frontend assets
COPY . .
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: PHP 8.3 FPM Production Runtime
# ------------------------------------------------------------------------------
FROM php:8.3-fpm-bookworm AS production

# Set working directory
WORKDIR /var/www/html

# Environment variables
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    LD_LIBRARY_PATH=/usr/lib/oracle/current \
    ORACLE_HOME=/usr/lib/oracle/current \
    TNS_ADMIN=/usr/lib/oracle/current/network/admin

# Install system dependencies & build tools (including $PHPIZE_DEPS for PECL compilation)
RUN apt-get update && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    build-essential \
    nginx \
    supervisor \
    curl \
    unzip \
    git \
    openssl \
    ca-certificates \
    libpq-dev \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libaio1 \
    libaio-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Oracle Instant Client 19c (19.24 LTS) for full compatibility with Oracle 11g Enterprise
RUN mkdir -p /opt/oracle /usr/lib/oracle && cd /opt/oracle \
    && curl -fSL -o instantclient-basic.zip https://download.oracle.com/otn_software/linux/instantclient/1924000/instantclient-basiclite-linux.x64-19.24.0.0.0dbru.zip \
    && curl -fSL -o instantclient-sdk.zip https://download.oracle.com/otn_software/linux/instantclient/1924000/instantclient-sdk-linux.x64-19.24.0.0.0dbru.zip \
    && unzip -q instantclient-basic.zip \
    && unzip -q instantclient-sdk.zip \
    && rm -f instantclient-basic.zip instantclient-sdk.zip \
    && mv instantclient_* /usr/lib/oracle/current \
    && mkdir -p /usr/lib/oracle/current/network/admin \
    && ln -sf /usr/lib/oracle/current/libclntsh.so.* /usr/lib/oracle/current/libclntsh.so \
    && ln -sf /usr/lib/oracle/current/libocci.so.* /usr/lib/oracle/current/libocci.so \
    && echo /usr/lib/oracle/current > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig \
    && printf "SQLNET.ALLOWED_LOGON_VERSION_CLIENT=8\nSQLNET.ALLOWED_LOGON_VERSION_SERVER=8\n" > /usr/lib/oracle/current/network/admin/sqlnet.ora \
    && rm -rf /opt/oracle

# Configure & Install PHP Extensions (including OCI8 and PDO_OCI)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/usr/lib/oracle/current \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    pdo_sqlite \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    pcntl \
    pdo_oci \
    && echo 'instantclient,/usr/lib/oracle/current' | pecl install oci8-3.4.0 \
    && docker-php-ext-enable oci8 \
    && php -m | grep -q oci8 \
    && php -m | grep -q pdo_oci \
    && echo "=== Oracle OCI8 and PDO_OCI extensions verified successfully ==="

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Application Source Code
COPY . /var/www/html

# Clean any host cached files from bootstrap/cache or storage before composer install
RUN rm -f /var/www/html/bootstrap/cache/*.php \
    && rm -rf /var/www/html/storage/framework/cache/data/* \
    && rm -rf /var/www/html/storage/framework/sessions/* \
    && rm -rf /var/www/html/storage/framework/views/*

# Install Composer production dependencies & generate optimized autoloader
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy Built Frontend Assets from Stage 1
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Copy Configurations
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Generate 10-year SAN SSL Certificate if not already present
RUN mkdir -p /etc/nginx/ssl \
    && if [ ! -s /etc/nginx/ssl/server.crt ] || [ ! -s /etc/nginx/ssl/server.key ]; then \
        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/nginx/ssl/server.key \
        -out /etc/nginx/ssl/server.crt \
        -subj "/C=IN/ST=Tripura/L=Agartala/O=Office of the Accountant General (A&E) Tripura/OU=Fund Section/CN=gpffp.local" \
        -addext "subjectAltName=DNS:gpffp.local,DNS:gpf-final-payment.local,DNS:gpf_final_payment.local,DNS:gpf.tripura.local,DNS:localhost,IP:10.47.240.169,IP:127.0.0.1"; \
    fi \
    && chmod 600 /etc/nginx/ssl/server.key \
    && chmod 644 /etc/nginx/ssl/server.crt

# Set permissions and fix line endings
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP & HTTPS ports
EXPOSE 80 443

# Define Entrypoint and default command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
