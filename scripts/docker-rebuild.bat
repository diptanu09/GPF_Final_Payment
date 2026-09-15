@echo off
TITLE GPF Final Payment Portal - Rebuild Docker Container
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\rebuild.ps1" %*
pause
