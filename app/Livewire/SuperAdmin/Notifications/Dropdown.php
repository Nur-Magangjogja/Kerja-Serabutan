<?php

namespace App\Livewire\SuperAdmin\Notifications;

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

    public static function applySuperAdminFilter($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('type', [
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
                \App\Notifications\NewWithdrawNotification::class,
                \App\Notifications\WithdrawStatusNotification::class,
                'App\Notifications\NewWithdrawNotification',
                'App\Notifications\WithdrawStatusNotification',
                \App\Notifications\NewKtpVerificationNotification::class,
                'App\Notifications\NewKtpVerificationNotification',
                \App\Notifications\VehicleVerificationNotification::class,
                'App\Notifications\VehicleVerificationNotification',
                \App\Notifications\NewVehicleSubmissionNotification::class,
                'App\Notifications\NewVehicleSubmissionNotification',
            ])
            ->orWhereIn('data->category', ['ktp', 'kendaraan', 'vehicle', 'withdraw', 'topup', 'top_up'])
            ->orWhere('data->type', 'like', '%ktp%')
            ->orWhere('data->type', 'like', '%vehicle%')
            ->orWhere('data->type', 'like', '%kendaraan%')
            ->orWhere('data->type', 'like', '%withdraw%')
            ->orWhere('data->type', 'like', '%penarikan%')
            ->orWhere('data->type', 'like', '%topup%')
            ->orWhere('data->type', 'like', '%top_up%')
            ->orWhere(function ($sub) {
                $sub->where('type', 'App\\Notifications\\AdminNotification')
                    ->where(function ($subTitle) {
                        $subTitle->where('data->title', 'like', '%KTP%')
                            ->orWhere('data->title', 'like', '%Verifikasi%')
                            ->orWhere('data->title', 'like', '%Penarikan%')
                            ->orWhere('data->title', 'like', '%Withdraw%')
                            ->orWhere('data->title', 'like', '%Top-Up%')
                            ->orWhere('data->title', 'like', '%Top Up%')
                            ->orWhere('data->title', 'like', '%Kendaraan%')
                            ->orWhere('data->message', 'like', '%KTP%')
                            ->orWhere('data->message', 'like', '%penarikan%')
                            ->orWhere('data->message', 'like', '%top up%')
                            ->orWhere('data->message', 'like', '%kendaraan%');
                    });
            });
        });
    }

    protected function getNotificationsBaseQuery($user)
    {
        return self::applySuperAdminFilter($user->notifications());
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

        $this->notifications = (clone $query)
            ->take(10)
            ->get();
        
        $this->unreadCount = (clone $query)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ?->notifications()
            ->find($notificationId);
        
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
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.superadmin.notifications.dropdown');
    }
}

