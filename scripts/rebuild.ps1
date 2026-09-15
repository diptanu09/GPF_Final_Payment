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
Write-Host ">>> Verifying Container Health & Status" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

Start-Sleep -Seconds 3

docker ps --filter "name=gpf_final_payment_app"

try {
    $res = Invoke-WebRequest -Uri "http://localhost/login" -UseBasicParsing -TimeoutSec 10
    if ($res.StatusCode -eq 200) {
        Write-Host "`n[SUCCESS] Container is ONLINE and serving requests on http://localhost/login (HTTP 200)" -ForegroundColor Green
    }
} catch {
    Write-Host "`n[WARNING] Health check failed or container still starting: $_" -ForegroundColor Red
}
