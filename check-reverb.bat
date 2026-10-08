@echo off
title Cek Status Laravel Reverb
echo Memeriksa status service Reverb (Port 8080)...
echo.

powershell -Command "$conn = Get-NetTCPConnection -LocalPort 8080 -ErrorAction SilentlyContinue; if ($conn) { Write-Host 'STATUS: Reverb AKTIF dan mendengarkan di Port 8080 (PID: ' $conn.OwningProcess ')' -ForegroundColor Green } else { Write-Host 'STATUS: Reverb TIDAK AKTIF' -ForegroundColor Red }"

echo.
pause
