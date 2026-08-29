<?php

namespace App\Console\Commands;

use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\Rating;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MigrateEscrowStatusBackfill extends Command
{
    protected $signature = 'migrate:escrow-status-backfill {--dry-run : Only simulate and report anomalies without updating DB}';
    protected $description = 'Audit and backfill legacy orders and balance transactions with escrow, payment, dispatch, and idempotency status.';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  AUDIT & BACKFILL: Escrow Status & Idempotency Keys" . ($isDryRun ? " (DRY-RUN)" : ""));
        $this->info("═══════════════════════════════════════════════════════════");

        $anomalies = [];
        $stats = [
            'total_helps'         => 0,
            'released'            => 0,
            'refunded'            => 0,
            'held'                => 0,
            'uninitialized'       => 0,
            'anomalies'           => 0,
            'total_txs_updated'   => 0,
            'duplicate_keys_skip' => 0,
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 1. Backfill Balance Transactions
        // ─────────────────────────────────────────────────────────────────────
        $this->info("Step 1: Auditing & backfilling BalanceTransaction records...");

        $transactions = BalanceTransaction::orderBy('id')->get();
        $seenKeys = [];

        foreach ($transactions as $tx) {
            $key = null;
            $refType = null;
            $direction = null;

            // Tentukan direction
            if (in_array($tx->type, ['topup', 'earning', 'refund'], true)) {
                $direction = 'credit';
            } elseif (in_array($tx->type, ['withdraw', 'deduction', 'penalty', 'escrow_lock', 'pg_fee_topup', 'pg_fee_withdraw'], true)) {
                $direction = 'debit';
            }

            // Generate deterministic idempotency_key jika berelasi dengan help
            if (!empty($tx->reference_id) && is_numeric($tx->reference_id)) {
                $refType = 'help';
                $helpId = (int) $tx->reference_id;

                if ($tx->type === 'escrow_lock') {
                    $key = "help:{$helpId}:escrow_lock:" . ($tx->user_id ?? '0');
                } elseif ($tx->type === 'earning') {
                    $key = "help:{$helpId}:earning:" . ($tx->user_id ?? '0');
                } elseif ($tx->type === 'platform_fee') {
                    $key = "help:{$helpId}:platform_fee";
                } elseif ($tx->type === 'refund') {
                    $key = "help:{$helpId}:refund:" . ($tx->user_id ?? '0');
                } elseif ($tx->type === 'penalty') {
                    $key = "help:{$helpId}:penalty:" . ($tx->user_id ?? '0');
                }
            } elseif ($tx->type === 'topup' && !empty($tx->order_id)) {
                $refType = 'topup_request';
                $key = "topup:{$tx->order_id}";
            }

            if ($key !== null) {
                if (isset($seenKeys[$key])) {
                    $stats['duplicate_keys_skip']++;
                    $anomalies[] = [
                        'type'        => 'DUPLICATE_TX_KEY',
                        'entity_id'   => $tx->id,
                        'description' => "Duplicate key '{$key}' found on transaction #{$tx->id}, duplicate with #{$seenKeys[$key]}",
                    ];
                    // Append suffix agar tidak crash saat UNIQUE constraint diaktifkan
                    $key = $key . ":legacy_dup_" . $tx->id;
                } else {
                    $seenKeys[$key] = $tx->id;
                }
            }

            if (!$isDryRun) {
                $updateData = [];
                if ($key && empty($tx->idempotency_key)) {
                    $updateData['idempotency_key'] = $key;
                }
                if ($refType && empty($tx->reference_type)) {
                    $updateData['reference_type'] = $refType;
                }
                if ($direction && empty($tx->direction)) {
                    $updateData['direction'] = $direction;
                }

                if (!empty($updateData)) {
                    $tx->update($updateData);
                    $stats['total_txs_updated']++;
                }
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. Backfill Helps (Order status dimensions)
        // ─────────────────────────────────────────────────────────────────────
        $this->info("Step 2: Auditing & backfilling Help records...");

        $helps = Help::orderBy('id')->get();
        $stats['total_helps'] = $helps->count();

        foreach ($helps as $help) {
            $hasEscrowLock = BalanceTransaction::where('reference_id', $help->id)
                ->where('type', 'escrow_lock')
                ->exists();

            $hasEarning = BalanceTransaction::where('reference_id', $help->id)
                ->whereIn('type', ['earning', 'topup'])
                ->exists();

            $hasRefund = BalanceTransaction::where('reference_id', $help->id)
                ->where('type', 'refund')
                ->exists();

            $hasRating = Rating::where('help_id', $help->id)->exists();

            $escrowStatus   = $help->escrow_status ?: 'uninitialized';
            $paymentStatus  = $help->payment_status ?: 'unpaid';
            $ratingStatus   = $help->rating_status ?: 'pending';
            $dispatchMode   = $help->dispatch_mode ?: 'seeking';
            $deadline       = $help->confirmation_deadline_at;

            $status = $help->status;

            if (in_array($status, [Help::STATUS_SELESAI, 'completed'], true)) {
                $escrowStatus   = 'released';
                $paymentStatus  = 'paid';
                $ratingStatus   = $hasRating ? 'rated' : 'pending';
                $dispatchMode   = 'closed';
                $stats['released']++;
            } elseif (in_array($status, [Help::STATUS_DIBATALKAN, 'cancelled'], true)) {
                $dispatchMode = 'closed';
                if ($hasRefund) {
                    $escrowStatus  = 'refunded';
                    $paymentStatus = 'refunded';
                    $stats['refunded']++;
                } elseif ($hasEscrowLock) {
                    $escrowStatus  = 'disputed_freeze';
                    $paymentStatus = 'paid';
                    $stats['anomalies']++;
                    $anomalies[] = [
                        'type'        => 'CANCELLED_WITHOUT_REFUND_LEDGER',
                        'entity_id'   => $help->id,
                        'description' => "Help #{$help->id} is cancelled with escrow_lock but has no refund ledger record.",
                    ];
                } else {
                    $escrowStatus  = 'uninitialized';
                    $paymentStatus = 'unpaid';
                    $stats['uninitialized']++;
                }
            } elseif ($status === Help::STATUS_MENUNGGU_MITRA) {
                $dispatchMode = 'pool';
                if ($hasEscrowLock) {
                    $escrowStatus  = 'held';
                    $paymentStatus = 'paid';
                    $stats['held']++;
                } else {
                    $escrowStatus  = 'uninitialized';
                    $paymentStatus = 'unpaid';
                    $stats['uninitialized']++;
                }
            } elseif (in_array($status, [
                Help::STATUS_TAKEN,
                'memperoleh_mitra',
                Help::STATUS_PARTNER_ON_THE_WAY,
                Help::STATUS_PARTNER_ARRIVED,
                Help::STATUS_IN_PROGRESS,
                'sedang_diproses',
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
            ], true)) {
                $dispatchMode  = 'assigned';
                $escrowStatus  = 'held';
                $paymentStatus = 'paid';
                $stats['held']++;
            } elseif ($status === Help::STATUS_WAITING_CONFIRMATION) {
                $dispatchMode  = 'assigned';
                $escrowStatus  = 'held';
                $paymentStatus = 'paid';
                $stats['held']++;
                if (!$deadline) {
                    $completedAt = $help->service_completed_at ?? $help->updated_at ?? now();
                    $deadline    = \Illuminate\Support\Carbon::parse($completedAt)->addHours(24);
                }
            } else {
                $stats['uninitialized']++;
            }

            if (!$isDryRun) {
                $help->update([
                    'escrow_status'            => $escrowStatus,
                    'payment_status'           => $paymentStatus,
                    'rating_status'            => $ratingStatus,
                    'dispatch_mode'            => $dispatchMode,
                    'confirmation_deadline_at' => $deadline,
                ]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. Export Anomalies Report
        // ─────────────────────────────────────────────────────────────────────
        $csvPath = storage_path('logs/migration_escrow_anomalies.csv');
        if (!empty($anomalies)) {
            $csvContent = "Type,Entity_ID,Description\n";
            foreach ($anomalies as $a) {
                $csvContent .= sprintf("\"%s\",\"%s\",\"%s\"\n", $a['type'], $a['entity_id'], str_replace('"', '""', $a['description']));
            }
            File::ensureDirectoryExists(dirname($csvPath));
            File::put($csvPath, $csvContent);
            $this->warn("⚠️  Saved " . count($anomalies) . " anomalies to: {$csvPath}");
        }

        // ─────────────────────────────────────────────────────────────────────
        // 4. Print Summary
        // ─────────────────────────────────────────────────────────────────────
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  MIGRATION & AUDIT SUMMARY");
        $this->info("═══════════════════════════════════════════════════════════");
        $this->line("  Total Helps Processed    : " . $stats['total_helps']);
        $this->line("  ├── Released (Selesai)   : " . $stats['released']);
        $this->line("  ├── Refunded (Batal)     : " . $stats['refunded']);
        $this->line("  ├── Held / Active        : " . $stats['held']);
        $this->line("  └── Uninitialized        : " . $stats['uninitialized']);
        $this->line("  ─────────────────────────────────────────────────────────");
        $this->line("  Balance Txs Updated      : " . $stats['total_txs_updated']);
        $this->line("  Duplicate Keys Handled   : " . $stats['duplicate_keys_skip']);
        $this->line("  ⚠️  Anomalies Flagged     : " . count($anomalies));
        $this->info("═══════════════════════════════════════════════════════════");

        return 0;
    }
}
