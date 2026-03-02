<?php

namespace App\Observers;

use App\Models\BalanceTransaction;
use App\Models\PartnerActivity;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BalanceTransactionObserver
{
    public function created(BalanceTransaction $transaction): void
    {
        Log::info('BalanceTransactionObserver: created', [
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
        ]);

        $this->handleCompletion($transaction, null);
    }

    public function updated(BalanceTransaction $transaction): void
    {
        $original = $transaction->getOriginal('status');

        Log::info('BalanceTransactionObserver: updated', [
            'transaction_id' => $transaction->id,
            'original' => $original,
            'current' => $transaction->status,
        ]);

        $this->handleCompletion($transaction, $original);
    }

    protected function handleCompletion(BalanceTransaction $transaction, ?string $originalStatus): void
    {
        try {
            if ($transaction->type !== 'topup') {
                return;
            }

            // Normalize
            $normalize = fn ($s) => strtolower(trim($s ?? ''));
            $curr = $normalize($transaction->status);
            $orig = $normalize($originalStatus);

            if ($orig === 'completed') return;
            if ($curr !== 'completed') return;

            $amount = (float) $transaction->amount;
            if ($amount <= 0) return;

            // prevent double processing
            $fresh = $transaction->fresh();
            if ($fresh && $fresh->processed_at) {
                Log::info("BalanceTransactionObserver: already processed, skip", [
                    'transaction_id' => $transaction->id,
                    'processed_at' => $fresh->processed_at,
                ]);
                return;
            }

            DB::transaction(function () use ($transaction, $amount) {
                Log::info('BalanceTransactionObserver: processing topup', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'amount' => $amount,
                ]);

                $userBalance = UserBalance::firstOrCreate(
                    ['user_id' => $transaction->user_id],
                    ['balance' => 0]
                );

                $userBalance->increment('balance', $amount);

                $transaction->processed_at = now();
                $transaction->save();

                try {
                    PartnerActivity::create([
                        'user_id' => $transaction->user_id,
                        'activity_type' => 'balance_topup',
                        'description' => sprintf(
                            'Top up berhasil Rp %s %s',
                            number_format($amount, 0, ',', '.'),
                            $transaction->order_id ? '(Order #' . $transaction->order_id . ')' : ''
                        ),
                        'ip_address' => request()?->ip(),
                        'user_agent' => request()?->header('User-Agent'),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('BalanceTransactionObserver: failed to log PartnerActivity', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info("BalanceTransactionObserver: completed", [
                    'transaction_id' => $transaction->id,
                    'balance_after' => $userBalance->balance,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('BalanceTransactionObserver ERROR', [
                'transaction_id' => $transaction->id,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
