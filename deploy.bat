@echo off
TITLE GPF Final Payment Portal - Deploy to Remote Docker (10.47.240.169)
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\deploy.ps1" %*
pause
