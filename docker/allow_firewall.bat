@echo off
TITLE Enable Windows Firewall for GPF Final Payment Portal
echo ===============================================================================
echo   Enabling Windows Firewall Inbound Rules for Port 80 (HTTP) and 443 (HTTPS)
echo ===============================================================================
echo.

netsh advfirewall firewall add rule name="GPF Final Payment Portal (HTTP 80)" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="GPF Final Payment Portal (HTTPS 443)" dir=in action=allow protocol=TCP localport=443

echo.
echo ===============================================================================
echo   Firewall rules added successfully! Other client PCs can now access:
echo   - http://10.47.240.169
echo   - https://10.47.240.169
echo   - http://gpffp.local (with hosts entry)
echo ===============================================================================
pause
