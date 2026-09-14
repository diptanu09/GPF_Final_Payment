# GPF Final Payment Portal - Docker Deployment & Operations Guide

This guide describes how to build, run, deploy, and maintain the Docker container for the **GPF Final Payment Portal**.

---

## 1. Architecture Overview

The application runs inside a single, unified, high-performance production container:
- **Web Server**: Nginx (handling HTTP on Port 80 and HTTPS with TLS 1.2/1.3 on Port 443)
- **Application Engine**: PHP 8.3-FPM (`bcmath`, `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `gd`, `zip`, `intl`, `opcache`, `pcntl`, `oci8`)
- **Process Manager**: Supervisord managing both `nginx` and `php-fpm`
- **Frontend Assets**: Vite + React 19 + Tailwind CSS compiled via multi-stage Node 20 builder
- **SSL / TLS**: Automated 10-year SAN certificate supporting `gpffp.local` and `10.47.240.169`

---

## 2. Quick Start Commands

### Production Build & Launch
```powershell
# Build and start in detached mode:
docker compose up -d --build

# View real-time container logs:
docker compose logs -f

# Check container health status:
docker compose ps
```

### Stop / Restart Containers
```powershell
# Restart container:
docker compose restart

# Stop container:
docker compose down
```

---

## 3. Useful Operational Commands

### Run Artisan Commands Inside Container
```powershell
# Clear and optimize caches:
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache

# Run database migrations:
docker compose exec app php artisan migrate --force

# Open interactive tinker shell:
docker compose exec app php artisan tinker
```

### Check Logs Inside Container
```powershell
# Nginx access & error logs:
docker compose exec app tail -f /var/log/nginx/access.log
docker compose exec app tail -f /var/log/nginx/error.log

# Laravel application logs:
docker compose exec app tail -f /var/www/html/storage/logs/laravel.log
```

---

## 4. Client PC Setup & Green Padlock SSL

To access `https://gpffp.local` with a green secure padlock on any office PC on the network:

1. Right-click `setup_client_pc.bat` on the client PC and click **Run as administrator**.
2. It automatically adds:
   `10.47.240.169  gpffp.local` to `C:\Windows\System32\drivers\etc\hosts`
3. It installs `server.crt` into the Windows **Trusted Root Certification Authorities** store.
4. Open your browser to **`https://gpffp.local`**.
