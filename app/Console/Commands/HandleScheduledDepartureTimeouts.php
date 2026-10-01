<?php

namespace App\Console\Commands;

use App\Services\ScheduledDepartureTimeoutService;
use Illuminate\Console\Command;

class HandleScheduledDepartureTimeouts extends Command
{
    protected $signature = 'helps:handle-scheduled-timeouts';
    protected $description = 'Handle overdue scheduled orders whose departure deadline has passed.';

    public function handle(ScheduledDepartureTimeoutService $timeoutService): int
    {
        $this->info("Memeriksa pesanan terjadwal yang melewati batas waktu keberangkatan...");
        $handledCount = $timeoutService->sweepScheduledTimeouts();
        $this->info("Selesai memproses {$handledCount} pesanan terjadwal yang terlambat.");
        return self::SUCCESS;
    }
}
