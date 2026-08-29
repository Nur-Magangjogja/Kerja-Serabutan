<?php

namespace App\Jobs;

use App\Services\HelpMatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleOfferExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $dispatchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $dispatchId)
    {
        $this->dispatchId = $dispatchId;
    }

    /**
     * Execute the job.
     */
    public function handle(HelpMatchingService $matchingService): void
    {
        Log::info("[HandleOfferExpiry] Processing expiry check for Dispatch #{$this->dispatchId}");
        $matchingService->handleExpiry($this->dispatchId);
    }
}
