# ==============================================================================
# GPF Final Payment Portal - Docker Rebuild & Live Update Script
# ==============================================================================
$ErrorActionPreference = "Stop"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ">>> GPF Final Payment Portal - Container Rebuild & Sync" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

# 1. Resolve configured direct port from .env
$port = "8082"
if (Test-Path ".env") {
    $envContent = Get-Content ".env"
    foreach ($line in $envContent) {
        if ($line -match "^PORT\s*=\s*(\d+)") {
            $port = $matches[1]
            break
        }
    }
}
Write-Host "Direct Host Port: $port | HTTPS Port: 8443" -ForegroundColor Gray

# 2. Rebuild multi-stage image and start standalone container
Write-Host "`nRebuilding multi-stage image and starting container..." -ForegroundColor Cyan
docker compose up -d --build

Write-Host "`n========================================================" -ForegroundColor Cyan
Write-Host ">>> Waiting for Container Startup & Health Verification" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

$maxRetries = 12
$retryCount = 0
$isHealthy = $false

while ($retryCount -lt $maxRetries) {
    $retryCount++
    Write-Host "Checking service health on port $port (Attempt $retryCount of $maxRetries)..." -ForegroundColor Gray
    try {
        $res = Invoke-WebRequest -Uri "http://localhost:$port/login" -UseBasicParsing -TimeoutSec 5
        if ($res.StatusCode -eq 200) {
            $isHealthy = $true
            break
        }
    } catch {
        try {
            $res = Invoke-WebRequest -Uri "http://127.0.0.1:$port/login" -UseBasicParsing -TimeoutSec 5
            if ($res.StatusCode -eq 200) {
                $isHealthy = $true
                break
            }
        } catch {
            # Waiting for PHP-FPM and Nginx startup
        }
    }
    Start-Sleep -Seconds 3
}

Write-Host "`nProcesses & Container Status:" -ForegroundColor Cyan
docker ps --filter "name=gpf_final_payment_app"

if ($isHealthy) {
    Write-Host @"
`n===============================================================================
[SUCCESS] Portal container is 100% ONLINE and serving requests!
===============================================================================
Access Options:
  1. Direct HTTP:  http://localhost:$port/login  or  http://10.47.240.169:$port/login
  2. Direct HTTPS: https://localhost:8443/login  or  https://10.47.240.169:8443/login
===============================================================================
"@ -ForegroundColor Green
} else {
    Write-Host "`n[NOTICE] Container is starting up. Please allow up to 15 seconds then refresh http://localhost:$port/login or http://gpffp.local." -ForegroundColor Yellow
}
