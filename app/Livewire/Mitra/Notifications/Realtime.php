<?php

namespace App\Livewire\Mitra\Notifications;

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
            $this->last_chat_id = ChatModel::where('mitra_id', auth()->id())->max('id') ?? 0;
            $this->last_notification_check = now();
        }
    }

    public function poll()
    {
        if (!auth()->check()) {
            Log::info('[RealtimeNotifications] poll called but no auth user.');
            return;
        }

        Log::info('[RealtimeNotifications] polling for mitra_id=' . auth()->id() . ' last_chat_id=' . $this->last_chat_id);

        $new = ChatModel::where('mitra_id', auth()->id())
            ->where('sender_type', 'customer')
            ->where('id', '>', $this->last_chat_id)
            ->orderBy('id', 'asc')
            ->first();

        if ($new) {
            Log::info('[RealtimeNotifications] found new chat id=' . $new->id . ' help_id=' . $new->help_id . ' message=' . Str::limit($new->message, 120));

            $this->last_chat_id = $new->id;

            // Dispatch with named parameters so Livewire exposes them as event detail in the browser
            $this->dispatch(
                'help-new-message',
                helpId: $new->help_id,
                message: Str::limit($new->message, 150),
                from: optional($new->customer)->name ?? 'Customer'
            );
        }

        // Check for new database notifications for mitra (help status updates, etc.)
        try {
            $newNotifications = auth()->user()->notifications()
                ->where('created_at', '>', $this->last_notification_check)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($newNotifications->count() > 0) {
                $this->last_notification_check = $newNotifications->last()->created_at;
            }

            foreach ($newNotifications as $notification) {
                $data = $notification->data;
                Log::info('[RealtimeNotifications] processing notification', ['id' => $notification->id, 'type' => $notification->type, 'data' => $data]);

                if (isset($data['type']) && $data['type'] === 'help_status') {
                    $helpId = $data['help_id'] ?? ($data['helpId'] ?? 0);
                    $newStatus = $data['new_status'] ?? ($data['newStatus'] ?? null);

                    // Dispatch a browser event via inline JS so frontend can react
                    $this->js(sprintf(
                        "console.log('🔔 Mitra help-status notification'); window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: { helpId: %d, newStatus: '%s', message: '%s' } }));",
                        $helpId,
                        addslashes($newStatus ?? ''),
                        addslashes($data['message'] ?? '')
                    ));

                    Log::info('[RealtimeNotifications] dispatched mitra-help-status', ['help_id' => $helpId, 'new_status' => $newStatus]);

                    // Previously we redirected mitra on cancel_rejected; keep notification dispatch-only
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[RealtimeNotifications] failed processing mitra notifications: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // render nothing visible; the view simply contains a poll directive
        return view('livewire.mitra.notifications.realtime');
    }
}

