# ==============================================================================
# GPF Final Payment Portal - Database Reset & Identity Restart
# ==============================================================================
$ErrorActionPreference = "Stop"

Write-Host "========================================================" -ForegroundColor Red
Write-Host "WARNING: DATABASE DATA RESET & IDENTITY SEQUENCE RESTART" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Red
Write-Host "This will truncate all operational tables (dockets, calculations," -ForegroundColor Gray
Write-Host "nominees, authorities, workflow history, signatures) and re-create" -ForegroundColor Gray
Write-Host "strictly the System Administrator user account." -ForegroundColor Gray
Write-Host "VLC Master replicas and interest rate slabs will be preserved." -ForegroundColor Gray
Write-Host "========================================================`n" -ForegroundColor Red

$confirm = Read-Host "Type 'YES' to proceed with resetting the database"
if ($confirm -ne "YES") {
    Write-Host "Database reset cancelled by user." -ForegroundColor Yellow
    exit 0
}

Write-Host "`nExecuting database reset script inside container..." -ForegroundColor Cyan
Get-Content "$PSScriptRoot\reset_db.php" | docker exec -i gpf_final_payment_app php artisan tinker

Write-Host "`nVerifying reset state..." -ForegroundColor Cyan
Get-Content "$PSScriptRoot\verify_reset.php" | docker exec -i gpf_final_payment_app php artisan tinker

Write-Host "`n[SUCCESS] Database reset and identity restart complete!" -ForegroundColor Green
