<?php

namespace App\Livewire\Customer\Notifications;

use Livewire\Component;
use App\Models\Chat as ChatModel;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Realtime extends Component
{
    public $last_chat_id = 0;
    public $last_cancel_msg_id = 0;
    public $last_report_msg_id = 0;
    public $last_notification_check = null;

    public function mount()
    {
        if (auth()->check()) {
            $customerId = auth()->id();
            $this->last_chat_id = ChatModel::where('customer_id', $customerId)->max('id') ?? 0;

            $customerCancels = HelpCancelRequest::where('customer_id', $customerId)
                ->orWhereHas('help', fn($q) => $q->where('user_id', $customerId))
                ->pluck('id');
            $this->last_cancel_msg_id = HelpCancelMessage::whereIn('help_cancel_request_id', $customerCancels)
                ->where(fn($q) => $q->where('sender_id', $customerId)->orWhereIn('recipient_type', ['customer', 'all', 'both']))
                ->max('id') ?? 0;

            $customerReports = PartnerReport::where('reporter_id', $customerId)
                ->orWhere('reported_user_id', $customerId)
                ->orWhereHas('reportedHelp', fn($q) => $q->where('user_id', $customerId))
                ->pluck('id');
            $this->last_report_msg_id = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
                ->where(fn($q) => $q->where('sender_id', $customerId)->orWhereIn('recipient_type', ['customer', 'all', 'both']))
                ->max('id') ?? 0;

            $this->last_notification_check = now();
        }
    }

    public function poll()
    {
        if (!auth()->check()) {
            return;
        }

        $customerId = auth()->id();

        // 1. Check for new incoming regular chat messages
        $newMessages = ChatModel::where('customer_id', $customerId)
            ->where('sender_type', '!=', 'customer')
            ->where('id', '>', $this->last_chat_id)
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->isNotEmpty()) {
            $this->last_chat_id = $newMessages->max('id');
            $latestMsg = $newMessages->last();
            $senderName = $latestMsg->sender_type === 'system' ? 'Sistem SayaBantu' : (optional($latestMsg->mitra)->name ?? 'Rekan Jasa');

            $this->dispatch(
                'help-new-message',
                helpId: $latestMsg->help_id,
                message: Str::limit($latestMsg->message, 150),
                from: $senderName,
                fromId: $latestMsg->mitra_id,
                url: route('customer.chat', ['help' => $latestMsg->help_id])
            );
            $this->dispatch('play-notification-sound', force: true);
            $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
        }

        // 2. Check for new incoming cancellation messages from Admin
        $customerCancels = HelpCancelRequest::where('customer_id', $customerId)
            ->orWhereHas('help', fn($q) => $q->where('user_id', $customerId))
            ->pluck('id');

        if ($customerCancels->isNotEmpty()) {
            $newCancelMsgs = HelpCancelMessage::whereIn('help_cancel_request_id', $customerCancels)
                ->where('sender_id', '!=', $customerId)
                ->whereIn('recipient_type', ['customer', 'all', 'both'])
                ->where('id', '>', $this->last_cancel_msg_id)
                ->orderBy('id', 'asc')
                ->get();

            if ($newCancelMsgs->isNotEmpty()) {
                $this->last_cancel_msg_id = $newCancelMsgs->max('id');
                $latestCancelMsg = $newCancelMsgs->last();
                $senderName = 'Tim Admin SayaBantu';
                $this->dispatch(
                    'help-new-message',
                    helpId: $latestCancelMsg->cancelRequest?->help_id,
                    cancelId: $latestCancelMsg->help_cancel_request_id,
                    message: Str::limit($latestCancelMsg->message, 150),
                    from: $senderName,
                    fromId: 'admin',
                    url: route('customer.chat', ['cancel_request' => $latestCancelMsg->help_cancel_request_id])
                );
                $this->dispatch('play-notification-sound', force: true);
                $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
            }
        }

        // 3. Check for new incoming report messages from Admin
        $customerReports = PartnerReport::where('reporter_id', $customerId)
            ->orWhere('reported_user_id', $customerId)
            ->orWhereHas('reportedHelp', fn($q) => $q->where('user_id', $customerId))
            ->pluck('id');

        if ($customerReports->isNotEmpty()) {
            $newReportMsgs = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
                ->where('sender_id', '!=', $customerId)
                ->whereIn('recipient_type', ['customer', 'all', 'both'])
                ->where('id', '>', $this->last_report_msg_id)
                ->orderBy('id', 'asc')
                ->get();

            if ($newReportMsgs->isNotEmpty()) {
                $this->last_report_msg_id = $newReportMsgs->max('id');
                $latestReportMsg = $newReportMsgs->last();
                $senderName = 'Tim Admin SayaBantu';
                $this->dispatch(
                    'help-new-message',
                    helpId: $latestReportMsg->partnerReport?->reported_help_id,
                    reportId: $latestReportMsg->partner_report_id,
                    message: Str::limit($latestReportMsg->message, 150),
                    from: $senderName,
                    fromId: 'admin',
                    url: route('customer.chat', ['report' => $latestReportMsg->partner_report_id])
                );
                $this->dispatch('play-notification-sound', force: true);
                $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
            }
        }

        // Check for new database notifications (help taken, status updates, etc)
        $newNotifications = auth()->user()->notifications()
            ->where('created_at', '>', $this->last_notification_check)
            ->orderBy('created_at', 'asc')
            ->get();

        Log::info('[Customer\RealtimeNotifications] checking notifications, last_check=' . $this->last_notification_check . ', found=' . $newNotifications->count());

        if ($newNotifications->count() > 0) {
            $this->last_notification_check = $newNotifications->last()->created_at;
            $this->dispatch('notifications-updated');
        }

        foreach ($newNotifications as $notification) {
            $data = $notification->data;

            Log::info('[Customer\RealtimeNotifications] processing notification', [
                'id'   => $notification->id,
                'type' => $notification->type,
                'data' => $data
            ]);

            // Handle help_taken notification
            if (isset($data['type']) && $data['type'] === 'help_taken') {
                Log::info('[Customer\RealtimeNotifications] MATCHED help_taken notification, dispatching events');

                $this->dispatch(
                    'help-taken',
                    helpId: $data['help_id'] ?? null,
                    mitraName: $data['mitra_name'] ?? 'Mitra'
                );

                $this->js(sprintf(
                    "console.log(' Dispatching help-taken event from backend'); window.dispatchEvent(new CustomEvent('help-taken', { detail: { helpId: %d, mitraName: '%s' } }))",
                    $data['help_id'] ?? 0,
                    addslashes($data['mitra_name'] ?? 'Mitra')
                ));

                Log::info('[Customer\RealtimeNotifications] dispatched help-taken event', [
                    'help_id'    => $data['help_id'] ?? null,
                    'mitra_name' => $data['mitra_name'] ?? 'Mitra'
                ]);
            }
            // Handle help status update notification
            if (isset($data['type']) && $data['type'] === 'help_status') {
                $helpId    = $data['help_id'] ?? ($data['helpId'] ?? null);
                $newStatus = $data['new_status'] ?? ($data['newStatus'] ?? ($data['status'] ?? null));

                // Jangan tembak help-status-update toast jika ini adalah notifikasi pesan chat moderasi admin
                $statusStr = strtolower((string)$newStatus);
                if ($statusStr === 'admin_clarification' || $statusStr === 'cancellation_response' || str_contains($statusStr, 'clarification') || str_contains($statusStr, 'chat')) {
                    continue;
                }

                Log::info('[Customer\RealtimeNotifications] MATCHED help_status notification, dispatching events', ['data' => $data]);

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
                    "console.log(' Dispatching help-status event from backend'); window.dispatchEvent(new CustomEvent('help-status-update', { detail: { helpId: %d, status: '%s', message: '%s', mitraName: '%s' } }))",
                    $helpId ?? 0,
                    addslashes($newStatus ?? ''),
                    addslashes($data['message'] ?? ''),
                    addslashes($data['mitra_name'] ?? ($data['mitraName'] ?? 'Mitra'))
                ));

                if ($newStatus && str_contains($newStatus, 'partner_on_the_way')) {
                    $this->js(sprintf(
                        "console.log(' Dispatching help-on-the-way event from backend'); window.dispatchEvent(new CustomEvent('help-on-the-way', { detail: { helpId: %d, mitraName: '%s' } }))",
                        $helpId ?? 0,
                        addslashes($data['mitra_name'] ?? ($data['mitraName'] ?? 'Mitra'))
                    ));
                }

                Log::info('[Customer\RealtimeNotifications] dispatched help_status events', ['help_id' => $helpId, 'new_status' => $newStatus]);
            }
        }
    }

    public function render()
    {
        return view('livewire.customer.notifications.realtime');
    }
}
