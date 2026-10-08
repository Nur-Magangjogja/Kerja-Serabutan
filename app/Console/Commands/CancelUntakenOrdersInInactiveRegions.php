<?php

namespace App\Console\Commands;

use App\Services\RegionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelUntakenOrdersInInactiveRegions extends Command
{
    protected $signature = 'helps:cancel-inactive-regions';
    protected $description = 'Safety net: Batalkan dan refund pesanan yang belum diambil di seluruh wilayah nonaktif.';

    public function handle(RegionService $regionService): int
    {
        $this->info('Memulai pemindaian safety-net pesanan pada wilayah yang dinonaktifkan...');

        try {
            $count = $regionService->sweepUntakenOrdersInAllInactiveRegions();
            $this->info("Berhasil memproses pembatalan {$count} pesanan di wilayah nonaktif.");
            Log::info("[CancelUntakenOrdersInInactiveRegions] Safety net swept and cancelled {$count} untaken orders in inactive regions.");
        } catch (\Throwable $e) {
            $this->error("Gagal menjalankan safety net: " . $e->getMessage());
            Log::error("[CancelUntakenOrdersInInactiveRegions] Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
