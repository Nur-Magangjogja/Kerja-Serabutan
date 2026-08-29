<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpDispatch extends Model
{
    use HasFactory;

    public const STATUS_OFFERED  = 'offered';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED  = 'expired';

    protected $fillable = [
        'help_id',
        'mitra_id',
        'round',
        'rank',
        'status',
        'offered_at',
        'expires_at',
        'responded_at',
        'rejection_reason',
        'score_snapshot',
    ];

    protected $casts = [
        'offered_at'     => 'datetime',
        'expires_at'     => 'datetime',
        'responded_at'   => 'datetime',
        'score_snapshot' => 'array',
        'round'          => 'integer',
        'rank'           => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────────

    public function help()
    {
        return $this->belongsTo(Help::class);
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────────────────

    public function isOffered(): bool
    {
        return $this->status === self::STATUS_OFFERED;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_OFFERED && $this->expires_at !== null && $this->expires_at->isFuture();
    }

    public function remainingSeconds(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }
}
