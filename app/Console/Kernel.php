<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\RecalculateUserBalances::class,
        // \App\Console\Commands\MidtransRecheck::class,
        \App\Console\Commands\BalanceSyncCheck::class,
        \App\Console\Commands\AutoConfirmHelps::class,
        \App\Console\Commands\AutoCancelExpiredHelps::class,
        \App\Console\Commands\SyncTopupActivities::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Recheck pending Midtrans topups (Nonaktif / Disabled)
        // $schedule->command('midtrans:recheck --all')->everyFiveMinutes();

        // Periodic balance synchronization check (auto-fix small deltas)
        $schedule->command('balances:sync-check --threshold=1000000')->everyFiveMinutes();

        // $schedule->command('userbalances:recalculate')->daily();

        // Auto-confirm helps waiting for customer confirmation after 24 hours
        $schedule->command('helps:auto-confirm')->hourly();

        // Auto-cancel helps that have not been taken within the deadline and refund customer escrow
        $schedule->command('helps:auto-cancel')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
