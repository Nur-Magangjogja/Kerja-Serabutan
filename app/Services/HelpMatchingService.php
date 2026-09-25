<?php

namespace App\Services;

use App\Jobs\HandleOfferExpiry;
use App\Models\AppSetting;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\Rating;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpMatchingService
{
    protected PartnerOnlineService $onlineService;
    protected GeoService $geoService;
    protected HelpPricingService $pricingService;

    public function __construct(
        PartnerOnlineService $onlineService,
        GeoService $geoService,
        HelpPricingService $pricingService
    ) {
        $this->onlineService  = $onlineService;
        $this->geoService     = $geoService;
        $this->pricingService = $pricingService;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. SCORING & CANDIDATE RANKING
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil statistik rating batch dengan caching untuk menghindari query agregasi berulang.
     */
    public function getCachedRatingStatsBatch(array $userIds): Collection
    {
        if (empty($userIds)) {
            return collect();
        }

        $results = collect();
        $missingUserIds = [];

        foreach ($userIds as $uid) {
            $cached = Cache::get("mitra_rating_stats_{$uid}");
            if ($cached !== null) {
                $results->put($uid, (object) $cached);
            } else {
                $missingUserIds[] = $uid;
            }
        }

        if (!empty($missingUserIds)) {
            $freshStats = Rating::whereIn('ratee_id', $missingUserIds)
                ->where(function ($q) {
                    $q->where('type', 'customer_to_mitra')
                      ->orWhereNull('type');
                })
                ->groupBy('ratee_id')
                ->selectRaw('ratee_id, COUNT(*) as rating_count, SUM(rating) as rating_sum')
                ->get()
                ->keyBy('ratee_id');

            foreach ($missingUserIds as $uid) {
                $stat = $freshStats->get($uid);
                $data = [
                    'ratee_id'     => $uid,
                    'rating_count' => $stat ? (int) $stat->rating_count : 0,
                    'rating_sum'   => $stat ? (float) $stat->rating_sum : 0.0,
                ];
                Cache::put("mitra_rating_stats_{$uid}", $data, now()->addSeconds(60));
                $results->put($uid, (object) $data);
            }
        }

        return $results;
    }

    /**
     * Ambil statistik riwayat bantuan batch (taken & completed) dengan caching.
     */
    public function getCachedHelpStatsBatch(array $userIds): Collection
    {
        if (empty($userIds)) {
            return collect();
        }

        $results = collect();
        $missingUserIds = [];

        foreach ($userIds as $uid) {
            $cached = Cache::get("mitra_help_stats_{$uid}");
            if ($cached !== null) {
                $results->put($uid, (object) $cached);
            } else {
                $missingUserIds[] = $uid;
            }
        }

        if (!empty($missingUserIds)) {
            $freshStats = Help::whereIn('mitra_id', $missingUserIds)
                ->groupBy('mitra_id')
                ->selectRaw("
                    mitra_id,
                    COUNT(*) as taken_count,
                    SUM(CASE WHEN status = '" . Help::STATUS_SELESAI . "' THEN 1 ELSE 0 END) as completed_count
                ")
                ->get()
                ->keyBy('mitra_id');

            foreach ($missingUserIds as $uid) {
                $stat = $freshStats->get($uid);
                $data = [
                    'mitra_id'        => $uid,
                    'taken_count'     => $stat ? (int) $stat->taken_count : 0,
                    'completed_count' => $stat ? (int) $stat->completed_count : 0,
                ];
                Cache::put("mitra_help_stats_{$uid}", $data, now()->addSeconds(60));
                $results->put($uid, (object) $data);
            }
        }

        return $results;
    }

    /**
     * Hitung jarak Haversine antara dua titik koordinat (dalam km).
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Hitung skor Bayesian Rating mitra (skala 0.0 - 1.0).
     */
    public function calculateBayesianRatingScore(User $mitra, ?object $prefetchedRating = null, ?array $config = null): float
    {
        if ($prefetchedRating !== null) {
            $count = (float) ($prefetchedRating->rating_count ?? 0);
            $sum   = (float) ($prefetchedRating->rating_sum ?? 0);
        } else {
            $ratings = Rating::where('ratee_id', $mitra->id)
                ->where(function ($q) {
                    $q->where('type', 'customer_to_mitra')
                      ->orWhereNull('type');
                });

            $count = (float) $ratings->count();
            $sum   = (float) $ratings->sum('rating');
        }

        $C = (float) ($config['rating_min_votes'] ?? AppSetting::getRatingMinVotes());
        $m = (float) ($config['neutral_rating_prior'] ?? AppSetting::getNeutralRatingPrior());

        $bayesianAvg = ($C * $m + $sum) / ($C + $count);

        return min(1.0, max(0.0, $bayesianAvg / 5.0));
    }

    /**
     * Hitung skor reliabilitas (completion rate) mitra (skala 0.0 - 1.0).
     */
    public function calculateReliabilityScore(User $mitra, ?object $prefetchedHelp = null): float
    {
        if ($prefetchedHelp !== null) {
            $takenCount = (int) ($prefetchedHelp->taken_count ?? 0);
            if ($takenCount === 0) {
                return 1.0; // Nilai default sempurna untuk mitra baru
            }
            $completedCount = (int) ($prefetchedHelp->completed_count ?? 0);
        } else {
            $takenCount = Help::where('mitra_id', $mitra->id)->count();
            if ($takenCount === 0) {
                return 1.0; // Nilai default sempurna untuk mitra baru
            }
            $completedCount = Help::where('mitra_id', $mitra->id)
                ->where('status', Help::STATUS_SELESAI)
                ->count();
        }

        return min(1.0, max(0.0, $completedCount / $takenCount));
    }

    /**
     * Hitung skor fairness berdasarkan durasi tunggu / waktu antrean pencarian (skala 0.0 - 1.0, cap dinamis)
     * dan memberikan Newbie Boost untuk akun mitra baru.
     */
    public function calculateFairnessScore(PartnerOnlineState $state, ?User $mitra = null, ?object $prefetchedHelp = null, ?array $config = null): float
    {
        // 1. Prioritaskan searching_since karena mitra sedang aktif mengantre mencari order
        $referenceTime = $state->searching_since ?? $state->last_completed_at ?? $state->last_seen_at;

        if (!$referenceTime) {
            $baseScore = 0.0;
        } else {
            $capMinutes = (float) ($config['max_fairness_minutes'] ?? AppSetting::getMaxFairnessBoostMinutes());
            $minutesElapsed = max(0, $referenceTime->diffInMinutes(now()));
            $baseScore = min(1.0, $minutesElapsed / max(1.0, $capMinutes));
        }

        // Newbie Boost: Berikan baseline fairness minimal bagi mitra baru yang terdaftar <= 7 hari atau < 3 order
        $isNewbieEnabled = $config['newbie_enabled'] ?? AppSetting::isNewbieBoostEnabled();
        if ($mitra && $isNewbieEnabled) {
            $boostDays = (int) ($config['newbie_days'] ?? AppSetting::getNewbieBoostDays());
            $threshold = (int) ($config['newbie_threshold'] ?? AppSetting::getNewbieOrderThreshold());
            $minScore  = (float) ($config['newbie_min_score'] ?? AppSetting::getNewbieMinFairnessScore());

            $isNewAccount = $mitra->created_at && $mitra->created_at->diffInDays(now()) <= $boostDays;
            
            if ($prefetchedHelp !== null) {
                $completedCount = (int) ($prefetchedHelp->completed_count ?? 0);
            } else {
                $completedCount = Help::where('mitra_id', $mitra->id)->where('status', Help::STATUS_SELESAI)->count();
            }

            if ($isNewAccount && $completedCount < $threshold) {
                $baseScore = max($baseScore, $minScore);
            }
        }

        return $baseScore;
    }

    /**
     * Hitung batas maksimum radius matching untuk order ini:
     * - On-Site (Kerja Serabutan): Tiered radius berdasarkan nominal biaya bantuan:
     *     * Biaya Rp 10.000 - Rp 20.000: Maksimal 5.0 KM
     *     * Biaya Rp 20.001 - Rp 40.000: Maksimal 7.5 KM
     *     * Biaya > Rp 40.000: Maksimal 10.0 KM
     * - Pickup/Delivery: Maksimal 10.0 KM (dengan dual-ring: 5 KM priority, 10 KM fallback)
     */
    public function computeServiceMaxMatchingRadius(Help $help): float
    {
        if ($help->service_type === Help::SERVICE_TYPE_ON_SITE) {
            $amount = (float) ($help->amount ?? $help->service_fee ?? 0);
            if ($amount <= 20000) {
                return 5.0;
            } elseif ($amount <= 40000) {
                return 7.5;
            }
            return (float) AppSetting::MAX_OPERATIONAL_RADIUS_KM; // 10.0 KM
        }

        return (float) AppSetting::MAX_OPERATIONAL_RADIUS_KM; // 10.0 KM
    }

    /**
     * Hitung skor komposit mitra untuk order bantuan tertentu berdasarkan bobot AppSetting.
     */
    public function calculatePartnerCompositeScore(
        Help $help, 
        User $mitra, 
        PartnerOnlineState $state, 
        ?object $prefetchedRating = null, 
        ?object $prefetchedHelp = null, 
        ?array $config = null
    ): array {
        $maxRadius = (float) ($config['max_radius'] ?? $this->computeServiceMaxMatchingRadius($help));
        $weights   = $config['weights'] ?? AppSetting::getMatchingWeights();

        // 1. Distance & District Alignment Score (Maksimal radius dari Titik Awal Layanan)
        if ($help->isPickup()) {
            $targetLat = (float) ($help->pickup_latitude ?: $help->latitude);
            $targetLng = (float) ($help->pickup_longitude ?: $help->longitude);
        } else {
            $targetLat = (float) ($help->latitude ?: 0);
            $targetLng = (float) ($help->longitude ?: 0);
        }

        $mitraLat = (float) ($state->latitude ?? $mitra->latitude ?? 0);
        $mitraLng = (float) ($state->longitude ?? $mitra->longitude ?? 0);

        if ($targetLat != 0 && $targetLng != 0 && $mitraLat != 0 && $mitraLng != 0) {
            $distKm = $this->geoService->calculateStraightDistance($targetLat, $targetLng, $mitraLat, $mitraLng);
            $distScore = max(0.0, 1.0 - min($distKm / max(0.1, $maxRadius), 1.0));
        } else {
            // Jika koordinat belum lengkap, gunakan kedekatan administratif Kecamatan
            $distKm = 0.0;
            $distScore = ($help->district_id && $mitra->district_id && $help->district_id === $mitra->district_id) ? 1.0 : 0.8;
        }

        // 2. Rating Bayesian Score (In-Memory)
        $ratingScore = $this->calculateBayesianRatingScore($mitra, $prefetchedRating, $config);

        // 3. Reliability Score (In-Memory)
        $reliabilityScore = $this->calculateReliabilityScore($mitra, $prefetchedHelp);

        // 4. Fairness Score dengan Newbie Boost (In-Memory)
        $fairnessScore = $this->calculateFairnessScore($state, $mitra, $prefetchedHelp, $config);

        // Total Score dynamically weighted
        $totalScore = ($weights['distance'] * $distScore) +
                      ($weights['rating'] * $ratingScore) +
                      ($weights['reliability'] * $reliabilityScore) +
                      ($weights['fairness'] * $fairnessScore);

        return [
            'total_score'       => round($totalScore, 4),
            'distance_km'       => round($distKm, 2),
            'distance_score'    => round($distScore, 4),
            'rating_score'      => round($ratingScore, 4),
            'reliability_score' => round($reliabilityScore, 4),
            'fairness_score'    => round($fairnessScore, 4),
            'searching_since'   => $state->searching_since?->toIso8601String(),
        ];
    }

    /**
     * Dapatkan daftar kandidat mitra terurut (Top N) untuk order tertentu.
     * Menggunakan kombinasi: Primary District + Adjacent District Expansion + GPS aktual <= Max Radius + Status + Kemampuan + Jadwal.
     */
    public function getRankedCandidates(Help $help, array $excludeMitraIds = [], ?float $ringRadius = null): Collection
    {
        $ttl = AppSetting::getHeartbeatTtlSeconds();
        $serviceMaxRadius = $this->computeServiceMaxMatchingRadius($help);
        $maxMatchingRadius = $ringRadius !== null ? min($serviceMaxRadius, $ringRadius) : $serviceMaxRadius;

        $excludedPartnerIds = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions')) {
                $excludedPartnerIds = \App\Models\HelpPartnerExclusion::where('help_id', $help->id)
                    ->pluck('mitra_id')
                    ->all();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $cancelledIds = $help->cancelled_mitra_ids ?? [];
        if (!is_array($cancelledIds)) {
            $cancelledIds = json_decode((string) $cancelledIds, true) ?? [];
        }
        $excludeIds = array_unique(array_merge($excludeMitraIds, $cancelledIds, $excludedPartnerIds));

        // 1. Tentukan Titik Koordinat Sasaran Pencocokan (Matching Target)
        if ($help->isPickup()) {
            $targetLat = (float) ($help->pickup_latitude ?: $help->latitude);
            $targetLng = (float) ($help->pickup_longitude ?: $help->longitude);
        } else {
            $targetLat = (float) ($help->latitude ?: 0);
            $targetLng = (float) ($help->longitude ?: 0);
        }

        // Tentukan daftar kecamatan yang eligible (Primary + Adjacent Districts)
        $eligibleDistrictIds = [];
        if ($help->district_id) {
            $eligibleDistrictIds[] = (int) $help->district_id;
            if (AppSetting::isAdjacentDistrictMatchingEnabled()) {
                $district = $help->relationLoaded('district') ? $help->district : \App\Models\District::find($help->district_id);
                if ($district) {
                    $adjacentIds = $district->getAdjacentDistricts()->pluck('id')->all();
                    $eligibleDistrictIds = array_unique(array_merge($eligibleDistrictIds, $adjacentIds));
                }
            }
        }

        $effectiveServiceType = $help->service_type ?: Help::SERVICE_TYPE_ON_SITE;

        $statesQuery = PartnerOnlineState::eligibleForMatching($ttl)
            ->whereNull('current_help_id')
            ->where(function ($q) use ($effectiveServiceType) {
                $q->where('service_preference', PartnerOnlineState::PREFERENCE_ALL)
                  ->orWhere('service_preference', $effectiveServiceType)
                  ->orWhereNull('service_preference');
            })
            ->whereHas('user', function ($q) use ($help, $excludeIds, $eligibleDistrictIds) {
                $q->where('role', 'mitra')
                  ->where('status', 'active')
                  ->where('is_shadow_banned', false)
                  ->where('warning_level', '<', 3)
                  ->whereNotIn('id', $excludeIds);

                // Untuk layanan Antar & Jemput, hanya kandidat yang sudah melengkapi & diverifikasi kendaraannya (Keduanya SIM & STNK)
                if ($help->isPickup()) {
                    $q->where('vehicle_verified', true)
                      ->where('vehicle_verification_status', 'verified')
                      ->whereNotNull('vehicle_plate_number')
                      ->whereNotNull('vehicle_sim_number')
                      ->whereNotNull('vehicle_sim_photo')
                      ->whereNotNull('vehicle_stnk_number')
                      ->whereNotNull('vehicle_stnk_photo');
                }

                if (!empty($eligibleDistrictIds)) {
                    $q->where(function ($sub) use ($eligibleDistrictIds, $help) {
                        $sub->whereIn('district_id', $eligibleDistrictIds)
                            ->orWhere(function ($s2) use ($help) {
                                if ($help->city_id) {
                                    $s2->where('city_id', $help->city_id);
                                }
                            });
                    });
                } elseif ($help->city_id) {
                    $q->where('city_id', $help->city_id);
                }
            });

        // Bounding Box Pre-Filter: Batasi kandidat secara instan berbasis rentang maxMatchingRadius
        if ($targetLat != 0 && $targetLng != 0) {
            $latDelta = $maxMatchingRadius / 111.045;
            $lngDelta = $maxMatchingRadius / (111.045 * max(0.01, cos(deg2rad($targetLat))));

            $minLat = $targetLat - $latDelta;
            $maxLat = $targetLat + $latDelta;
            $minLng = $targetLng - $lngDelta;
            $maxLng = $targetLng + $lngDelta;

            $statesQuery->where(function ($q) use ($minLat, $maxLat, $minLng, $maxLng) {
                $q->where(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng) {
                    $sub->whereBetween('latitude', [$minLat, $maxLat])
                        ->whereBetween('longitude', [$minLng, $maxLng]);
                })->orWhereNull('latitude')->orWhereNull('longitude');
            });
        }

        // Top-N Candidate Pre-selection
        $eligibleStates = $statesQuery->with(['user', 'user.district'])->limit(50)->get();

        if ($eligibleStates->isEmpty()) {
            return collect();
        }

        $userIds = $eligibleStates->pluck('user_id')->unique()->filter()->values()->all();

        // 2. Bulk Aggregate & Cache: Rating & Help Stats Batch Retrieval
        $ratingStats = $this->getCachedRatingStatsBatch($userIds);
        $helpStats   = $this->getCachedHelpStatsBatch($userIds);

        // 3. Batch Pre-fetch All System Configs (In-Memory)
        $config = [
            'max_radius'           => $maxMatchingRadius,
            'weights'              => AppSetting::getMatchingWeights(),
            'rating_min_votes'     => (float) AppSetting::getRatingMinVotes(),
            'neutral_rating_prior' => (float) AppSetting::getNeutralRatingPrior(),
            'max_fairness_minutes' => (float) AppSetting::getMaxFairnessBoostMinutes(),
            'newbie_enabled'       => (bool) AppSetting::isNewbieBoostEnabled(),
            'newbie_days'          => (int) AppSetting::getNewbieBoostDays(),
            'newbie_threshold'     => (int) AppSetting::getNewbieOrderThreshold(),
            'newbie_min_score'     => (float) AppSetting::getNewbieMinFairnessScore(),
        ];

        // 4. In-Memory Mathematical Scoring & Hard Filter MAX_MATCHING_DISTANCE <= maxMatchingRadius
        $scoredCandidates = $eligibleStates->map(function ($state) use ($help, $ratingStats, $helpStats, $config, $maxMatchingRadius) {
            $userRating = $ratingStats->get($state->user_id);
            $userHelp   = $helpStats->get($state->user_id);

            $scoreDetails = $this->calculatePartnerCompositeScore(
                $help,
                $state->user,
                $state,
                $userRating,
                $userHelp,
                $config
            );

            // HARD LIMIT RULE: Jika jarak matching mitra ke titik awal > maxMatchingRadius, eliminasi kandidat
            if ($scoreDetails['distance_km'] > $maxMatchingRadius && $scoreDetails['distance_km'] > 0) {
                return null;
            }

            return (object) [
                'user'            => $state->user,
                'state'           => $state,
                'score_details'   => $scoreDetails,
                'total_score'     => $scoreDetails['total_score'],
                'searching_since' => $state->searching_since ?? now(),
                'user_id'         => $state->user_id,
            ];
        })->filter()->values();

        // 5. Deterministic Sorting: Total Score DESC, Waiting Time DESC (searching_since ASC), User ID ASC
        return $scoredCandidates->sort(function ($a, $b) {
            if ($b->total_score <=> $a->total_score) {
                return $b->total_score <=> $a->total_score;
            }

            if ($a->searching_since <=> $b->searching_since) {
                return $a->searching_since <=> $b->searching_since;
            }

            return $a->user_id <=> $b->user_id;
        })->values();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. MATCHING INITIATION & SEQUENTIAL DISPATCH
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Memulai proses pencocokan otomatis (Matching Engine) untuk bantuan baru.
     * Mengurutkan Top N mitra dan mengirim tawaran ke Rank 1.
     */
    public function initiateMatching(Help $help): bool
    {
        // 1. Cek konfigurasi SuperAdmin / Wilayah: Apakah fitur cari order / matching seeking aktif
        if (!AppSetting::isMatchingSeekingEnabled($help->city_id)) {
            Log::info("[HelpMatchingService] Cari order is disabled for City #{$help->city_id}. Falling back to Open Pool for Help #{$help->id}.");
            $this->fallbackToOpenPool($help);
            return false;
        }

        if ($help->order_mode === Help::ORDER_MODE_SCHEDULED || $help->dispatch_mode === Help::DISPATCH_MODE_POOL) {
            $this->fallbackToOpenPool($help);
            return false;
        }

        // 2. Matching Radius:
        // - Pickup & Delivery: Dual-Ring (Prioritas Ring 1, fallback ke Ring 2)
        // - On-Site: Tiered radius berdasarkan nominal biaya bantuan (5.0 KM, 7.5 KM, atau 10.0 KM)
        if ($help->isPickup()) {
            if (!AppSetting::isPickupDeliveryMatchingEnabled()) {
                Log::info("[HelpMatchingService] Cari order is disabled for Pickup/Delivery globally. Falling back to Open Pool for Help #{$help->id}.");
                $this->fallbackToOpenPool($help);
                return false;
            }

            $ring1 = AppSetting::getPickupDeliveryMatchingRing1Km();
            $ring2 = AppSetting::getPickupDeliveryMaxMatchingRadiusKm();

            // Ring 1: Prioritas <= ring1 KM dari titik jemput
            $candidates = $this->getRankedCandidates($help, [], $ring1);
            
            // Ring 2: Fallback hingga ring2 KM jika ring 1 kosong
            if ($candidates->isEmpty()) {
                Log::info("[HelpMatchingService] Priority Ring (<= {$ring1} KM) empty for Pickup/Delivery Help #{$help->id}. Expanding to Fallback Ring (<= {$ring2} KM).");
                $candidates = $this->getRankedCandidates($help, [], $ring2);
            }
        } else {
            $maxRadius = $this->computeServiceMaxMatchingRadius($help);
            $candidates = $this->getRankedCandidates($help, [], $maxRadius);
        }

        if ($candidates->isEmpty()) {
            Log::info("[HelpMatchingService] No eligible candidates found for Help #{$help->id}. Falling back to Open Pool.");
            $this->fallbackToOpenPool($help);
            return false;
        }

        // Ambil Top N kandidat dari AppSetting
        $maxCandidates = AppSetting::getMaxDispatchCandidates();
        $topCandidates = $candidates->take($maxCandidates);

        Log::info("[HelpMatchingService] Found {$topCandidates->count()} candidates for Help #{$help->id}. Initiating Rank 1 dispatch.");

        return $this->dispatchOfferToCandidate($help, $topCandidates, 1, 1);
    }

    /**
     * Mengirim tawaran order ke kandidat peringkat tertentu.
     * Dual Pessimistic Locking (Help + PartnerOnlineState) dalam SATU Transaksi Atomik Murni.
     * Mengeliminasi race condition gap antara status Mitra dan status Help/Dispatch.
     */
    public function dispatchOfferToCandidate(Help $help, Collection $candidates, int $rank, int $round = 1): bool
    {
        $candidateIndex = $rank - 1;

        if (!$candidates->has($candidateIndex)) {
            Log::info("[HelpMatchingService] No candidate available at Rank {$rank} for Help #{$help->id}. Opening Pool.");
            $this->fallbackToOpenPool($help);
            return false;
        }

        $candidate  = $candidates->get($candidateIndex);
        $mitra      = $candidate->user;
        $ttl        = AppSetting::getHeartbeatTtlSeconds();
        $timeoutSec = AppSetting::getOfferTimeoutSeconds();
        $expiresAt  = now()->addSeconds($timeoutSec);

        // Eksekusi Single Atomic Transaction dengan Dual Pessimistic Locking
        $dispatchResult = DB::transaction(function () use ($help, $mitra, $round, $rank, $expiresAt, $candidate, $ttl) {
            // 1. Lock record Help dan verifikasi ketersediaan order
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp || $lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                Log::warning("[HelpMatchingService] Cannot dispatch offer: Help #{$help->id} is already taken or unavailable.");
                return null; // Order sudah diambil orang lain atau dibatalkan
            }

            // 2. Lock record PartnerOnlineState dan verifikasi mitra masih searching dengan heartbeat segar
            if ($mitra->isShadowBanned() || $mitra->warning_level >= 3 || $mitra->status !== 'active') {
                Log::warning("[HelpMatchingService] Cannot lock Mitra #{$mitra->id}: account is restricted (shadow_banned, SP3, or not active).");
                return false;
            }

            // Validasi Kelayakan Kendaraan untuk Layanan Antar & Jemput
            if ($lockedHelp->isPickup() && !$mitra->canTakePickupDelivery()) {
                Log::warning("[HelpMatchingService] Cannot dispatch offer to Mitra #{$mitra->id}: missing or unverified vehicle data for pickup_delivery.");
                return false;
            }

            $state = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$state || $state->matching_status !== PartnerOnlineState::STATUS_SEARCHING || !$state->isHeartbeatFresh($ttl)) {
                Log::warning("[HelpMatchingService] Cannot lock Mitra #{$mitra->id} at Rank {$rank}: status '{$state?->matching_status}', fresh: " . ($state?->isHeartbeatFresh($ttl) ? 'yes' : 'no'));
                return false; // Mitra tidak dapat dikunci (sudah offline/ambil order), lanjut ke rank berikutnya
            }

            // Validasi Preferensi Jenis Layanan Mitra
            $effectiveServiceType = $lockedHelp->service_type ?: Help::SERVICE_TYPE_ON_SITE;
            if (!$state->allowsServiceType($effectiveServiceType)) {
                Log::info("[HelpMatchingService] Skipping Mitra #{$mitra->id} for Help #{$lockedHelp->id}: preference '{$state->service_preference}' does not allow service type '{$effectiveServiceType}'.");
                return false;
            }

            // 3. Atomically update PartnerOnlineState menjadi OFFER_PENDING
            $state->matching_status = PartnerOnlineState::STATUS_OFFER_PENDING;
            $state->current_help_id = $lockedHelp->id;
            $state->save();
            \App\Services\PartnerOnlineService::clearStateCache($mitra->id);

            // 4. Atomically create HelpDispatch record
            $record = HelpDispatch::create([
                'help_id'        => $lockedHelp->id,
                'mitra_id'       => $mitra->id,
                'round'          => $round,
                'rank'           => $rank,
                'status'         => HelpDispatch::STATUS_OFFERED,
                'offered_at'     => now(),
                'expires_at'     => $expiresAt,
                'score_snapshot' => $candidate->score_details,
            ]);

            // 5. Atomically update Help dispatch mode
            $lockedHelp->update([
                'dispatch_mode' => Help::DISPATCH_MODE_OFFERED,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
            ]);

            return $record;
        });

        // Kasus 1: Order sudah tidak tersedia (diambil orang lain / dibatalkan)
        if ($dispatchResult === null) {
            return false;
        }

        // Kasus 2: Mitra tidak dapat dikunci (sudah offline / ambil order lain) -> Coba kandidat berikutnya
        if ($dispatchResult === false) {
            return $this->dispatchOfferToCandidate($help, $candidates, $rank + 1, $round);
        }

        $dispatch = $dispatchResult;

        // Jadwalkan job kadaluarsa tawaran
        HandleOfferExpiry::dispatch($dispatch->id)
            ->delay($expiresAt);

        // Kirim notifikasi / event ke Mitra
        try {
            $mitra->notify(new HelpStatusNotification(
                $help,
                Help::STATUS_MENUNGGU_MITRA,
                'offer_received',
                $help->user
            ));
        } catch (\Throwable $e) {
            Log::warning("[HelpMatchingService] Failed to send offer notification to Mitra #{$mitra->id}: " . $e->getMessage());
        }

        // Broadcast realtime WebSocket event via Laravel Reverb
        try {
            event(new \App\Events\MitraOfferDispatched($dispatch, $help, (int) $timeoutSec));
        } catch (\Throwable $e) {
            Log::warning("[HelpMatchingService] Failed to broadcast MitraOfferDispatched for Mitra #{$mitra->id}: " . $e->getMessage());
        }

        Log::info("[HelpMatchingService] Offer atomically dispatched to Mitra #{$mitra->id} (Rank {$rank}, Round {$round}) for Help #{$help->id}. Expires at {$expiresAt}.");
        return true;
    }

    /**
     * Memproses penerimaan tawaran oleh mitra (Accept Offer).
     * Atomic Lock: Menjamin maksimal 1 mitra yang berhasil mengambil order (No Double Assignment).
     */
    public function acceptOffer(int $dispatchId, User $mitra): Help
    {
        $isExpired = false;

        try {
            return DB::transaction(function () use ($dispatchId, $mitra, &$isExpired) {
                // STEP 1 (Tier 1): Intip help_id dari dispatch, lalu Lock baris Help terlebih dahulu
                $dispatchPeek = HelpDispatch::where('id', $dispatchId)->firstOrFail();

                $lockedHelp = Help::where('id', $dispatchPeek->help_id)->lockForUpdate()->firstOrFail();

                if ($lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA || $lockedHelp->isExpired()) {
                    if ($lockedHelp->isExpired()) {
                        app(\App\Services\HelpCancellationService::class)->autoCancelExpiredHelp($lockedHelp, 'Batas waktu pencarian rekan jasa telah habis.');
                    }
                    $this->onlineService->releaseCancelledOffer($mitra->id, $dispatchPeek->help_id);
                    throw new \RuntimeException('Bantuan ini sudah diambil, dibatalkan oleh pemesan, atau batas waktu pencarian telah habis.');
                }

                // STEP 2 (Tier 2): Lock baris HelpDispatch
                $dispatch = HelpDispatch::where('id', $dispatchId)->lockForUpdate()->firstOrFail();

                if ($dispatch->status !== HelpDispatch::STATUS_OFFERED) {
                    throw new \RuntimeException('Tawaran ini sudah tidak tersedia atau telah kadaluarsa.');
                }

                if ($dispatch->mitra_id !== $mitra->id) {
                    throw new \RuntimeException('Tawaran ini tidak ditujukan kepada akun Anda.');
                }

                // Toleransi latensi jaringan 2 detik (Network Grace Window) agar klik mitra di detik-detik terakhir tetap valid
                $graceExpiry = $dispatch->expires_at ? $dispatch->expires_at->copy()->addSeconds(2) : null;
                if ($graceExpiry && $graceExpiry->isPast()) {
                    $isExpired = true;
                    throw new \RuntimeException('Waktu respon penawaran telah habis.');
                }

                // Validasi Kelayakan Akun Mitra di Backend Service
            if ($mitra->role !== 'mitra') {
                throw new \RuntimeException('Hanya pengguna dengan peran Rekan Jasa (Mitra) yang diizinkan menerima tawaran bantuan.');
            }
            if ($mitra->status !== 'active') {
                throw new \RuntimeException('Akun Anda saat ini tidak aktif (' . ($mitra->status ?? 'non-aktif') . '). Harap hubungi administrator.');
            }
            if ($mitra->isShadowBanned()) {
                throw new \RuntimeException('Akun Anda sedang dalam pembatasan fitur (moderasi) dan tidak diizinkan menerima tawaran.');
            }
            if ($mitra->warning_level >= 3) {
                throw new \RuntimeException('Akun Anda sedang dalam masa penangguhan (SP 3) akibat pelanggaran kepatuhan.');
            }

            // Validasi Kelayakan Kendaraan untuk Layanan Antar & Jemput
            if ($lockedHelp->isPickup() && !$mitra->canTakePickupDelivery()) {
                $dispatch->update([
                    'status'           => HelpDispatch::STATUS_REJECTED,
                    'responded_at'     => now(),
                    'rejection_reason' => 'Data kendaraan tidak lengkap atau belum diverifikasi admin',
                ]);
                throw new \RuntimeException('Untuk menerima tawaran Antar & Jemput, data kendaraan (Plat Nomor, SIM Motor, dan STNK) harus telah diverifikasi oleh Admin.');
            }

            // STEP 3 (Tier 3): Lock PartnerOnlineState mitra
            $partnerState = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$partnerState || $partnerState->matching_status !== PartnerOnlineState::STATUS_OFFER_PENDING || $partnerState->current_help_id != $dispatch->help_id) {
                $dispatch->update([
                    'status'           => HelpDispatch::STATUS_REJECTED,
                    'responded_at'     => now(),
                    'rejection_reason' => 'Status penawaran tidak valid atau mitra tidak sedang menunggu tawaran ini',
                ]);
                throw new \RuntimeException('Status penawaran Anda sudah tidak valid, telah kadaluarsa, atau telah dibatalkan.');
            }

            // Lock & pastikan mitra tidak sedang memiliki pekerjaan aktif lain di tabel helps
            $hasOtherActive = Help::where('mitra_id', $mitra->id)
                ->where('id', '!=', $dispatch->help_id)
                ->active()
                ->lockForUpdate()
                ->exists();

            if ($hasOtherActive) {
                $dispatch->update([
                    'status'           => HelpDispatch::STATUS_REJECTED,
                    'responded_at'     => now(),
                    'rejection_reason' => 'Mitra sedang sibuk mengerjakan tugas aktif lain',
                ]);
                $this->onlineService->revertFromOfferPending($mitra->id, $dispatch->help_id);
                throw new \RuntimeException('Anda tidak dapat menerima tawaran ini karena sedang memiliki tugas aktif yang berjalan.');
            }

            // STEP 4: Finalisasi Tarif & Jarak Perjalanan Aktual Mitra (Fase 2 Pricing)
            $partnerLat = (float) ($partnerState->latitude ?? $mitra->latitude ?? 0);
            $partnerLng = (float) ($partnerState->longitude ?? $mitra->longitude ?? 0);

            $finalPricing = $this->pricingService->finalizePartnerPricing($lockedHelp, $partnerLat, $partnerLng);

            // STEP 5: Mutasi Atomik Bersama
            $dispatch->update([
                'status'       => HelpDispatch::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            $lockedHelp->update(array_merge([
                'mitra_id'            => $mitra->id,
                'status'              => Help::STATUS_TAKEN,
                'dispatch_mode'       => Help::DISPATCH_MODE_ASSIGNED,
                'assigned_at'         => now(),
                'taken_at'            => now(),
                'partner_initial_lat' => $partnerLat,
                'partner_initial_lng' => $partnerLng,
                'partner_current_lat' => $partnerLat,
                'partner_current_lng' => $partnerLng,
            ], $finalPricing));

            $partnerState->update([
                'matching_status'      => PartnerOnlineState::STATUS_BUSY,
                'current_help_id'      => $lockedHelp->id,
                'searching_since'      => null,
                'consecutive_declines' => 0,
            ]);

            Log::info("[HelpMatchingService] Mitra #{$mitra->id} ACCEPTED Dispatch #{$dispatch->id} for Help #{$lockedHelp->id}.");
            return $lockedHelp;
        });
        } catch (\RuntimeException $e) {
            if ($isExpired) {
                $this->handleExpiry($dispatchId, true);
            }
            throw $e;
        }
    }

    /**
     * Memproses penolakan tawaran oleh mitra (Reject Offer).
     * Mengembalikan status mitra dan langsung meneruskan ke kandidat ranking berikutnya.
     */
    public function rejectOffer(int $dispatchId, User $mitra, ?string $reason = null): void
    {
        $dispatchData = DB::transaction(function () use ($dispatchId, $mitra, $reason) {
            $dispatch = HelpDispatch::where('id', $dispatchId)->lockForUpdate()->first();

            if (!$dispatch || $dispatch->status !== HelpDispatch::STATUS_OFFERED || $dispatch->mitra_id !== $mitra->id) {
                return null;
            }

            $dispatch->update([
                'status'           => HelpDispatch::STATUS_REJECTED,
                'responded_at'     => now(),
                'rejection_reason' => $reason,
            ]);

            $this->onlineService->revertFromOfferPending($mitra->id, $dispatch->help_id);

            return [
                'help_id' => $dispatch->help_id,
                'round'   => $dispatch->round,
                'rank'    => $dispatch->rank,
            ];
        });

        if ($dispatchData) {
            Log::info("[HelpMatchingService] Mitra #{$mitra->id} REJECTED Dispatch #{$dispatchId}. Advancing to Rank " . ($dispatchData['rank'] + 1));
            $this->dispatchNextCandidate($dispatchData['help_id'], $dispatchData['round'], $dispatchData['rank'] + 1);
        }
    }

    /**
     * Menangani kadaluarsa tawaran jika batas waktu terlewati tanpa respon.
     */
    public function handleExpiry(int $dispatchId, bool $force = false): void
    {
        $dispatchData = DB::transaction(function () use ($dispatchId, $force) {
            $dispatch = HelpDispatch::where('id', $dispatchId)->lockForUpdate()->first();

            if (!$dispatch || $dispatch->status !== HelpDispatch::STATUS_OFFERED) {
                return null;
            }

            // Jangan expire-kan jika waktu expires_at masih di masa depan (misal sync queue test) kecuali jika dipanggil force
            if (!$force && $dispatch->expires_at && $dispatch->expires_at->isFuture()) {
                return null;
            }

            $dispatch->update([
                'status'       => HelpDispatch::STATUS_EXPIRED,
                'responded_at' => now(),
            ]);

            $this->onlineService->revertFromOfferPending($dispatch->mitra_id, $dispatch->help_id);

            return [
                'help_id'  => $dispatch->help_id,
                'mitra_id' => $dispatch->mitra_id,
                'round'    => $dispatch->round,
                'rank'     => $dispatch->rank,
            ];
        });

        if ($dispatchData) {
            Log::info("[HelpMatchingService] Dispatch #{$dispatchId} EXPIRED. Advancing to Rank " . ($dispatchData['rank'] + 1));
            $this->dispatchNextCandidate($dispatchData['help_id'], $dispatchData['round'], $dispatchData['rank'] + 1);
        }
    }

    /**
     * Meneruskan tawaran ke kandidat ranking berikutnya atau fallback ke pool terbuka jika sudah habis.
     */
    public function dispatchNextCandidate(int $helpId, int $round, int $nextRank): void
    {
        $help = Help::find($helpId);

        if (!$help || $help->mitra_id !== null || $help->status !== Help::STATUS_MENUNGGU_MITRA) {
            return;
        }

        $maxCandidates = AppSetting::getMaxDispatchCandidates();
        if ($nextRank > $maxCandidates) {
            Log::info("[HelpMatchingService] Reached max sequential ranks for Help #{$helpId}. Opening Pool.");
            $this->fallbackToOpenPool($help);
            return;
        }

        // Ambil daftar mitra yang sudah pernah ditawarkan di round ini
        $dispatchedMitraIds = HelpDispatch::where('help_id', $helpId)
            ->where('round', $round)
            ->pluck('mitra_id')
            ->toArray();

        $candidates = $this->getRankedCandidates($help, $dispatchedMitraIds);

        if ($candidates->isEmpty()) {
            Log::info("[HelpMatchingService] No more candidate available for Help #{$helpId}. Opening Pool.");
            $this->fallbackToOpenPool($help);
            return;
        }

        $nextCandidate = $candidates->first();
        $mitra         = $nextCandidate->user;
        $ttl           = AppSetting::getHeartbeatTtlSeconds();

        // Kunci status online mitra
        $locked = $this->onlineService->setOfferPending($mitra->id, $help->id, $ttl);

        if (!$locked) {
            Log::warning("[HelpMatchingService] Failed to lock Mitra #{$mitra->id} at Rank {$nextRank}. Advancing.");
            $this->dispatchNextCandidate($helpId, $round, $nextRank + 1);
            return;
        }

        $timeoutSec = AppSetting::getOfferTimeoutSeconds();
        $expiresAt  = now()->addSeconds($timeoutSec);

        $dispatch = DB::transaction(function () use ($help, $mitra, $round, $nextRank, $expiresAt, $nextCandidate) {
            $record = HelpDispatch::create([
                'help_id'        => $help->id,
                'mitra_id'       => $mitra->id,
                'round'          => $round,
                'rank'           => $nextRank,
                'status'         => HelpDispatch::STATUS_OFFERED,
                'offered_at'     => now(),
                'expires_at'     => $expiresAt,
                'score_snapshot' => $nextCandidate->score_details,
            ]);

            $help->update([
                'dispatch_mode' => Help::DISPATCH_MODE_OFFERED,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
            ]);

            return $record;
        });

        HandleOfferExpiry::dispatch($dispatch->id)->delay($expiresAt);

        try {
            $mitra->notify(new HelpStatusNotification(
                $help,
                Help::STATUS_MENUNGGU_MITRA,
                'offer_received',
                $help->user
            ));
        } catch (\Throwable $e) {
            Log::warning("[HelpMatchingService] Failed to send offer notification to Mitra #{$mitra->id}: " . $e->getMessage());
        }

        Log::info("[HelpMatchingService] Next offer dispatched to Mitra #{$mitra->id} (Rank {$nextRank}, Round {$round}) for Help #{$help->id}.");
    }

    /**
     * Membuka bantuan ke Pool Terbuka (Open Pool fallback) agar dapat diambil langsung oleh mitra mana saja.
     */
    public function fallbackToOpenPool(Help $help): void
    {
        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp && $lockedHelp->mitra_id === null && $lockedHelp->status === Help::STATUS_MENUNGGU_MITRA) {
                $lockedHelp->update([
                    'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
                    'pool_opened_at' => now(),
                ]);
            }
        });

        Log::info("[HelpMatchingService] Help #{$help->id} dispatch_mode is now POOL (Open Pool).");
    }

    /**
     * Cari pesanan instan yang sedang menunggu mitra dan cocok dengan preferensi serta lokasi mitra yang sedang mencari order.
     */
    public function matchPendingOrderForPartner(User $mitra): ?Help
    {
        $state = PartnerOnlineState::where('user_id', $mitra->id)->first();
        $ttl   = AppSetting::getHeartbeatTtlSeconds();

        if (
            !$state ||
            $state->matching_status !== PartnerOnlineState::STATUS_SEARCHING ||
            !$state->isHeartbeatFresh($ttl) ||
            $state->current_help_id !== null ||
            $mitra->isShadowBanned() ||
            $mitra->warning_level >= 3 ||
            $mitra->status !== 'active'
        ) {
            return null;
        }

        if (!AppSetting::isMatchingSeekingEnabledForUser($mitra)) {
            return null;
        }

        $mitraLat = (float) ($state->latitude ?? $mitra->latitude ?? 0);
        $mitraLng = (float) ($state->longitude ?? $mitra->longitude ?? 0);

        // Ambil ID bantuan yang pernah ditolak/kedaluwarsa oleh mitra ini
        $excludedHelpIds = HelpDispatch::where('mitra_id', $mitra->id)
            ->whereIn('status', [HelpDispatch::STATUS_REJECTED, HelpDispatch::STATUS_EXPIRED])
            ->pluck('help_id')
            ->all();

        $query = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->where('order_mode', Help::ORDER_MODE_INSTANT)
            ->whereIn('dispatch_mode', [Help::DISPATCH_MODE_SEEKING, Help::DISPATCH_MODE_POOL])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereHas('city', fn($c) => $c->where('is_active', true))
            ->where(function ($q) {
                $q->whereNull('district_id')
                  ->orWhereHas('district', fn($d) => $d->where('is_active', true));
            })
            ->availableForMitra($mitra->id);

        if (!empty($excludedHelpIds)) {
            $query->whereNotIn('id', $excludedHelpIds);
        }

        $effectivePref = $state->service_preference ?? PartnerOnlineState::PREFERENCE_ALL;
        if ($effectivePref === PartnerOnlineState::PREFERENCE_ON_SITE) {
            $query->where(function ($q) {
                $q->where('service_type', Help::SERVICE_TYPE_ON_SITE)
                  ->orWhereNull('service_type');
            });
        } elseif ($effectivePref === PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY) {
            if (!$mitra->canTakePickupDelivery()) {
                return null;
            }
            $query->where('service_type', Help::SERVICE_TYPE_PICKUP_DELIVERY);
        } else {
            // PREFERENCE_ALL: Jika mitra belum verifikasi kendaraan, hanya ambil on_site
            if (!$mitra->canTakePickupDelivery()) {
                $query->where(function ($q) {
                    $q->where('service_type', '!=', Help::SERVICE_TYPE_PICKUP_DELIVERY)
                      ->orWhereNull('service_type');
                });
            }
        }

        // Bounding Box GPS Pre-filter atau Fallback ke City ID
        if ($mitraLat != 0 && $mitraLng != 0) {
            $maxDist = ($effectivePref === PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY)
                ? AppSetting::getPickupDeliveryMaxMatchingRadiusKm()
                : AppSetting::getMaxMatchingRadiusKm();
            $latDelta = $maxDist / 111.045;
            $lngDelta = $maxDist / (111.045 * max(0.01, cos(deg2rad($mitraLat))));
            $minLat = $mitraLat - $latDelta;
            $maxLat = $mitraLat + $latDelta;
            $minLng = $mitraLng - $lngDelta;
            $maxLng = $mitraLng + $lngDelta;

            $query->where(function ($q) use ($minLat, $maxLat, $minLng, $maxLng, $mitra) {
                $q->where(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng) {
                    $sub->whereBetween('latitude', [$minLat, $maxLat])
                        ->whereBetween('longitude', [$minLng, $maxLng]);
                })->orWhere(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng) {
                    $sub->whereBetween('pickup_latitude', [$minLat, $maxLat])
                        ->whereBetween('pickup_longitude', [$minLng, $maxLng]);
                });

                if ($mitra->city_id) {
                    $q->orWhere('city_id', $mitra->city_id);
                }
            });
        } elseif ($mitra->city_id) {
            $query->where('city_id', $mitra->city_id);
        }

        $pendingHelps = $query->latest()->limit(15)->get();

        foreach ($pendingHelps as $help) {
            if ($help->isPickup()) {
                if (!$mitra->canTakePickupDelivery()) {
                    continue;
                }
                $targetLat = (float) ($help->pickup_latitude ?: $help->latitude ?: 0);
                $targetLng = (float) ($help->pickup_longitude ?: $help->longitude ?: 0);
                $maxRadius = 10.0;
            } else {
                $targetLat = (float) ($help->latitude ?: 0);
                $targetLng = (float) ($help->longitude ?: 0);
                $maxRadius = $this->computeServiceMaxMatchingRadius($help);
            }

            if ($targetLat != 0 && $targetLng != 0 && $mitraLat != 0 && $mitraLng != 0) {
                $dist = $this->geoService->calculateStraightDistance($targetLat, $targetLng, $mitraLat, $mitraLng);
                if ($dist > $maxRadius) {
                    continue;
                }
            }

            // Temukan pesanan yang cocok dan kirimkan tawaran (dispatch)
            $help->update(['dispatch_mode' => Help::DISPATCH_MODE_SEEKING]);
            $help->refresh();
            $matched = $this->initiateMatching($help);
            if ($matched) {
                return $help;
            }
        }

        return null;
    }
}
