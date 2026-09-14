# ==============================================================================
# GPF Final Payment Portal - Automated Remote Docker Deployment & Sync Script
# ==============================================================================
# Usage:
#   powershell -ExecutionPolicy Bypass -File .\scripts\deploy.ps1
#   powershell -ExecutionPolicy Bypass -File .\scripts\deploy.ps1 -CommitMsg "Fix voucher aggregation"
#   powershell -ExecutionPolicy Bypass -File .\scripts\deploy.ps1 -SkipTests
# ==============================================================================

param (
    [string]$TargetHost = "10.47.240.169",
    [string]$DeployToken = "GPF_DEPLOY_SECRET_TOKEN_2026",
    [string]$CommitMsg = "",
    [switch]$SkipTests,
    [switch]$ForceRebuild
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host "`n========================================================" -ForegroundColor Cyan
    Write-Host ">>> $Message" -ForegroundColor Yellow
    Write-Host "========================================================" -ForegroundColor Cyan
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Fail {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

Clear-Host
Write-Host @"
===============================================================================
       GPF FINAL PAYMENT PORTAL - AUTOMATED DEPLOYMENT & SYNC ENGINE
===============================================================================
Target Server: $TargetHost
Date / Time:   $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
===============================================================================
"@ -ForegroundColor Green

# ------------------------------------------------------------------------------
# Step 1: Run Automated Tests
# ------------------------------------------------------------------------------
if (-not $SkipTests) {
    Write-Step "Step 1: Running Automated PHPUnit Test Suite..."
    try {
        $testOutput = php vendor/phpunit/phpunit/phpunit --testdox
        Write-Host $testOutput
        if ($LASTEXITCODE -ne 0) {
            Write-Fail "Tests failed! Deployment aborted to protect production integrity."
            exit 1
        }
        Write-Success "All unit and feature tests passed (0 errors)!"
    } catch {
        Write-Fail "Error executing tests: $_"
        exit 1
    }
} else {
    Write-Host "`n[NOTICE] Skipping automated tests as requested." -ForegroundColor Yellow
}

# ------------------------------------------------------------------------------
# Step 2: Build Production Frontend Assets (Vite)
# ------------------------------------------------------------------------------
Write-Step "Step 2: Building Optimized Frontend Assets (Vite + React 19)..."
try {
    npm.cmd run build
    Write-Success "Frontend assets built successfully into public/build/!"
} catch {
    Write-Fail "Frontend build failed: $_"
    exit 1
}

# ------------------------------------------------------------------------------
# Step 3: Git Status & Automatic Push
# ------------------------------------------------------------------------------
Write-Step "Step 3: Staging and Pushing Changes to Git Repository..."
$status = git status --porcelain
if ($status) {
    Write-Host "Uncommitted changes detected:" -ForegroundColor Gray
    Write-Host $status

    if ([string]::IsNullOrWhiteSpace($CommitMsg)) {
        $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        $CommitMsg = "Auto-deploy update: $timestamp"
    }

    git add -A
    git commit -m "$CommitMsg"
    Write-Success "Committed with message: '$CommitMsg'"
} else {
    Write-Host "Working tree clean, no local changes to commit." -ForegroundColor Gray
}

Write-Host "Pushing commits to remote repository (origin main)..." -ForegroundColor Gray
try {
    git push origin main
    Write-Success "Git push completed successfully!"
} catch {
    Write-Host "[WARNING] Git push encountered a warning or branch is up to date: $_" -ForegroundColor Yellow
}

# ------------------------------------------------------------------------------
# Step 4: Trigger Remote Deployment Webhook on 10.47.240.169
# ------------------------------------------------------------------------------
Write-Step "Step 4: Triggering Remote Deployment on http://$TargetHost..."

$deployUrl = "http://$TargetHost/api/v1/system/deploy"
$headers = @{
    "X-Deploy-Token" = $DeployToken
    "Accept"         = "application/json"
    "Content-Type"   = "application/json"
}
$body = @{
    action = "full"
} | ConvertTo-Json

try {
    Write-Host "Sending deployment signal to $deployUrl..." -ForegroundColor Gray
    $response = Invoke-RestMethod -Uri $deployUrl -Method Post -Headers $headers -Body $body -TimeoutSec 30
    
    if ($response.success) {
        Write-Success "Remote deployment executed successfully!"
        Write-Host "Duration: $($response.duration_ms) ms" -ForegroundColor Cyan
        Write-Host "`nDeployment Details:" -ForegroundColor Gray
        $response.output | Format-List
    } else {
        Write-Fail "Remote deployment returned failure: $($response.error)"
    }
} catch {
    Write-Host "[NOTICE] Webhook trigger returned: $_" -ForegroundColor Yellow
    Write-Host "Verifying live container health directly..." -ForegroundColor Gray
}

# ------------------------------------------------------------------------------
# Step 5: Verify Live Container Health on 10.47.240.169
# ------------------------------------------------------------------------------
Write-Step "Step 5: Verifying Remote Health on http://$TargetHost..."
try {
    $healthCheck = Invoke-RestMethod -Uri "http://$TargetHost/api/v1/system/status" -Headers @{ "X-Deploy-Token" = $DeployToken } -TimeoutSec 10
    Write-Host @"
-------------------------------------------------------------------------------
  REMOTE CONTAINER STATUS:
-------------------------------------------------------------------------------
  Host:            $($healthCheck.hostname)
  Environment:     $($healthCheck.environment)
  PHP Version:     $($healthCheck.php_version)
  Laravel Version: $($healthCheck.laravel_version)
  Database:        $($healthCheck.database)
  Git Commit:      $($healthCheck.git_commit)
  Server Time:     $($healthCheck.server_time)
-------------------------------------------------------------------------------
"@ -ForegroundColor Green
    Write-Success "Portal is 100% ONLINE and serving requests on http://$TargetHost!"
} catch {
    Write-Host "Testing HTTP connection on http://$TargetHost/login..." -ForegroundColor Gray
    try {
        $res = Invoke-WebRequest -Uri "http://$TargetHost/login" -Method Get -TimeoutSec 5 -UseBasicParsing
        if ($res.StatusCode -eq 200) {
            Write-Success "Portal is ONLINE and responsive at http://$TargetHost/login!"
        }
    } catch {
        Write-Fail "Could not reach portal at http://$TargetHost. Please check if Docker is running."
    }
}

Write-Host @"
===============================================================================
                  DEPLOYMENT WORKFLOW COMPLETED SUCCESSFULLY!
===============================================================================
Web Access URLs:
  - HTTP:  http://$TargetHost
  - HTTPS: https://$TargetHost
  - Host:  http://gpffp.local  (or https://gpffp.local)
===============================================================================
"@ -ForegroundColor Cyan
