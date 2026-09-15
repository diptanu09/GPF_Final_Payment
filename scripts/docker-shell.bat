@echo off
TITLE GPF Final Payment Portal - Container Terminal Shell
cd /d "%~dp0"
docker exec -it gpf_final_payment_app bash
pause
