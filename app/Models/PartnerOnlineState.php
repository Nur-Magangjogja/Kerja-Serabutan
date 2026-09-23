<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerOnlineState extends Model
{
    use HasFactory;

    public const STATUS_OFFLINE       = 'offline';
    public const STATUS_ONLINE        = 'online';
    public const STATUS_SEARCHING     = 'searching';
    public const STATUS_OFFER_PENDING = 'offer_pending';
    public const STATUS_BUSY          = 'busy';

    public const PREFERENCE_ALL             = 'all';
    public const PREFERENCE_ON_SITE         = 'on_site_service';
    public const PREFERENCE_PICKUP_DELIVERY = 'pickup_delivery';

    protected $attributes = [
        'matching_status'    => self::STATUS_OFFLINE,
        'service_preference' => self::PREFERENCE_ALL,
    ];

    protected $fillable = [
        'user_id',
        'matching_status',
        'service_preference',
        'current_help_id',
        'consecutive_declines',
        'last_seen_at',
        'searching_since',
        'last_completed_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'consecutive_declines' => 'integer',
        'last_seen_at'         => 'datetime',
        'searching_since'      => 'datetime',
        'last_completed_at'    => 'datetime',
        'latitude'             => 'decimal:8',
        'longitude'            => 'decimal:8',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentHelp()
    {
        return $this->belongsTo(Help::class, 'current_help_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCOPES & HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Scope mitra yang berhak (eligible) menerima penawaran matching.
     * Syarat: status = 'searching' DAN heartbeat masih segar (last_seen_at >= now() - TTL).
     */
    public function scopeEligibleForMatching($query, int $heartbeatTtlSeconds = 60)
    {
        $cutoff = now()->subSeconds($heartbeatTtlSeconds);

        return $query->where('matching_status', self::STATUS_SEARCHING)
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', $cutoff)
            ->whereHas('user', function ($q) {
                $q->where('status', 'active')
                  ->where('is_shadow_banned', false)
                  ->where('warning_level', '<', 3);
            });
    }

    public function scopeSearching($query)
    {
        return $query->where('matching_status', self::STATUS_SEARCHING);
    }

    public function scopeOnline($query)
    {
        return $query->whereIn('matching_status', [
            self::STATUS_ONLINE,
            self::STATUS_SEARCHING,
            self::STATUS_OFFER_PENDING,
            self::STATUS_BUSY,
        ]);
    }

    public function isHeartbeatFresh(int $heartbeatTtlSeconds = 60): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->greaterThanOrEqualTo(now()->subSeconds($heartbeatTtlSeconds));
    }

    public function isEligibleForOffer(int $heartbeatTtlSeconds = 60): bool
    {
        return $this->matching_status === self::STATUS_SEARCHING && $this->isHeartbeatFresh($heartbeatTtlSeconds);
    }

    public function isSearching(): bool
    {
        return $this->matching_status === self::STATUS_SEARCHING;
    }

    public function isBusy(): bool
    {
        return $this->matching_status === self::STATUS_BUSY;
    }

    public function isOfferPending(): bool
    {
        return $this->matching_status === self::STATUS_OFFER_PENDING;
    }

    public function isOffline(): bool
    {
        return $this->matching_status === self::STATUS_OFFLINE;
    }

    /**
     * Cek apakah preferensi pencarian mitra mengizinkan jenis layanan bantuan tertentu.
     */
    public function allowsServiceType(?string $serviceType): bool
    {
        if (empty($this->service_preference) || $this->service_preference === self::PREFERENCE_ALL) {
            return true;
        }

        $effectiveType = $serviceType ?: Help::SERVICE_TYPE_ON_SITE;

        return $this->service_preference === $effectiveType;
    }

    /**
     * Label teks preferensi layanan untuk antarmuka pengguna.
     */
    public function getServicePreferenceLabelAttribute(): string
    {
        return match ($this->service_preference) {
            self::PREFERENCE_ON_SITE         => 'Khusus Kerja Serabutan',
            self::PREFERENCE_PICKUP_DELIVERY => 'Khusus Antar & Jemput',
            default                          => 'Semua Jenis Bantuan',
        };
    }
}
