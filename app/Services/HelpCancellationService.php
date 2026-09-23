<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\HelpPartnerExclusion;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use App\Services\Cancellation\CancellationAuditService;
use App\Services\Cancellation\CancellationSettlementService;
use App\Services\Cancellation\OnSiteCancellationService;
use App\Services\Cancellation\PickupDeliveryCancellationService;
use App\Services\HelpChatService;
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
    protected HelpChatService $chatService;

    public function __construct(
        HelpEscrowService $escrowService,
        PartnerOnlineService $onlineService,
        PartnerDisciplineService $disciplineService,
        OnSiteCancellationService $onSiteCancellation,
        PickupDeliveryCancellationService $pickupCancellation,
        CancellationAuditService $auditService,
        CancellationSettlementService $settlementService,
        ?HelpChatService $chatService = null
    ) {
        $this->escrowService      = $escrowService;
        $this->onlineService      = $onlineService;
        $this->disciplineService  = $disciplineService;
        $this->onSiteCancellation = $onSiteCancellation;
        $this->pickupCancellation = $pickupCancellation;
        $this->auditService       = $auditService;
        $this->settlementService  = $settlementService;
        $this->chatService        = $chatService ?? app(HelpChatService::class);
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

        // Auto-recover/heal stranded 'seeking' tasks without active dispatch offers to Open Pool
        Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->where('dispatch_mode', Help::DISPATCH_MODE_SEEKING)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->update([
                'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
                'pool_opened_at' => now(),
            ]);

        // Auto-recover expired/finished offers that never opened to pool
        $staleOfferedHelps = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->where('dispatch_mode', Help::DISPATCH_MODE_OFFERED)
            ->whereDoesntHave('dispatches', function ($d) {
                $d->where('status', \App\Models\HelpDispatch::STATUS_OFFERED)
                  ->where('expires_at', '>', now());
            })
            ->get();

        foreach ($staleOfferedHelps as $stale) {
            $stale->update([
                'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
                'pool_opened_at' => now(),
            ]);
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
                'admin_notes'           => "Customer No-Show dipicu oleh Mitra {$partner->name} setelah masa tunggu 10 menit terlewati. 100% ongkos antar (Rp " . number_format($serviceFee, 0, ',', '.') . ") diteruskan ke saldo mitra.",
            ]);

            $this->onlineService->releaseBusy($partner->id, $lockedHelp->id);

            $customer = $lockedHelp->user ?? User::find($lockedHelp->user_id);

            if ($customer) {
                try {
                    $customer->notify(new HelpStatusNotification(
                        $lockedHelp,
                        "Pesanan Anda dibatalkan karena Anda tidak hadir/merespons di titik penjemputan lebih dari 10 menit. Ongkos antar telah diteruskan ke mitra."
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying customer of no-show: " . $e->getMessage());
                }

                // Kirim notifikasi chat pembatalan no-show
                $this->chatService->sendNoShowCancellationChat($lockedHelp, $partner, $customer);
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

            $partnerId = $lockedHelp->mitra_id;

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'admin_notes'    => "Customer menyetujui pembatalan dari Mitra. Pengembalian 100% Saldo Escrow berhasil.",
            ]);

            if ($partnerId) {
                $this->onlineService->releaseBusy($partnerId, $lockedHelp->id);
            }

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

            $mitra = $partnerId ? ($lockedHelp->mitra ?? User::find($partnerId)) : null;
            if ($mitra) {
                $this->chatService->sendCustomerResolutionToPartnerCancelChat($lockedHelp, $customer, $mitra, 'accepted');
            }

            Log::info("[HelpCancellationService] Customer #{$customer->id} accepted cancellation for Help #{$lockedHelp->id}.");
        });
    }

    /**
     * Customer memilih mencari rekan jasa pengganti (kembalikan ke pool) saat mitra mengajukan pembatalan.
     * Syarat: Batas waktu pencarian pesanan (effective_expires_at) belum berakhir.
     */
    public function customerRelistPartnerCancellation(Help $help, User $customer): void
    {
        DB::transaction(function () use ($help, $customer) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->user_id !== $customer->id) {
                throw new \RuntimeException('Hanya customer pemilik pesanan yang dapat mengelola pembatalan.');
            }

            if ($lockedHelp->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Pesanan tidak sedang dalam pengajuan pembatalan mitra.');
            }

            $effectiveExpiry = $lockedHelp->effective_expires_at;
            if ($effectiveExpiry && now()->gte($effectiveExpiry)) {
                throw new \RuntimeException('Batas waktu pencarian pesanan telah berakhir. Silakan pilih opsi Batalkan & Tarik Saldo (Refund 100%).');
            }

            $oldPartnerId = $lockedHelp->mitra_id;
            $oldMitra = $oldPartnerId ? ($lockedHelp->mitra ?? User::find($oldPartnerId)) : null;

            // 1. Rekam eksklusi agar mitra yang membatalkan tidak mengambil order yang sama kembali
            if ($oldPartnerId) {
                $lockedHelp->addExcludedPartner($oldPartnerId, "Customer memilih mencari mitra pengganti atas pembatalan mitra #{$oldPartnerId}.");

                // 2. Lepaskan status busy mitra lama
                $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);
            }

            // 3. Kembalikan tugas ke pool pencarian rekan jasa baru (STATUS_MENUNGGU_MITRA)
            $lockedHelp->update([
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
                'partner_cancel_reason'       => null,
                'dispatch_mode'               => Help::DISPATCH_MODE_POOL,
                'pool_opened_at'              => now(),
                'admin_notes'                 => "Customer memilih mencari mitra pengganti atas pengajuan kendala Mitra sebelumnya. Tugas dialihkan kembali ke pool.",
            ]);

            // 4. Update tiket audit cancel request jika ada
            HelpCancelRequest::where('help_id', $lockedHelp->id)
                ->where('status', HelpCancelRequest::STATUS_PENDING)
                ->update([
                    'status'                 => HelpCancelRequest::STATUS_APPROVED,
                    'settlement_type'        => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                    'refund_amount_customer' => 0,
                    'payout_amount_mitra'    => 0,
                    'reviewed_at'            => now(),
                    'admin_notes'            => 'Disetujui oleh Customer untuk mencari rekan jasa pengganti (Kembalikan ke Pool).',
                ]);

            if ($oldMitra) {
                $this->chatService->sendCustomerResolutionToPartnerCancelChat($lockedHelp, $customer, $oldMitra, 'relisted');
            }

            Log::info("[HelpCancellationService] Customer #{$customer->id} relisted Help #{$lockedHelp->id} back to pool after partner cancel request.");
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
                        $isSwitchPartner = ($lockedReq->action_type === HelpCancelRequest::ACTION_SWITCH_PARTNER) 
                            || ($lockedReq->settlement_type === HelpCancelRequest::SETTLEMENT_RELIST_POOL);

                        if ($isSwitchPartner) {
                            // Lepaskan mitra lama, catat eksklusi, dan kembalikan pesanan ke pool
                            $oldPartnerId = $lockedHelp->mitra_id ?: $lockedReq->partner_id;
                            if ($oldPartnerId) {
                                $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);
                                $lockedHelp->addExcludedPartner($oldPartnerId, "Batas waktu konfirmasi mitra kadaluwarsa atas permintaan ganti mitra.");
                            }

                            $lockedHelp->update([
                                'status'              => Help::STATUS_MENUNGGU_MITRA,
                                'mitra_id'            => null,
                                'service_stage'       => null,
                                'cancel_requested_by' => null,
                                'cancel_deadline_at'  => null,
                                'dispatch_mode'       => Help::DISPATCH_MODE_POOL,
                                'pool_opened_at'      => now(),
                                'admin_notes'         => "Otomatis dialihkan ke pool baru oleh sistem karena batas waktu konfirmasi mitra berakhir.",
                            ]);

                            $lockedReq->update([
                                'status'                 => HelpCancelRequest::STATUS_APPROVED,
                                'settlement_type'        => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                                'partner_response_type'  => HelpCancelRequest::PARTNER_RESPONSE_EXPIRED,
                                'refund_amount_customer' => 0,
                                'payout_amount_mitra'    => 0,
                                'reviewed_at'            => now(),
                                'admin_notes'            => 'Otomatis dialihkan ke pool baru (Batas waktu konfirmasi mitra berakhir).',
                            ]);
                        } else {
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
        $isPartnerSp      = in_array($spTarget, [HelpCancelRequest::SP_TARGET_PARTNER, HelpCancelRequest::SP_TARGET_BOTH], true);
        $isCustomerSp     = in_array($spTarget, [HelpCancelRequest::SP_TARGET_CUSTOMER, HelpCancelRequest::SP_TARGET_BOTH], true);

        $partnerSpLevel   = ($isPartnerSp && isset($extraData['partner_sp_level'])) ? (int) $extraData['partner_sp_level'] : null;
        $partnerSpReason  = $isPartnerSp ? ($extraData['partner_sp_reason'] ?? 'Pelanggaran pembatalan tugas bantuan') : null;
        $customerSpLevel  = ($isCustomerSp && isset($extraData['customer_sp_level'])) ? (int) $extraData['customer_sp_level'] : null;
        $customerSpReason = $isCustomerSp ? ($extraData['customer_sp_reason'] ?? 'Pelanggaran / kejanggalan dalam pesanan') : null;
        $adminNotes       = $extraData['admin_notes'] ?? null;
        $refundAmount     = isset($extraData['refund_amount']) ? (float) $extraData['refund_amount'] : null;
        $partnerAmount    = isset($extraData['partner_amount']) ? (float) $extraData['partner_amount'] : null;

        $decision = $isApproved ? (($isPartnerSp || $isCustomerSp) ? 'penalty_issued' : 'valid_no_sp') : 'rejected';

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

    /**
     * Admin membantu memisahkan/membebaskan mitra dari tugas saat customer lambat atau belum merespon pembatalan.
     * Mitra dilepaskan & dibebaskan dari status BUSY agar dapat langsung menerima pekerjaan lain.
     * Tugas ditahan (pending / dispatch_mode closed) sehingga TIDAK muncul di pool sampai customer mengonfirmasi.
     */
    public function adminUnlinkPartnerAndHoldTask(
        HelpCancelRequest $request,
        User $adminUser,
        ?string $adminNotes = null
    ): void {
        DB::transaction(function () use ($request, $adminUser, $adminNotes) {
            $lockedReq = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            $help = Help::where('id', $lockedReq->help_id)->lockForUpdate()->firstOrFail();

            $partnerId = $help->mitra_id ?? $lockedReq->partner_id;

            if ($partnerId) {
                // 1. Rekam eksklusi agar mitra yang bersangkutan tidak otomatis mengambil tugas ini lagi
                $help->addExcludedPartner($partnerId, "Admin memisahkan mitra dari tugas karena customer belum merespons kendala pembatalan.");

                // 2. Bebaskan status BUSY mitra agar dapat menerima tugas lain
                $this->onlineService->releaseBusy($partnerId, $help->id);
            }

            // 3. Update status tugas: lepaskan mitra & kunci pool (closed) agar TIDAK muncul di pool sampai customer konfirmasi
            $help->update([
                'mitra_id'                    => null,
                'service_stage'               => null,
                'status'                      => Help::STATUS_PARTNER_CANCEL_REQUESTED,
                'dispatch_mode'               => Help::DISPATCH_MODE_CLOSED,
                'partner_initial_lat'         => null,
                'partner_initial_lng'         => null,
                'partner_current_lat'         => null,
                'partner_current_lng'         => null,
                'partner_started_at'          => null,
                'partner_started_moving_at'   => null,
                'partner_arrived_at'          => null,
                'arrived_at'                  => null,
                'partner_location_updated_at' => null,
                'admin_notes'                 => ($help->admin_notes ? $help->admin_notes . ' | ' : '') . "Admin {$adminUser->name} memisahkan Mitra terkait. Tugas ditahan (pending) hingga Customer mengonfirmasi.",
            ]);

            // 4. Update tiket cancel request
            $lockedReq->update([
                'reviewed_by'     => $adminUser->id,
                'reviewed_at'     => now(),
                'admin_notes'     => ($lockedReq->admin_notes ? $lockedReq->admin_notes . ' | ' : '') . ($adminNotes ?: "Mitra dipisahkan & dibebaskan oleh Admin. Tugas ditahan menunggu respon Customer."),
                'settlement_type' => HelpCancelRequest::SETTLEMENT_PARTNER_UNLINKED_HELD,
            ]);

            // 5. Kirim notifikasi ke Mitra & Customer
            if ($partner = User::find($partnerId)) {
                try {
                    $partner->notify(new HelpStatusNotification(
                        $help,
                        null,
                        'partner_unlinked_free',
                        $partner
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying partner of unlinking: " . $e->getMessage());
                }
            }

            if ($customer = $help->user) {
                try {
                    $customer->notify(new HelpStatusNotification(
                        $help,
                        null,
                        'partner_cancel_requested',
                        $partner ?? null
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying customer of unlinking: " . $e->getMessage());
                }
            }

            Log::info("[HelpCancellationService] Admin #{$adminUser->id} unlinked Partner #{$partnerId} from Help #{$help->id}. Task held pending customer confirmation.");
        });
    }
}
