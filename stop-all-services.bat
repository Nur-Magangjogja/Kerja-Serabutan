@echo off
title Menghentikan Semua Layanan Latar Belakang SayaBantu
echo Menghentikan Reverb, Scheduler, dan Queue Worker...

powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*artisan*reverb:start*' -or $_.CommandLine -like '*artisan*schedule:work*' -or $_.CommandLine -like '*artisan*queue:work*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }"

echo Semua layanan latar belakang berhasil dihentikan.
pause
