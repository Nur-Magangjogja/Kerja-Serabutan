<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('helps:auto-confirm')->everyMinute();
Schedule::command('partners:clean-stale-states --ttl=60')->everyMinute();
Schedule::command('city:evaluate-capacities')->hourly();

