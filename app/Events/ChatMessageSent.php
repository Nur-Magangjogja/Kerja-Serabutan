<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'broadcast';

    public int $chatId;
    public int $helpId;
    public int $senderId;
    public string $senderType;
    public string $senderName;
    public string $message;
    public ?string $photoUrl;
    public int $customerId;
    public int $mitraId;
    public string $createdAt;

    /**
     * Create a new event instance.
     */
    public function __construct(Chat $chat)
    {
        $this->chatId     = (int) $chat->id;
        $this->helpId     = (int) $chat->help_id;
        $this->senderType = (string) ($chat->sender_type ?? 'customer');

        if (!empty($chat->sender_id)) {
            $this->senderId = (int) $chat->sender_id;
        } elseif ($this->senderType === 'customer') {
            $this->senderId = (int) $chat->customer_id;
        } elseif ($this->senderType === 'mitra') {
            $this->senderId = (int) $chat->mitra_id;
        } else {
            $this->senderId = 0;
        }

        if ($this->senderType === 'customer') {
            $this->senderName = $chat->customer?->name ?? 'Customer';
        } elseif ($this->senderType === 'mitra') {
            $this->senderName = $chat->mitra?->name ?? 'Mitra';
        } elseif (in_array($this->senderType, ['admin', 'super_admin', 'superadmin'], true)) {
            $this->senderName = $chat->sender?->name ?? 'Admin SayaBantu';
        } elseif ($this->senderType === 'system') {
            $this->senderName = 'Sistem SayaBantu';
        } else {
            $this->senderName = $chat->sender?->name ?? 'Pengguna';
        }

        $this->message    = (string) ($chat->message ?? '');
        $this->photoUrl   = $chat->photo ? asset('storage/' . $chat->photo) : null;
        $this->customerId = (int) $chat->customer_id;
        $this->mitraId    = (int) $chat->mitra_id;
        $this->createdAt  = $chat->created_at ? $chat->created_at->toISOString() : now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.help.' . $this->helpId),
            new PrivateChannel('user.' . $this->customerId),
            new PrivateChannel('user.' . $this->mitraId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id'     => $this->chatId,
            'help_id'     => $this->helpId,
            'sender_id'   => $this->senderId,
            'sender_type' => $this->senderType,
            'sender_name' => $this->senderName,
            'message'     => $this->message,
            'photo_url'   => $this->photoUrl,
            'customer_id' => $this->customerId,
            'mitra_id'    => $this->mitraId,
            'created_at'  => $this->createdAt,
        ];
    }
}

