<?php

namespace App\Livewire\Chat;

use App\Models\Chat;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatIcon extends Component
{
    public $route = null;
    public $class = 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center text-white shadow-xs cursor-pointer relative';
    public $role = null;
    public $unreadCount = 0;

    public function mount($route = null, $class = null, $role = null)
    {
        $this->role = $role ?? (Auth::user()?->role ?? 'customer');

        if ($route) {
            $this->route = $route;
        } else {
            $this->route = $this->role === 'mitra' ? route('mitra.chat') : route('customer.chat');
        }

        if ($class) {
            $this->class = $class;
        }

        $this->refreshCount();
    }

    #[On('help-new-message')]
    #[On('chat-messages-read')]
    #[On('refresh-chat-icon')]
    #[On('notifications-updated')]
    public function refreshCount(...$params)
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function getUnreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        try {
            $userId = Auth::id();
            $role = $this->role ?? Auth::user()->role;

            if ($role === 'mitra') {
                // 1. Unread chat dari Customer & System
                $unreadChats = Chat::where('mitra_id', $userId)
                    ->whereIn('sender_type', ['customer', 'system'])
                    ->where(function ($q) {
                        $q->whereNull('read_at')->orWhere('is_read', false);
                    })
                    ->count();

                // 2. Unread messages dari Admin Report
                $mitraReports = PartnerReport::where('reported_user_id', $userId)
                    ->orWhere('reporter_id', $userId)
                    ->orWhereHas('reportedHelp', function ($q) use ($userId) {
                        $q->where('mitra_id', $userId);
                    })
                    ->pluck('id');

                $unreadAdminReports = 0;
                if ($mitraReports->isNotEmpty()) {
                    $unreadAdminReports = PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
                        ->where('sender_id', '!=', $userId)
                        ->where('is_read', false)
                        ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                        ->count();
                }

                // 3. Unread messages dari Cancellation Review
                $mitraCancels = HelpCancelRequest::where('partner_id', $userId)
                    ->orWhereHas('help', fn($q) => $q->where('mitra_id', $userId))
                    ->pluck('id');

                $unreadAdminCancels = 0;
                if ($mitraCancels->isNotEmpty()) {
                    $unreadAdminCancels = HelpCancelMessage::whereIn('help_cancel_request_id', $mitraCancels)
                        ->where('sender_id', '!=', $userId)
                        ->where('is_read', false)
                        ->whereIn('recipient_type', ['mitra', 'all', 'both'])
                        ->count();
                }

                return $unreadChats + $unreadAdminReports + $unreadAdminCancels;
            } else {
                // 1. Unread chat dari Mitra & System
                $unreadChats = Chat::where('customer_id', $userId)
                    ->whereIn('sender_type', ['mitra', 'system'])
                    ->where(function ($q) {
                        $q->whereNull('read_at')->orWhere('is_read', false);
                    })
                    ->count();

                // 2. Unread messages dari Admin Report
                $customerReports = PartnerReport::where('reporter_id', $userId)
                    ->orWhere('reported_user_id', $userId)
                    ->orWhereHas('reportedHelp', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->pluck('id');

                $unreadAdminReports = 0;
                if ($customerReports->isNotEmpty()) {
                    $unreadAdminReports = PartnerReportMessage::whereIn('partner_report_id', $customerReports)
                        ->where('sender_id', '!=', $userId)
                        ->where('is_read', false)
                        ->whereIn('recipient_type', ['customer', 'all', 'both'])
                        ->count();
                }

                // 3. Unread messages dari Cancellation Review
                $customerCancels = HelpCancelRequest::where('customer_id', $userId)
                    ->orWhereHas('help', fn($q) => $q->where('user_id', $userId))
                    ->pluck('id');

                $unreadAdminCancels = 0;
                if ($customerCancels->isNotEmpty()) {
                    $unreadAdminCancels = HelpCancelMessage::whereIn('help_cancel_request_id', $customerCancels)
                        ->where('sender_id', '!=', $userId)
                        ->where('is_read', false)
                        ->whereIn('recipient_type', ['customer', 'all', 'both'])
                        ->count();
                }

                return $unreadChats + $unreadAdminReports + $unreadAdminCancels;
            }
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.chat.chat-icon');
    }
}
