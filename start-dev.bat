@echo off
cd /d "%~dp0"
title SayaBantu Development Launcher

echo [1/5] Menjalankan Service Reverb (Background)...
start wscript "%~dp0start-reverb-silent.vbs"

echo [2/5] Menjalankan Laravel Scheduler (Background)...
start wscript "%~dp0start-scheduler-silent.vbs"

echo [3/5] Menjalankan Laravel Queue Worker (Background)...
start wscript "%~dp0start-queue-silent.vbs"

echo [4/5] Menjalankan Laravel Server (Jendela Baru)...
start "SayaBantu Server" cmd /k "cd /d %~dp0 && php artisan serve"

echo [5/5] Menjalankan Vite Asset Bundler...
call npm run dev
