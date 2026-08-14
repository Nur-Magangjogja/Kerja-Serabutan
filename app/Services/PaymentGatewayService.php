<?php

namespace App\Services;

use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Dummy Payment Gateway Service for Disbursement (simulate Xendit/Midtrans)
 * In production replace with real SDK integration.
 */
class PaymentGatewayService
{
    /**
     * Create a disbursement request at the gateway.
     * This dummy function simulates a gateway call and then calls the callback handler internally.
     */
    public function createDisbursement(WithdrawRequest $withdraw): array
    {
        // Simulate creating disbursement at gateway
        $externalId = 'gw_' . Str::random(16);

        // Mark as processing (immediate)
        $withdraw->update([
            'external_id' => $externalId,
            'status' => WithdrawRequest::STATUS_PROCESSING,
        ]);

        Log::info('PaymentGatewayService: created disbursement', ['withdraw_id' => $withdraw->id, 'external_id' => $externalId]);

        // Simulate an asynchronous callback from gateway.
        // For demo we will call the callback handler synchronously after a short determination.
        // In real life the gateway will call our `/gateway/callback` endpoint.

        // For demo: succeed 80% of time
        $succeeds = rand(1, 100) <= 80;

        // Simulate callback payload
        $payload = [
            'external_id' => $externalId,
            'status' => $succeeds ? WithdrawRequest::STATUS_SUCCESS : WithdrawRequest::STATUS_FAILED,
            'amount' => $withdraw->amount,
            'reference' => 'demo_ref_' . $withdraw->id,
        ];

        // Call handler directly (synchronous simulation)
        $this->handleGatewayCallback($payload);

        return ['external_id' => $externalId, 'simulated_status' => $payload['status']];
    }

    /**
     * Handle gateway callback (internal helper used by simulation).
     * In real world, this logic should be in a Controller endpoint that validates signature.
     */
    public function handleGatewayCallback(array $payload): void
    {
        try {
            $withdraw = WithdrawRequest::where('external_id', $payload['external_id'])->first();
            if (!$withdraw) {
                Log::warning('PaymentGatewayService: callback for unknown external_id', $payload);
                return;
            }

            $newStatus = $payload['status'];

            if ($newStatus === WithdrawRequest::STATUS_SUCCESS) {
                $withdraw->update(['status' => WithdrawRequest::STATUS_SUCCESS, 'processed_at' => now()]);
                Log::info('PaymentGatewayService: withdraw success', ['withdraw_id' => $withdraw->id]);
            } else {
                // failed --> refund user
                $withdraw->update(['status' => WithdrawRequest::STATUS_FAILED, 'processed_at' => now()]);
                $user = $withdraw->user;
                if ($user) {
                    $user->adjustBalance($withdraw->amount);
                }
                Log::info('PaymentGatewayService: withdraw failed and refunded', ['withdraw_id' => $withdraw->id]);
            }
        } catch (\Throwable $e) {
            Log::error('PaymentGatewayService: error handling callback', ['error' => $e->getMessage()]);
        }
    }
}
