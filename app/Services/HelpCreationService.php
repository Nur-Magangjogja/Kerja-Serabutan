<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpCreationService
{
    protected HelpPricingService $pricingService;
    protected HelpScheduleService $scheduleService;
    protected HelpMatchingService $matchingService;

    public function __construct(
        HelpPricingService $pricingService,
        HelpScheduleService $scheduleService,
        HelpMatchingService $matchingService
    ) {
        $this->pricingService  = $pricingService;
        $this->scheduleService = $scheduleService;
        $this->matchingService = $matchingService;
    }

    /**
     * Membuat pesanan bantuan baru secara aman, atomik, dan mencatat snapshot parameter.
     */
    public function createHelp(User $customer, array $data): Help
    {
        $serviceType = $data['service_type'] ?? Help::SERVICE_TYPE_ON_SITE;

        // 1. Validasi Jarak Maksimal untuk Pickup/Delivery
        $routeDistanceKm = (float) ($data['route_distance_km'] ?? $data['service_route_distance_km'] ?? 0.0);
        $maxDistanceKm   = AppSetting::getPickupDeliveryMaxDistanceKm(); // 40.0 KM

        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY && $routeDistanceKm > $maxDistanceKm) {
            throw new \InvalidArgumentException("Jarak pengantaran ({$routeDistanceKm} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk layanan sepeda motor.");
        }

        // 2. Hitung Rincian Harga & Estimasi Finansial
        $estimate = $this->pricingService->calculateInitialOrderEstimate([
            'service_type'              => $serviceType,
            'service_category'          => $data['service_category'] ?? 'general',
            'service_duration_hours'    => (float) ($data['service_duration_hours'] ?? 1.0),
            'amount'                    => (float) ($data['amount'] ?? 0),
            'service_route_distance_km' => $routeDistanceKm,
            'route_distance_km'         => $routeDistanceKm,
            'material_fee'              => (float) ($data['material_fee'] ?? 0),
            'item_fund'                 => (float) ($data['item_fund'] ?? 0),
            'pickup_latitude'           => $data['pickup_latitude'] ?? null,
            'pickup_longitude'          => $data['pickup_longitude'] ?? null,
            'delivery_latitude'         => $data['delivery_latitude'] ?? null,
            'delivery_longitude'        => $data['delivery_longitude'] ?? null,
            'latitude'                  => $data['latitude'] ?? null,
            'longitude'                 => $data['longitude'] ?? null,
        ]);

        $totalAmount = (float) $estimate['total_amount'];

        // 3. Validasi Saldo Customer
        $customerBalance = UserBalance::where('user_id', $customer->id)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0.0;

        if ($currentBalance < $totalAmount) {
            throw new \RuntimeException("Saldo Anda (Rp " . number_format($currentBalance, 0, ',', '.') . ") tidak mencukupi untuk total biaya Rp " . number_format($totalAmount, 0, ',', '.') . ".");
        }

        // 4. Hitung Jadwal & Visibilitas Pool (published_at)
        $targetScheduledAt = null;
        if (!empty($data['scheduled_date'])) {
            $time = !empty($data['scheduled_time']) ? trim($data['scheduled_time']) : '08:00';
            $targetScheduledAt = Carbon::parse($data['scheduled_date'] . ' ' . $time);
        }

        $orderMode = $targetScheduledAt ? Help::ORDER_MODE_SCHEDULED : Help::ORDER_MODE_INSTANT;
        $scheduleData = $this->scheduleService->computeScheduleTimestamps(
            $orderMode,
            $targetScheduledAt,
            0.0,
            $serviceType,
            $routeDistanceKm,
            !empty($data['early_departure_minutes']) ? (int) $data['early_departure_minutes'] : null
        );

        $expiresAt = !empty($data['expires_at'])
            ? Carbon::parse($data['expires_at'])
            : Carbon::now()->addHours(24);

        if ($targetScheduledAt && $expiresAt->lte($targetScheduledAt)) {
            $expiresAt = $targetScheduledAt->copy()->addHours(2);
        }

        // 5. Eksekusi Transaksi Database & Escrow Lock
        $createdHelp = DB::transaction(function () use ($customer, $data, $serviceType, $estimate, $totalAmount, $scheduleData, $expiresAt, $routeDistanceKm) {
            $orderId = $this->generateOrderId();

            $baseFareApplied    = (float) AppSetting::getPickupDeliveryBaseFare();
            $pricePerKmApplied  = (float) AppSetting::getPickupDeliveryPricePerKm();
            $platformFeeApplied = (float) ($estimate['platform_fee'] ?? AppSetting::getPlatformServiceFee());

            $help = Help::create([
                'user_id'                       => $customer->id,
                'order_id'                      => $orderId,
                'city_id'                       => $data['city_id'],
                'district_id'                   => $data['district_id'] ?? null,
                'title'                         => $data['title'],
                'service_type'                  => $serviceType,
                'service_stage'                 => null,
                'order_mode'                    => $scheduleData['order_mode'],
                'service_category'              => $data['service_category'] ?? 'general',
                'service_duration_hours'        => (float) ($data['service_duration_hours'] ?? 1.0),
                'amount'                        => $estimate['service_fee'],
                'service_fee'                   => $estimate['service_fee'],
                'travel_fee'                    => 0.0,
                'material_fee'                  => 0.0,
                'item_fund'                     => 0.0,
                'item_fund_mode'                => $data['item_fund_mode'] ?? 'customer_paid_in_app',
                'customer_reimbursement_method' => $data['customer_reimbursement_method'] ?? 'cash',
                'advance_limit'                 => (float) ($data['advance_limit'] ?? 100000),
                'minimum_service_fee'           => $estimate['minimum_service_fee'],
                'minimum_order_value'           => $estimate['minimum_order_value'],
                'admin_fee'                     => $platformFeeApplied,
                'platform_fee_amount'           => $platformFeeApplied,
                'total_amount'                  => $totalAmount,
                'mitra_earning'                 => $estimate['mitra_earning'],
                'description'                   => $data['description'],
                'equipment_provided'            => $data['equipment_provided'] ?? null,
                'location'                      => $data['location'] ?? null,
                'full_address'                  => $data['full_address'] ?? null,
                'latitude'                      => $data['latitude'],
                'longitude'                     => $data['longitude'],
                'pickup_address'                => $data['pickup_address'] ?? null,
                'pickup_latitude'               => $data['pickup_latitude'] ?? null,
                'pickup_longitude'              => $data['pickup_longitude'] ?? null,
                'delivery_address'              => $data['delivery_address'] ?? null,
                'delivery_latitude'             => $data['delivery_latitude'] ?? null,
                'delivery_longitude'            => $data['delivery_longitude'] ?? null,
                'store_name'                    => null,
                'store_address'                 => null,
                'store_latitude'                => null,
                'store_longitude'               => null,
                'service_route_distance_km'     => ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $routeDistanceKm : 0.0,
                
                // Snapshot Immutability Fields
                'base_fare_applied'             => $baseFareApplied,
                'price_per_km_applied'          => $pricePerKmApplied,
                'platform_fee_applied'          => $platformFeeApplied,
                'estimated_road_distance_km'    => ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $routeDistanceKm : null,

                // Scheduling Fields
                'scheduled_at'                  => $scheduleData['service_scheduled_at'],
                'published_at'                  => $scheduleData['published_at'],
                'departure_at'                  => $scheduleData['departure_at'],
                'service_scheduled_at'          => $scheduleData['service_scheduled_at'],
                'pickup_scheduled_at'           => $scheduleData['pickup_scheduled_at'],
                'delivery_deadline_at'          => $scheduleData['delivery_deadline_at'],
                'early_departure_minutes'       => $scheduleData['early_departure_minutes'],
                'expires_at'                    => $expiresAt->format('Y-m-d H:i:s'),
                'photo'                         => $data['photo_path'] ?? null,
                'status'                        => Help::STATUS_MENUNGGU_MITRA,
                'payment_status'                => Help::PAYMENT_STATUS_PAID,
                'escrow_status'                 => Help::ESCROW_STATUS_HELD,
                'dispatch_mode'                 => ($scheduleData['order_mode'] === Help::ORDER_MODE_SCHEDULED || $serviceType !== Help::SERVICE_TYPE_ON_SITE) ? Help::DISPATCH_MODE_POOL : Help::DISPATCH_MODE_SEEKING,
                'rating_status'                 => Help::RATING_STATUS_PENDING,
                'model_version'                 => 3,
                'escrow_locked_at'              => now(),
            ]);

            // Escrow Lock ke Saldo Customer
            $customerBalance = UserBalance::firstOrCreate(
                ['user_id' => $customer->id],
                ['balance' => 0]
            );

            $descParts = [];
            $descParts[] = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY ? "Ongkos Antar: " : "Jasa: ") . "Rp " . number_format($estimate['service_fee'], 0, ',', '.');
            $descParts[] = "Layanan: Rp " . number_format($platformFeeApplied, 0, ',', '.');
            $lockDescription = "Dana Ditahan untuk Permintaan Bantuan '{$help->title}' (" . implode(' + ', $descParts) . ")";

            $escrowTx = $customerBalance->lockForEscrow(
                $totalAmount,
                $help->id,
                $help->order_id,
                $lockDescription
            );
            $help->update(['escrow_transaction_id' => $escrowTx->id]);

            Log::info("[HelpCreationService] Help #{$help->id} ({$help->order_id}) successfully created. Escrow locked Rp {$totalAmount}.", [
                'service_type' => $serviceType,
                'distance_km'  => $routeDistanceKm,
                'published_at' => $scheduleData['published_at'],
            ]);

            return $help;
        });

        // 6. Picu Matching Engine jika On-Site Instan dan Fitur Seeking Aktif
        if (
            $createdHelp &&
            $createdHelp->order_mode === Help::ORDER_MODE_INSTANT &&
            $createdHelp->service_type === Help::SERVICE_TYPE_ON_SITE &&
            AppSetting::isMatchingSeekingEnabled($createdHelp->city_id)
        ) {
            try {
                $this->matchingService->initiateMatching($createdHelp);
            } catch (\Throwable $e) {
                Log::error('[HelpCreationService] Failed initiating matching: ' . $e->getMessage(), [
                    'help_id' => $createdHelp->id,
                ]);
            }
        }

        return $createdHelp;
    }

    private function generateOrderId(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $candidate = 'HELP-' . date('YmdHis') . '-' . random_int(1000, 9999);
            if (!Help::where('order_id', $candidate)->exists()) {
                return $candidate;
            }
            usleep(200);
        }
        return 'HELP-' . uniqid();
    }
}
