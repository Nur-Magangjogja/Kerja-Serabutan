<?php

namespace App\Livewire\SuperAdmin\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read
    public $perPage = 15;

    public function mount()
    {
        // Load notifications on mount
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->find($notificationId);
        
        if ($notification) {
            $notification->delete();
        }
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Auth::user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate($this->perPage);
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return view('livewire.superadmin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}

