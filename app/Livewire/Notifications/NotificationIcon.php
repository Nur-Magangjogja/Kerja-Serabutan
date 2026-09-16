<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationIcon extends Component
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
            $this->route = $this->role === 'mitra' ? route('mitra.notifications.index') : route('customer.notifications.index');
        }

        if ($class) {
            $this->class = $class;
        }

        $this->refreshCount();
    }

    #[On('help-taken')]
    #[On('status-changed')]
    #[On('help-status-update')]
    #[On('notification-read')]
    #[On('refresh-notifications')]
    #[On('notifications-updated')]
    public function refreshCount()
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function getUnreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        try {
            return Auth::user()->unreadNotifications()
                ->where('type', '!=', 'App\Notifications\ChatMessageNotification')
                ->where(function ($q) {
                    $q->whereNull('data->type')->orWhere('data->type', '!=', 'chat_message');
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.notifications.notification-icon');
    }
}
