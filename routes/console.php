<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('helps:auto-confirm')->everyMinute();
Schedule::command('helps:auto-cancel')->everyFiveMinutes();
Schedule::command('helps:remind-pending-confirmation')->hourly();
Schedule::command('helps:send-departure-reminders')->everyMinute();
Schedule::command('partners:clean-stale-states --ttl=60')->everyMinute();
Schedule::command('city:evaluate-capacities')->hourly();
Schedule::command('balances:sync-check --threshold=1000000')->everyFiveMinutes();

// Pembersihan otomatis akun unverified (10 menit) dan akun inactive yang tidak menyelesaikan form (1x24 jam)
Schedule::call(function () {
    \App\Models\User::purgeExpiredUnverified();
    \App\Models\User::purgeExpiredInactive();
})->hourly()->name('purge-expired-accounts');

