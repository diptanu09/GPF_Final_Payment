@echo off
TITLE GPF Final Payment Portal - Docker Status & Diagnostics
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\status.ps1" %*
pause
