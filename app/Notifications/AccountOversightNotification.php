<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountOversightNotification extends Notification
{
    use Queueable;

    public User $managedUser;
    public string $eventType;
    public string $title;
    public string $message;
    public ?string $publicRef;
    public ?string $incidentTerritory;
    public ?string $status;
    public array $extraData;

    public function __construct(
        User $managedUser,
        string $eventType,
        string $title,
        string $message,
        ?string $publicRef = null,
        ?string $incidentTerritory = null,
        ?string $status = null,
        array $extraData = []
    ) {
        $this->managedUser       = $managedUser;
        $this->eventType         = $eventType;
        $this->title             = $title;
        $this->message           = $message;
        $this->publicRef         = $publicRef;
        $this->incidentTerritory = $incidentTerritory;
        $this->status            = $status;
        $this->extraData         = $extraData;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $roleLabel = ($this->managedUser->role === 'mitra') ? 'Mitra' : 'Customer';
        $userDisplay = "{$roleLabel} {$this->managedUser->name}";

        return [
            'type'               => 'account_oversight',
            'category'           => 'account_oversight',
            'event_type'         => $this->eventType,
            'user_id'            => $this->managedUser->id,
            'user_name'          => $this->managedUser->name,
            'user_role'          => $this->managedUser->role,
            'user_display'       => $userDisplay,
            'public_ref'         => $this->publicRef,
            'incident_territory' => $this->incidentTerritory,
            'status'             => $this->status,
            'title'              => $this->title,
            'message'            => $this->message,
            'url'                => route('admin.users.index', ['search' => $this->managedUser->name, 'tab' => 'audit']),
            'icon'               => 'shield-exclamation',
            'extra_data'         => $this->extraData,
        ];
    }
}
