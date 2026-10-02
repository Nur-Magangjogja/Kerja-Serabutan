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

            if (in_array($lockedHelp->escrow_status, [Help::ESCROW_STATUS_RELEASED, Help::ESCROW_STATUS_PARTIAL_REFUND], true)) {
                Log::warning("[CancellationSettlementService] Cannot full-refund Help #{$lockedHelp->id}: escrow already settled ({$lockedHelp->escrow_status}). Skipping.");
                return;
            }

            if ($lockedHelp->escrow_status !== Help::ESCROW_STATUS_REFUNDED) {
                $customer = $lockedHelp->user;
                if ($customer) {
                    $this->escrowService->refundFromEscrow($lockedHelp, $customer);
                }
            } else {
                Log::info("[CancellationSettlementService] Escrow was already refunded for Help #{$lockedHelp->id}, finalizing status cancellation.");
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

            if ($lockedHelp->escrow_status !== Help::ESCROW_STATUS_HELD) {
                throw new \RuntimeException("Escrow untuk pesanan #{$lockedHelp->id} sudah diselesaikan atau tidak dalam status ditahan (status: {$lockedHelp->escrow_status}).");
            }

            if (in_array($lockedHelp->status, [Help::STATUS_DIBATALKAN, Help::STATUS_SELESAI], true)) {
                throw new \RuntimeException("Pesanan #{$lockedHelp->id} sudah ditutup (status: {$lockedHelp->status}).");
            }

            $settlementAmount = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

            if ($compensationMitra < 0 || $refundCustomer < 0) {
                throw new \InvalidArgumentException('Nominal refund dan kompensasi tidak boleh bernilai negatif.');
            }

            if ($compensationMitra > $settlementAmount || $refundCustomer > $settlementAmount) {
                throw new \InvalidArgumentException('Nominal refund atau kompensasi tidak boleh melebihi nilai escrow.');
            }

            if (abs(($compensationMitra + $refundCustomer) - $settlementAmount) > 0.01) {
                throw new \RuntimeException("Total pembagian partial settlement (Rp " . number_format($compensationMitra + $refundCustomer, 0) . ") tidak sama dengan nilai escrow (Rp " . number_format($settlementAmount, 0) . ").");
            }

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
