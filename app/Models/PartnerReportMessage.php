<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerReportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_report_id',
        'sender_id',
        'recipient_type',
        'message',
        'photo',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function report(): BelongsTo
    {
        return $this->belongsTo(PartnerReport::class, 'partner_report_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromAdmin(): bool
    {
        return in_array($this->sender?->role ?? '', ['admin', 'super_admin', 'superadmin']);
    }

    public function isFromCustomer(): bool
    {
        return $this->sender?->role === 'customer';
    }

    public function isFromMitra(): bool
    {
        return $this->sender?->role === 'mitra';
    }
}
