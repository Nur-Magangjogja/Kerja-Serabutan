<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardStatsService
{
    public const STATS_TTL = 30; // 30 seconds
    public const JOBS_TTL  = 25; // 25 seconds
    public const CHAT_TTL  = 15; // 15 seconds

    /**
     * Aggregated summary metrics for partner dashboard.
     *
     * @return array{balance: float, available: int, inProgress: int, completed: int}
     */
    public function getSummaryStats(User $user, bool $forceFresh = false): array
    {
        $cacheKey = "mitra_dash_stats_{$user->id}";

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::STATS_TTL, function () use ($user) {
            $balanceRecord = UserBalance::where('user_id', $user->id)->first();
            $balance = $balanceRecord ? (float) $balanceRecord->balance : 0.0;

            $available = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user->id)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->count();

            $inProgress = Help::where('mitra_id', $user->id)
                ->whereIn('status', [
                    'memperoleh_mitra',
                    Help::STATUS_TAKEN,
                    'sedang_diproses',
                    Help::STATUS_IN_PROGRESS,
                    Help::STATUS_PARTNER_ON_THE_WAY,
                    Help::STATUS_PARTNER_ARRIVED,
                    Help::STATUS_WAITING_CONFIRMATION,
                    Help::STATUS_PARTNER_CANCEL_REQUESTED,
                ])
                ->count();

            $completed = Help::where('mitra_id', $user->id)
                ->whereIn('status', [Help::STATUS_SELESAI, 'completed'])
                ->count();

            return [
                'balance'    => $balance,
                'available'  => $available,
                'inProgress' => $inProgress,
                'completed'  => $completed,
            ];
        });
    }

    /**
     * Recommended open pool jobs for partner.
     */
    public function getRecommendedHelps(User $user, int $limit = 3, bool $forceFresh = false): Collection
    {
        $cityId   = $user->city_id;
        $cacheKey = "mitra_dash_rec_{$user->id}_{$cityId}";

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::JOBS_TTL, function () use ($user, $cityId, $limit) {
            return Help::where('status', Help::STATUS_MENUNGGU_MITRA)
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user->id)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->when($cityId, function ($query, $cId) {
                    return $query->where('city_id', $cId);
                })
                ->with(['user', 'city'])
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Latest open pool jobs across the platform.
     */
    public function getLatestHelps(User $user, int $limit = 5, bool $forceFresh = false): Collection
    {
        $cacheKey = "mitra_dash_latest_{$user->id}";

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::JOBS_TTL, function () use ($user, $limit) {
            return Help::where('status', Help::STATUS_MENUNGGU_MITRA)
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user->id)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->with(['user', 'city'])
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Nearby open pool jobs within the partner's city.
     */
    public function getNearbyHelps(User $user, int $limit = 3, bool $forceFresh = false): Collection
    {
        $cityId   = $user->city_id;
        $cacheKey = "mitra_dash_nearby_{$user->id}_{$cityId}";

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::JOBS_TTL, function () use ($user, $cityId, $limit) {
            return Help::where('status', Help::STATUS_MENUNGGU_MITRA)
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user->id)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->when($cityId, function ($query, $cId) {
                    return $query->where('city_id', $cId);
                })
                ->with(['user', 'city'])
                ->take($limit)
                ->get();
        });
    }

    /**
     * Unread chat messages count for the partner.
     */
    public function getUnreadChatCount(User $user, bool $forceFresh = false): int
    {
        $cacheKey = "mitra_dash_unread_chat_{$user->id}";

        if ($forceFresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CHAT_TTL, function () use ($user) {
            try {
                if (Schema::hasTable('chats')) {
                    $myHelpIds = Help::where('mitra_id', $user->id)
                        ->whereIn('status', [
                            'memperoleh_mitra',
                            Help::STATUS_TAKEN,
                            'sedang_diproses',
                            Help::STATUS_IN_PROGRESS,
                            Help::STATUS_PARTNER_ON_THE_WAY,
                            Help::STATUS_PARTNER_ARRIVED,
                            Help::STATUS_WAITING_CONFIRMATION,
                        ])
                        ->pluck('id');

                    return Chat::whereIn('help_id', $myHelpIds)
                        ->where('sender_id', '!=', $user->id)
                        ->where('is_read', false)
                        ->count();
                }
            } catch (\Throwable $e) {
                // Ignore table or query error gracefully
            }
            return 0;
        });
    }

    /**
     * Explicit cache invalidator for partner dashboard data.
     */
    public function clearStatsCache(int $userId, ?int $cityId = null): void
    {
        Cache::forget("mitra_dash_stats_{$userId}");
        Cache::forget("mitra_dash_rec_{$userId}_{$cityId}");
        Cache::forget("mitra_dash_latest_{$userId}");
        Cache::forget("mitra_dash_nearby_{$userId}_{$cityId}");
        Cache::forget("mitra_dash_unread_chat_{$userId}");
    }
}
