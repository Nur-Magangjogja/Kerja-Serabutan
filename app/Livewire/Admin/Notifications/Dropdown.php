<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dropdown extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public bool $isOpen = false;

    public function mount()
    {
        $this->loadUnreadCount();
    }

    public static function applyAdminFilter($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('type', [
                \App\Notifications\NewKtpVerificationNotification::class,
                'App\Notifications\NewKtpVerificationNotification',
                \App\Notifications\VehicleVerificationNotification::class,
                'App\Notifications\VehicleVerificationNotification',
                \App\Notifications\NewVehicleSubmissionNotification::class,
                'App\Notifications\NewVehicleSubmissionNotification',
                \App\Notifications\NewReportNotification::class,
                'App\Notifications\NewReportNotification',
                \App\Notifications\NewReportMessageNotification::class,
                'App\Notifications\NewReportMessageNotification',
                \App\Notifications\NewCancellationReviewNotification::class,
                'App\Notifications\NewCancellationReviewNotification',
                \App\Notifications\NewWithdrawNotification::class,
                \App\Notifications\WithdrawStatusNotification::class,
                'App\Notifications\NewWithdrawNotification',
                'App\Notifications\WithdrawStatusNotification',
                \App\Notifications\NewTopupRequest::class,
                \App\Notifications\TopupRequestSubmitted::class,
                \App\Notifications\TopupApproved::class,
                \App\Notifications\TopupRejected::class,
                \App\Notifications\TopupCancelled::class,
                'App\Notifications\NewTopupRequest',
                'App\Notifications\TopupRequestSubmitted',
                'App\Notifications\TopupApproved',
                'App\Notifications\TopupRejected',
                'App\Notifications\TopupCancelled',
                \App\Notifications\HelpStatusNotification::class,
                'App\Notifications\HelpStatusNotification',
                \App\Notifications\NewHelpAvailableNotification::class,
                'App\Notifications\NewHelpAvailableNotification',
                \App\Notifications\HelpTakenNotification::class,
                'App\Notifications\HelpTakenNotification',
                \App\Notifications\AccountOversightNotification::class,
                'App\Notifications\AccountOversightNotification',
                'App\Notifications\AdminNotification',
            ])
            ->orWhereIn('data->category', ['ktp', 'kendaraan', 'vehicle', 'report', 'support', 'cancellation', 'dispute', 'withdraw', 'topup', 'top_up', 'help', 'account_oversight', 'oversight'])
            ->orWhere('data->type', 'like', '%ktp%')
            ->orWhere('data->type', 'like', '%vehicle%')
            ->orWhere('data->type', 'like', '%kendaraan%')
            ->orWhere('data->type', 'like', '%report%')
            ->orWhere('data->type', 'like', '%support%')
            ->orWhere('data->type', 'like', '%chat%')
            ->orWhere('data->type', 'like', '%cancel%')
            ->orWhere('data->type', 'like', '%dispute%')
            ->orWhere('data->type', 'like', '%withdraw%')
            ->orWhere('data->type', 'like', '%topup%')
            ->orWhere('data->type', 'like', '%top_up%')
            ->orWhere('data->type', 'like', '%oversight%')
            ->orWhere('data->type', 'like', '%help%');
        });
    }

    protected function getNotificationsBaseQuery($user)
    {
        return self::applyAdminFilter($user->notifications());
    }

    public function loadUnreadCount()
    {
        $user = Auth::user();

        if (!$user) {
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            return;
        }

        $this->unreadCount = $this->getNotificationsBaseQuery($user)
            ->whereNull('read_at')
            ->count();

        if ($this->isOpen) {
            $this->loadNotifications();
        }
    }

    public function loadNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            return;
        }

        $query = $this->getNotificationsBaseQuery($user);

        // Load 10 recent activity notifications for admin
        $this->notifications = (clone $query)
            ->take(10)
            ->get();

        $this->unreadCount = (clone $query)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->loadNotifications();
        }
    }

    public function closeDropdown()
    {
        $this->isOpen = false;
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $this->getNotificationsBaseQuery($user)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.admin.notifications.dropdown');
    }
}
