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
     * Note: All scheduled commands are defined in routes/console.php.
     */
    protected function schedule(Schedule $schedule)
    {
        // Scheduled commands are registered in routes/console.php to prevent duplication.
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
