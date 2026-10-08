@echo off
title Menghentikan Service Reverb
echo Menghentikan proses Reverb yang berjalan di latar belakang...

powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*artisan*reverb:start*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }"

echo Reverb berhasil dihentikan.
pause
