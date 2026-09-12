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
     * Admin Wilayah mereview tiket pembatalan / sengketa On-Site.
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
        ?string $customerSpReason = null
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
            $customerSpReason
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

            // 2. Tentukan status akhir dan settlement jika customer meminta cancel
            $finalStatus = ($decision === 'rejected') ? HelpCancelRequest::STATUS_REJECTED : HelpCancelRequest::STATUS_APPROVED;

            if ($help && $help->status === Help::STATUS_CUSTOMER_CANCEL_REQUESTED) {
                if ($decision === 'rejected') {
                    // Kembalikan order ke status sebelumnya
                    $help->update([
                        'status'              => $lockedReq->previous_status ?: Help::STATUS_IN_PROGRESS,
                        'cancel_requested_by' => null,
                        'cancel_deadline_at'  => null,
                    ]);
                } else {
                    // Batalkan order dan refund
                    $this->settlementService->processFullRefund($help, $adminNotes ?? 'Pembatalan disetujui Admin Wilayah.');
                }
            }

            // 3. Update Tiket Audit
            $lockedReq->update([
                'status'             => $finalStatus,
                'reviewed_by'        => $adminUser->id,
                'reviewed_at'        => now(),
                'admin_notes'        => $adminNotes,
                'sp_target'          => $spTarget ?? HelpCancelRequest::SP_TARGET_NONE,
                'partner_sp_level'   => $partnerSpLevel,
                'partner_sp_reason'  => $partnerSpReason,
                'customer_sp_level'  => $customerSpLevel,
                'customer_sp_reason' => $customerSpReason,
                'audit_decision'     => $decision,
            ]);

            Log::info("[CancellationAuditService] Cancel request reviewed by admin #{$adminUser->id}", [
                'request_id' => $lockedReq->id,
                'decision'   => $decision,
                'sp_target'  => $spTarget,
            ]);

            return $lockedReq;
        });
    }
}
