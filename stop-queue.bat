@echo off
title Menghentikan Service Laravel Queue Worker
echo Menghentikan proses Laravel Queue Worker yang berjalan di latar belakang...

powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*artisan*queue:work*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }"

echo Laravel Queue Worker berhasil dihentikan.
pause
