<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'help_id',
        'user_id',
        'mitra_id',
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

    // Legacy: customer who gave the rating
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Legacy: mitra who received the rating
    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    // New: who gives the rating (can be customer or mitra)
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    // New: who receives the rating (can be customer or mitra)
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
    public static function hasRated($helpId, $raterId, $type)
    {
        return self::where('help_id', $helpId)
                   ->where('rater_id', $raterId)
                   ->where('type', $type)
                   ->exists();
    }
}
