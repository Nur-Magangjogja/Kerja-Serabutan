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

    protected function getNotificationsBaseQuery($user)
    {
        return $user->notifications()
            ->where(function ($q) {
                $q->whereIn('type', [
                    \App\Notifications\NewTopupRequest::class,
                    \App\Notifications\NewWithdrawNotification::class,
                    'App\Notifications\NewTopupRequest',
                    'App\Notifications\NewWithdrawNotification',
                ])
                ->orWhere('data->type', 'like', '%topup%')
                ->orWhere('data->type', 'like', '%withdraw%')
                ->orWhere('data->category', 'topup')
                ->orWhere('data->category', 'withdraw');
            });
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

