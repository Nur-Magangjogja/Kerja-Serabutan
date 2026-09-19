<?php

namespace App\Livewire\Mitra\Notifications;

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
            $mitraId = auth()->id();
            $this->last_chat_id = ChatModel::where('mitra_id', $mitraId)->max('id') ?? 0;
            
            $mitraCancels = HelpCancelRequest::where('partner_id', $mitraId)
                ->orWhereHas('help', fn($q) => $q->where('mitra_id', $mitraId))
                ->pluck('id');
            $this->last_cancel_msg_id = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
                ->where(fn($q) => $q->where('sender_id', $mitraId)->orWhereIn('recipient_type', ['mitra', 'all', 'both']))
                ->max('id') ?? 0;

            $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
                ->orWhere('reporter_id', $mitraId)
                ->orWhereHas('reportedHelp', fn($q) => $q->where('mitra_id', $mitraId))
                ->pluck('id');
            $this->last_report_msg_id = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
                ->where(fn($q) => $q->where('sender_id', $mitraId)->orWhereIn('recipient_type', ['mitra', 'all', 'both']))
                ->max('id') ?? 0;

            $this->last_notification_check = now();
        }
    }

    public function poll()
    {
        if (!auth()->check()) {
            return;
        }

        $mitraId = auth()->id();

        // 1. Check for new incoming regular chat messages
        $newMessages = ChatModel::where('mitra_id', $mitraId)
            ->where('sender_type', '!=', 'mitra')
            ->where('id', '>', $this->last_chat_id)
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->isNotEmpty()) {
            $this->last_chat_id = $newMessages->max('id');
            $latestMsg = $newMessages->last();
            $senderName = $latestMsg->sender_type === 'system' ? 'Sistem SayaBantu' : (optional($latestMsg->customer)->name ?? 'Customer');

            $this->dispatch(
                'help-new-message',
                helpId: $latestMsg->help_id,
                message: Str::limit($latestMsg->message, 150),
                from: $senderName,
                fromId: $latestMsg->customer_id,
                url: route('mitra.chat', ['customer' => $latestMsg->customer_id, 'help' => $latestMsg->help_id])
            );
            $this->dispatch('play-notification-sound', force: true);
            $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
        }

        // 2. Check for new incoming cancellation messages from Admin
        $mitraCancels = HelpCancelRequest::where('partner_id', $mitraId)
            ->orWhereHas('help', fn($q) => $q->where('mitra_id', $mitraId))
            ->pluck('id');

        if ($mitraCancels->isNotEmpty()) {
            $newCancelMsgs = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
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
                    url: route('mitra.chat', ['cancel_request' => $latestCancelMsg->help_cancel_request_id])
                );
                $this->dispatch('play-notification-sound', force: true);
                $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
            }
        }

        // 3. Check for new incoming report messages from Admin
        $mitraReports = PartnerReport::where('reported_user_id', $mitraId)
            ->orWhere('reporter_id', $mitraId)
            ->orWhereHas('reportedHelp', fn($q) => $q->where('mitra_id', $mitraId))
            ->pluck('id');

        if ($mitraReports->isNotEmpty()) {
            $newReportMsgs = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
                ->where('sender_id', '!=', $mitraId)
                ->whereIn('recipient_type', ['mitra', 'all', 'both'])
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
                    url: route('mitra.chat', ['report' => $latestReportMsg->partner_report_id])
                );
                $this->dispatch('play-notification-sound', force: true);
                $this->js("if(typeof window.playNotificationSound==='function'){window.playNotificationSound({force:true});}");
            }
        }

        // Check for new database notifications for mitra (help status updates, etc.)
        try {
            $newNotifications = auth()->user()->notifications()
                ->where('created_at', '>', $this->last_notification_check)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($newNotifications->count() > 0) {
                $this->last_notification_check = $newNotifications->last()->created_at;
                $this->dispatch('notifications-updated');
            }

            foreach ($newNotifications as $notification) {
                $data = $notification->data;
                Log::info('[RealtimeNotifications] processing notification', ['id' => $notification->id, 'type' => $notification->type, 'data' => $data]);

                if (isset($data['type']) && $data['type'] === 'help_status') {
                    $helpId = $data['help_id'] ?? ($data['helpId'] ?? 0);
                    $newStatus = $data['new_status'] ?? ($data['newStatus'] ?? null);

                    // Dispatch a browser event via inline JS so frontend can react
                    $this->js(sprintf(
                        "console.log(' Mitra help-status notification'); window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: { helpId: %d, newStatus: '%s', message: '%s' } }));",
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

