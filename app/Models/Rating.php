<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'help_id',
        'rater_id',
        'ratee_id',
        'type',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // Relationships
    public function help()
    {
        return $this->belongsTo(Help::class);
    }

    // Who gives the rating (can be customer or mitra)
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    // Who receives the rating (can be customer or mitra)
    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }

    // Query Scopes
    public function scopeForMitra($query, $mitraId)
    {
        return $query->where('ratee_id', $mitraId)
                     ->where('type', 'customer_to_mitra');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('ratee_id', $customerId)
                     ->where('type', 'mitra_to_customer');
    }

    public function scopeByRater($query, $raterId)
    {
        return $query->where('rater_id', $raterId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Check if user already rated this help
    public static function hasRated($helpId, $raterId = null, $type = 'customer_to_mitra')
    {
        $query = self::where('help_id', $helpId);
        
        if ($raterId) {
            $query->where('rater_id', $raterId);
        }

        if ($type) {
            $query->where(function ($q) use ($type) {
                $q->where('type', $type)
                  ->orWhereNull('type');
            });
        }

        return $query->exists();
    }

    protected static function booted()
    {
        static::created(function ($rating) {
            if ($rating->type === 'customer_to_mitra' || empty($rating->type)) {
                static::checkConsecutiveLowRatings($rating->ratee_id);
            }
        });
    }

    /**
     * Memeriksa apakah mitra menerima rating 1 bintang sebanyak 3x berturut-turut.
     * Jika ya, otomatis masukkan mitra ke Daftar Abu-Abu (Greylist) untuk peninjauan manual Admin.
     */
    public static function checkConsecutiveLowRatings($mitraId): void
    {
        $mitra = User::find($mitraId);
        if (!$mitra || $mitra->role !== 'mitra') {
            return;
        }

        $recentRatings = static::where('ratee_id', $mitraId)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            })
            ->latest('id')
            ->take(3)
            ->get();

        if ($recentRatings->count() >= 3 && $recentRatings->every(fn($r) => (int)$r->rating === 1)) {
            if (!$mitra->is_greylisted) {
                $mitra->update([
                    'is_greylisted'   => true,
                    'greylisted_at'   => now(),
                    'greylist_reason' => 'Otomatis Sistem: Terdeteksi menerima rating 1 bintang sebanyak 3x berturut-turut. Memerlukan audit manual Admin terkait pemberian SP.',
                ]);

                UserGreylistLog::create([
                    'user_id'       => $mitra->id,
                    'admin_id'      => null,
                    'action'        => 'greylist_add',
                    'warning_level' => (int) $mitra->warning_level,
                    'reason'        => 'Menerima rating 1 bintang 3x berturut-turut dari customer.',
                    'message'       => 'Akun mitra otomatis dimasukkan ke Daftar Abu-Abu untuk verifikasi manual Admin.',
                ]);

                \App\Models\ActivityLog::record(
                    null,
                    'auto_greylist_low_rating',
                    "Sistem otomatis memasukkan mitra {$mitra->name} (#{$mitra->id}) ke Daftar Abu-Abu karena menerima rating 1 bintang 3x berturut-turut.",
                    ['mitra_id' => $mitra->id]
                );
            }
        }
    }
}
