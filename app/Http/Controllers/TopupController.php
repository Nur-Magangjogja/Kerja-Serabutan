<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;
use App\Jobs\ProcessMidtransOrder;

class TopupController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Handle finish callback from Midtrans
     */
    public function finish(Request $request)
    {
        $orderId = $request->order_id ?? session('midtrans_last_order_id');

        Log::info('Midtrans finish callback', ['order_id' => $orderId]);

        // Try to fetch the latest status from Midtrans API and update the transaction
        try {
            $resp = Transaction::status($orderId);

            // Normalize response (SDK may return object or array)
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

            // Log response for debugging
            Log::info('Midtrans status fetch', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            // Find local transaction
            $transaction = null;
            if ($orderId) {
                $transaction = BalanceTransaction::where('order_id', $orderId)->first();
            }

            // Fallback: if no order_id or no match, try recent pending topup for authenticated user
            if (!$transaction && auth()->check()) {
                $transaction = BalanceTransaction::where('user_id', auth()->id())
                    ->where('type', 'topup')
                    ->where('status', 'pending')
                    ->orderByDesc('created_at')
                    ->first();
            }

            if ($transaction) {
                if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
                    $this->updateTransactionSuccess($transaction, $paymentType);
                    return redirect()->route('topup.success', ['order_id' => $orderId]);
                }

                if ($transactionStatus === 'settlement') {
                    $this->updateTransactionSuccess($transaction, $paymentType);
                    return redirect()->route('topup.success', ['order_id' => $orderId]);
                }

                if ($transactionStatus === 'pending') {
                    $transaction->status = 'pending';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    return redirect()->route('customer.topup')->with('status', 'Pembayaran masih pending. Silakan tunggu konfirmasi.');
                }

                if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $transaction->status = 'failed';
                    $transaction->payment_type = $paymentType;
                    $transaction->save();
                    return redirect()->route('customer.topup')->with('error', 'Pembayaran gagal atau dibatalkan.');
                }
            }

        } catch (\Throwable $e) {
            Log::warning('Failed to fetch Midtrans status on finish callback', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }

        return redirect()->route('customer.topup')->with('status', 'Pembayaran sedang diproses. Silakan tunggu konfirmasi.');
    }

    public function notification(Request $request)
    {
        try {
            // Create notification instance
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;

            Log::info('Midtrans notification', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            // Find transaction by order_id
            $transaction = BalanceTransaction::where('order_id', $orderId)->first();

            // If not found, attempt heuristic matching (order format TOPUP-{userId}-{ts})
            if (!$transaction) {
                Log::warning('Transaction not found by order_id, attempting heuristic match', ['order_id' => $orderId]);

                $candidate = null;
                // Try parse order id like TOPUP-{userId}-{timestamp}
                if (is_string($orderId) && preg_match('/^TOPUP-(\d+)-(\d+)$/', $orderId, $matches)) {
                    $userFromOrder = (int) $matches[1];
                    $tsFromOrder = (int) $matches[2];

                    // attempt to extract gross amount from notification response
                    $grossAmount = $notification->gross_amount ?? ($notification->gross_amount ?? null);

                    $from = date('Y-m-d H:i:s', max(0, $tsFromOrder - 300));
                    $to = date('Y-m-d H:i:s', $tsFromOrder + 300);

                    $qb = BalanceTransaction::where('user_id', $userFromOrder)
                        ->where('type', 'topup')
                        ->where('status', 'pending')
                        ->whereBetween('created_at', [$from, $to]);

                    if (!empty($grossAmount)) {
                        $qb->where('amount', (float) $grossAmount);
                    }

                    $candidate = $qb->orderByDesc('created_at')->first();
                }

                // Fallback: try match by midtrans transaction id or snap token if present in payload
                if (!$candidate) {
                    $midtransTxnId = $notification->transaction_id ?? null;
                    $snapToken = $notification->snap_token ?? null;

                    if ($midtransTxnId) {
                        $candidate = BalanceTransaction::where('reference_id', $midtransTxnId)
                            ->where('type', 'topup')
                            ->where('status', 'pending')
                            ->orderByDesc('created_at')
                            ->first();
                    }

                    if (!$candidate && $snapToken) {
                        $candidate = BalanceTransaction::where('snap_token', $snapToken)
                            ->where('type', 'topup')
                            ->where('status', 'pending')
                            ->orderByDesc('created_at')
                            ->first();
                    }
                }

                if ($candidate) {
                    // Attach order_id to candidate and continue processing
                    $candidate->order_id = $orderId;
                    $candidate->midtrans_response = json_encode($notification->getResponse());
                    $candidate->save();
                    Log::info('Attached order_id to candidate transaction', ['candidate_id' => $candidate->id, 'order_id' => $orderId]);
                    $transaction = $candidate;
                }
            }

            if (!$transaction) {
                Log::warning('Transaction not found after heuristics', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            // Update midtrans response
            $transaction->midtrans_response = json_encode($notification->getResponse());

            // Handle transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // Payment success
                    $this->updateTransactionSuccess($transaction, $notification->payment_type);
                }
            } elseif ($transactionStatus == 'settlement') {
                // Payment success
                $this->updateTransactionSuccess($transaction, $notification->payment_type);
            } elseif ($transactionStatus == 'pending') {
                // Payment pending
                $transaction->status = 'pending';
                $transaction->payment_type = $notification->payment_type;
                $transaction->save();
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                // Payment failed
                $transaction->status = 'failed';
                $transaction->payment_type = $notification->payment_type;
                $transaction->save();
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Midtrans notification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Client-side callback endpoint.
     * Receives a small payload from the browser after Snap reports success and triggers
     * a server-side Midtrans status fetch to update the local transaction immediately.
     */
    public function clientCallback(Request $request)
    {
        $orderId = $request->input('order_id');

        if (empty($orderId)) {
            return response()->json(['status' => 'error', 'message' => 'order_id required'], 422);
        }

        Log::info('Midtrans client callback received', ['order_id' => $orderId]);

        try {
            // Try to fetch latest status from Midtrans immediately
            $resp = Transaction::status($orderId);

            if (is_object($resp)) {
                $transactionStatus = $resp->transaction_status ?? null;
                $fraudStatus = $resp->fraud_status ?? null;
                $paymentType = $resp->payment_type ?? null;
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

            // Attempt to find local transaction by order_id
            $transaction = BalanceTransaction::where('order_id', $orderId)->first();

            // If not found, try heuristic match similar to notification/job
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
                    // attach order id and midtrans response
                    $candidate->order_id = $orderId;
                    $candidate->midtrans_response = $raw;
                    $candidate->save();
                    $transaction = $candidate;
                    Log::info('Client callback: attached order_id to candidate', ['candidate_id' => $candidate->id, 'order_id' => $orderId]);
                }
            }

            if (!$transaction) {
                Log::warning('Client callback: transaction not found for order_id', ['order_id' => $orderId]);
                // Still dispatch background job in case heuristic in job is more successful
                ProcessMidtransOrder::dispatch($orderId);
                return response()->json(['status' => 'queued', 'message' => 'transaction not found locally, job queued']);
            }

            // Update midtrans_response if available
            try {
                // Log current user balance before update for debugging
                try {
                    $ubBefore = UserBalance::where('user_id', $transaction->user_id)->first();
                    Log::info('Client callback: user balance before update', ['user_id' => $transaction->user_id, 'balance_before' => $ubBefore ? $ubBefore->balance : null]);
                } catch (\Throwable $__e) {
                    Log::warning('Client callback: failed to read user balance before update', ['error' => $__e->getMessage()]);
                }

                $transaction->midtrans_response = $raw;
                $transaction->payment_type = $paymentType ?? $transaction->payment_type;
                // Map statuses
                if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
                    $transaction->status = 'completed';
                } elseif ($transactionStatus === 'settlement') {
                    $transaction->status = 'completed';
                } elseif ($transactionStatus === 'pending') {
                    $transaction->status = 'pending';
                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $transaction->status = 'failed';
                }

                $transaction->save();

                // If completed, make sure balance is recalculated (observer should handle it)
                if ($transaction->status === 'completed') {
                    Log::info('Client callback: transaction marked completed', ['order_id' => $orderId, 'transaction_id' => $transaction->id]);

                    // Safety fallback: recompute user's balance from completed topups
                    try {
                        $sum = BalanceTransaction::where('user_id', $transaction->user_id)
                            ->where('type', 'topup')
                            ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                            ->sum('amount');

                        UserBalance::updateOrCreate(
                            ['user_id' => $transaction->user_id],
                            ['balance' => (float) $sum]
                        );
                        Log::info('Client callback: recomputed user balance (safety fallback)', ['user_id' => $transaction->user_id, 'balance' => $sum]);
                        try {
                            $ubAfter = UserBalance::where('user_id', $transaction->user_id)->first();
                            Log::info('Client callback: user balance after recompute', ['user_id' => $transaction->user_id, 'balance_after' => $ubAfter ? $ubAfter->balance : null]);
                        } catch (\Throwable $__e) {
                            Log::warning('Client callback: failed to read user balance after recompute', ['error' => $__e->getMessage()]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Client callback: failed to recompute user balance', ['user_id' => $transaction->user_id, 'error' => $e->getMessage()]);
                    }
                }

                return response()->json(['status' => 'processed', 'transaction_status' => $transaction->status]);

            } catch (\Throwable $e) {
                Log::warning('Client callback: failed to update transaction', ['order_id' => $orderId, 'error' => $e->getMessage()]);
                ProcessMidtransOrder::dispatch($orderId);
                return response()->json(['status' => 'queued', 'message' => 'failed to update, job queued'], 500);
            }

        } catch (\Throwable $e) {
            Log::warning('Failed to query Midtrans on clientCallback', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update transaction to success and add balance
     */
    private function updateTransactionSuccess(BalanceTransaction $transaction, $paymentType)
    {
        // Update transaction status
        $transaction->status = 'completed';
        $transaction->payment_type = $paymentType;
        $transaction->save();

        // Let the BalanceTransactionObserver handle the actual balance increment
        // to keep balance updates centralized and avoid double-processing.
        Log::info('Transaction marked completed (observer will update balance)', [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
        ]);
    }

    /**
     * Show payment success page.
     */
    public function success(Request $request)
    {
        $orderId = $request->query('order_id') ?? session('midtrans_last_order_id');

        $transaction = null;
        if ($orderId) {
            $transaction = BalanceTransaction::where('order_id', $orderId)->first();
        }

        return view('topup.success', ['transaction' => $transaction, 'order_id' => $orderId]);
    }
}
