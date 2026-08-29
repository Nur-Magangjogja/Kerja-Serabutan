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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpMatchingService
{
    protected PartnerOnlineService $onlineService;

    public function __construct(PartnerOnlineService $onlineService)
    {
        $this->onlineService = $onlineService;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. SCORING & CANDIDATE RANKING
    // ═════════════════════════════════════════════════════════════════════════

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
    public function calculateBayesianRatingScore(User $mitra): float
    {
        $ratings = Rating::where('ratee_id', $mitra->id)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            });

        $count = (float) $ratings->count();
        $sum   = (float) $ratings->sum('rating');

        $C = (float) AppSetting::getRatingMinVotes();
        $m = (float) AppSetting::getNeutralRatingPrior();

        $bayesianAvg = ($C * $m + $sum) / ($C + $count);

        return min(1.0, max(0.0, $bayesianAvg / 5.0));
    }

    /**
     * Hitung skor reliabilitas (completion rate) mitra (skala 0.0 - 1.0).
     */
    public function calculateReliabilityScore(User $mitra): float
    {
        $takenCount = Help::where('mitra_id', $mitra->id)->count();

        if ($takenCount === 0) {
            return 1.0; // Nilai default sempurna untuk mitra baru
        }

        $completedCount = Help::where('mitra_id', $mitra->id)
            ->where('status', Help::STATUS_SELESAI)
            ->count();

        return min(1.0, max(0.0, $completedCount / $takenCount));
    }

    /**
     * Hitung skor fairness berdasarkan durasi tunggu / waktu sejak tugas terakhir (skala 0.0 - 1.0, cap dinamis).
     */
    public function calculateFairnessScore(PartnerOnlineState $state): float
    {
        $referenceTime = $state->last_completed_at ?? $state->searching_since ?? $state->last_seen_at;

        if (!$referenceTime) {
            return 0.5;
        }

        $capMinutes = (float) AppSetting::getMaxFairnessBoostMinutes();
        $minutesElapsed = max(0, $referenceTime->diffInMinutes(now()));

        return min(1.0, $minutesElapsed / max(1.0, $capMinutes));
    }

    /**
     * Hitung skor komposit mitra untuk order bantuan tertentu berdasarkan bobot AppSetting.
     */
    public function calculatePartnerCompositeScore(Help $help, User $mitra, PartnerOnlineState $state): array
    {
        $maxRadius = AppSetting::getMaxMatchingRadiusKm();
        $weights   = AppSetting::getMatchingWeights();

        // 1. Distance Score
        $helpLat  = (float) ($help->latitude ?? 0);
        $helpLng  = (float) ($help->longitude ?? 0);
        $mitraLat = (float) ($state->latitude ?? $mitra->latitude ?? 0);
        $mitraLng = (float) ($state->longitude ?? $mitra->longitude ?? 0);

        if ($helpLat != 0 && $helpLng != 0 && $mitraLat != 0 && $mitraLng != 0) {
            $distKm = $this->calculateDistance($helpLat, $helpLng, $mitraLat, $mitraLng);
            $distScore = max(0.0, 1.0 - min($distKm / max(0.1, $maxRadius), 1.0));
        } else {
            $distKm = 0.0;
            $distScore = 0.8; // Default jika lokasi belum lengkap
        }

        // 2. Rating Bayesian Score
        $ratingScore = $this->calculateBayesianRatingScore($mitra);

        // 3. Reliability Score
        $reliabilityScore = $this->calculateReliabilityScore($mitra);

        // 4. Fairness Score
        $fairnessScore = $this->calculateFairnessScore($state);

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
     */
    public function getRankedCandidates(Help $help, array $excludeMitraIds = []): Collection
    {
        $ttl = AppSetting::getHeartbeatTtlSeconds();

        // Cari mitra di kota yang sama dengan status 'searching' dan heartbeat segar
        $eligibleStates = PartnerOnlineState::eligibleForMatching($ttl)
            ->whereHas('user', function ($q) use ($help, $excludeMitraIds) {
                $q->where('role', 'mitra')
                  ->where('is_shadow_banned', false)
                  ->whereNotIn('id', $excludeMitraIds);

                if ($help->city_id) {
                    $q->where('city_id', $help->city_id);
                }
            })
            ->with('user')
            ->get();

        $scoredCandidates = $eligibleStates->map(function ($state) use ($help) {
            $scoreDetails = $this->calculatePartnerCompositeScore($help, $state->user, $state);

            return (object) [
                'user'            => $state->user,
                'state'           => $state,
                'score_details'   => $scoreDetails,
                'total_score'     => $scoreDetails['total_score'],
                'searching_since' => $state->searching_since ?? now(),
                'user_id'         => $state->user_id,
            ];
        });

        // Deterministic Sorting: Total Score DESC, Waiting Time DESC (searching_since ASC), User ID ASC
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
        $candidates = $this->getRankedCandidates($help);

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
     * Mengirim tawaran order ke kandidat peringkat tertentu (Atomic Lock).
     */
    public function dispatchOfferToCandidate(Help $help, Collection $candidates, int $rank, int $round = 1): bool
    {
        $candidateIndex = $rank - 1;

        if (!$candidates->has($candidateIndex)) {
            Log::info("[HelpMatchingService] No candidate available at Rank {$rank} for Help #{$help->id}. Opening Pool.");
            $this->fallbackToOpenPool($help);
            return false;
        }

        $candidate = $candidates->get($candidateIndex);
        $mitra     = $candidate->user;
        $ttl       = AppSetting::getHeartbeatTtlSeconds();

        // Coba kunci status mitra menjadi OFFER_PENDING
        $locked = $this->onlineService->setOfferPending($mitra->id, $help->id, $ttl);

        if (!$locked) {
            Log::warning("[HelpMatchingService] Failed to lock Mitra #{$mitra->id} at Rank {$rank}. Skipping to next rank.");
            return $this->dispatchOfferToCandidate($help, $candidates, $rank + 1, $round);
        }

        $timeoutSec = AppSetting::getOfferTimeoutSeconds();
        $expiresAt  = now()->addSeconds($timeoutSec);

        // Buat record HelpDispatch
        $dispatch = DB::transaction(function () use ($help, $mitra, $round, $rank, $expiresAt, $candidate) {
            $record = HelpDispatch::create([
                'help_id'        => $help->id,
                'mitra_id'       => $mitra->id,
                'round'          => $round,
                'rank'           => $rank,
                'status'         => HelpDispatch::STATUS_OFFERED,
                'offered_at'     => now(),
                'expires_at'     => $expiresAt,
                'score_snapshot' => $candidate->score_details,
            ]);

            $help->update([
                'dispatch_mode' => Help::DISPATCH_MODE_OFFERED,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
            ]);

            return $record;
        });

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

        Log::info("[HelpMatchingService] Offer dispatched to Mitra #{$mitra->id} (Rank {$rank}, Round {$round}) for Help #{$help->id}. Expires at {$expiresAt}.");
        return true;
    }

    /**
     * Memproses penerimaan tawaran oleh mitra (Accept Offer).
     * Atomic Lock: Menjamin maksimal 1 mitra yang berhasil mengambil order (No Double Assignment).
     */
    public function acceptOffer(int $dispatchId, User $mitra): bool
    {
        return DB::transaction(function () use ($dispatchId, $mitra) {
            $dispatch = HelpDispatch::where('id', $dispatchId)->lockForUpdate()->firstOrFail();

            if ($dispatch->status !== HelpDispatch::STATUS_OFFERED) {
                throw new \RuntimeException('Tawaran ini sudah tidak tersedia atau telah kadaluarsa.');
            }

            if ($dispatch->mitra_id !== $mitra->id) {
                throw new \RuntimeException('Tawaran ini tidak ditujukan kepada akun Anda.');
            }

            if ($dispatch->expires_at && $dispatch->expires_at->isPast()) {
                $dispatch->update(['status' => HelpDispatch::STATUS_EXPIRED, 'responded_at' => now()]);
                $this->onlineService->revertFromOfferPending($mitra->id, $dispatch->help_id);
                throw new \RuntimeException('Waktu respon penawaran (45 detik) telah habis.');
            }

            $lockedHelp = Help::where('id', $dispatch->help_id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                $dispatch->update(['status' => HelpDispatch::STATUS_EXPIRED, 'responded_at' => now()]);
                $this->onlineService->revertFromOfferPending($mitra->id, $dispatch->help_id);
                throw new \RuntimeException('Bantuan ini sudah diambil atau tidak lagi tersedia.');
            }

            // 1. Update Dispatch Status
            $dispatch->update([
                'status'       => HelpDispatch::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            // 2. Assign Mitra ke Help & set dispatch_mode = assigned
            $lockedHelp->update([
                'mitra_id'      => $mitra->id,
                'status'        => Help::STATUS_TAKEN,
                'dispatch_mode' => Help::DISPATCH_MODE_ASSIGNED,
                'assigned_at'   => now(),
                'taken_at'      => now(),
            ]);

            // 3. Set status mitra menjadi BUSY
            $this->onlineService->setBusy($mitra->id, $lockedHelp->id);

            Log::info("[HelpMatchingService] Mitra #{$mitra->id} ACCEPTED Dispatch #{$dispatch->id} for Help #{$lockedHelp->id}.");
            return true;
        });
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
     * Menangani kadaluarsa tawaran jika batas 45 detik terlewati tanpa respon.
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
                'help_id' => $dispatch->help_id,
                'round'   => $dispatch->round,
                'rank'    => $dispatch->rank,
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
}
