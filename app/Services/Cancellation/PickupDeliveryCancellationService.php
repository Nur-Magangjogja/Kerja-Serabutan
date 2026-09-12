<?php

namespace App\Services\Cancellation;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\User;
use App\Services\GeoService;
use App\Services\HelpEscrowService;
use App\Services\PartnerOnlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PickupDeliveryCancellationService
{
    protected GeoService $geoService;
    protected PartnerOnlineService $onlineService;
    protected HelpEscrowService $escrowService;

    public function __construct(
        GeoService $geoService,
        PartnerOnlineService $onlineService,
        HelpEscrowService $escrowService
    ) {
        $this->geoService = $geoService;
        $this->onlineService = $onlineService;
        $this->escrowService = $escrowService;
    }

    /**
     * LANGKAH 1: Evaluasi Kelayakan Pembatalan (Eligibility Check).
     * Memisahkan aturan "Boleh Batal atau Tidak" dari perhitungan kompensasi.
     *
     * Aturan Inti:
     * - Kondisi A (menunggu_mitra)                  -> Boleh (100% Refund)
     * - Kondisi B (taken, belum bergerak)           -> Boleh (100% Refund)
     * - Kondisi C (going_to_pickup)                 -> Boleh (Kompensasi D_leg1)
     * - Kondisi D (at_pickup/waiting_for_customer)  -> Boleh (Kompensasi 100% Service Fee)
     * - Kondisi E (item_collected & D_cancel <= 5KM)-> Boleh (Kompensasi D_leg1 + D_cancel)
     * - Kondisi F (item_collected & D_cancel > 5KM) -> TERKUNCI (Anti-Bypass Lock)
     * - Final Approach / At Destination / Selesai   -> TERKUNCI
     */
    public function evaluateCancellationEligibility(
        Help $help,
        ?float $currentPartnerLat = null,
        ?float $currentPartnerLng = null
    ): array {
        if (!$help->isPickup()) {
            return [
                'allowed'   => true,
                'condition' => 'ON_SITE',
                'reason'    => null,
                'd_cancel'  => 0.0,
            ];
        }

        // Status selesai atau sudah batal
        if (in_array($help->status, [Help::STATUS_SELESAI, Help::STATUS_DIBATALKAN], true)) {
            return [
                'allowed'   => false,
                'condition' => 'TERMINATED',
                'reason'    => 'Pesanan sudah selesai atau telah dibatalkan sebelumnya.',
                'd_cancel'  => 0.0,
            ];
        }

        // Kondisi A: Belum ada mitra
        if ($help->status === Help::STATUS_MENUNGGU_MITRA || !$help->mitra_id) {
            return [
                'allowed'   => true,
                'condition' => 'A_UNASSIGNED',
                'reason'    => null,
                'd_cancel'  => 0.0,
            ];
        }

        // Kondisi B: Mitra baru menerima dan belum berangkat
        if ($help->status === Help::STATUS_TAKEN && !$help->partner_started_at) {
            return [
                'allowed'   => true,
                'condition' => 'B_PRE_DEPARTURE',
                'reason'    => null,
                'd_cancel'  => 0.0,
            ];
        }

        // Kondisi C: Sedang menuju titik jemput
        if ($help->status === Help::STATUS_PARTNER_ON_THE_WAY || $help->service_stage === Help::STAGE_GOING_TO_PICKUP) {
            return [
                'allowed'   => true,
                'condition' => 'C_PICKUP_TRAVEL',
                'reason'    => null,
                'd_cancel'  => 0.0,
            ];
        }

        // Kondisi D: Tiba di titik jemput / menunggu customer
        if ($help->status === Help::STATUS_PARTNER_ARRIVED || in_array($help->service_stage, [Help::STAGE_AT_PICKUP, Help::STAGE_WAITING_FOR_CUSTOMER], true)) {
            return [
                'allowed'   => true,
                'condition' => 'D_AT_PICKUP',
                'reason'    => null,
                'd_cancel'  => 0.0,
            ];
        }

        // Penguncian Tahap Akhir (Final Approach / At Destination)
        if (in_array($help->service_stage, [Help::STAGE_FINAL_APPROACH, Help::STAGE_AT_DESTINATION], true)) {
            return [
                'allowed'   => false,
                'condition' => 'F_LOCKED_FINAL_STAGE',
                'reason'    => 'Pesanan telah mendekati atau tiba di titik tujuan akhir. Pembatalan terkunci demi keamanan.',
                'd_cancel'  => 0.0,
            ];
        }

        // Kondisi Pasca-Jemput (Barang / Penumpang Sedang Diantar)
        if (in_array($help->service_stage, [Help::STAGE_ITEM_COLLECTED, Help::STAGE_GOING_TO_DESTINATION], true) || $help->status === Help::STATUS_IN_PROGRESS) {
            $partnerLat = $currentPartnerLat ?: (float) ($help->partner_current_lat ?: $help->partner_initial_lat ?: 0);
            $partnerLng = $currentPartnerLng ?: (float) ($help->partner_current_lng ?: $help->partner_initial_lng ?: 0);

            $dCancel = $this->geoService->calculateCancelDistanceAfterPickup($help, $partnerLat, $partnerLng);
            $maxPostPickupKm = AppSetting::getPickupDeliveryMaxCancellationDistanceAfterPickup(); // 5.0 KM

            // Kondisi F: Lock > 5 KM
            if ($dCancel > $maxPostPickupKm) {
                return [
                    'allowed'   => false,
                    'condition' => 'F_LOCKED_OVER_5KM',
                    'reason'    => "Pembatalan ditolak. Rekan jasa telah menempuh {$dCancel} KM dari titik penjemputan (melebihi batas maksimal pembatalan {$maxPostPickupKm} KM). Pembatalan terkunci demi keselamatan barang/penumpang, pengantaran wajib diselesaikan.",
                    'd_cancel'  => $dCancel,
                ];
            }

            // Kondisi E: Boleh Batal <= 5 KM dari titik jemput
            return [
                'allowed'   => true,
                'condition' => 'E_POST_PICKUP_UNDER_5KM',
                'reason'    => null,
                'd_cancel'  => $dCancel,
            ];
        }

        return [
            'allowed'   => true,
            'condition' => 'STANDARD',
            'reason'    => null,
            'd_cancel'  => 0.0,
        ];
    }

    /**
     * LANGKAH 2: Kalkulasi Formula Finansial Pembatalan (Financial Settlement Calculation).
     * Menghitung nilai pasti kompensasi mitra, refund customer, dan platform fee.
     */
    public function calculateCancellationFinancials(
        Help $help,
        string $condition,
        float $dCancel = 0.0,
        ?float $partnerLat = null,
        ?float $partnerLng = null
    ): array {
        $serviceFee   = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $materialFee  = (float) ($help->material_fee ?? 0);
        $itemFund     = (float) ($help->item_fund ?? 0);
        $platformFee  = (float) $help->getPlatformFee();
        $totalPaid    = (float) ($help->total_amount ?: ($serviceFee + $materialFee + $platformFee + $itemFund));

        $baseFare     = (float) ($help->base_fare_applied ?: AppSetting::getPickupDeliveryCancellationBaseFare());
        $pricePerKm   = (float) ($help->price_per_km_applied ?: AppSetting::getPickupDeliveryPricePerKm());

        // Hitung D_leg1 (Jarak jalan raya dari posisi awal mitra saat terima order ke Pickup)
        $dLeg1 = 0.0;
        $initLat = (float) ($help->partner_initial_lat ?: 0);
        $initLng = (float) ($help->partner_initial_lng ?: 0);
        $pickupLat = (float) ($help->pickup_latitude ?: $help->latitude ?: 0);
        $pickupLng = (float) ($help->pickup_longitude ?: $help->longitude ?: 0);

        if ($initLat != 0 && $initLng != 0 && $pickupLat != 0 && $pickupLng != 0) {
            $dLeg1 = $this->geoService->getRouteDistance($initLat, $initLng, $pickupLat, $pickupLng);
        } else {
            $dLeg1 = (float) ($help->travel_distance_km ?: $help->matching_distance_km ?: 1.0);
        }

        $compensationMitra = 0.0;
        $refundCustomer    = 0.0;
        $dCompensated      = 0.0;
        $platformRetained  = 0.0;

        switch ($condition) {
            case 'A_UNASSIGNED':
            case 'B_PRE_DEPARTURE':
                // 100% Refund utuh termasuk Biaya Layanan Platform
                $compensationMitra = 0.0;
                $refundCustomer    = $totalPaid;
                $platformRetained  = 0.0;
                break;

            case 'C_PICKUP_TRAVEL':
                // Kompensasi D_leg1: ceil(D_leg1) * 2.500 (dibatasi maksimal service_fee)
                $effectiveLeg1Km = max(1.0, ceil($dLeg1));
                $rawCompensation = $effectiveLeg1Km * $pricePerKm;
                $compensationMitra = min($serviceFee, $rawCompensation);

                // Platform fee tetap dipertahankan karena matching & dispatch telah berjalan
                $platformRetained  = $platformFee;
                $refundCustomer    = max(0.0, $serviceFee - $compensationMitra) + $materialFee + $itemFund;
                $dCompensated      = $dLeg1;
                break;

            case 'D_AT_PICKUP':
                // 100% Service Fee ke Mitra sebagai kompensasi tiba di titik jemput
                $compensationMitra = $serviceFee;
                $platformRetained  = $platformFee;
                $refundCustomer    = $materialFee + $itemFund;
                $dCompensated      = $dLeg1;
                break;

            case 'E_POST_PICKUP_UNDER_5KM':
                // D_compensated = D_leg1 + D_cancel
                $dCompensated = $dLeg1 + $dCancel;
                $effectiveCompKm = max(1.0, ceil($dCompensated));
                $rawCompensation = $baseFare + ($effectiveCompKm * $pricePerKm);
                $compensationMitra = min($serviceFee, $rawCompensation);

                $platformRetained  = $platformFee;
                $refundCustomer    = max(0.0, $serviceFee - $compensationMitra) + $materialFee + $itemFund;
                break;

            default:
                $compensationMitra = 0.0;
                $refundCustomer    = $totalPaid;
                $platformRetained  = 0.0;
                break;
        }

        return [
            'condition'           => $condition,
            'd_leg1_km'           => round($dLeg1, 2),
            'd_cancel_km'         => round($dCancel, 2),
            'd_compensated_km'    => round($dCompensated, 2),
            'compensation_mitra'  => round($compensationMitra, 2),
            'refund_customer'     => round($refundCustomer, 2),
            'platform_retained'   => round($platformRetained, 2),
            'total_paid'          => round($totalPaid, 2),
        ];
    }

    /**
     * LANGKAH 3: Eksekusi Pembatalan Antar/Jemput (Atomic Execution).
     */
    public function executeCancellation(
        Help $help,
        User $requester,
        string $reason,
        ?string $evidencePhotoPath = null,
        ?string $notes = null,
        ?float $partnerLat = null,
        ?float $partnerLng = null
    ): array {
        return DB::transaction(function () use ($help, $requester, $reason, $evidencePhotoPath, $notes, $partnerLat, $partnerLng) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            // 1. Evaluasi Kelayakan Pembatalan
            $eligibility = $this->evaluateCancellationEligibility($lockedHelp, $partnerLat, $partnerLng);
            if (!$eligibility['allowed']) {
                throw new \RuntimeException($eligibility['reason'] ?? 'Pesanan tidak dapat dibatalkan pada tahapan ini.');
            }

            // 2. Hitung Rincian Finansial Sesuai Kondisi
            $financials = $this->calculateCancellationFinancials(
                $lockedHelp,
                $eligibility['condition'],
                $eligibility['d_cancel'],
                $partnerLat,
                $partnerLng
            );

            $customer = $lockedHelp->user;
            $mitra    = $lockedHelp->mitra;

            // 3. Eksekusi Pembagian Dana Escrow
            if ($financials['refund_customer'] > 0 && $customer) {
                $this->escrowService->refundFromEscrowDirect(
                    $lockedHelp,
                    $customer,
                    $financials['refund_customer'],
                    "Pengembalian Dana Pembatalan ({$eligibility['condition']})"
                );
            }

            if ($financials['compensation_mitra'] > 0 && $mitra) {
                $this->escrowService->payoutPartialFromEscrowDirect(
                    $lockedHelp,
                    $mitra,
                    $financials['compensation_mitra'],
                    "Kompensasi Pembatalan Perjalanan ({$eligibility['condition']})"
                );
            }

            // 4. Update Status Order
            $escrowStatus = ($financials['compensation_mitra'] > 0)
                ? Help::ESCROW_STATUS_PARTIAL_REFUND
                : Help::ESCROW_STATUS_REFUNDED;

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'escrow_status'  => $escrowStatus,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
            ]);

            // 5. Bebaskan Mitra jika ada
            if ($mitra) {
                $this->onlineService->releaseBusy($mitra->id, $lockedHelp->id);
            }

            // 6. Buat Record Audit HelpCancelRequest
            $cancelRequest = HelpCancelRequest::create([
                'help_id'                    => $lockedHelp->id,
                'requester_type'             => ($requester->id === $lockedHelp->user_id) ? HelpCancelRequest::REQUESTER_CUSTOMER : HelpCancelRequest::REQUESTER_PARTNER,
                'partner_id'                 => $lockedHelp->mitra_id,
                'customer_id'                => $lockedHelp->user_id,
                'district_id'                => $lockedHelp->district_id,
                'previous_status'            => $lockedHelp->status,
                'previous_stage'             => $lockedHelp->service_stage,
                'd_cancel_km'                => $financials['d_cancel_km'],
                'd_leg1_km'                  => $financials['d_leg1_km'],
                'd_compensated_km'           => $financials['d_compensated_km'],
                'compensation_amount'        => $financials['compensation_mitra'],
                'refund_amount'              => $financials['refund_customer'],
                'cancellation_stage'         => $lockedHelp->service_stage ?: $lockedHelp->status,
                'reason'                     => $reason,
                'notes'                      => $notes,
                'evidence_photo'             => $evidencePhotoPath,
                'status'                     => HelpCancelRequest::STATUS_APPROVED,
                'settlement_type'            => ($financials['compensation_mitra'] > 0) ? HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT : HelpCancelRequest::SETTLEMENT_FULL_REFUND,
                'refund_amount_customer'     => $financials['refund_customer'],
                'payout_amount_mitra'        => $financials['compensation_mitra'],
                'requested_at'               => now(),
                'reviewed_at'                => now(),
                'audit_decision'             => HelpCancelRequest::AUDIT_VALID_NO_SP,
            ]);

            Log::info("[PickupDeliveryCancellationService] Cancellation executed successfully.", [
                'help_id'           => $lockedHelp->id,
                'condition'         => $eligibility['condition'],
                'financials'        => $financials,
                'cancel_request_id' => $cancelRequest->id,
            ]);

            return [
                'success'           => true,
                'condition'         => $eligibility['condition'],
                'financials'        => $financials,
                'cancel_request_id' => $cancelRequest->id,
                'message'           => "Pesanan berhasil dibatalkan. Pengembalian dana Rp " . number_format($financials['refund_customer'], 0, ',', '.') . " dan kompensasi mitra Rp " . number_format($financials['compensation_mitra'], 0, ',', '.') . " telah diproses otomatis.",
            ];
        });
    }
}
