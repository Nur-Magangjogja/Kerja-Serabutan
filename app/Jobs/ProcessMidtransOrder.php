<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Midtrans\Config;
use Midtrans\Transaction;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessMidtransOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load Midtrans config each run (safe for queued jobs)
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = config('services.midtrans.is_sanitized');
            Config::$is3ds = config('services.midtrans.is_3ds');

            $orderId = $this->orderId;

            Log::info('ProcessMidtransOrder: checking order', ['order_id' => $orderId]);

            $resp = Transaction::status($orderId);

            if (is_object($resp)) {
                $transactionStatus = $resp->transaction_status ?? null;
                $fraudStatus = $resp->fraud_status ?? null;
                $paymentType = $resp->payment_type ?? null;
                // Midtrans SDK may return stdClass; json_encode handles objects and arrays safely
                $raw = json_encode($resp);
            } elseif (is_array($resp)) {
                $transactionStatus = $resp['transaction_status'] ?? null;
                $fraudStatus = $resp['fraud_status'] ?? null;
                $paymentType = $resp['payment_type'] ?? null;
                $raw = json_encode($resp);
            } else {
                $transactionStatus = null;
                $fraudStatus = null;
                $paymentType = null;
                $raw = null;
            }

            // free heavy object
            unset($resp);
            if (function_exists('gc_collect_cycles'))
                gc_collect_cycles();

            // Find local transaction
            $transaction = BalanceTransaction::where('order_id', $orderId)->first();

            // If not found, attempt heuristic as in controller
            if (!$transaction && preg_match('/^TOPUP-(\d+)-(\d+)/', $orderId, $m)) {
                $userFromOrder = (int) $m[1];
                $tsFromOrder = (int) $m[2];
                $from = date('Y-m-d H:i:s', max(0, $tsFromOrder - 300));
                $to = date('Y-m-d H:i:s', $tsFromOrder + 300);

                $candidate = BalanceTransaction::where('user_id', $userFromOrder)
                    ->where('type', 'topup')
                    ->where('status', 'pending')
                    ->whereBetween('created_at', [$from, $to])
                    ->orderByDesc('created_at')
                    ->first();

                if ($candidate) {
                    try {
                        // Attach order_id and midtrans_response via Eloquent so observers/events run
                        $candidate->order_id = $orderId;
                        $candidate->midtrans_response = $raw;
                        $candidate->save();
                        $transaction = $candidate;
                    } catch (\Throwable $e) {
                        Log::warning('ProcessMidtransOrder: failed to attach order_id (eloquent)', ['error' => $e->getMessage(), 'candidate_id' => $candidate->id]);
                        // fallback to using the candidate instance
                        $transaction = $candidate;
                    }
                }
            }

            if (!$transaction) {
                Log::warning('ProcessMidtransOrder: transaction not found for order', ['order_id' => $orderId]);
                return;
            }

            // Update midtrans_response column if available
            try {
                // Save midtrans_response on the model so events can trigger if necessary
                $transaction->midtrans_response = $raw;
                $transaction->save();
            } catch (\Throwable $e) {
                Log::warning('ProcessMidtransOrder: failed to save midtrans_response (eloquent)', ['id' => $transaction->id, 'error' => $e->getMessage()]);
            }

            // Handle statuses
            if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
                $this->markCompleted($transaction, $paymentType, $raw);
                return;
            }

            if ($transactionStatus === 'settlement') {
                $this->markCompleted($transaction, $paymentType, $raw);
                return;
            }

            if ($transactionStatus === 'pending') {
                try {
                    $transaction->status = 'pending';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                } catch (\Throwable $e) {
                    Log::warning('ProcessMidtransOrder: failed to mark pending (eloquent)', ['id' => $transaction->id, 'error' => $e->getMessage()]);
                }
                return;
            }

            if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                try {
                    $transaction->status = 'failed';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                } catch (\Throwable $e) {
                    Log::warning('ProcessMidtransOrder: failed to mark failed (eloquent)', ['id' => $transaction->id, 'error' => $e->getMessage()]);
                }
                return;
            }

        } catch (\Throwable $e) {
            Log::error('ProcessMidtransOrder failed', ['order_id' => $this->orderId, 'error' => $e->getMessage()]);
        }
    }

    protected function markCompleted(BalanceTransaction $transaction, $paymentType = null, $raw = null)
    {
        try {
            // Use Eloquent save so that BalanceTransactionObserver runs and increments user balance
            $transaction->payment_type = $paymentType ?? $transaction->payment_type;
            if ($raw !== null) {
                $transaction->midtrans_response = $raw;
            }
            $transaction->status = 'completed';
            $transaction->save();

            Log::info('ProcessMidtransOrder: marked completed (eloquent save)', ['order_id' => $transaction->order_id, 'transaction_id' => $transaction->id]);
        } catch (\Throwable $e) {
            Log::warning('ProcessMidtransOrder: failed to mark completed via eloquent, falling back to DB update', ['id' => $transaction->id, 'error' => $e->getMessage()]);

            try {
                // Fallback to DB update and recompute (less ideal but safer for batch contexts)
                $now = now();
                DB::table((new BalanceTransaction())->getTable())
                    ->where('id', $transaction->id)
                    ->update(['status' => 'completed', 'payment_type' => $paymentType, 'midtrans_response' => $raw, 'updated_at' => $now]);

                $sum = (float) DB::table((new BalanceTransaction())->getTable())
                    ->where('user_id', $transaction->user_id)
                    ->where('type', 'topup')
                    ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                    ->sum('amount');

                DB::table((new UserBalance())->getTable())
                    ->updateOrInsert(
                        ['user_id' => $transaction->user_id],
                        ['balance' => $sum, 'updated_at' => $now, 'created_at' => $now]
                    );

            } catch (\Throwable $e2) {
                Log::error('ProcessMidtransOrder: fallback DB update also failed', ['id' => $transaction->id, 'error' => $e2->getMessage()]);
            }
        }
    }
}
