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
     * Disabled: Payment gateway integration is dormant and live withdrawals are processed manually by Admin.
     */
    public function handleGatewayCallback(array $payload): void
    {
        Log::warning('PaymentGatewayService::handleGatewayCallback called on dormant service. Ignoring callback.', $payload);
    }
}
