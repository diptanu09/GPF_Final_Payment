@echo off
TITLE GPF Final Payment Portal - Live Docker Logs
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\logs.ps1" %*
pause
