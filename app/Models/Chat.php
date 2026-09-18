<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'help_id',
        'mitra_id',
        'customer_id',
        'message',
        'photo',
        'sender_type',
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
    public function help(): BelongsTo
    {
        return $this->belongsTo(Help::class);
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    protected static function booted(): void
    {
        static::created(function (Chat $chat) {
            try {
                broadcast(new \App\Events\ChatMessageSent($chat));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[Chat] ChatMessageSent broadcast failed: ' . $e->getMessage());
            }
        });
    }
}
