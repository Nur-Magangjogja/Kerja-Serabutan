<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use App\Services\Cancellation\CancellationAuditService;
use App\Services\Cancellation\CancellationSettlementService;
use App\Services\Cancellation\OnSiteCancellationService;
use App\Services\Cancellation\PickupDeliveryCancellationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpCancellationService
{
    protected HelpEscrowService $escrowService;
    protected PartnerOnlineService $onlineService;
    protected PartnerDisciplineService $disciplineService;
    protected OnSiteCancellationService $onSiteCancellation;
    protected PickupDeliveryCancellationService $pickupCancellation;
    protected CancellationAuditService $auditService;
    protected CancellationSettlementService $settlementService;

    public function __construct(
        HelpEscrowService $escrowService,
        PartnerOnlineService $onlineService,
        PartnerDisciplineService $disciplineService,
        OnSiteCancellationService $onSiteCancellation,
        PickupDeliveryCancellationService $pickupCancellation,
        CancellationAuditService $auditService,
        CancellationSettlementService $settlementService
    ) {
        $this->escrowService      = $escrowService;
        $this->onlineService      = $onlineService;
        $this->disciplineService  = $disciplineService;
        $this->onSiteCancellation = $onSiteCancellation;
        $this->pickupCancellation = $pickupCancellation;
        $this->auditService       = $auditService;
        $this->settlementService  = $settlementService;
    }

    /**
     * Membatalkan pesanan langsung saat masih dalam status MENUNGGU_MITRA (belum diambil mitra manapun).
     * Berlaku untuk semua jenis layanan: 100% total bayar dikembalikan ke saldo customer.
     */
    public function cancelOrderBeforePartnerTaken(Help $help, User $customer, string $reason = 'Dibatalkan oleh customer sebelum diambil mitra'): void
    {
        if ($help->isPickup()) {
            $this->pickupCancellation->executeCancellation(
                $help,
                $customer,
                $reason
            );
            return;
        }

        $this->settlementService->processFullRefund($help, $reason);
    }

    /**
     * Membatalkan pesanan yang kadaluwarsa (expired) secara otomatis.
     * Mengembalikan 100% dana escrow ke customer dan menutup status pesanan.
     */
    public function autoCancelExpiredHelp(Help $help, string $reason = 'Batas waktu pencarian Rekan Jasa telah berakhir'): void
    {
        DB::transaction(function () use ($help, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA || $lockedHelp->mitra_id !== null) {
                return;
            }

            if ($lockedHelp->isPickup()) {
                if ($lockedHelp->user) {
                    $this->pickupCancellation->executeCancellation($lockedHelp, $lockedHelp->user, $reason);
                } else {
                    $this->settlementService->processFullRefund($lockedHelp, $reason);
                }
            } else {
                $this->settlementService->processFullRefund($lockedHelp, $reason);
            }

            $lockedHelp->update([
                'admin_notes' => "Dibatalkan otomatis oleh sistem. Alasan: {$reason}",
            ]);
        });
    }

    /**
     * Menyapu dan membatalkan seluruh pesanan menunggu mitra yang sudah melewati batas waktu kadaluwarsa.
     */
    public function sweepAndAutoCancelExpiredHelps(?int $userId = null): int
    {
        $now = now();
        $fallbackHours = AppSetting::getHelpAutoCancelHours();
        $fallbackCutoff = $now->copy()->subHours($fallbackHours);

        $query = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->where(function ($q) use ($now, $fallbackCutoff) {
                $q->where(function ($sub) use ($now) {
                    $sub->whereNotNull('expires_at')
                        ->where('expires_at', '<=', $now);
                })->orWhere(function ($sub) use ($fallbackCutoff) {
                    $sub->whereNull('expires_at')
                        ->where('created_at', '<=', $fallbackCutoff);
                });
            });

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $expiredHelps = $query->take(20)->get();
        $count = 0;

        foreach ($expiredHelps as $help) {
            try {
                $reason = ($help->expires_at && \Carbon\Carbon::parse($help->expires_at)->isPast())
                    ? 'Batas waktu pencarian Rekan Jasa yang ditentukan telah berakhir'
                    : "Tidak ada Rekan Jasa yang mengambil bantuan dalam batas waktu {$fallbackHours} jam";

                $this->autoCancelExpiredHelp($help, $reason);
                $count++;
            } catch (\Throwable $e) {
                Log::warning("[HelpCancellationService] Failed to auto-cancel expired help #{$help->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // KHUSUS LAYANAN ANTAR / JEMPUT (PICKUP & DELIVERY)
    // SISTEM PEMBATALAN BERBASIS TAHAPAN PERJALANAN (ANTI-BYPASS LOCK)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Hitung kalkulasi kompensasi mitra dan refund customer untuk pembatalan pickup_delivery.
     * Mengembalikan [
     *    'stage' => string,
     *    'allowed' => bool,
     *    'partner_compensation' => float,
     *    'customer_refund' => float,
     *    'reason' => string
     * ]
     */
    public function calculatePickupDeliveryCancellationSplit(Help $help): array
    {
        $eligibility = $this->pickupCancellation->evaluateCancellationEligibility($help);
        $financials  = $this->pickupCancellation->calculateCancellationFinancials(
            $help,
            $eligibility['condition'],
            $eligibility['d_cancel'] ?? 0.0
        );

        return [
            'phase'                => ($eligibility['condition'] === 'A_UNASSIGNED' || $eligibility['condition'] === 'B_PRE_DEPARTURE') ? 1 : (($eligibility['condition'] === 'C_PICKUP_TRAVEL') ? 2 : (($eligibility['condition'] === 'D_AT_PICKUP') ? 3 : 4)),
            'stage'                => $help->service_stage ?: $help->status,
            'allowed'              => $eligibility['allowed'],
            'travel_km'            => $financials['d_compensated_km'] ?? 1.0,
            'partner_compensation' => $financials['compensation_mitra'],
            'customer_refund'      => $financials['refund_customer'],
            'reason'               => $eligibility['reason'] ?? "Kalkulasi pembatalan tahap {$eligibility['condition']}.",
        ];
    }

    /**
     * Customer membatalkan pesanan Antar/Jemput (pickup_delivery) dengan kalkulasi kompensasi tahapan.
     */
    public function cancelPickupDeliveryByCustomer(
        Help $help,
        User $customer,
        string $reason,
        ?float $partnerLat = null,
        ?float $partnerLng = null
    ): void {
        $this->pickupCancellation->executeCancellation(
            $help,
            $customer,
            $reason,
            null,
            null,
            $partnerLat,
            $partnerLng
        );
    }

    /**
     * Mitra memicu Customer No-Show untuk layanan Antar/Jemput (pickup_delivery)
     * setelah tiba di titik jemput dan menunggu minimal 10 menit tanpa respon.
     * Mengakibatkan 100% ongkos antar diteruskan ke mitra.
     */
    public function triggerCustomerNoShow(Help $help, User $partner, ?string $notes = null, ?string $photo = null): void
    {
        DB::transaction(function () use ($help, $partner, $notes, $photo) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra yang ditugaskan pada pesanan ini yang dapat memicu Customer No-Show.');
            }

            if (!$lockedHelp->isPickup()) {
                throw new \RuntimeException('Customer No-Show khusus untuk layanan antar/jemput (pickup_delivery).');
            }

            if (!$lockedHelp->canPartnerTriggerNoShow()) {
                $waitMinutes = AppSetting::getPickupDeliveryNoShowWaitMinutes();
                throw new \RuntimeException("Customer No-Show hanya dapat dipicu setelah menunggu minimal {$waitMinutes} menit di titik penjemputan.");
            }

            $serviceFee = (float) ($lockedHelp->service_fee > 0 ? $lockedHelp->service_fee : $lockedHelp->amount);

            // 100% Service Fee ke Mitra
            if ($serviceFee > 0) {
                $this->escrowService->payoutPartialFromEscrowDirect($lockedHelp, $partner, $serviceFee, 'Kompensasi Customer No-Show Antar/Jemput (100% Ongkos Antar)');
            }

            $lockedHelp->update([
                'status'                => Help::STATUS_DIBATALKAN,
                'dispatch_mode'         => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'         => Help::ESCROW_STATUS_RELEASED,
                'payment_status'        => Help::PAYMENT_STATUS_PAID,
                'cancel_evidence_photo' => $photo,
                'admin_notes'           => "Customer No-Show dipicu oleh Mitra #{$partner->id} ({$partner->name}) setelah masa tunggu 10 menit terlewati. 100% ongkos antar (Rp " . number_format($serviceFee, 0, ',', '.') . ") diteruskan ke saldo mitra.",
            ]);

            $this->onlineService->releaseBusy($partner->id, $lockedHelp->id);

            if ($lockedHelp->user) {
                try {
                    $lockedHelp->user->notify(new HelpStatusNotification(
                        $lockedHelp,
                        "Pesanan #{$lockedHelp->id} dibatalkan karena Anda tidak hadir/merespons di titik penjemputan lebih dari 10 menit. Ongkos antar telah diteruskan ke mitra."
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying customer of no-show: " . $e->getMessage());
                }
            }

            Log::info("[HelpCancellationService] Customer No-Show triggered by Partner #{$partner->id} on Help #{$lockedHelp->id}.");
        });
    }

    /**
     * Mitra mengajukan pembatalan pesanan.
     * Untuk Layanan Biasa (On-Site): Otomatis relist ke pool mitra baru & buat tiket audit untuk admin wilayah (Konsep 1).
     * Untuk Pickup & Delivery: Menggunakan alur pembatalan bertahap dengan kalkulasi kompensasi perjalanan.
     */
    public function submitPartnerCancelRequest(
        Help $help,
        User $partner,
        string $reason,
        ?string $notes = null,
        ?string $evidencePhoto = null,
        bool $itemPurchased = false,
        float $itemPurchaseAmount = 0.0,
        float $workCompletedPercentage = 0.0,
        int $deadlineMinutes = 30
    ): HelpCancelRequest {
        if ($help->isOnSite() || $help->isRegular()) {
            $result = $this->onSiteCancellation->cancelByPartner(
                $help,
                $partner,
                $reason,
                $evidencePhoto,
                $notes
            );
            return HelpCancelRequest::findOrFail($result['cancel_request_id']);
        }

        // Untuk pickup delivery:
        $result = $this->pickupCancellation->executeCancellation(
            $help,
            $partner,
            $reason,
            $evidencePhoto,
            $notes
        );
        return HelpCancelRequest::findOrFail($result['cancel_request_id']);
    }

    /**
     * Backward-compatible alias for partner cancellation.
     */
    public function cancelByPartnerUnilaterally(
        Help $help,
        User $partner,
        string $reason,
        ?string $notes = null,
        ?string $evidencePhoto = null
    ): HelpCancelRequest {
        return $this->submitPartnerCancelRequest(
            $help,
            $partner,
            $reason,
            $notes,
            $evidencePhoto
        );
    }

    /**
     * Customer memilih ganti mitra (untuk On-Site / Regular Service).
     * Melepaskan mitra lama yang tidak responsif/tidak kunjung datang dan me-relist order ke pool.
     */
    public function switchPartnerByCustomer(
        Help $help,
        User $customer,
        string $reason = 'Mitra tidak bergerak / tidak merespons chat',
        ?string $notes = null
    ): HelpCancelRequest {
        $result = $this->onSiteCancellation->switchPartnerByCustomer(
            $help,
            $customer,
            $reason,
            $notes
        );

        return HelpCancelRequest::findOrFail($result['cancel_request_id']);
    }

    /**
     * Customer mengajukan penarikan pekerjaan (Batal Total & Full Refund).
     * Untuk Layanan Biasa (On-Site): Meminta konfirmasi mitra / diaudit Admin Wilayah jika mitra menolak.
     * Untuk Pickup & Delivery: Menggunakan formula kompensasi bertahap.
     */
    public function submitCustomerCancelRequest(
        Help $help,
        User $customer,
        string $reason,
        ?string $notes = null,
        ?string $evidencePhoto = null,
        int $deadlineMinutes = 30
    ): HelpCancelRequest {
        if ($help->isOnSite() || $help->isRegular()) {
            $result = $this->onSiteCancellation->requestWithdrawByCustomer(
                $help,
                $customer,
                $reason,
                $evidencePhoto,
                $notes
            );

            if (isset($result['cancel_request_id'])) {
                return HelpCancelRequest::findOrFail($result['cancel_request_id']);
            }

            // Jika langsung dibatalkan (misal belum ada mitra / unassigned):
            return HelpCancelRequest::create([
                'help_id'                 => $help->id,
                'requester_type'          => HelpCancelRequest::REQUESTER_CUSTOMER,
                'action_type'             => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
                'customer_id'             => $customer->id,
                'partner_id'              => $help->mitra_id,
                'district_id'             => $help->district_id,
                'previous_status'         => $help->status ?? Help::STATUS_MENUNGGU_MITRA,
                'previous_stage'          => $help->service_stage,
                'reason'                  => $reason,
                'notes'                   => $notes,
                'evidence_photo'          => $evidencePhoto,
                'status'                  => HelpCancelRequest::STATUS_APPROVED,
                'settlement_type'         => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                'refund_amount_customer'  => (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount),
                'payout_amount_mitra'     => 0,
                'requested_at'            => now(),
                'reviewed_at'             => now(),
                'audit_decision'          => HelpCancelRequest::AUDIT_VALID_NO_SP,
            ]);
        }

        $result = $this->pickupCancellation->executeCancellation(
            $help,
            $customer,
            $reason,
            $evidencePhoto,
            $notes
        );
        return HelpCancelRequest::findOrFail($result['cancel_request_id']);
    }

    /**
     * Mitra merespons pengajuan penarikan pekerjaan oleh Customer (Setujui atau Tolak/Pembelaan).
     */
    public function respondWithdrawByPartner(
        HelpCancelRequest $request,
        User $partner,
        bool $isConfirmed,
        ?string $notes = null,
        ?string $photo = null
    ): array {
        return $this->onSiteCancellation->respondWithdrawByPartner(
            $request,
            $partner,
            $isConfirmed,
            $notes,
            $photo
        );
    }

    /**
     * Mitra memberikan klarifikasi / tanggapan opsional atas pengajuan pembatalan Customer.
     */
    public function submitPartnerClarification(
        HelpCancelRequest $request,
        User $partner,
        string $clarification,
        ?string $photo = null
    ): void {
        DB::transaction(function () use ($request, $partner, $clarification, $photo) {
            $lockedReq = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->partner_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra terkait yang dapat memberikan klarifikasi.');
            }

            $lockedReq->update([
                'partner_clarification'       => $clarification,
                'partner_clarification_photo' => $photo,
                'partner_clarified_at'        => now(),
            ]);

            Log::info("[HelpCancellationService] Partner #{$partner->id} provided clarification for Cancel Request #{$lockedReq->id}.");
        });
    }

    /**
     * Customer menyetujui pembatalan dari mitra.
     * Mengakibatkan 100% refund escrow ke customer dan pesanan resmi dibatalkan.
     */
    public function customerAcceptPartnerCancellation(Help $help, User $customer): void
    {
        DB::transaction(function () use ($help, $customer) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->user_id !== $customer->id) {
                throw new \RuntimeException('Hanya customer pemilik pesanan yang dapat menyetujui pembatalan.');
            }

            if ($lockedHelp->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Pesanan tidak sedang dalam pengajuan pembatalan mitra.');
            }

            $totalPaid = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

            if ($totalPaid > 0) {
                $this->escrowService->refundFromEscrowDirect($lockedHelp, $customer, $totalPaid, 'Customer Menerima Pembatalan Mitra');
            }

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'admin_notes'    => "Customer menyetujui pembatalan dari Mitra. Pengembalian 100% Saldo Escrow berhasil.",
            ]);

            // Update tiket cancel request jika ada
            HelpCancelRequest::where('help_id', $lockedHelp->id)
                ->where('status', HelpCancelRequest::STATUS_PENDING)
                ->update([
                    'status'                 => HelpCancelRequest::STATUS_APPROVED,
                    'settlement_type'        => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                    'refund_amount_customer' => $totalPaid,
                    'payout_amount_mitra'    => 0,
                    'reviewed_at'            => now(),
                    'admin_notes'            => 'Disetujui langsung oleh Customer.',
                ]);

            Log::info("[HelpCancellationService] Customer #{$customer->id} accepted cancellation for Help #{$lockedHelp->id}.");
        });
    }

    /**
     * Periksa dan otomatis batalkan pesanan yang batas waktu konfirmasinya telah habis (expired).
     */
    public function checkAndAutoCancelExpiredRequests(): int
    {
        $expiredRequests = HelpCancelRequest::with(['help.user', 'help.mitra'])
            ->where('status', HelpCancelRequest::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $processed = 0;

        foreach ($expiredRequests as $request) {
            try {
                DB::transaction(function () use ($request) {
                    $lockedReq  = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->first();
                    if (!$lockedReq || $lockedReq->status !== HelpCancelRequest::STATUS_PENDING) {
                        return;
                    }

                    $lockedHelp = Help::where('id', $lockedReq->help_id)->lockForUpdate()->first();
                    if (!$lockedHelp) {
                        return;
                    }

                    if (in_array($lockedHelp->status, [Help::STATUS_PARTNER_CANCEL_REQUESTED, Help::STATUS_CUSTOMER_CANCEL_REQUESTED])) {
                        $totalPaid = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

                        if ($totalPaid > 0 && $lockedHelp->user) {
                            $this->escrowService->refundFromEscrowDirect($lockedHelp, $lockedHelp->user, $totalPaid, 'Otomatis Batal: Batas Waktu Konfirmasi Berakhir');
                        }

                        $lockedHelp->update([
                            'status'         => Help::STATUS_DIBATALKAN,
                            'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                            'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                            'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                            'admin_notes'    => "Otomatis dibatalkan sistem karena batas waktu konfirmasi telah terlewati.",
                        ]);

                        if ($lockedHelp->mitra_id) {
                            $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
                        }

                        $lockedReq->update([
                            'status'                 => HelpCancelRequest::STATUS_APPROVED,
                            'settlement_type'        => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                            'refund_amount_customer' => $totalPaid,
                            'payout_amount_mitra'    => 0,
                            'reviewed_at'            => now(),
                            'admin_notes'            => 'Otomatis disetujui sistem (Batas waktu expired).',
                        ]);
                    }
                });

                $processed++;
            } catch (\Throwable $e) {
                Log::error("[HelpCancellationService] Error auto-cancelling expired request #{$request->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }

    /**
     * Peninjauan manual oleh Admin Wilayah.
     * Mengatur persetujuan pembatalan, penyelesaian keuangan, serta pemberian SP kepada Mitra, Customer, atau Kedua-duanya.
     */
    public function reviewByAdmin(
        HelpCancelRequest $request,
        User $admin,
        bool $isApproved,
        string $settlementType = HelpCancelRequest::SETTLEMENT_FULL_REFUND,
        array $extraData = []
    ): void {
        $spTarget         = $extraData['sp_target'] ?? HelpCancelRequest::SP_TARGET_NONE;
        $partnerSpLevel   = isset($extraData['partner_sp_level']) ? (int) $extraData['partner_sp_level'] : null;
        $partnerSpReason  = $extraData['partner_sp_reason'] ?? 'Pelanggaran pembatalan tugas bantuan';
        $customerSpLevel  = isset($extraData['customer_sp_level']) ? (int) $extraData['customer_sp_level'] : null;
        $customerSpReason = $extraData['customer_sp_reason'] ?? 'Pelanggaran / kejanggalan dalam pesanan';
        $adminNotes       = $extraData['admin_notes'] ?? null;
        $refundAmount     = isset($extraData['refund_amount']) ? (float) $extraData['refund_amount'] : null;
        $partnerAmount    = isset($extraData['partner_amount']) ? (float) $extraData['partner_amount'] : null;

        $decision = $isApproved ? ($spTarget !== HelpCancelRequest::SP_TARGET_NONE ? 'penalty_issued' : 'valid_no_sp') : 'rejected';

        $this->auditService->reviewCancelRequest(
            $request,
            $admin,
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
        );
    }

    /**
     * Alias method for backward compatibility.
     */
    public function approveCancellation(
        HelpCancelRequest $request,
        User $admin,
        string $settlementType = HelpCancelRequest::SETTLEMENT_FULL_REFUND,
        ?string $adminNotes = null,
        ?float $customRefund = null,
        ?float $customPayout = null
    ): void {
        $this->reviewByAdmin(
            $request,
            $admin,
            true,
            $settlementType,
            [
                'admin_notes'    => $adminNotes,
                'refund_amount'  => $customRefund,
                'partner_amount' => $customPayout,
            ]
        );
    }

    /**
     * Alias method for backward compatibility.
     */
    public function rejectCancellation(
        HelpCancelRequest $request,
        User $admin,
        ?string $adminNotes = null
    ): void {
        $this->reviewByAdmin(
            $request,
            $admin,
            false,
            HelpCancelRequest::SETTLEMENT_FULL_REFUND,
            [
                'admin_notes' => $adminNotes,
            ]
        );
    }
}
