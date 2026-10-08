<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read, ktp, vehicle, report, support, cancellation, finance
    public $perPage = 15;

    public function mount()
    {
        // Load initial state
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ?->notifications()
            ->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            Dropdown::applyAdminFilter($user->notifications())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function deleteNotification($notificationId)
    {
        $notification = Auth::user()
            ?->notifications()
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
        $user = Auth::user();
        $baseQuery = Dropdown::applyAdminFilter($user->notifications());
        $query = clone $baseQuery;

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($this->filter === 'ktp') {
            $query->where(function ($q) {
                $q->where('data->category', 'ktp')->orWhere('data->type', 'like', '%ktp%');
            });
        } elseif ($this->filter === 'vehicle') {
            $query->where(function ($q) {
                $q->whereIn('data->category', ['kendaraan', 'vehicle'])->orWhere('data->type', 'like', '%vehicle%')->orWhere('data->type', 'like', '%kendaraan%');
            });
        } elseif ($this->filter === 'report') {
            $query->where(function ($q) {
                $q->where('data->category', 'report')->orWhere('data->type', 'like', '%report%');
            });
        } elseif ($this->filter === 'support') {
            $query->where(function ($q) {
                $q->where('data->category', 'support')->orWhere('data->type', 'like', '%support%')->orWhere('data->type', 'like', '%chat%');
            });
        } elseif ($this->filter === 'cancellation') {
            $query->where(function ($q) {
                $q->whereIn('data->category', ['cancellation', 'dispute'])->orWhere('data->type', 'like', '%cancel%')->orWhere('data->type', 'like', '%dispute%');
            });
        } elseif ($this->filter === 'finance') {
            $query->where(function ($q) {
                $q->whereIn('data->category', ['withdraw', 'topup', 'top_up'])->orWhere('data->type', 'like', '%withdraw%')->orWhere('data->type', 'like', '%topup%');
            });
        } elseif ($this->filter === 'oversight') {
            $query->where(function ($q) {
                $q->whereIn('data->category', ['account_oversight', 'oversight'])->orWhere('data->type', 'like', '%oversight%');
            });
        }

        $notifications = $query->paginate($this->perPage);
        $unreadCount = (clone $baseQuery)->whereNull('read_at')->count();

        return view('livewire.admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
