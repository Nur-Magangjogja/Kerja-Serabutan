<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityCapacity extends Model
{
    use HasFactory;

    public const STATUS_OPEN    = 'open';
    public const STATUS_LIMITED = 'limited';
    public const STATUS_CLOSED  = 'closed';

    protected $fillable = [
        'city_id',
        'capacity_status',
        'consecutive_closed_evaluations',
        'consecutive_open_evaluations',
        'current_unmatched_demand',
        'recent_request_volume_2h',
        'searching_now',
        'busy_now',
        'online_total',
        'avg_waiting_minutes',
        'unserved_requests_24h',
        'partner_utilization_rate',
        'max_matching_radius_km',
        'auto_manage',
        'admin_override_status',
        'admin_override_until',
        'admin_override_notes',
        'waiting_list_count',
        'last_calculated_at',
    ];

    protected $casts = [
        'auto_manage'              => 'boolean',
        'admin_override_until'     => 'datetime',
        'last_calculated_at'       => 'datetime',
        'avg_waiting_minutes'      => 'decimal:2',
        'partner_utilization_rate' => 'decimal:2',
        'max_matching_radius_km'   => 'decimal:1',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────────

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATUS & HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Dapatkan status efektif kota (memperhitungkan admin override aktif).
     */
    public function getEffectiveStatus(): string
    {
        if ($this->admin_override_status !== null) {
            if ($this->admin_override_until === null || $this->admin_override_until->isFuture()) {
                return $this->admin_override_status;
            }
        }

        return $this->capacity_status ?? self::STATUS_OPEN;
    }

    public function isOpen(): bool
    {
        return $this->getEffectiveStatus() === self::STATUS_OPEN;
    }

    public function isLimited(): bool
    {
        return $this->getEffectiveStatus() === self::STATUS_LIMITED;
    }

    public function isClosed(): bool
    {
        return $this->getEffectiveStatus() === self::STATUS_CLOSED;
    }

    public function canRegisterNewPartners(): bool
    {
        return $this->getEffectiveStatus() !== self::STATUS_CLOSED;
    }
}
