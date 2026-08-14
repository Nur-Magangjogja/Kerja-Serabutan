<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'title',
        'amount',
        'admin_fee',
        'total_amount',
        'description',
        'equipment_provided',
        'photo',
        'location',
        'full_address',
        'latitude',
        'longitude',
        'status',
        'mitra_id',
        'taken_at',
        'completed_at',
        'admin_notes',
        'order_id',
        'voucher_code',
        'discount_amount',
        'booking_fee',
        'mitra_assigned_at',
        'partner_started_at',
        'partner_arrived_at',
        'service_started_at',
        'service_completed_at',
        'scheduled_at',
        'partner_initial_lat',
        'partner_initial_lng',
        'partner_current_lat',
        'partner_current_lng',
        'partner_started_moving_at',
        'partner_cancel_requested_at',
        'partner_cancel_reason',
        'partner_cancel_prev_status',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'discount_amount' => 'decimal:2',
        'booking_fee' => 'decimal:2',
        'mitra_assigned_at' => 'datetime',
        'partner_started_at' => 'datetime',
        'partner_arrived_at' => 'datetime',
        'service_started_at' => 'datetime',
        'service_completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'partner_initial_lat' => 'decimal:8',
        'partner_initial_lng' => 'decimal:8',
        'partner_current_lat' => 'decimal:8',
        'partner_current_lng' => 'decimal:8',
        'partner_started_moving_at' => 'datetime',
        'partner_cancel_requested_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Alias untuk customer (sama dengan user)
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * The rating left by the owner of this help (if any).
     * This is useful to eager-load the single rating that belongs to the help's creator.
     */
    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(Chat::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'approved')->whereNull('mitra_id');
    }

    public function scopeTaken($query)
    {
        return $query->whereIn('status', ['taken', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
