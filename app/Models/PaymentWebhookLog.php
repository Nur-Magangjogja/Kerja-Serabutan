<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    protected $fillable = [
        'event_id',
        'help_id',
        'provider',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function help()
    {
        return $this->belongsTo(Help::class);
    }
}
