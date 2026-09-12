<?php

namespace App\Services\Cancellation;

use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\HelpPartnerExclusion;
use App\Models\User;
use App\Services\HelpEscrowService;
use App\Services\PartnerOnlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnSiteCancellationService
{
    protected PartnerOnlineService $onlineService;
    protected HelpEscrowService $escrowService;

    public function __construct(
        PartnerOnlineService $onlineService,
        HelpEscrowService $escrowService
    ) {
        $this->onlineService = $onlineService;
        $this->escrowService = $escrowService;
    }

    /**
     * KONSEP 1: Pembatalan oleh Mitra pada Layanan Biasa (On-Site).
     * Alur:
     * 1. Rekam eksklusi mitra agar tidak menerima dispatch order ini lagi.
     * 2. Langsung relist order kembali ke pool umum / status MENUNGGU_MITRA (Customer tidak terkatung-katung).
     * 3. Mitra langsung bebas (release busy) dan dapat mencari order lain.
     * 4. Buat tiket audit HelpCancelRequest agar Admin Wilayah dapat menginvestigasi alasan pembatalan
     *    dan mengenakan sanksi SP (1-3) bila terbukti melanggar disiplin.
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

            // 1. Rekam eksklusi mitra pada order ini (tabel relasional)
            HelpPartnerExclusion::firstOrCreate(
                [
                    'help_id'  => $lockedHelp->id,
                    'mitra_id' => $mitra->id,
                ],
                [
                    'reason' => "Mitra membatalkan tugas On-Site: {$reason}",
                ]
            );

            // 2. Relist order kembali ke pool / antrean pencarian mitra baru
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

            // 3. Bebaskan status BUSY mitra agar bisa langsung mencari order lain
            $this->onlineService->releaseBusy($mitra->id, $lockedHelp->id);

            // 4. Buat tiket audit untuk Admin Wilayah
            $cancelRequest = HelpCancelRequest::create([
                'help_id'                    => $lockedHelp->id,
                'requester_type'             => HelpCancelRequest::REQUESTER_PARTNER,
                'partner_id'                 => $mitra->id,
                'customer_id'                => $lockedHelp->user_id,
                'district_id'                => $lockedHelp->district_id,
                'previous_status'            => $prevStatus,
                'previous_stage'             => $prevStage,
                'reason'                     => $reason,
                'notes'                      => $notes,
                'evidence_photo'             => $evidencePhotoPath,
                'status'                     => HelpCancelRequest::STATUS_PENDING,
                'settlement_type'            => HelpCancelRequest::SETTLEMENT_RELIST_POOL,
                'sp_target'                  => HelpCancelRequest::SP_TARGET_NONE,
                'requested_at'               => now(),
                'expires_at'                 => now()->addHours(24),
            ]);

            Log::info("[OnSiteCancellationService] Mitra cancelled On-Site task. Order relisted, audit ticket created.", [
                'help_id'           => $lockedHelp->id,
                'mitra_id'          => $mitra->id,
                'cancel_request_id' => $cancelRequest->id,
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
     * KONSEP 2: Pembatalan / Pengajuan Kendala oleh Customer pada Layanan Biasa (On-Site).
     * Alur:
     * 1. Customer mengajukan pembatalan sebelum pekerjaan dimulai.
     * 2. Status order menjadi customer_cancel_requested untuk ditinjau Admin Wilayah.
     * 3. Mitra dapat memberikan klarifikasi sebelum admin mengambil keputusan.
     */
    public function requestCancelByCustomer(
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

            // Jika sudah ada mitra -> masukan ke peninjauan kendala admin
            $prevStatus = $lockedHelp->status;
            $prevStage  = $lockedHelp->service_stage;

            $lockedHelp->update([
                'status'                    => Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
                'cancel_requested_by'       => 'customer',
                'cancel_deadline_at'        => now()->addHours(2),
                'cancel_evidence_photo'     => $evidencePhotoPath,
            ]);

            $cancelRequest = HelpCancelRequest::create([
                'help_id'                    => $lockedHelp->id,
                'requester_type'             => HelpCancelRequest::REQUESTER_CUSTOMER,
                'partner_id'                 => $lockedHelp->mitra_id,
                'customer_id'                => $customer->id,
                'district_id'                => $lockedHelp->district_id,
                'previous_status'            => $prevStatus,
                'previous_stage'             => $prevStage,
                'reason'                     => $reason,
                'notes'                      => $notes,
                'evidence_photo'             => $evidencePhotoPath,
                'status'                     => HelpCancelRequest::STATUS_PENDING,
                'settlement_type'            => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                'requested_at'               => now(),
                'expires_at'                 => now()->addHours(24),
            ]);

            Log::info("[OnSiteCancellationService] Customer requested cancel for On-Site task. Sent to admin audit.", [
                'help_id'           => $lockedHelp->id,
                'customer_id'       => $customer->id,
                'cancel_request_id' => $cancelRequest->id,
            ]);

            return [
                'success'           => true,
                'under_review'      => true,
                'cancel_request_id' => $cancelRequest->id,
                'message'           => 'Permintaan pembatalan telah dikirimkan ke Admin Wilayah untuk diverifikasi bersama Rekan Jasa.',
            ];
        });
    }
}
