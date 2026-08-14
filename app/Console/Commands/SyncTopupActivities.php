<?php

namespace App\Console\Commands;

use App\Models\BalanceTransaction;
use App\Models\PartnerActivity;
use Illuminate\Console\Command;

class SyncTopupActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partner-activities:sync-topups
        {--dry-run : Hanya tampilkan rencana perubahan tanpa menulis ke database}
        {--limit=0 : Batasi jumlah aktivitas yang dibuat (0 = semua)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill PartnerActivity dari transaksi top up yang sudah completed.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $created = 0;

        $formatDescription = function (BalanceTransaction $tx): string {
            $amount = number_format((float) $tx->amount, 0, ',', '.');
            $order = $tx->order_id ? ('Order #' . $tx->order_id) : 'Order #?';

            return sprintf('Top up berhasil Rp %s (%s)', $amount, $order);
        };

        BalanceTransaction::query()
            ->where('type', 'topup')
            ->where('status', 'completed')
            ->orderBy('id')
            ->chunkById(200, function ($transactions) use (&$created, $dryRun, $limit, $formatDescription) {
                foreach ($transactions as $tx) {
                    if ($limit > 0 && $created >= $limit) {
                        return false; // stop chunking
                    }

                    $description = $formatDescription($tx);

                    $already = PartnerActivity::query()
                        ->where('user_id', $tx->user_id)
                        ->where('activity_type', 'balance_topup')
                        ->where('description', $description)
                        ->exists();

                    if ($already) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would create activity for transaction #{$tx->id} ({$description})");
                        $created++;
                        continue;
                    }

                    PartnerActivity::create([
                        'user_id' => $tx->user_id,
                        'activity_type' => 'balance_topup',
                        'description' => $description,
                        'ip_address' => request()?->ip() ?? 'system',
                        'user_agent' => 'topup-backfill',
                    ]);

                    $this->info("Created activity for transaction #{$tx->id}");
                    $created++;
                }
            });

        if ($dryRun) {
            $this->comment("Dry run selesai. {$created} aktivitas akan dibuat jika dijalankan tanpa --dry-run.");
        } else {
            $this->info("Berhasil membuat {$created} aktivitas top up.");
        }

        return self::SUCCESS;
    }
}

