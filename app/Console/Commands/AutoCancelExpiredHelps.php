<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Help;
use App\Models\AppSetting;
use App\Services\HelpTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredHelps extends Command
{
    protected $signature = 'helps:auto-cancel';
    protected $description = 'Auto-cancel customer help requests that have exceeded the timeout waiting for a partner.';

    public function handle(HelpTransactionService $service)
    {
        $now = Carbon::now();
        $fallbackHours = AppSetting::getHelpAutoCancelHours();
        $fallbackCutoff = $now->copy()->subHours($fallbackHours);

        $expiredHelps = Help::whereNull('mitra_id')
            ->where('status', Help::STATUS_MENUNGGU_MITRA)
            ->where(function ($query) use ($now, $fallbackCutoff) {
                // 1. Batas waktu pencarian yang ditentukan oleh customer (expires_at)
                $query->where(function ($q) use ($now) {
                    $q->whereNotNull('expires_at')
                      ->where('expires_at', '<=', $now);
                })
                // 2. Fallback jika expires_at kosong (menggunakan batas auto-cancel sistem)
                ->orWhere(function ($q) use ($fallbackCutoff) {
                    $q->whereNull('expires_at')
                      ->where('created_at', '<=', $fallbackCutoff);
                });
            })
            ->get();

        $this->info("Found {$expiredHelps->count()} expired help requests to auto-cancel.");

        $count = 0;
        foreach ($expiredHelps as $help) {
            try {
                if ($help->expires_at && Carbon::parse($help->expires_at)->isPast()) {
                    $reason = 'Batas waktu pencarian Rekan Jasa yang ditentukan telah berakhir';
                } else {
                    $reason = "Tidak ada Rekan Jasa yang mengambil bantuan dalam batas waktu {$fallbackHours} jam";
                }

                $service->autoCancelExpiredHelp($help, $reason);
                $this->info("Auto-cancelled help #{$help->id} ({$help->order_id})");
                $count++;
            } catch (\Throwable $e) {
                Log::error("Failed to auto-cancel help #{$help->id}: " . $e->getMessage());
                $this->error("Failed to auto-cancel help #{$help->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully processed {$count} auto-cancellations.");
        return 0;
    }
}
