@echo off
title Menghentikan Service Laravel Scheduler
echo Menghentikan proses Laravel Scheduler yang berjalan di latar belakang...

powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*artisan*schedule:work*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }"

echo Laravel Scheduler berhasil dihentikan.
pause
