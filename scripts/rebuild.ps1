# ==============================================================================
# GPF Final Payment Portal - Docker Rebuild & Live Update Script
# ==============================================================================
$ErrorActionPreference = "Stop"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ">>> Rebuilding Docker Image & Restarting Live Container" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

# Rebuild multi-stage image and recreate container
docker compose up -d --build

Write-Host "`n========================================================" -ForegroundColor Cyan
Write-Host ">>> Waiting for Container Startup & Health Verification" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

$maxRetries = 10
$retryCount = 0
$isHealthy = $false

while ($retryCount -lt $maxRetries) {
    $retryCount++
    Write-Host "Checking service health (Attempt $retryCount of $maxRetries)..." -ForegroundColor Gray
    try {
        $res = Invoke-WebRequest -Uri "http://localhost/login" -UseBasicParsing -TimeoutSec 5
        if ($res.StatusCode -eq 200) {
            $isHealthy = $true
            break
        }
    } catch {
        # Waiting for Nginx / PHP-FPM to complete startup
    }
    Start-Sleep -Seconds 3
}

Write-Host "`nProcesses & Container Status:" -ForegroundColor Cyan
docker ps --filter "name=gpf_final_payment_app"

if ($isHealthy) {
    Write-Host "`n[SUCCESS] Portal container is 100% ONLINE and serving requests on http://localhost/login (HTTP 200 OK)" -ForegroundColor Green
} else {
    Write-Host "`n[NOTICE] Container is starting up. Please allow up to 15 seconds then refresh http://localhost/login." -ForegroundColor Yellow
}
