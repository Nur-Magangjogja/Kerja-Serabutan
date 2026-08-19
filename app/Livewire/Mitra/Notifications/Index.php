<?php

namespace App\Livewire\Mitra\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Notifications\DatabaseNotification;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selected = [];
    public $perPage = 20;

    public function mount()
    {
        $this->checkAutoMarkAsRead();
    }

    public function hydrate()
    {
        $this->checkAutoMarkAsRead();
    }

    protected function checkAutoMarkAsRead()
    {
        if (auth()->check()) {
            $settings = auth()->user()->notification_settings ?? [];
            if (!empty($settings['auto_mark_read'])) {
                auth()->user()->unreadNotifications->markAsRead();
            }
        }
    }

    public function markAsRead($notificationId)
    {
        if (auth()->check()) {
            $notification = DatabaseNotification::find($notificationId);
            if ($notification && $notification->notifiable_id === auth()->id()) {
                $notification->markAsRead();
            }
        }
    }

    public function markAllAsRead()
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
            session()->flash('message', 'Semua notifikasi ditandai telah dibaca');
        }
    }

    public function selectAllOnPage($ids = [])
    {
        $this->selected = is_array($ids) ? $ids : [];
    }

    public function clearSelection()
    {
        $this->selected = [];
    }

    public function bulkDelete()
    {
        if (empty($this->selected)) return;

        DatabaseNotification::whereIn('id', $this->selected)
            ->where('notifiable_id', auth()->id())
            ->delete();

        $count = count($this->selected);
        $this->selected = [];
        session()->flash('message', "{$count} notifikasi terpilih berhasil dihapus");
    }

    public function deleteNotification($notificationId)
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->delete();
            session()->flash('message', 'Notifikasi berhasil dihapus');
        }
    }

    public function deleteAllNotifications()
    {
        if (auth()->check()) {
            auth()->user()->notifications()->delete();
            $this->selected = [];
            session()->flash('message', 'Semua notifikasi berhasil dihapus');
        }
    }

    public function render()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate($this->perPage);
        $totalCount = auth()->user()->notifications()->count();

        return view('livewire.mitra.notifications.index', [
            'notifications' => $notifications,
            'totalCount' => $totalCount,
        ]);
    }
}
