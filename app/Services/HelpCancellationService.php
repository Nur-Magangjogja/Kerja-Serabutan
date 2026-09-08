<?php

namespace App\Services;

use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpCancellationService
{
    protected HelpTransactionService $transactionService;
    protected PartnerOnlineService $onlineService;

    public function __construct(HelpTransactionService $transactionService, PartnerOnlineService $onlineService)
    {
        $this->transactionService = $transactionService;
        $this->onlineService      = $onlineService;
    }

    /**
     * Alias method accepting an associative array.
     */
    public function submitRequest(Help $help, User $partner, array $data): HelpCancelRequest
    {
        return $this->submitCancellationRequest(
            $help,
            $partner,
            $data['reason'] ?? 'Kendala operasional',
            $data['notes'] ?? null,
            $data['evidence_photo'] ?? null,
            (bool) ($data['item_purchased'] ?? false),
            (float) ($data['item_purchase_amount'] ?? 0.0),
            (float) ($data['work_completed_percentage'] ?? 0.0)
        );
    }

    /**
     * Mitra membatalkan pesanan secara sepihak (Direct Unilateral Cancellation).
     * Pesanan langsung dilepaskan kembali ke pool/pencarian tanpa menunggu customer.
     * Sebagai konsekuensinya, mitra dikenakan poin penalti kedisiplinan yang dapat berujung pada SP 1 - SP 3 / Shadow Ban.
     */
    public function cancelByPartnerUnilaterally(
        Help $help,
        User $partner,
        string $reason,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($help, $partner, $reason, $notes) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra yang ditugaskan pada pesanan ini yang dapat melakukan pembatalan.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, 'completed', Help::STATUS_DIBATALKAN, 'cancelled'])) {
                throw new \RuntimeException('Pesanan ini sudah selesai atau telah dibatalkan.');
            }

            // 1. Tambahkan mitra ke daftar exclusion bantuan ini (agar tidak ditawarkan kembali ke mitra ini)
            if (\Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions')) {
                \App\Models\HelpPartnerExclusion::firstOrCreate([
                    'help_id'  => $lockedHelp->id,
                    'mitra_id' => $partner->id,
                ]);
            }

            $cancelledMitraIds = $lockedHelp->cancelled_mitra_ids ?? [];
            if (!is_array($cancelledMitraIds)) {
                $cancelledMitraIds = json_decode((string) $cancelledMitraIds, true) ?? [];
            }
            if (!in_array($partner->id, $cancelledMitraIds)) {
                $cancelledMitraIds[] = $partner->id;
            }

            // 2. Kembalikan bantuan ke status 'menunggu_mitra' (Pool/Searching) agar customer tidak tertahan
            $lockedHelp->update([
                'mitra_id'                   => null,
                'status'                     => Help::STATUS_MENUNGGU_MITRA,
                'dispatch_mode'              => Help::DISPATCH_MODE_POOL,
                'service_stage'              => null,
                'pool_opened_at'             => now(),
                'taken_at'                   => null,
                'mitra_assigned_at'          => null,
                'partner_started_at'         => null,
                'partner_arrived_at'         => null,
                'partner_started_moving_at'  => null,
                'partner_current_lat'        => null,
                'partner_current_lng'        => null,
                'departure_at'               => null,
                'cancelled_mitra_ids'        => $cancelledMitraIds,
                'partner_cancel_prev_status' => null,
                'partner_cancel_reason'      => $reason,
                'partner_cancel_notes'       => $notes,
            ]);

            // 3. Bebaskan status online mitra (lepas dari busy)
            $this->onlineService->releaseFromCancelledHelp($partner->id, $lockedHelp->id);

            // 4. Catat konsekuensi poin pelanggaran dan evaluasi eskalasi Surat Peringatan (SP)
            app(\App\Services\PartnerDisciplineService::class)->recordPartnerCancellation($partner, $lockedHelp, $reason);

            // 5. Kirim notifikasi ke Customer bahwa mitra membatalkan dan sistem mencari rekan baru
            if ($lockedHelp->user) {
                try {
                    $lockedHelp->user->notify(new \App\Notifications\HelpStatusNotification(
                        $lockedHelp,
                        "Mitra {$partner->name} membatalkan pesanan (Alasan: {$reason}). Sistem otomatis mencarikan Rekan Jasa pengganti untuk Anda."
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying customer: " . $e->getMessage());
                }
            }

            Log::info("[HelpCancellationService] Partner #{$partner->id} cancelled Help #{$lockedHelp->id} unilaterally with SP penalty evaluation.");
        });
    }

    /**
     * Mitra mengajukan pembatalan dengan alasan kendala di lapangan dan bukti verifikasi.
     */
    public function submitCancellationRequest(
        Help $help,
        User $partner,
        string $reason,
        ?string $notes = null,
        ?string $evidencePhoto = null,
        bool $itemPurchased = false,
        float $itemPurchaseAmount = 0.0,
        float $workCompletedPercentage = 0.0
    ): HelpCancelRequest {
        return DB::transaction(function () use (
            $help,
            $partner,
            $reason,
            $notes,
            $evidencePhoto,
            $itemPurchased,
            $itemPurchaseAmount,
            $workCompletedPercentage
        ) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra yang ditugaskan pada pesanan ini yang dapat mengajukan pembatalan.');
            }

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;

            $request = HelpCancelRequest::create([
                'help_id'                   => $lockedHelp->id,
                'partner_id'                => $partner->id,
                'district_id'               => $lockedHelp->district_id,
                'previous_status'           => $prevStatus,
                'previous_stage'            => $prevStage,
                'reason'                    => $reason,
                'notes'                     => $notes,
                'evidence_photo'            => $evidencePhoto,
                'item_purchased'            => $itemPurchased,
                'item_purchase_amount'      => $itemPurchaseAmount,
                'work_completed_percentage' => $workCompletedPercentage,
                'status'                    => HelpCancelRequest::STATUS_PENDING,
                'requested_at'              => now(),
            ]);

            $lockedHelp->update([
                'status'                      => Help::STATUS_PARTNER_CANCEL_REQUESTED,
                'partner_cancel_prev_status'  => $prevStatus,
                'partner_cancel_reason'       => $reason,
                'partner_cancel_notes'        => $notes,
                'partner_cancel_requested_at' => now(),
            ]);

            Log::info("[HelpCancellationService] Partner #{$partner->id} submitted Cancel Request #{$request->id} for Help #{$lockedHelp->id}.");
            return $request;
        });
    }

    /**
     * Facade method for Admin Review modal.
     */
    public function reviewByAdmin(
        HelpCancelRequest $request,
        User $admin,
        bool $isApproved,
        string $settlementType = HelpCancelRequest::SETTLEMENT_FULL_REFUND,
        array $extraData = []
    ): void {
        if ($isApproved) {
            $adminNotes    = $extraData['admin_notes'] ?? null;
            $customRefund  = isset($extraData['refund_amount']) && $extraData['refund_amount'] !== '' ? (float) $extraData['refund_amount'] : null;
            $customPartner = isset($extraData['partner_amount']) && $extraData['partner_amount'] !== '' ? (float) $extraData['partner_amount'] : null;

            $this->approveCancellation(
                $request,
                $admin,
                $settlementType,
                $adminNotes,
                $customRefund,
                $customPartner
            );
        } else {
            $this->rejectCancellation($request, $admin, $extraData['admin_notes'] ?? null);
        }
    }

    /**
     * Admin Kecamatan menyetujui pembatalan dengan penyelesaian keuangan state-aware.
     */
    public function approveCancellation(
        HelpCancelRequest $request,
        User $admin,
        string $settlementType = HelpCancelRequest::SETTLEMENT_FULL_REFUND,
        ?string $adminNotes = null,
        ?float $customRefund = null,
        ?float $customPayout = null
    ): void {
        DB::transaction(function () use ($request, $admin, $settlementType, $adminNotes, $customRefund, $customPayout) {
            $lockedReq  = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            $lockedHelp = Help::where('id', $lockedReq->help_id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->status !== HelpCancelRequest::STATUS_PENDING) {
                throw new \RuntimeException('Pengajuan pembatalan ini sudah pernah diproses sebelumnya.');
            }

            $totalPaid = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : ($lockedHelp->amount + ($lockedHelp->travel_fee ?? 0) + ($lockedHelp->item_fund ?? 0) + ($lockedHelp->platform_fee_amount ?? 2000)));
            $itemFund  = (float) ($lockedHelp->item_fund ?? 0);
            $serviceFee = (float) ($lockedHelp->service_fee > 0 ? $lockedHelp->service_fee : $lockedHelp->amount);

            $refundCustomer = 0.0;
            $payoutMitra    = 0.0;

            if ($customRefund !== null && $customPayout !== null) {
                // Gunakan nilai eksplisit hasil peninjauan admin
                $refundCustomer = max(0.0, $customRefund);
                $payoutMitra    = max(0.0, $customPayout);
            } else {
                // State-Aware Financial Settlement Logic
                if ($settlementType === HelpCancelRequest::SETTLEMENT_FULL_REFUND) {
                    // Layanan belum dimulai & barang belum dibeli -> 100% Refund ke customer
                    $refundCustomer = $totalPaid;
                    $payoutMitra    = 0.0;
                } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_ITEM_SETTLED) {
                    // Barang sudah dibeli oleh mitra -> Ganti dana barang ke mitra, refund jasa ke customer
                    $actualItemCost = min($itemFund > 0 ? $itemFund : $lockedReq->item_purchase_amount, $lockedReq->item_purchase_amount);
                    $payoutMitra    = $actualItemCost;
                    $refundCustomer = max(0.0, $totalPaid - $payoutMitra);
                } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT) {
                    // Sebagian pekerjaan telah dilakukan (misal 50%) -> Mitra dapat kompensasi parsial
                    $pct = min(1.0, max(0.0, ((float) $lockedReq->work_completed_percentage) / 100.0));
                    $payoutMitra    = round($serviceFee * $pct, 2);
                    $refundCustomer = max(0.0, $totalPaid - $payoutMitra);
                } else {
                    $refundCustomer = $totalPaid;
                }
            }

            // Eksekusi mutasi saldo via HelpTransactionService
            if ($refundCustomer > 0 && $lockedHelp->user) {
                $this->transactionService->refundFromEscrowDirect($lockedHelp, $lockedHelp->user, $refundCustomer, 'Pembatalan Disetujui Admin');
            }

            if ($payoutMitra > 0 && $lockedHelp->mitra) {
                $this->transactionService->payoutPartialFromEscrowDirect($lockedHelp, $lockedHelp->mitra, $payoutMitra, 'Kompensasi Pembatalan oleh Admin');
            }

            $lockedReq->update([
                'status'                 => HelpCancelRequest::STATUS_APPROVED,
                'settlement_type'        => $settlementType,
                'refund_amount_customer' => $refundCustomer,
                'payout_amount_mitra'    => $payoutMitra,
                'reviewed_by'            => $admin->id,
                'admin_notes'            => $adminNotes,
                'reviewed_at'            => now(),
            ]);

            $escrowFinalStatus = ($refundCustomer > 0 && $payoutMitra > 0)
                ? Help::ESCROW_STATUS_PARTIAL_REFUND
                : ($refundCustomer > 0 ? Help::ESCROW_STATUS_REFUNDED : Help::ESCROW_STATUS_RELEASED);

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => $escrowFinalStatus,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'admin_notes'    => "Pembatalan disetujui Admin #{$admin->id} ({$admin->name}). Resolusi: {$settlementType}. Refund: Rp " . number_format($refundCustomer, 0, ',', '.') . ", Kompensasi Mitra: Rp " . number_format($payoutMitra, 0, ',', '.'),
            ]);

            // Bebaskan status online mitra tanpa penalti (Zero Penalty)
            if ($lockedHelp->mitra_id) {
                $this->onlineService->releaseFromCancelledHelp($lockedHelp->mitra_id, $lockedHelp->id);
            }

            Log::info("[HelpCancellationService] Admin #{$admin->id} APPROVED Cancel Request #{$lockedReq->id} for Help #{$lockedHelp->id}.");
        });
    }

    /**
     * Admin Kecamatan menolak pembatalan mitra (Kembalikan ke status pengerjaan semula).
     */
    public function rejectCancellation(
        HelpCancelRequest $request,
        User $admin,
        ?string $adminNotes = null
    ): void {
        DB::transaction(function () use ($request, $admin, $adminNotes) {
            $lockedReq  = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            $lockedHelp = Help::where('id', $lockedReq->help_id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->status !== HelpCancelRequest::STATUS_PENDING) {
                throw new \RuntimeException('Pengajuan pembatalan ini sudah pernah diproses sebelumnya.');
            }

            $restoredStatus = $lockedReq->previous_status ?: Help::STATUS_TAKEN;
            $restoredStage  = $lockedReq->previous_stage;

            $lockedReq->update([
                'status'      => HelpCancelRequest::STATUS_REJECTED,
                'reviewed_by' => $admin->id,
                'admin_notes' => $adminNotes,
                'reviewed_at' => now(),
            ]);

            $lockedHelp->update([
                'status'        => $restoredStatus,
                'service_stage' => $restoredStage,
            ]);

            Log::info("[HelpCancellationService] Admin #{$admin->id} REJECTED Cancel Request #{$lockedReq->id}. Restored to status '{$restoredStatus}'.");
        });
    }
}
