<?php

namespace App\Services\Cancellation;

use App\Models\Help;
use App\Models\User;
use App\Services\HelpEscrowService;
use App\Services\PartnerOnlineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancellationSettlementService
{
    protected HelpEscrowService $escrowService;
    protected PartnerOnlineService $onlineService;

    public function __construct(
        HelpEscrowService $escrowService,
        PartnerOnlineService $onlineService
    ) {
        $this->escrowService = $escrowService;
        $this->onlineService = $onlineService;
    }

    /**
     * Eksekusi Full Refund (100% Saldo Kembali ke Customer).
     */
    public function processFullRefund(Help $help, string $reason = 'Pembatalan Pesanan'): void
    {
        DB::transaction(function () use ($help, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_REFUNDED) {
                Log::info("[CancellationSettlementService] Escrow already refunded for Help #{$lockedHelp->id}");
                return;
            }

            $customer = $lockedHelp->user;
            if ($customer) {
                $this->escrowService->refundFromEscrow($lockedHelp, $customer);
            }

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
            ]);

            if ($lockedHelp->mitra_id) {
                $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
            }

            Log::info("[CancellationSettlementService] Full refund processed for Help #{$lockedHelp->id}: {$reason}");
        });
    }

    /**
     * Eksekusi Parsial Kompensasi Mitra + Parsial Refund Customer (Idempotent).
     */
    public function processPartialSettlement(
        Help $help,
        float $compensationMitra,
        float $refundCustomer,
        string $reason = 'Kompensasi Pembatalan'
    ): void {
        DB::transaction(function () use ($help, $compensationMitra, $refundCustomer, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            $customer = $lockedHelp->user;
            $mitra    = $lockedHelp->mitra;

            if ($refundCustomer > 0 && $customer) {
                $this->escrowService->refundFromEscrowDirect(
                    $lockedHelp,
                    $customer,
                    $refundCustomer,
                    $reason
                );
            }

            if ($compensationMitra > 0 && $mitra) {
                $this->escrowService->payoutPartialFromEscrowDirect(
                    $lockedHelp,
                    $mitra,
                    $compensationMitra,
                    $reason
                );
            }

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'escrow_status'  => ($compensationMitra > 0) ? Help::ESCROW_STATUS_PARTIAL_REFUND : Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
            ]);

            if ($lockedHelp->mitra_id) {
                $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
            }

            Log::info("[CancellationSettlementService] Partial settlement processed for Help #{$lockedHelp->id}", [
                'compensation_mitra' => $compensationMitra,
                'refund_customer'    => $refundCustomer,
                'reason'             => $reason,
            ]);
        });
    }
}
