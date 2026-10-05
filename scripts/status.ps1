# ==============================================================================
# GPF Final Payment Portal - Container Status & Diagnostics
# ==============================================================================
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ">>> Container Process Status" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan
docker ps --filter "name=gpf_final_payment_app"

Write-Host "`n========================================================" -ForegroundColor Cyan
Write-Host ">>> Container Resource Usage (CPU / Memory / Network)" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan
docker stats gpf_final_payment_app --no-stream

Write-Host "`n========================================================" -ForegroundColor Cyan
Write-Host ">>> Application Health Diagnostic (/api/v1/system/status)" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan
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

try {
    $res = Invoke-RestMethod -Uri "http://localhost:$port/api/v1/system/status" -Headers @{ "X-Deploy-Token" = "GPF_DEPLOY_SECRET_TOKEN_2026"; "Host" = "gpffp.local" } -TimeoutSec 5
    $res | Format-List
} catch {
    Write-Host "Health check response on port $port: $_" -ForegroundColor Yellow
}
