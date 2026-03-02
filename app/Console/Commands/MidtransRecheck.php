<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Midtrans\Config;
use Midtrans\Transaction;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\UserBalance;

class MidtransRecheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:recheck {order_id? : Optional order id to recheck} {--user= : Optional user id to limit or fallback} {--all : Scan all pending topup transactions in batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recheck Midtrans for an order_id (or recent pending for a user) and update local transaction accordingly';

    public function handle()
    {
        // Ensure Midtrans config is loaded
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        $orderId = $this->argument('order_id');
        $userId = $this->option('user');

        if ($orderId) {
            $this->info("Checking Midtrans for order_id={$orderId} ...");
            try {
                $resp = Transaction::status($orderId);

                // Normalize response and free the full response ASAP to avoid memory spikes
                if (is_object($resp)) {
                    $transactionStatus = $resp->transaction_status ?? null;
                    $fraudStatus = $resp->fraud_status ?? null;
                    $paymentType = $resp->payment_type ?? null;
                    $grossAmount = $resp->gross_amount ?? null;
                } elseif (is_array($resp)) {
                    $transactionStatus = $resp['transaction_status'] ?? null;
                    $fraudStatus = $resp['fraud_status'] ?? null;
                    $paymentType = $resp['payment_type'] ?? null;
                    $grossAmount = $resp['gross_amount'] ?? ($resp['gross_amount'] ?? null);
                } else {
                    $transactionStatus = null;
                    $fraudStatus = null;
                    $paymentType = null;
                    $grossAmount = null;
                }

                // free heavy object
                unset($resp);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }

                $this->line('Midtrans response: ' . json_encode([
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                    'payment_type' => $paymentType,
                    'gross_amount' => $grossAmount ?? null,
                ]));

                $transaction = BalanceTransaction::where('order_id', $orderId)->first();

                if (!$transaction && $userId) {
                    $transaction = BalanceTransaction::where('user_id', $userId)
                        ->where('type', 'topup')
                        ->where('status', 'pending')
                        ->orderByDesc('created_at')
                        ->first();
                }

                // If still not found, attempt heuristic match by parsing order id
                if (!$transaction) {
                    $this->warn('No local transaction found for that order_id; attempting heuristic match...');

                    // try parse order id like TOPUP-{userId}-{timestamp}
                    if (preg_match('/^TOPUP-(\d+)-(\d+)$/', $orderId, $matches)) {
                        $userFromOrder = (int) $matches[1];
                        $tsFromOrder = (int) $matches[2];

                        // gross amount already extracted earlier (if available)

                        $from = date('Y-m-d H:i:s', max(0, $tsFromOrder - 300));
                        $to = date('Y-m-d H:i:s', $tsFromOrder + 300);

                        $qb = BalanceTransaction::where('user_id', $userFromOrder)
                            ->where('type', 'topup')
                            ->where('status', 'pending')
                            ->whereBetween('created_at', [$from, $to]);

                        if ($grossAmount !== null) {
                            $qb->where('amount', (float) $grossAmount);
                        }

                        $candidate = $qb->orderByDesc('created_at')->first();

                        if ($candidate) {
                            $this->info('Found candidate txn id=' . $candidate->id . ' — attaching order_id and applying status.');
                            // Use direct DB update to avoid heavy Eloquent save memory spikes
                            try {
                                DB::table((new BalanceTransaction())->getTable())
                                    ->where('id', $candidate->id)
                                    ->update([
                                        'order_id' => $orderId,
                                        'midtrans_response' => null,
                                        'updated_at' => now(),
                                    ]);
                                // re-fetch a lightweight model instance for later processing
                                $transaction = BalanceTransaction::find($candidate->id);
                            } catch (\Throwable $e) {
                                $this->error('Failed to attach order_id to candidate txn: ' . $e->getMessage());
                                // attempt to continue with candidate instance if available
                                $transaction = $candidate;
                            }

                            // free candidate reference memory
                            unset($candidate);
                            if (function_exists('gc_collect_cycles')) {
                                gc_collect_cycles();
                            }
                        } else {
                            $this->warn('No candidate pending transaction found by heuristic.');
                            return 1;
                        }
                    } else {
                        $this->warn('Order id format not recognized; cannot heuristically match.');
                        return 1;
                    }
                }

                // Apply status mapping
                if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
                    $transaction->status = 'completed';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    // Recalculate user balance for idempotent consistency (handle cases observer missed)
                    try {
                        $sum = BalanceTransaction::where('user_id', $transaction->user_id)
                            ->where('type', 'topup')
                            ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                            ->sum('amount');

                        UserBalance::updateOrCreate(
                            ['user_id' => $transaction->user_id],
                            ['balance' => $sum]
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to recalc user balance after recheck', ['user_id' => $transaction->user_id, 'error' => $e->getMessage()]);
                    }

                    $this->info('Marked transaction completed (capture+accept).');
                    Log::info('MidtransRecheck: marked completed', ['order_id' => $orderId, 'transaction_id' => $transaction->id]);
                    return 0;
                }

                if ($transactionStatus === 'settlement') {
                    $transaction->status = 'completed';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    try {
                        $sum = BalanceTransaction::where('user_id', $transaction->user_id)
                            ->where('type', 'topup')
                            ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                            ->sum('amount');

                        UserBalance::updateOrCreate(
                            ['user_id' => $transaction->user_id],
                            ['balance' => $sum]
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to recalc user balance after recheck', ['user_id' => $transaction->user_id, 'error' => $e->getMessage()]);
                    }

                    $this->info('Marked transaction completed (settlement).');
                    Log::info('MidtransRecheck: marked completed', ['order_id' => $orderId, 'transaction_id' => $transaction->id]);
                    return 0;
                }

                if ($transactionStatus === 'pending') {
                    $transaction->status = 'pending';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    $this->info('Left transaction as pending.');
                    return 0;
                }

                if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $transaction->status = 'failed';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    $this->info('Marked transaction failed.');
                    return 0;
                }

                $this->warn('Midtrans returned an unexpected status: ' . ($transactionStatus ?? 'null'));
                return 1;

            } catch (\Throwable $e) {
                $this->error('Failed to query Midtrans: ' . $e->getMessage());
                Log::warning('MidtransRecheck failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
                return 1;
            }
        }

        // No order_id provided, but user option or --all may be present
        if ($this->option('all')) {
            $this->info('Rechecking all pending topup transactions in batches...');

            // Process in small chunks and avoid heavy Eloquent operations to reduce memory use
            $chunkSize = 10;
            BalanceTransaction::where('type', 'topup')
                ->where('status', 'pending')
                ->whereNotNull('order_id')
                ->chunkById($chunkSize, function ($txns) {
                    foreach ($txns as $txn) {
                        $this->line("Checking order_id={$txn->order_id} (txn_id={$txn->id}) ...");

                        try {
                            $resp = Transaction::status($txn->order_id);
                        } catch (\Throwable $e) {
                            $this->error('Failed to query Midtrans for ' . $txn->order_id . ': ' . $e->getMessage());
                            // ensure we drop references and continue
                            unset($resp);
                            if (function_exists('gc_collect_cycles'))
                                gc_collect_cycles();
                            continue;
                        }

                        // normalize
                        if (is_object($resp)) {
                            $transactionStatus = $resp->transaction_status ?? null;
                            $fraudStatus = $resp->fraud_status ?? null;
                            $paymentType = $resp->payment_type ?? null;
                        } elseif (is_array($resp)) {
                            $transactionStatus = $resp['transaction_status'] ?? null;
                            $fraudStatus = $resp['fraud_status'] ?? null;
                            $paymentType = $resp['payment_type'] ?? null;
                        } else {
                            $transactionStatus = null;
                            $fraudStatus = null;
                            $paymentType = null;
                        }

                        // free heavy object asap
                        unset($resp);
                        if (function_exists('gc_collect_cycles'))
                            gc_collect_cycles();

                        // Prefer Eloquent save here so observers run and user balances update correctly.
                        // This trades some performance for correctness; chunking keeps memory bounded.
                        if (($transactionStatus === 'capture' && $fraudStatus === 'accept') || $transactionStatus === 'settlement') {
                            try {
                                // Use the Eloquent model instance loaded by chunk to update status
                                $txn->status = 'completed';
                                $txn->payment_type = $paymentType;
                                $txn->save();

                                Log::info('MidtransRecheck: marked completed (eloquent)', ['order_id' => $txn->order_id, 'transaction_id' => $txn->id]);
                            } catch (\Throwable $e) {
                                Log::warning('MidtransRecheck: failed to mark completed via eloquent for txn', ['id' => $txn->id, 'error' => $e->getMessage()]);
                                // Fallback: try direct DB update + recompute to avoid leaving the txn pending
                                try {
                                    $now = now();
                                    DB::table((new BalanceTransaction())->getTable())
                                        ->where('id', $txn->id)
                                        ->update([
                                            'status' => 'completed',
                                            'payment_type' => $paymentType,
                                            'updated_at' => $now,
                                        ]);

                                    $sum = (float) DB::table((new BalanceTransaction())->getTable())
                                        ->where('user_id', $txn->user_id)
                                        ->where('type', 'topup')
                                        ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                                        ->sum('amount');

                                    DB::table((new UserBalance())->getTable())
                                        ->updateOrInsert(
                                            ['user_id' => $txn->user_id],
                                            ['balance' => $sum, 'updated_at' => $now, 'created_at' => $now]
                                        );
                                } catch (\Throwable $e2) {
                                    Log::error('MidtransRecheck: fallback DB update also failed', ['id' => $txn->id, 'error' => $e2->getMessage()]);
                                }
                            }

                            // free txn reference and force GC between iterations
                            unset($txn);
                            if (function_exists('gc_collect_cycles'))
                                gc_collect_cycles();
                            // tiny pause to allow system memory to stabilize
                            usleep(50000);
                            continue;
                        }

                        // leave pending/other statuses untouched; drop txn reference
                        unset($txn);
                        if (function_exists('gc_collect_cycles'))
                            gc_collect_cycles();
                        usleep(20000);
                    }
                });

            $this->info('Batch recheck complete.');
            return 0;
        }

        if ($userId) {
            $this->info("Rechecking recent pending topup(s) for user_id={$userId} ...");
            $txns = BalanceTransaction::where('user_id', $userId)
                ->where('type', 'topup')
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get();

            if ($txns->isEmpty()) {
                $this->warn('No pending topup transactions found for that user.');
                return 1;
            }

            foreach ($txns as $txn) {
                $this->line("Checking order_id={$txn->order_id} (txn_id={$txn->id}) ...");
                if (empty($txn->order_id)) {
                    $this->warn('Skipping txn without order_id.');
                    continue;
                }

                try {
                    $resp = Transaction::status($txn->order_id);
                } catch (\Throwable $e) {
                    $this->error('Failed to query Midtrans for ' . $txn->order_id . ': ' . $e->getMessage());
                    continue;
                }

                // normalize
                if (is_object($resp)) {
                    $transactionStatus = $resp->transaction_status ?? null;
                    $fraudStatus = $resp->fraud_status ?? null;
                    $paymentType = $resp->payment_type ?? null;
                } elseif (is_array($resp)) {
                    $transactionStatus = $resp['transaction_status'] ?? null;
                    $fraudStatus = $resp['fraud_status'] ?? null;
                    $paymentType = $resp['payment_type'] ?? null;
                } else {
                    $transactionStatus = null;
                    $fraudStatus = null;
                    $paymentType = null;
                }

                if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
                    $txn->status = 'completed';
                    $txn->payment_type = $paymentType;
                    $txn->save();
                    $this->info("Txn {$txn->id} marked completed.");
                    continue;
                }

                if ($transactionStatus === 'settlement') {
                    $txn->status = 'completed';
                    $txn->payment_type = $paymentType;
                    $txn->save();
                    $this->info("Txn {$txn->id} marked completed.");
                    continue;
                }

                $this->line("Txn {$txn->id} status on Midtrans: " . ($transactionStatus ?? 'null'));
            }

            return 0;
        }

        $this->info('Usage: midtrans:recheck {order_id} OR midtrans:recheck --user=USER_ID');
        return 1;
    }
}
