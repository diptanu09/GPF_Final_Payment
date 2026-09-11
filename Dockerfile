# ==============================================================================
# Multi-Stage Dockerfile for GPF Final Payment Portal (Production)
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite + React 19 + Tailwind CSS)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci --silent || npm install --silent

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
    LD_LIBRARY_PATH=/usr/lib/oracle/current/client64/lib \
    ORACLE_HOME=/usr/lib/oracle/current/client64

# Install system dependencies & build tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    unzip \
    git \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libaio1 \
    libaio-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install Oracle Instant Client for OCI8 & PDO_OCI (Oracle 11g / 19c connection)
RUN mkdir -p /opt/oracle && cd /opt/oracle \
    && curl -o instantclient-basic.zip https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-basiclite-linux.x64-21.13.0.0.0dbru.zip \
    && curl -o instantclient-sdk.zip https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-sdk-linux.x64-21.13.0.0.0dbru.zip \
    && unzip instantclient-basic.zip \
    && unzip instantclient-sdk.zip \
    && rm -f instantclient-basic.zip instantclient-sdk.zip \
    && mv instantclient_21_13 /usr/lib/oracle/current \
    && echo /usr/lib/oracle/current > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig \
    || true

# Configure & Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        pcntl

# Install & Configure OCI8 if instantclient library exists
RUN if [ -d "/usr/lib/oracle/current" ]; then \
        echo 'instantclient,/usr/lib/oracle/current' | pecl install oci8-3.4.0 \
        && docker-php-ext-enable oci8; \
    fi

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Application Source Code
COPY . /var/www/html

# Copy Built Frontend Assets from Stage 1
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy Configurations
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set permissions
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP port
EXPOSE 80

# Define Entrypoint and default command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
