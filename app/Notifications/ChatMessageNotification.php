<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public $helpId;
    public $message;
    public $fromId;
    public $fromName;

    public function __construct($helpId, $message, $fromId = null, $fromName = null)
    {
        $this->helpId = $helpId;
        $this->message = $message;
        $this->fromId = $fromId;
        $this->fromName = $fromName;
    }

    public function via($notifiable)
    {
        // Chat messages are handled directly in chats table and realtime polling, not in notifications table
        return [];
    }

    public function toArray($notifiable)
    {
        $role = $notifiable->role ?? null;
        if ($role === 'mitra') {
            $url = $this->helpId ? route('mitra.chat', ['help' => $this->helpId]) : route('mitra.chat');
        } else {
            $url = $this->helpId ? route('customer.chat', ['help' => $this->helpId]) : route('customer.chat');
        }

        return [
            'type' => 'chat_message',
            'title' => 'Pesan dari ' . ($this->fromName ?? 'Partner'),
            'help_id' => $this->helpId,
            'message' => $this->message,
            'body' => $this->message,
            'from_id' => $this->fromId,
            'from_name' => $this->fromName,
            'url' => $url,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
}
