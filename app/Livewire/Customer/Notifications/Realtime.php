<?php

namespace App\Livewire\Customer\Notifications;

use Livewire\Component;
use App\Models\Chat as ChatModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Realtime extends Component
{
    public $last_chat_id = 0;
    public $last_notification_check = null;

    public function mount()
    {
        if (auth()->check()) {
            $this->last_chat_id = ChatModel::where('customer_id', auth()->id())->max('id') ?? 0;
            $this->last_notification_check = now();
        }
    }

    public function poll()
    {
        if (!auth()->check()) {
            Log::info('[Customer\\RealtimeNotifications] poll called but no auth user.');
            return;
        }

        Log::info('[Customer\\RealtimeNotifications] polling for customer_id=' . auth()->id() . ' last_chat_id=' . $this->last_chat_id);

        // Check for new chat messages
        $new = ChatModel::where('customer_id', auth()->id())
            ->where('sender_type', 'mitra')
            ->where('id', '>', $this->last_chat_id)
            ->orderBy('id', 'asc')
            ->first();

        if ($new) {
            Log::info('[Customer\\RealtimeNotifications] found new chat id=' . $new->id . ' help_id=' . $new->help_id . ' message=' . Str::limit($new->message, 120));

            $this->last_chat_id = $new->id;

            $this->dispatch(
                'help-new-message',
                helpId: $new->help_id,
                message: Str::limit($new->message, 150),
                from: optional($new->mitra)->name ?? 'Mitra'
            );
        }

        // Check for new database notifications (help taken, status updates, etc)
        $newNotifications = auth()->user()->notifications()
            ->where('created_at', '>', $this->last_notification_check)
            ->orderBy('created_at', 'asc')
            ->get();

        Log::info('[Customer\\RealtimeNotifications] checking notifications, last_check=' . $this->last_notification_check . ', found=' . $newNotifications->count());

        if ($newNotifications->count() > 0) {
            $this->last_notification_check = $newNotifications->last()->created_at;
        }

        foreach ($newNotifications as $notification) {
            $data = $notification->data;

            Log::info('[Customer\\RealtimeNotifications] processing notification', [
                'id'   => $notification->id,
                'type' => $notification->type,
                'data' => $data
            ]);

            // Handle help_taken notification
            if (isset($data['type']) && $data['type'] === 'help_taken') {
                Log::info('[Customer\\RealtimeNotifications] MATCHED help_taken notification, dispatching events');

                $this->dispatch(
                    'help-taken',
                    helpId: $data['help_id'] ?? null,
                    mitraName: $data['mitra_name'] ?? 'Mitra'
                );

                $this->js(sprintf(
                    "console.log('🔔 Dispatching help-taken event from backend'); window.dispatchEvent(new CustomEvent('help-taken', { detail: { helpId: %d, mitraName: '%s' } }))",
                    $data['help_id'] ?? 0,
                    addslashes($data['mitra_name'] ?? 'Mitra')
                ));

                Log::info('[Customer\\RealtimeNotifications] dispatched help-taken event', [
                    'help_id'    => $data['help_id'] ?? null,
                    'mitra_name' => $data['mitra_name'] ?? 'Mitra'
                ]);
            }
            // Handle help status update notification
            if (isset($data['type']) && $data['type'] === 'help_status') {
                Log::info('[Customer\\RealtimeNotifications] MATCHED help_status notification, dispatching events', ['data' => $data]);

                $helpId    = $data['help_id'] ?? ($data['helpId'] ?? null);
                $newStatus = $data['new_status'] ?? ($data['newStatus'] ?? ($data['status'] ?? null));

                $this->dispatch(
                    'status-changed',
                    [
                        'helpId'    => $helpId,
                        'oldStatus' => $data['old_status'] ?? null,
                        'newStatus' => $newStatus,
                        'message'   => $data['message'] ?? null,
                        'mitraName' => $data['mitra_name'] ?? ($data['mitraName'] ?? 'Mitra')
                    ]
                );

                $this->js(sprintf(
                    "console.log('🔔 Dispatching help-status event from backend'); window.dispatchEvent(new CustomEvent('help-status-update', { detail: { helpId: %d, status: '%s', message: '%s', mitraName: '%s' } }))",
                    $helpId ?? 0,
                    addslashes($newStatus ?? ''),
                    addslashes($data['message'] ?? ''),
                    addslashes($data['mitra_name'] ?? ($data['mitraName'] ?? 'Mitra'))
                ));

                if ($newStatus && str_contains($newStatus, 'partner_on_the_way')) {
                    $this->js(sprintf(
                        "console.log('🔔 Dispatching help-on-the-way event from backend'); window.dispatchEvent(new CustomEvent('help-on-the-way', { detail: { helpId: %d, mitraName: '%s' } }))",
                        $helpId ?? 0,
                        addslashes($data['mitra_name'] ?? ($data['mitraName'] ?? 'Mitra'))
                    ));
                }

                Log::info('[Customer\\RealtimeNotifications] dispatched help_status events', ['help_id' => $helpId, 'new_status' => $newStatus]);
            }
        }
    }

    public function render()
    {
        return view('livewire.customer.notifications.realtime');
    }
}
