<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the activity log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user role
     */
    public function scopeByRole($query, $role)
    {
        return $query->whereHas('user', function ($q) use ($role) {
            $q->where('role', $role);
        });
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Record a system-wide activity log.
     *
     * @param mixed $user User model, user id, or null (fallback to auth)
     * @param string $action Action key (e.g. login, topup_approval, ktp_verified)
     * @param string|null $description Human readable description
     * @param mixed $properties Additional json metadata/payload
     * @param string|null $ip
     * @param string|null $userAgent
     * @return static|null
     */
    public static function record($user, string $action, ?string $description = null, $properties = null, ?string $ip = null, ?string $userAgent = null)
    {
        $userId = null;
        if (is_object($user) && isset($user->id)) {
            $userId = $user->id;
        } elseif (is_numeric($user)) {
            $userId = (int) $user;
        } elseif (auth()->check()) {
            $userId = auth()->id();
        }

        try {
            return self::create([
                'user_id'     => $userId,
                'action'      => $action,
                'description' => $description,
                'properties'  => $properties,
                'ip_address'  => $ip ?? (function_exists('request') && request() ? request()->ip() : null),
                'user_agent'  => $userAgent ?? (function_exists('request') && request() ? request()->header('User-Agent') : null),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[ActivityLog::record] Failed: ' . $e->getMessage());
            return null;
        }
    }
}
