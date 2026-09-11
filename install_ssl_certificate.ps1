# Install SSL Certificate into Windows Trusted Root Certification Authorities
$certPath = Join-Path $PSScriptRoot "server.crt"

if (-not (Test-Path $certPath)) {
    Write-Host "✗ Error: server.crt not found in current directory." -ForegroundColor Red
    Exit
}

Write-Host "Installing {$certPath} into Windows Trusted Root Certificate Store..." -ForegroundColor Cyan

try {
    $cert = New-Object System.Security.Cryptography.X509Certificates.X509Certificate2 $certPath
    $store = New-Object System.Security.Cryptography.X509Certificates.X509Store "Root", "LocalMachine"
    $store.Open("ReadWrite")
    $store.Add($cert)
    $store.Close()
    Write-Host "✓ SUCCESS: SSL Certificate installed into Trusted Root Authorities!" -ForegroundColor Green
    Write-Host "✓ You now have a Green Secure Padlock on https://gpffp.local and https://10.47.240.169" -ForegroundColor Green
} catch {
    Write-Host "✗ Error installing certificate: $_" -ForegroundColor Red
    Write-Host "Please make sure you run PowerShell as Administrator." -ForegroundColor Yellow
}
