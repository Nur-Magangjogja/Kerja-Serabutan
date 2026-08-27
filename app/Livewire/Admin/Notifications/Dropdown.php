<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dropdown extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            return;
        }

        // Load 10 recent notifications for admin (reports, topup, withdraw, ktp)
        $this->notifications = $user->notifications()
            ->take(10)
            ->get();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()?->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.admin.notifications.dropdown');
    }
}
