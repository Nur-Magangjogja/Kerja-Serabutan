<?php

namespace App\Console\Commands;

use App\Services\PartnerOnlineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanStalePartnerStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partners:clean-stale-states {--ttl=60 : Heartbeat TTL in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demote searching partners with stale heartbeats to online standby status.';

    /**
     * Execute the console command.
     */
    public function handle(PartnerOnlineService $service)
    {
        $ttl = (int) $this->option('ttl');
        $this->info("Scanning for stale searching partners (TTL: {$ttl}s)...");

        $demoted = $service->cleanupStaleStates($ttl);

        if ($demoted > 0) {
            $this->warn("Demoted {$demoted} partner(s) from 'searching' to 'online'.");
        } else {
            $this->info("No stale partner states found.");
        }

        // Clean & advance any expired help dispatches
        $expiredDispatches = \App\Models\HelpDispatch::where('status', \App\Models\HelpDispatch::STATUS_OFFERED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredDispatches->isNotEmpty()) {
            $matchingService = app(\App\Services\HelpMatchingService::class);
            foreach ($expiredDispatches as $d) {
                $matchingService->handleExpiry($d->id, true);
            }
            $this->warn("Processed and advanced {$expiredDispatches->count()} expired offer dispatch(es).");
        }

        return Command::SUCCESS;
    }
}
