<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'city_id',
        'ktp_path',
        'verified',
        'status',
        'phone',
        'address',
        // KTP Fields
        'nik',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'city',
        'province',
        'religion',
        'marital_status',
        'occupation',
        'ktp_photo',
        'selfie_photo',
        'notification_settings',
        // Greylist, Shadow Ban, and Warning Fields
        'is_greylisted',
        'greylisted_at',
        'greylist_reason',
        'is_shadow_banned',
        'shadow_banned_at',
        'warning_level',
        'latest_warning_message',
        'latest_warning_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified' => 'boolean',
            'date_of_birth' => 'date',
            'rt' => 'integer',
            'rw' => 'integer',
            'notification_settings' => 'array',
            'is_greylisted' => 'boolean',
            'greylisted_at' => 'datetime',
            'is_shadow_banned' => 'boolean',
            'shadow_banned_at' => 'datetime',
            'warning_level' => 'integer',
            'latest_warning_at' => 'datetime',
        ];
    }

    public function greylistLogs()
    {
        return $this->hasMany(UserGreylistLog::class, 'user_id')->latest();
    }

    public function getWarningLevelLabelAttribute(): string
    {
        return match ($this->warning_level) {
            1       => 'SP 1 (Peringatan Ringan)',
            2       => 'SP 2 (Peringatan Sedang)',
            3       => 'SP 3 (Peringatan Keras / Terakhir)',
            default => 'Normal',
        };
    }

    public function getIsVerifiedAttribute(): bool
    {
        return (bool) ($this->verified ?? false);
    }

    public function isShadowBanned(): bool
    {
        return (bool) $this->is_shadow_banned;
    }

    public function isGreylisted(): bool
    {
        return (bool) $this->is_greylisted;
    }

    public function hasWarning(): bool
    {
        return $this->warning_level > 0;
    }

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Cities managed by this admin (many-to-many)
    public function managedCities()
    {
        return $this->belongsToMany(City::class, 'admin_city', 'user_id', 'city_id')
                    ->withTimestamps();
    }

    public function helps()
    {
        return $this->hasMany(Help::class);
    }

    public function takenHelps()
    {
        return $this->hasMany(Help::class, 'mitra_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'mitra_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    // Ratings received as customer (from mitra)
    public function customerRatings()
    {
        return $this->hasMany(Rating::class, 'ratee_id')->where('type', 'mitra_to_customer');
    }

    // Ratings received as mitra (from customer)  
    public function mitraRatings()
    {
        return $this->hasMany(Rating::class, 'ratee_id')
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            });
    }

    public function partnerReports()
    {
        return $this->hasMany(\App\Models\PartnerReport::class);
    }


    public function balance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function userBalance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(\App\Models\WithdrawRequest::class);
    }

    public function hasPendingOrProcessingWithdraws(): bool
    {
        return $this->withdrawRequests()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }

    public function adjustBalance(int $amountDelta): void
    {
        // Use UserBalance helper methods to modify balance and record transactions.
        $userBalance = $this->userBalance()->first() ?? $this->balance()->first();
        if (!$userBalance) {
            $userBalance = $this->balance()->create(['balance' => 0]);
        }

        if ($amountDelta === 0)
            return;

        if ($amountDelta > 0) {
            // Refund / topup
            $userBalance->addBalance($amountDelta, 'refund');
        } else {
            // Deduction
            $userBalance->deductBalance(abs($amountDelta), 'withdraw_deduction');
        }
    }

    /**
     * Accessor to get numeric balance conveniently via $user->balance
     */
    public function getBalanceAttribute()
    {
        // Prefer UserBalance row
        $userBalance = $this->getRelationValue('userBalance')
            ?? $this->getRelationValue('balance')
            ?? $this->userBalance()->first()
            ?? $this->balance()->first();

        if ($userBalance && isset($userBalance->balance)) {
            return (float) $userBalance->balance;
        }

        return 0.0;
    }

    public function transactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    // Helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMitra()
    {
        return $this->role === 'mitra';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isKustomer()
    {
        return $this->isCustomer();
    }

    public function isVerified()
    {
        return $this->verified;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Waktu aktivitas terakhir pengguna (berdasarkan permintaan/pekerjaan bantuan terbaru).
     */
    public function getLastActivityAtAttribute(): ?\Carbon\Carbon
    {
        $dt1 = $this->helps_max_updated_at ?? null;
        $dt2 = $this->taken_helps_max_updated_at ?? null;

        if ($dt1 || $dt2) {
            $c1 = $dt1 ? \Carbon\Carbon::parse($dt1) : null;
            $c2 = $dt2 ? \Carbon\Carbon::parse($dt2) : null;
            if ($c1 && $c2) return $c1->max($c2);
            return $c1 ?: $c2;
        }

        // Direct database lookup if withMax was not loaded
        $latestCustomerHelp = $this->helps()->latest('updated_at')->value('updated_at');
        $latestMitraHelp = $this->takenHelps()->latest('updated_at')->value('updated_at');

        $dates = collect([$latestCustomerHelp, $latestMitraHelp])
            ->filter()
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        return $dates->max();
    }

    /**
     * Teks waktu relatif aktivitas terakhir (misal: "2 jam yang lalu").
     */
    public function getLastActivityForHumansAttribute(): string
    {
        $last = $this->last_activity_at;
        if (!$last) {
            return 'Belum ada aktivitas';
        }

        return $last->diffForHumans();
    }

    /**
     * Return the display name for the city.
     * Prefer the related City model (or city_id lookup), fallback to users.city attribute.
     */
    public function getCityNameAttribute(): ?string
    {
        $rel = $this->getRelationValue('city');
        if ($rel && isset($rel->name)) {
            return $rel->name;
        }

        if (!empty($this->city_id)) {
            $cityModel = City::find($this->city_id);
            if ($cityModel && isset($cityModel->name)) {
                return $cityModel->name;
            }
        }

        return isset($this->attributes['city']) && $this->attributes['city'] !== null
            ? (string) $this->attributes['city']
            : null;
    }

    // Notification Settings Accessor with robust defaults
    public function getNotificationSettingsAttribute($value)
    {
        $defaults = [
            'help_updates' => true,
            'chat_messages' => true,
            'transactions' => true,
            'sound_enabled' => true,
            'auto_mark_read' => false,
            'auto_cleanup_read' => false,
        ];

        if (empty($value)) {
            return $defaults;
        }

        $decoded = is_string($value) ? json_decode($value, true) : (array) $value;
        return array_merge($defaults, is_array($decoded) ? $decoded : []);
    }

    // Unified Rating Accessors
    public function getAverageRatingAttribute()
    {
        if ($this->isMitra()) {
            return $this->mitra_average_rating;
        } elseif ($this->isCustomer()) {
            return $this->customer_average_rating;
        }
        return $this->mitra_average_rating ?: $this->customer_average_rating;
    }

    public function getRatingCountAttribute()
    {
        if ($this->isMitra()) {
            return $this->mitra_rating_count;
        } elseif ($this->isCustomer()) {
            return $this->customer_rating_count;
        }
        return $this->mitra_rating_count ?: $this->customer_rating_count;
    }

    // Customer Rating Methods
    public function getCustomerAverageRatingAttribute()
    {
        $avg = Rating::where('ratee_id', $this->id)
            ->where('type', 'mitra_to_customer')
            ->avg('rating');

        return $avg ? round((float) $avg, 1) : 0;
    }

    public function getCustomerRatingCountAttribute()
    {
        return Rating::where('ratee_id', $this->id)
            ->where('type', 'mitra_to_customer')
            ->count();
    }

    public function getCustomerRatingBadgeAttribute()
    {
        $rating = $this->customer_average_rating;
        
        if ($rating >= 4.5) {
            return [
                'text' => 'Customer Terpercaya',
                'color' => 'green',
                'emoji' => '🌟'
            ];
        } elseif ($rating >= 4.0) {
            return [
                'text' => 'Customer Baik',
                'color' => 'blue',
                'emoji' => '⭐'
            ];
        } elseif ($rating >= 3.0) {
            return [
                'text' => 'Standar',
                'color' => 'yellow',
                'emoji' => '✓'
            ];
        } else {
            return [
                'text' => 'Perlu Perbaikan',
                'color' => 'red',
                'emoji' => '⚠️'
            ];
        }
    }

    // Mitra Rating Methods
    public function getMitraAverageRatingAttribute()
    {
        $avg = Rating::where('ratee_id', $this->id)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            })->avg('rating');

        return $avg ? round((float) $avg, 1) : 0;
    }

    public function getMitraRatingCountAttribute()
    {
        return Rating::where('ratee_id', $this->id)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            })->count();
    }


}
