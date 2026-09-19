<?php

namespace App\Console\Commands;

use App\Models\Help;
use App\Notifications\HelpStatusNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindPendingConfirmationHelps extends Command
{
    protected $signature = 'helps:remind-pending-confirmation';
    protected $description = 'Kirim notifikasi pengingat setiap 1 jam kepada customer yang belum mengonfirmasi penyelesaian bantuan.';

    public function handle(): int
    {
        $this->info("Memeriksa pesanan yang menunggu konfirmasi customer...");

        $count = 0;
        $helps = Help::where('status', Help::STATUS_WAITING_CONFIRMATION)
            ->where('escrow_status', Help::ESCROW_STATUS_HELD)
            ->whereNotNull('confirmation_deadline_at')
            ->where('confirmation_deadline_at', '>', now())
            ->with(['user', 'mitra'])
            ->get();

        foreach ($helps as $help) {
            $customer = $help->user;
            if (!$customer) {
                continue;
            }

            $remainingMinutes = (int) max(0, now()->diffInMinutes($help->confirmation_deadline_at, false));
            $remainingHours   = (int) ceil($remainingMinutes / 60);
            $mitraName        = $help->mitra?->name ?? 'Rekan Jasa';

            try {
                $customer->notify(new HelpStatusNotification(
                    $help,
                    $help->status,
                    Help::STATUS_WAITING_CONFIRMATION,
                    $help->mitra
                ));
                $count++;
                $this->info("  [✓] Notifikasi pengingat dikirim ke {$customer->name} untuk Pesanan #{$help->id} (Sisa waktu: {$remainingHours} jam)");
            } catch (\Throwable $e) {
                Log::warning("[RemindPendingConfirmation] Gagal kirim notifikasi ke user #{$customer->id}: " . $e->getMessage());
            }
        }

        $this->info("Selesai: {$count} pengingat terkirim.");
        return 0;
    }
}
