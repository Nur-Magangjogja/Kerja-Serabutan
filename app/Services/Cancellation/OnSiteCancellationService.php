<?php

namespace App\Services\Cancellation;

use App\Models\Chat;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\HelpPartnerExclusion;
use App\Models\User;
use App\Services\GeoService;
use App\Services\HelpEscrowService;
use App\Services\PartnerOnlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnSiteCancellationService
{
    protected PartnerOnlineService $onlineService;
    protected HelpEscrowService $escrowService;
    protected GeoService $geoService;

    public function __construct(
        PartnerOnlineService $onlineService,
        HelpEscrowService $escrowService,
        GeoService $geoService
    ) {
        $this->onlineService = $onlineService;
        $this->escrowService = $escrowService;
        $this->geoService    = $geoService;
    }

    /**
     * KONDISI 1: Pembatalan karena Insiden di Perjalanan oleh Mitra.
     * Alur:
     * 1. Rekam telemetri GPS awal, GPS saat insiden, dan jarak tempuh riil.
     * 2. Langsung relist order kembali ke pool umum / status MENUNGGU_MITRA (Customer tidak terkatung-katung).
     * 3. Mitra langsung bebas (release busy).
     * 4. Buat tiket audit HelpCancelRequest (action_type: partner_incident) agar Admin Wilayah dapat mengevaluasi SP.
     */
    public function cancelByPartner(
        Help $help,
        User $mitra,
        string $reason,
        ?string $evidencePhotoPath = null,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($help, $mitra, $reason, $evidencePhotoPath, $notes) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== $mitra->id) {
                throw new \RuntimeException('Anda tidak memiliki otorisasi untuk membatalkan bantuan ini.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, Help::STATUS_DIBATALKAN], true)) {
                throw new \RuntimeException('Tugas ini sudah selesai atau telah dibatalkan sebelumnya.');
            }

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;

            // 1. Ekstraksi data telemetri
            $startLat = (float) ($lockedHelp->partner_initial_lat ?: 0);
            $startLng = (float) ($lockedHelp->partner_initial_lng ?: 0);
            $cancelLat = (float) ($lockedHelp->partner_current_lat ?: $startLat);
            $cancelLng = (float) ($lockedHelp->partner_current_lng ?: $startLng);
            $targetLat = (float) ($lockedHelp->latitude ?: 0);
            $targetLng = (float) ($lockedHelp->longitude ?: 0);

            $partnerMovedKm = ($startLat && $cancelLat)
                ? $this->geoService->calculateStraightDistance($startLat, $startLng, $cancelLat, $cancelLng)
                : 0.0;

            $distToTargetKm = ($cancelLat && $targetLat)
                ? $this->geoService->calculateStraightDistance($cancelLat, $cancelLng, $targetLat, $targetLng)
                : 0.0;

            $timeElapsed = $lockedHelp->taken_at ? Carbon::parse($lockedHelp->taken_at)->diffInMinutes(now()) : 0;
            $chatCount   = Chat::where('help_id', $lockedHelp->id)->count();
            $lastChat    = Chat::where('help_id', $lockedHelp->id)->where('sender_type', 'mitra')->latest()->value('created_at');

            $isWorkStarted = ($lockedHelp->status === Help::STATUS_IN_PROGRESS || in_array($lockedHelp->service_stage, ['in_progress', 'at_customer', 'at_destination'], true));

            // 2. Rekam eksklusi mitra pada order ini
            HelpPartnerExclusion::firstOrCreate(
                [
                    'help_id'  => $lockedHelp->id,
                    'mitra_id' => $mitra->id,
                ],
                [
                    'reason' => "Mitra membatalkan tugas On-Site: {$reason}",
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // KONSEP 2: Mitra membatalkan tugas SETELAH menekan "Mulai Pekerjaan" (in_progress).
            // Alur:
            // 1. Kunci status tugas ke STATUS_PARTNER_CANCEL_REQUESTED (tidak dilempar langsung ke pool).
            // 2. Buat tiket HelpCancelRequest dengan cancellation_stage = 'in_progress'.
            // 3. Admin Wilayah wajib meninjau & menghubungi Customer terlebih dahulu untuk klarifikasi dan kesepakatan refund.
            // ─────────────────────────────────────────────────────────────────
            if ($isWorkStarted) {
                $lockedHelp->update([
                    'status'                      => Help::STATUS_PARTNER_CANCEL_REQUESTED,
                    'cancel_requested_by'         => 'partner',
                    'partner_cancel_requested_at' => now(),
                    'partner_cancel_reason'       => $reason,
                    'cancel_deadline_at'          => now()->addHours(24),
                    'cancel_evidence_photo'       => $evidencePhotoPath,
                    'admin_notes'                 => "Mitra #{$mitra->id} mengajukan kendala lapangan (Konsep 2 - Pengerjaan Dimulai): {$reason}. Menunggu klarifikasi Admin ke Customer.",
                ]);

                $cancelRequest = HelpCancelRequest::create([
                    'help_id'               => $lockedHelp->id,
                    'requester_type'        => HelpCancelRequest::REQUESTER_PARTNER,
                    'action_type'           => HelpCancelRequest::ACTION_PARTNER_INCIDENT,
                    'cancellation_stage'    => 'in_progress',
                    'partner_id'            => $mitra->id,
                    'customer_id'           => $lockedHelp->user_id,
                    'district_id'           => $lockedHelp->district_id,
                    'previous_status'       => $prevStatus,
                    'previous_stage'        => $prevStage,
                    'reason'                => $reason,
                    'notes'                 => $notes,
                    'evidence_photo'        => $evidencePhotoPath,
                    'partner_start_lat'     => $startLat ?: null,
                    'partner_start_lng'     => $startLng ?: null,
                    'partner_cancel_lat'    => $cancelLat ?: null,
                    'partner_cancel_lng'    => $cancelLng ?: null,
                    'partner_moved_km'      => $partnerMovedKm,
                    'distance_to_target_km' => $distToTargetKm,
                    'time_elapsed_minutes'  => $timeElapsed,
                    'chat_messages_count'   => $chatCount,
                    'partner_last_chat_at'  => $lastChat,
                    'status'                => HelpCancelRequest::STATUS_PENDING,
                    'settlement_type'       => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                    'sp_target'             => HelpCancelRequest::SP_TARGET_NONE,
                    'requested_at'          => now(),
                    'expires_at'            => now()->addHours(24),
                ]);

                // Pastikan status online mitra tetap terkunci ke BUSY agar tidak dapat mengambil tugas lain sementara waktu
                $this->onlineService->markBusy($mitra->id, $lockedHelp->id);

                Log::info("[OnSiteCancellationService] Mitra on-site cancel (Konsep 2). Locked in partner_cancel_requested & BUSY state, waiting admin & customer clarification.", [
                    'help_id'           => $lockedHelp->id,
                    'mitra_id'          => $mitra->id,
                    'cancel_request_id' => $cancelRequest->id,
                ]);

                return [
                    'success'           => true,
                    'relisted'          => false,
                    'under_review'      => true,
                    'cancel_request_id' => $cancelRequest->id,
                    'message'           => 'Pengajuan kendala pengerjaan (Konsep 2) berhasil dikirim. Admin Wilayah akan menghubungi Customer terlebih dahulu untuk konfirmasi dan proses kesepakatan refund.',
                ];
            }

            // ─────────────────────────────────────────────────────────────────
            // KONSEP 1: Mitra membatalkan tugas SEBELUM mulai pengerjaan (saat perjalanan/tiba).
            // Alur:
            // 1. Relist order kembali ke pool pencarian mitra baru.
            // 2. Bebaskan status BUSY mitra.
            // 3. Buat tiket audit HelpCancelRequest (cancellation_stage = 'transit') untuk evaluasi SP Admin.
            // ─────────────────────────────────────────────────────────────────
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
                'dispatch_mode'               => Help::DISPATCH_MODE_POOL,
                'pool_opened_at'              => now(),
            ]);

            // Bebaskan status BUSY mitra
            $this->onlineService->releaseBusy($mitra->id, $lockedHelp->id);

            // Buat tiket audit untuk Admin Wilayah
            $cancelRequest = HelpCancelRequest::create([
                'help_id'               => $lockedHelp->id,
                'requester_type'        => HelpCancelRequest::REQUESTER_PARTNER,
                'action_type'           => HelpCancelRequest::ACTION_PARTNER_INCIDENT,
                'cancellation_stage'    => 'transit',
                'partner_id'            => $mitra->id,
                'customer_id'           => $lockedHelp->user_id,
                'district_id'           => $lockedHelp->district_id,
                'previous_status'       => $prevStatus,
                'previous_stage'        => $prevStage,
                'reason'                => $reason,
                'notes'                 => $notes,
                'evidence_photo'        => $evidencePhotoPath,
                'partner_start_lat'     => $startLat ?: null,
                'partner_start_lng'     => $startLng ?: null,
                'partner_cancel_lat'    => $cancelLat ?: null,
                'partner_cancel_lng'    => $cancelLng ?: null,
                'partner_moved_km'      => $partnerMovedKm,
                'distance_to_target_km' => $distToTargetKm,
                'time_elapsed_minutes'  => $timeElapsed,
                'chat_messages_count'   => $chatCount,
                'partner_last_chat_at'  => $lastChat,
                'status'                => HelpCancelRequest::STATUS_PENDING,
                'settlement_type'       => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                'sp_target'             => HelpCancelRequest::SP_TARGET_NONE,
                'requested_at'          => now(),
                'expires_at'            => now()->addHours(24),
            ]);

            Log::info("[OnSiteCancellationService] Mitra incident cancel (Konsep 1). Relisted to pool, audit ticket created.", [
                'help_id'           => $lockedHelp->id,
                'mitra_id'          => $mitra->id,
                'cancel_request_id' => $cancelRequest->id,
                'moved_km'          => $partnerMovedKm,
            ]);

            return [
                'success'           => true,
                'relisted'          => true,
                'cancel_request_id' => $cancelRequest->id,
                'message'           => 'Pembatalan telah diajukan. Tugas otomatis dikembalikan ke pool pencarian mitra dan alasan Anda akan ditinjau oleh Admin Wilayah.',
            ];
        });
    }

    /**
     * KONDISI 2A: Customer memilih "Ganti Mitra" karena mitra lama tidak kunjung bergerak / tidak merespons.
     * Alur:
     * 1. Mitra lama dilepaskan dan dicatat eksklusi.
     * 2. Pesanan langsung dikembalikan ke pool pencarian mitra baru.
     * 3. Tiket audit dibuat untuk Admin Wilayah untuk evaluasi sanksi SP atas kelalaian mitra lama.
     */
    public function switchPartnerByCustomer(
        Help $help,
        User $customer,
        string $reason = 'Mitra tidak bergerak / tidak merespons chat',
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($help, $customer, $reason, $notes) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->user_id !== $customer->id) {
                throw new \RuntimeException('Anda tidak memiliki otorisasi untuk mengganti mitra pada pesanan ini.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, Help::STATUS_DIBATALKAN], true)) {
                throw new \RuntimeException('Pesanan sudah selesai atau telah dibatalkan.');
            }

            $oldPartnerId = $lockedHelp->mitra_id;
            if (!$oldPartnerId) {
                throw new \RuntimeException('Belum ada mitra yang ditugaskan pada pesanan ini.');
            }

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;

            // 1. Ekstraksi telemetri
            $startLat = (float) ($lockedHelp->partner_initial_lat ?: 0);
            $startLng = (float) ($lockedHelp->partner_initial_lng ?: 0);
            $cancelLat = (float) ($lockedHelp->partner_current_lat ?: $startLat);
            $cancelLng = (float) ($lockedHelp->partner_current_lng ?: $startLng);
            $targetLat = (float) ($lockedHelp->latitude ?: 0);
            $targetLng = (float) ($lockedHelp->longitude ?: 0);

            $partnerMovedKm = ($startLat && $cancelLat)
                ? $this->geoService->calculateStraightDistance($startLat, $startLng, $cancelLat, $cancelLng)
                : 0.0;

            $distToTargetKm = ($cancelLat && $targetLat)
                ? $this->geoService->calculateStraightDistance($cancelLat, $cancelLng, $targetLat, $targetLng)
                : 0.0;

            $timeElapsed = $lockedHelp->taken_at ? Carbon::parse($lockedHelp->taken_at)->diffInMinutes(now()) : 0;
            $chatCount   = Chat::where('help_id', $lockedHelp->id)->count();
            $lastChat    = Chat::where('help_id', $lockedHelp->id)->where('sender_type', 'mitra')->latest()->value('created_at');

            // 2. Eksklusi mitra lama
            HelpPartnerExclusion::firstOrCreate(
                [
                    'help_id'  => $lockedHelp->id,
                    'mitra_id' => $oldPartnerId,
                ],
                [
                    'reason' => "Customer meminta ganti mitra: {$reason}",
                ]
            );

            // 3. Bebaskan mitra lama
            $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);

            // 4. Relist ke pool pencarian mitra baru
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
                'dispatch_mode'               => Help::DISPATCH_MODE_POOL,
                'pool_opened_at'              => now(),
                'admin_notes'                 => "Mitra #{$oldPartnerId} dilepas oleh Customer (Ganti Mitra). Order dialihkan ke pool baru.",
            ]);

            // 5. Buat tiket audit SP untuk mitra yang lalai
            $cancelRequest = HelpCancelRequest::create([
                'help_id'               => $lockedHelp->id,
                'requester_type'        => HelpCancelRequest::REQUESTER_CUSTOMER,
                'action_type'           => HelpCancelRequest::ACTION_SWITCH_PARTNER,
                'partner_id'            => $oldPartnerId,
                'customer_id'           => $customer->id,
                'district_id'           => $lockedHelp->district_id,
                'previous_status'       => $prevStatus,
                'previous_stage'        => $prevStage,
                'reason'                => $reason,
                'notes'                 => $notes,
                'partner_start_lat'     => $startLat ?: null,
                'partner_start_lng'     => $startLng ?: null,
                'partner_cancel_lat'    => $cancelLat ?: null,
                'partner_cancel_lng'    => $cancelLng ?: null,
                'partner_moved_km'      => $partnerMovedKm,
                'distance_to_target_km' => $distToTargetKm,
                'time_elapsed_minutes'  => $timeElapsed,
                'chat_messages_count'   => $chatCount,
                'partner_last_chat_at'  => $lastChat,
                'status'                => HelpCancelRequest::STATUS_PENDING,
                'settlement_type'       => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                'sp_target'             => HelpCancelRequest::SP_TARGET_PARTNER,
                'partner_sp_level'      => 1,
                'partner_sp_reason'     => 'Mitra tidak kunjung bergerak / tidak merespons pesanan customer.',
                'requested_at'          => now(),
                'expires_at'            => now()->addHours(24),
            ]);

            Log::info("[OnSiteCancellationService] Customer switched partner. Order relisted, audit ticket created.", [
                'help_id'           => $lockedHelp->id,
                'old_partner_id'    => $oldPartnerId,
                'cancel_request_id' => $cancelRequest->id,
            ]);

            return [
                'success'           => true,
                'relisted'          => true,
                'cancel_request_id' => $cancelRequest->id,
                'message'           => 'Mitra sebelumnya telah dilepaskan. Sistem sedang mencari mitra baru untuk Anda.',
            ];
        });
    }

    /**
     * KONDISI 2B: Customer memilih "Tarik Pekerjaan" (Batal Total & Full Refund).
     * Alur:
     * 1. Jika belum ada mitra -> Batal langsung & Full Refund 100%.
     * 2. Jika sudah ada mitra -> Masuk status customer_cancel_requested & kirim notifikasi konfirmasi ke Mitra.
     */
    public function requestWithdrawByCustomer(
        Help $help,
        User $customer,
        string $reason,
        ?string $evidencePhotoPath = null,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($help, $customer, $reason, $evidencePhotoPath, $notes) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->user_id !== $customer->id) {
                throw new \RuntimeException('Anda tidak memiliki otorisasi untuk membatalkan bantuan ini.');
            }

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, Help::STATUS_DIBATALKAN], true)) {
                throw new \RuntimeException('Pesanan sudah selesai atau sudah dibatalkan sebelumnya.');
            }

            // Jika belum ada mitra yang mengambil -> langsung batalkan dan refund 100%
            if ($lockedHelp->status === Help::STATUS_MENUNGGU_MITRA || !$lockedHelp->mitra_id) {
                $this->escrowService->refundFromEscrow($lockedHelp, $customer);
                $lockedHelp->update([
                    'status'         => Help::STATUS_DIBATALKAN,
                    'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                    'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                    'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                ]);

                return [
                    'success'  => true,
                    'refunded' => true,
                    'message'  => 'Pesanan berhasil dibatalkan dan seluruh saldo Anda telah dikembalikan 100%.',
                ];
            }

            // Ekstraksi telemetri aktual
            $startLat = (float) ($lockedHelp->partner_initial_lat ?: 0);
            $startLng = (float) ($lockedHelp->partner_initial_lng ?: 0);
            $cancelLat = (float) ($lockedHelp->partner_current_lat ?: $startLat);
            $cancelLng = (float) ($lockedHelp->partner_current_lng ?: $startLng);
            $targetLat = (float) ($lockedHelp->latitude ?: 0);
            $targetLng = (float) ($lockedHelp->longitude ?: 0);

            $partnerMovedKm = ($startLat && $cancelLat)
                ? $this->geoService->calculateStraightDistance($startLat, $startLng, $cancelLat, $cancelLng)
                : 0.0;

            $distToTargetKm = ($cancelLat && $targetLat)
                ? $this->geoService->calculateStraightDistance($cancelLat, $cancelLng, $targetLat, $targetLng)
                : 0.0;

            $timeElapsed = $lockedHelp->taken_at ? Carbon::parse($lockedHelp->taken_at)->diffInMinutes(now()) : 0;
            $chatCount   = Chat::where('help_id', $lockedHelp->id)->count();
            $lastChat    = Chat::where('help_id', $lockedHelp->id)->where('sender_type', 'mitra')->latest()->value('created_at');

            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;

            $lockedHelp->update([
                'status'                => Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
                'cancel_requested_by'   => 'customer',
                'cancel_deadline_at'    => now()->addHours(2),
                'cancel_evidence_photo' => $evidencePhotoPath,
            ]);

            $cancelRequest = HelpCancelRequest::create([
                'help_id'               => $lockedHelp->id,
                'requester_type'        => HelpCancelRequest::REQUESTER_CUSTOMER,
                'action_type'           => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
                'partner_response_type' => HelpCancelRequest::PARTNER_RESPONSE_PENDING,
                'partner_id'            => $lockedHelp->mitra_id,
                'customer_id'           => $customer->id,
                'district_id'           => $lockedHelp->district_id,
                'previous_status'       => $prevStatus,
                'previous_stage'        => $prevStage,
                'reason'                => $reason,
                'notes'                 => $notes,
                'evidence_photo'        => $evidencePhotoPath,
                'partner_start_lat'     => $startLat ?: null,
                'partner_start_lng'     => $startLng ?: null,
                'partner_cancel_lat'    => $cancelLat ?: null,
                'partner_cancel_lng'    => $cancelLng ?: null,
                'partner_moved_km'      => $partnerMovedKm,
                'distance_to_target_km' => $distToTargetKm,
                'time_elapsed_minutes'  => $timeElapsed,
                'chat_messages_count'   => $chatCount,
                'partner_last_chat_at'  => $lastChat,
                'status'                => HelpCancelRequest::STATUS_PENDING,
                'settlement_type'       => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                'requested_at'          => now(),
                'expires_at'            => now()->addHours(24),
            ]);

            Log::info("[OnSiteCancellationService] Customer withdraw request submitted. Waiting partner confirmation / admin audit.", [
                'help_id'           => $lockedHelp->id,
                'customer_id'       => $customer->id,
                'cancel_request_id' => $cancelRequest->id,
            ]);

            return [
                'success'           => true,
                'under_review'      => true,
                'cancel_request_id' => $cancelRequest->id,
                'message'           => 'Permintaan penarikan pekerjaan telah dikirimkan. Mitra akan mengonfirmasi dan kasus ini dipantau oleh Admin Wilayah.',
            ];
        });
    }

    /**
     * Mitra merespons pengajuan penarikan pekerjaan dari Customer (Konfirmasi Setuju vs Tolak & Pembelaan).
     */
    public function respondWithdrawByPartner(
        HelpCancelRequest $request,
        User $partner,
        bool $isConfirmed,
        ?string $notes = null,
        ?string $photoPath = null
    ): array {
        return DB::transaction(function () use ($request, $partner, $isConfirmed, $notes, $photoPath) {
            $lockedReq = HelpCancelRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if ($lockedReq->partner_id !== $partner->id) {
                throw new \RuntimeException('Hanya mitra terkait yang dapat merespons penarikan pekerjaan ini.');
            }

            $lockedHelp = Help::where('id', $lockedReq->help_id)->lockForUpdate()->firstOrFail();

            if ($isConfirmed) {
                // Mitra menyetujui penarikan -> Eksekusi 100% full refund ke Customer
                $gross = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);
                if ($gross > 0 && $lockedHelp->user) {
                    $this->escrowService->refundFromEscrowDirect($lockedHelp, $lockedHelp->user, $gross, 'Mitra menyetujui penarikan pekerjaan oleh Customer.');
                }

                $lockedHelp->update([
                    'status'         => Help::STATUS_DIBATALKAN,
                    'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                    'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                    'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                    'admin_notes'    => "Customer menarik pekerjaan dan disetujui oleh Mitra. Saldo 100% dikembalikan.",
                ]);

                $this->onlineService->releaseBusy($partner->id, $lockedHelp->id);

                $lockedReq->update([
                    'status'                 => HelpCancelRequest::STATUS_APPROVED,
                    'partner_response_type'  => HelpCancelRequest::PARTNER_RESPONSE_CONFIRMED,
                    'partner_response_notes' => $notes,
                    'partner_response_photo' => $photoPath,
                    'partner_responded_at'   => now(),
                    'settlement_type'        => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                    'refund_amount_customer' => $gross,
                    'payout_amount_mitra'    => 0,
                    'reviewed_at'            => now(),
                    'admin_notes'            => 'Disetujui bersama oleh Mitra dan Customer.',
                ]);

                Log::info("[OnSiteCancellationService] Partner confirmed customer withdrawal. 100% refunded.", [
                    'help_id'    => $lockedHelp->id,
                    'partner_id' => $partner->id,
                ]);

                return [
                    'success'   => true,
                    'confirmed' => true,
                    'message'   => 'Anda telah menyetujui penarikan pesanan. Pesanan resmi dibatalkan.',
                ];
            }

            // Mitra menolak penarikan -> Catat pembelaan & bukti untuk Admin Wilayah
            $lockedReq->update([
                'partner_response_type'  => HelpCancelRequest::PARTNER_RESPONSE_REJECTED,
                'partner_response_notes' => $notes,
                'partner_response_photo' => $photoPath,
                'partner_responded_at'   => now(),
            ]);

            Log::info("[OnSiteCancellationService] Partner rejected customer withdrawal. Sent to Admin jury.", [
                'help_id'    => $lockedHelp->id,
                'partner_id' => $partner->id,
            ]);

            return [
                'success'   => true,
                'confirmed' => false,
                'message'   => 'Penolakan Anda telah dicatat. Kasus ini diteruskan ke Admin Wilayah untuk diaudit secara adil.',
            ];
        });
    }

    /**
     * Backward-compatible alias for requestCancelByCustomer.
     */
    public function requestCancelByCustomer(
        Help $help,
        User $customer,
        string $reason,
        ?string $evidencePhotoPath = null,
        ?string $notes = null
    ): array {
        return $this->requestWithdrawByCustomer($help, $customer, $reason, $evidencePhotoPath, $notes);
    }
}

