<?php

namespace App\Console\Commands;

use App\Models\Help;
use App\Notifications\HelpStatusNotification;
use App\Services\HelpNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledDepartureRemindersCommand extends Command
{
    protected $signature = 'helps:send-departure-reminders';
    protected $description = 'Kirim notifikasi pengingat keberangkatan otomatis kepada mitra saat jendela keberangkatan pesanan terjadwal terbuka.';

    public function handle(HelpNotificationService $notificationService): int
    {
        $this->info("Memeriksa pesanan terjadwal untuk pengingat keberangkatan...");

        $now = now();
        $helps = Help::where('status', Help::STATUS_TAKEN)
            ->where('order_mode', Help::ORDER_MODE_SCHEDULED)
            ->whereNotNull('mitra_id')
            ->whereNull('departure_reminder_sent_at')
            ->with(['user', 'mitra'])
            ->get();

        $count = 0;

        foreach ($helps as $help) {
            // Periksa apakah waktu sekarang sudah masuk ke jendela keberangkatan
            if (!$help->canPartnerStartDeparture()) {
                continue;
            }

            $mitra = $help->mitra;
            if (!$mitra) {
                continue;
            }

            $targetTime = $help->getScheduledTargetTime()?->format('H:i') ?? '-';

            try {
                // Update reminder timestamp agar tidak dikirim ulang
                $help->update([
                    'departure_reminder_sent_at' => $now,
                ]);

                // 1. Kirim notifikasi ke Mitra
                $mitra->notify(new HelpStatusNotification(
                    $help,
                    Help::STATUS_TAKEN,
                    'scheduled_departure_due',
                    $help->user
                ));

                // 2. Catat audit activity log
                $notificationService->logActivity(
                    $mitra->id,
                    $help->id,
                    'scheduled_departure_reminder',
                    "Pengingat keberangkatan tugas terjadwal #{$help->id} (Target Pkl {$targetTime}) terkirim ke Mitra {$mitra->name}"
                );

                $count++;
                $this->info("  [✓] Pengingat keberangkatan dikirim ke Mitra {$mitra->name} untuk Pesanan #{$help->id} (Target: {$targetTime})");
            } catch (\Throwable $e) {
                Log::warning("[SendScheduledDepartureReminders] Gagal kirim reminder untuk Help #{$help->id}: " . $e->getMessage());
            }
        }

        $this->info("Selesai: {$count} pengingat keberangkatan terkirim.");
        return 0;
    }
}
