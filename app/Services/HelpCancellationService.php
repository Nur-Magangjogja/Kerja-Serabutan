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
    protected HelpEscrowService $escrowService;
    protected PartnerOnlineService $onlineService;
    protected PartnerDisciplineService $disciplineService;

    public function __construct(
        HelpEscrowService $escrowService,
        PartnerOnlineService $onlineService,
        PartnerDisciplineService $disciplineService
    ) {
        $this->escrowService     = $escrowService;
        $this->onlineService     = $onlineService;
        $this->disciplineService = $disciplineService;
    }

    /**
     * Mitra mengajukan pembatalan pesanan (dengan alasan wajib & foto bukti opsional).
     * Sesuai aturan:
     * 1. Status pesanan masuk ke 'partner_cancel_requested' (pending, tidak langsung masuk pool).
     * 2. Mitra dibebaskan dari status BUSY agar bisa mencari order baru.
     * 3. Memiliki batas waktu (deadline) konfirmasi (default 30 menit).
     * 4. Tanpa SP otomatis; evaluasi SP dilakukan secara manual oleh Admin Wilayah.
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
        return DB::transaction(function () use (
            $help,
            $partner,
            $reason,
            $notes,
            $evidencePhoto,
            $itemPurchased,
            $itemPurchaseAmount,
            $workCompletedPercentage,
            $deadlineMinutes
        ) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra yang ditugaskan pada pesanan ini yang dapat mengajukan pembatalan.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, 'completed', Help::STATUS_DIBATALKAN, 'cancelled'])) {
                throw new \RuntimeException('Pesanan ini sudah selesai atau telah dibatalkan.');
            }

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;
            $deadline   = now()->addMinutes($deadlineMinutes);

            // 1. Catat ke help_cancel_requests
            $request = HelpCancelRequest::create([
                'help_id'                   => $lockedHelp->id,
                'requester_type'            => HelpCancelRequest::REQUESTER_PARTNER,
                'partner_id'                => $partner->id,
                'customer_id'               => $lockedHelp->user_id,
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
                'expires_at'                => $deadline,
            ]);

            // 2. Perbarui Help: pending pembatalan mitra & set batas waktu
            $lockedHelp->update([
                'status'                      => Help::STATUS_PARTNER_CANCEL_REQUESTED,
                'partner_cancel_prev_status'  => $prevStatus,
                'partner_cancel_reason'       => $reason,
                'partner_cancel_notes'        => $notes,
                'partner_cancel_requested_at' => now(),
                'cancel_requested_by'         => 'partner',
                'cancel_deadline_at'          => $deadline,
                'cancel_evidence_photo'       => $evidencePhoto,
            ]);

            // 3. Bebaskan status online mitra (lepas dari BUSY agar bisa mencari order baru)
            $this->onlineService->releaseBusy($partner->id, $lockedHelp->id);

            // 4. Tambahkan ke exclusions agar tidak match kembali ke bantuan ini
            if (\Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions')) {
                \App\Models\HelpPartnerExclusion::firstOrCreate([
                    'help_id'  => $lockedHelp->id,
                    'mitra_id' => $partner->id,
                ], [
                    'reason'   => $reason,
                ]);
            }

            // 5. Notifikasi ke Customer
            if ($lockedHelp->user) {
                try {
                    $lockedHelp->user->notify(new HelpStatusNotification(
                        $lockedHelp,
                        "Mitra {$partner->name} mengajukan pembatalan (Alasan: {$reason}). Anda dapat menerima pembatalan untuk pengembalian dana 100% atau menunggu konfirmasi Admin Wilayah."
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying customer: " . $e->getMessage());
                }
            }

            Log::info("[HelpCancellationService] Partner #{$partner->id} submitted Cancel Request #{$request->id} for Help #{$lockedHelp->id} (Pending confirmation).");
            return $request;
        });
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
     * Customer mengajukan pembatalan pada pesanan yang sudah diambil mitra
     * (karena mitra tidak kunjung datang / tidak responsif).
     * Order masuk status 'customer_cancel_requested' / pending review Admin Wilayah.
     */
    public function submitCustomerCancelRequest(
        Help $help,
        User $customer,
        string $reason,
        ?string $notes = null,
        ?string $evidencePhoto = null,
        int $deadlineMinutes = 30
    ): HelpCancelRequest {
        return DB::transaction(function () use (
            $help,
            $customer,
            $reason,
            $notes,
            $evidencePhoto,
            $deadlineMinutes
        ) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->user_id !== $customer->id) {
                throw new \RuntimeException('Hanya pemesan (Customer) yang dapat mengajukan pembatalan.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, 'completed', Help::STATUS_DIBATALKAN, 'cancelled'])) {
                throw new \RuntimeException('Pesanan ini sudah selesai atau telah dibatalkan.');
            }

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;
            $deadline   = now()->addMinutes($deadlineMinutes);

            $request = HelpCancelRequest::create([
                'help_id'                   => $lockedHelp->id,
                'requester_type'            => HelpCancelRequest::REQUESTER_CUSTOMER,
                'partner_id'                => $lockedHelp->mitra_id,
                'customer_id'               => $customer->id,
                'district_id'               => $lockedHelp->district_id,
                'previous_status'           => $prevStatus,
                'previous_stage'            => $prevStage,
                'reason'                    => $reason,
                'notes'                     => $notes,
                'evidence_photo'            => $evidencePhoto,
                'status'                    => HelpCancelRequest::STATUS_PENDING,
                'requested_at'              => now(),
                'expires_at'                => $deadline,
            ]);

            $lockedHelp->update([
                'status'                      => Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
                'partner_cancel_prev_status'  => $prevStatus,
                'partner_cancel_reason'       => $reason,
                'partner_cancel_notes'        => $notes,
                'partner_cancel_requested_at' => now(),
                'cancel_requested_by'         => 'customer',
                'cancel_deadline_at'          => $deadline,
                'cancel_evidence_photo'       => $evidencePhoto,
            ]);

            // Notifikasi ke Mitra bahwa Customer mengajukan pembatalan (Mitra dapat beri klarifikasi opsional)
            if ($lockedHelp->mitra) {
                try {
                    $lockedHelp->mitra->notify(new HelpStatusNotification(
                        $lockedHelp,
                        "Customer mengajukan pembatalan pesanan (Alasan: {$reason}). Anda dapat memberikan klarifikasi/tanggapan sebelum diputuskan Admin Wilayah."
                    ));
                } catch (\Throwable $e) {
                    Log::warning("[HelpCancellationService] Failed notifying partner: " . $e->getMessage());
                }
            }

            Log::info("[HelpCancellationService] Customer #{$customer->id} submitted Cancel Request #{$request->id} for Help #{$lockedHelp->id}.");
            return $request;
        });
    }

    /**
     * Mitra memberikan pengakuan / klarifikasi / tanggapan opsional atas pengajuan pembatalan Customer.
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
        DB::transaction(function () use ($request, $admin, $isApproved, $settlementType, $extraData) {
            $lockedReq  = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            $lockedHelp = Help::where('id', $lockedReq->help_id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->status !== HelpCancelRequest::STATUS_PENDING) {
                throw new \RuntimeException('Pengajuan pembatalan ini sudah pernah diproses sebelumnya.');
            }

            $adminNotes       = $extraData['admin_notes'] ?? null;
            $spTarget         = $extraData['sp_target'] ?? HelpCancelRequest::SP_TARGET_NONE; // 'none', 'partner', 'customer', 'both'
            $partnerSpLevel   = isset($extraData['partner_sp_level']) ? (int) $extraData['partner_sp_level'] : null;
            $partnerSpReason  = $extraData['partner_sp_reason'] ?? 'Pelanggaran pembatalan tugas bantuan';
            $customerSpLevel  = isset($extraData['customer_sp_level']) ? (int) $extraData['customer_sp_level'] : null;
            $customerSpReason = $extraData['customer_sp_reason'] ?? 'Pelanggaran / kejanggalan dalam pesanan';

            if ($isApproved) {
                if ($settlementType === HelpCancelRequest::SETTLEMENT_RELIST_POOL) {
                    $formerMitraId = $lockedHelp->mitra_id;
                    $formerMitra   = $lockedHelp->mitra;

                    $cancelledMitraIds = is_array($lockedHelp->cancelled_mitra_ids) ? $lockedHelp->cancelled_mitra_ids : [];
                    if ($formerMitraId && !in_array($formerMitraId, $cancelledMitraIds, false)) {
                        $cancelledMitraIds[] = $formerMitraId;
                    }

                    if ($formerMitraId && \Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions')) {
                        \App\Models\HelpPartnerExclusion::firstOrCreate([
                            'help_id'  => $lockedHelp->id,
                            'mitra_id' => $formerMitraId,
                        ], [
                            'reason'   => $lockedReq->reason ?? 'Dialihkan kembali ke pool oleh Admin',
                        ]);
                    }

                    $lockedHelp->update([
                        'status'                      => Help::STATUS_MENUNGGU_MITRA,
                        'dispatch_mode'               => Help::DISPATCH_MODE_POOL,
                        'mitra_id'                    => null,
                        'cancelled_mitra_ids'         => $cancelledMitraIds,
                        'partner_cancel_prev_status'  => null,
                        'partner_cancel_reason'       => null,
                        'partner_cancel_requested_at' => null,
                        'cancel_requested_by'         => null,
                        'cancel_deadline_at'          => null,
                        'partner_current_lat'         => null,
                        'partner_current_lng'         => null,
                        'partner_initial_lat'         => null,
                        'partner_initial_lng'         => null,
                        'partner_started_at'          => null,
                        'partner_arrived_at'          => null,
                        'service_started_at'          => null,
                        'admin_notes'                 => "Aduan pembatalan customer ditinjau Admin #{$admin->id} ({$admin->name}). Mitra lama dilepaskan dan pesanan dikembalikan ke pool terbuka untuk mencari mitra baru.",
                    ]);

                    if ($formerMitraId) {
                        $this->onlineService->releaseBusy($formerMitraId, $lockedHelp->id);
                    }

                    $auditDecision = ($spTarget !== HelpCancelRequest::SP_TARGET_NONE)
                        ? HelpCancelRequest::AUDIT_PENALTY_ISSUED
                        : HelpCancelRequest::AUDIT_VALID_NO_SP;

                    $lockedReq->update([
                        'status'                 => HelpCancelRequest::STATUS_APPROVED,
                        'settlement_type'        => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                        'refund_amount_customer' => 0,
                        'payout_amount_mitra'    => 0,
                        'reviewed_by'            => $admin->id,
                        'admin_notes'            => $adminNotes ?: 'Pesanan dilempar kembali ke pool terbuka untuk mencari mitra lain.',
                        'sp_target'              => $spTarget,
                        'partner_sp_level'       => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpLevel : null,
                        'partner_sp_reason'      => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpReason : null,
                        'customer_sp_level'      => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpLevel : null,
                        'customer_sp_reason'     => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpReason : null,
                        'audit_decision'         => $auditDecision,
                        'reviewed_at'            => now(),
                    ]);

                    if ($lockedHelp->user) {
                        try {
                            $lockedHelp->user->notify(new HelpStatusNotification(
                                $lockedHelp,
                                "Admin telah memproses aduan Anda. Mitra lama telah dilepaskan dan pesanan Anda sedang dicarikan Rekan Jasa pengganti."
                            ));
                        } catch (\Throwable $e) {
                            Log::warning("[HelpCancellationService] Failed notifying customer: " . $e->getMessage());
                        }
                    }

                    if ($formerMitra) {
                        try {
                            $formerMitra->notify(new HelpStatusNotification(
                                $lockedHelp,
                                "Penugasan Anda pada pesanan #{$lockedHelp->id} telah dialihkan kembali ke sistem oleh Admin."
                            ));
                        } catch (\Throwable $e) {
                            Log::warning("[HelpCancellationService] Failed notifying former partner: " . $e->getMessage());
                        }
                    }
                } else {
                    $totalPaid = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : ($lockedHelp->amount + ($lockedHelp->travel_fee ?? 0) + ($lockedHelp->item_fund ?? 0) + ($lockedHelp->platform_fee_amount ?? 2000)));
                    $itemFund  = (float) ($lockedHelp->item_fund ?? 0);
                    $serviceFee = (float) ($lockedHelp->service_fee > 0 ? $lockedHelp->service_fee : $lockedHelp->amount);

                    $customRefund  = isset($extraData['refund_amount']) && $extraData['refund_amount'] !== '' ? (float) $extraData['refund_amount'] : null;
                    $customPartner = isset($extraData['partner_amount']) && $extraData['partner_amount'] !== '' ? (float) $extraData['partner_amount'] : null;

                    if ($customRefund !== null && $customPartner !== null) {
                        $refundCustomer = max(0.0, $customRefund);
                        $payoutMitra    = max(0.0, $customPartner);
                    } else {
                        if ($settlementType === HelpCancelRequest::SETTLEMENT_FULL_REFUND) {
                            $refundCustomer = $totalPaid;
                            $payoutMitra    = 0.0;
                        } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_ITEM_SETTLED) {
                            $actualItemCost = min($itemFund > 0 ? $itemFund : $lockedReq->item_purchase_amount, $lockedReq->item_purchase_amount);
                            $payoutMitra    = $actualItemCost;
                            $refundCustomer = max(0.0, $totalPaid - $payoutMitra);
                        } elseif ($settlementType === HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT) {
                            $pct = min(1.0, max(0.0, ((float) $lockedReq->work_completed_percentage) / 100.0));
                            $payoutMitra    = round($serviceFee * $pct, 2);
                            $refundCustomer = max(0.0, $totalPaid - $payoutMitra);
                        } else {
                            $refundCustomer = $totalPaid;
                            $payoutMitra    = 0.0;
                        }
                    }

                    // Eksekusi mutasi saldo
                    if ($refundCustomer > 0 && $lockedHelp->user) {
                        $this->escrowService->refundFromEscrowDirect($lockedHelp, $lockedHelp->user, $refundCustomer, 'Pembatalan Disetujui Admin Wilayah');
                    }

                    if ($payoutMitra > 0 && $lockedHelp->mitra) {
                        $this->escrowService->payoutPartialFromEscrowDirect($lockedHelp, $lockedHelp->mitra, $payoutMitra, 'Kompensasi Pembatalan oleh Admin Wilayah');
                    }

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

                    if ($lockedHelp->mitra_id) {
                        $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
                    }

                    $auditDecision = ($spTarget !== HelpCancelRequest::SP_TARGET_NONE)
                        ? HelpCancelRequest::AUDIT_PENALTY_ISSUED
                        : HelpCancelRequest::AUDIT_VALID_NO_SP;

                    $lockedReq->update([
                        'status'                 => HelpCancelRequest::STATUS_APPROVED,
                        'settlement_type'        => $settlementType,
                        'refund_amount_customer' => $refundCustomer,
                        'payout_amount_mitra'    => $payoutMitra,
                        'reviewed_by'            => $admin->id,
                        'admin_notes'            => $adminNotes,
                        'sp_target'              => $spTarget,
                        'partner_sp_level'       => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpLevel : null,
                        'partner_sp_reason'      => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpReason : null,
                        'customer_sp_level'      => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpLevel : null,
                        'customer_sp_reason'     => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpReason : null,
                        'audit_decision'         => $auditDecision,
                        'reviewed_at'            => now(),
                    ]);
                }
            } else {
                // Penolakan Pembatalan: Kembalikan status pengerjaan semula
                $restoredStatus = $lockedReq->previous_status ?: Help::STATUS_TAKEN;
                $restoredStage  = $lockedReq->previous_stage;

                $auditDecision = ($spTarget !== HelpCancelRequest::SP_TARGET_NONE)
                    ? HelpCancelRequest::AUDIT_PENALTY_ISSUED
                    : HelpCancelRequest::AUDIT_REJECTED;

                $lockedReq->update([
                    'status'             => HelpCancelRequest::STATUS_REJECTED,
                    'reviewed_by'        => $admin->id,
                    'admin_notes'        => $adminNotes,
                    'sp_target'          => $spTarget,
                    'partner_sp_level'   => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpLevel : null,
                    'partner_sp_reason'  => ($spTarget === 'partner' || $spTarget === 'both') ? $partnerSpReason : null,
                    'customer_sp_level'  => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpLevel : null,
                    'customer_sp_reason' => ($spTarget === 'customer' || $spTarget === 'both') ? $customerSpReason : null,
                    'audit_decision'     => $auditDecision,
                    'reviewed_at'        => now(),
                ]);

                $lockedHelp->update([
                    'status'        => $restoredStatus,
                    'service_stage' => $restoredStage,
                    'admin_notes'   => "Pengajuan pembatalan ditolak oleh Admin #{$admin->id} ({$admin->name}). Order dilanjutkan.",
                ]);
            }

            // Eksekusi penjatuhan SP jika dipilih oleh Admin
            if (in_array($spTarget, [HelpCancelRequest::SP_TARGET_PARTNER, HelpCancelRequest::SP_TARGET_BOTH]) && $partnerSpLevel && $lockedHelp->mitra) {
                $this->disciplineService->issueManualWarningToUser(
                    $lockedHelp->mitra,
                    $partnerSpLevel,
                    $partnerSpReason,
                    $admin,
                    $lockedHelp
                );
            }

            if (in_array($spTarget, [HelpCancelRequest::SP_TARGET_CUSTOMER, HelpCancelRequest::SP_TARGET_BOTH]) && $customerSpLevel && $lockedHelp->user) {
                $this->disciplineService->issueManualWarningToUser(
                    $lockedHelp->user,
                    $customerSpLevel,
                    $customerSpReason,
                    $admin,
                    $lockedHelp
                );
            }

            Log::info("[HelpCancellationService] Admin #{$admin->id} reviewed Cancel Request #{$lockedReq->id} (Approved: " . ($isApproved ? 'yes' : 'no') . ", SP Target: {$spTarget}).");
        });
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
