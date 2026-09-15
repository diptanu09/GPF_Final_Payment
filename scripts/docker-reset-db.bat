@echo off
TITLE GPF Final Payment Portal - Reset Database Data
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\reset-db.ps1" %*
pause
