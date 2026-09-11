@echo off
:: ==============================================================================
:: GPF Final Payment Portal - 1-Click Client PC Setup
:: ==============================================================================
:: Run this batch file as Administrator on any office PC to:
:: 1. Add gpffp.local domain to hosts file
:: 2. Install SSL certificate for Green Padlock
:: ==============================================================================

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Please right-click this file and select "Run as administrator".
    pause
    exit /b 1
)

set SERVER_IP=10.47.240.169
set DOMAIN=gpffp.local

echo ====================================================================
echo   Setting up GPF Final Payment Portal Client
echo ====================================================================

:: 1. Add hosts entry
findstr /i "%DOMAIN%" "%WINDIR%\System32\drivers\etc\hosts" >nul
if %errorLevel% neq 0 (
    echo %SERVER_IP%  %DOMAIN% >> "%WINDIR%\System32\drivers\etc\hosts"
    echo [OK] Added %DOMAIN% -^> %SERVER_IP% to hosts file.
) else (
    echo [OK] %DOMAIN% already exists in hosts file.
)

:: 2. Install Certificate if present in current folder
if exist "%~dp0server.crt" (
    certutil -addstore -f "ROOT" "%~dp0server.crt" >nul 2>&1
    echo [OK] SSL Certificate installed into Trusted Root Authorities.
) else (
    echo [INFO] server.crt not found in this folder, skipping certificate install.
)

echo ====================================================================
echo   SETUP COMPLETE!
echo   You can now open: https://%DOMAIN% or http://%SERVER_IP%
echo ====================================================================
pause
