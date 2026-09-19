<?php

namespace App\Services\Cancellation;

use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\User;
use App\Services\PartnerDisciplineService;
use App\Services\PartnerOnlineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancellationAuditService
{
    protected PartnerDisciplineService $disciplineService;
    protected PartnerOnlineService $onlineService;
    protected CancellationSettlementService $settlementService;

    public function __construct(
        PartnerDisciplineService $disciplineService,
        PartnerOnlineService $onlineService,
        CancellationSettlementService $settlementService
    ) {
        $this->disciplineService = $disciplineService;
        $this->onlineService = $onlineService;
        $this->settlementService = $settlementService;
    }

    /**
     * Admin Wilayah mereview tiket pembatalan / sengketa On-Site & Antar-Jemput.
     * Dapat memberikan keputusan:
     * - valid_no_sp   : Alasan darurat sah, tidak ada SP.
     * - penalty_issued: Dikenakan sanksi Surat Peringatan (SP 1, SP 2, atau SP 3).
     * - rejected      : Permintaan pembatalan ditolak.
     */
    public function reviewCancelRequest(
        HelpCancelRequest $request,
        User $adminUser,
        string $decision,
        ?string $adminNotes = null,
        ?string $spTarget = 'none',
        ?int $partnerSpLevel = null,
        ?string $partnerSpReason = null,
        ?int $customerSpLevel = null,
        ?string $customerSpReason = null,
        string $settlementType = HelpCancelRequest::SETTLEMENT_FULL_REFUND,
        ?float $refundAmount = null,
        ?float $partnerAmount = null
    ): HelpCancelRequest {
        return DB::transaction(function () use (
            $request,
            $adminUser,
            $decision,
            $adminNotes,
            $spTarget,
            $partnerSpLevel,
            $partnerSpReason,
            $customerSpLevel,
            $customerSpReason,
            $settlementType,
            $refundAmount,
            $partnerAmount
        ) {
            $lockedReq = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->status !== HelpCancelRequest::STATUS_PENDING) {
                throw new \RuntimeException('Tiket pembatalan ini sudah pernah diproses sebelumnya.');
            }

            $help = Help::where('id', $lockedReq->help_id)->lockForUpdate()->first();

            // 1. Eksekusi sanksi SP bila ada
            if ($spTarget === HelpCancelRequest::SP_TARGET_PARTNER || $spTarget === HelpCancelRequest::SP_TARGET_BOTH) {
                if ($partnerSpLevel && $lockedReq->partner_id) {
                    $this->disciplineService->issueWarning(
                        $lockedReq->partner_id,
                        $partnerSpLevel,
                        $partnerSpReason ?? $adminNotes ?? 'Pelanggaran pembatalan tugas sepihak.',
                        $adminUser,
                        $help
                    );
                }
            }

            if ($spTarget === HelpCancelRequest::SP_TARGET_CUSTOMER || $spTarget === HelpCancelRequest::SP_TARGET_BOTH) {
                if ($customerSpLevel && $lockedReq->customer_id) {
                    $this->disciplineService->issueWarning(
                        $lockedReq->customer_id,
                        $customerSpLevel,
                        $customerSpReason ?? $adminNotes ?? 'Pelanggaran / kejanggalan pesanan bantuan.',
                        $adminUser,
                        $help
                    );
                }
            }

            // 2. Tentukan status akhir dan settlement
            $finalStatus = ($decision === 'rejected') ? HelpCancelRequest::STATUS_REJECTED : HelpCancelRequest::STATUS_APPROVED;
            $finalRefund = 0.0;
            $finalPayout = 0.0;

            if ($help) {
                $gross = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);

                if ($decision === 'rejected') {
                    // Kembalikan order ke status sebelumnya jika masih menunggu review
                    if (in_array($help->status, [Help::STATUS_CUSTOMER_CANCEL_REQUESTED, Help::STATUS_PARTNER_CANCEL_REQUESTED])) {
                        $help->update([
                            'status'              => $lockedReq->previous_status ?: Help::STATUS_IN_PROGRESS,
                            'cancel_requested_by' => null,
                            'cancel_deadline_at'  => null,
                        ]);
                    }
                } else {
                    // Disetujui
                    if ($settlementType === HelpCancelRequest::SETTLEMENT_RELIST_POOL) {
                        $oldPartnerId = $help->mitra_id ?: $lockedReq->partner_id;
                        // Lepas mitra jika masih terpasang, catat eksklusi, dan buka pool pencarian mitra baru
                        if ($oldPartnerId) {
                            $this->onlineService->releaseBusy($oldPartnerId, $help->id);
                            $help->addExcludedPartner($oldPartnerId, "Admin mengembalikan tugas ke pool via audit pembatalan/ganti mitra: " . ($adminNotes ?? ''));
                        }
                        $help->update([
                            'status'                      => Help::STATUS_MENUNGGU_MITRA,
                            'mitra_id'                    => null,
                            'service_stage'               => null,
                            'partner_initial_lat'         => null,
                            'partner_initial_lng'         => null,
                            'partner_current_lat'         => null,
                            'partner_current_lng'         => null,
                            'partner_started_at'          => null,
                            'partner_started_moving_at'   => null,
                            'partner_arrived_at'          => null,
                            'arrived_at'                  => null,
                            'partner_location_updated_at' => null,
                            'cancel_requested_by'         => null,
                            'cancel_deadline_at'          => null,
                            'dispatch_mode'               => Help::DISPATCH_MODE_POOL,
                            'pool_opened_at'              => now(),
                            'admin_notes'                 => $adminNotes ?: 'Dikonfirmasi oleh Admin untuk mengembalikan tugas ke pool pencarian rekan jasa baru.',
                        ]);
                        $finalRefund = 0.0;
                        $finalPayout = 0.0;
                    } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_FULL_REFUND) {
                        if ($help->status !== Help::STATUS_DIBATALKAN) {
                            $this->settlementService->processFullRefund($help, $adminNotes ?? 'Pembatalan disetujui Admin Wilayah.');
                        }
                        $finalRefund = $gross;
                        $finalPayout = 0.0;
                    } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT || $settlementType === HelpCancelRequest::SETTLEMENT_ITEM_SETTLED) {
                        $pAmt = (float) ($partnerAmount ?? 0);
                        $rAmt = (float) ($refundAmount ?? max(0, $gross - $pAmt));
                        if ($help->status !== Help::STATUS_DIBATALKAN) {
                            $this->settlementService->processPartialSettlement($help, $pAmt, $rAmt, $adminNotes ?? 'Penyelesaian audit parsial admin.');
                        }
                        $finalRefund = $rAmt;
                        $finalPayout = $pAmt;
                    }
                }
            }

            $isPartnerSp  = in_array($spTarget, [HelpCancelRequest::SP_TARGET_PARTNER, HelpCancelRequest::SP_TARGET_BOTH], true);
            $isCustomerSp = in_array($spTarget, [HelpCancelRequest::SP_TARGET_CUSTOMER, HelpCancelRequest::SP_TARGET_BOTH], true);

            // 3. Update Tiket Audit
            $lockedReq->update([
                'status'                 => $finalStatus,
                'reviewed_by'            => $adminUser->id,
                'reviewed_at'            => now(),
                'admin_notes'            => $adminNotes,
                'settlement_type'        => $settlementType,
                'refund_amount_customer' => $finalRefund,
                'payout_amount_mitra'    => $finalPayout,
                'sp_target'              => $spTarget ?? HelpCancelRequest::SP_TARGET_NONE,
                'partner_sp_level'       => $isPartnerSp ? $partnerSpLevel : null,
                'partner_sp_reason'      => $isPartnerSp ? $partnerSpReason : null,
                'customer_sp_level'      => $isCustomerSp ? $customerSpLevel : null,
                'customer_sp_reason'     => $isCustomerSp ? $customerSpReason : null,
                'audit_decision'         => $decision,
            ]);

            Log::info("[CancellationAuditService] Cancel request reviewed by admin #{$adminUser->id}", [
                'request_id'      => $lockedReq->id,
                'decision'        => $decision,
                'settlement_type' => $settlementType,
                'sp_target'       => $spTarget,
            ]);

            return $lockedReq;
        });
    }
}
