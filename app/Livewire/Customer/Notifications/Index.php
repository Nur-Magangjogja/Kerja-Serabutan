<?php

namespace App\Livewire\Customer\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Notifications\DatabaseNotification;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selected = [];
    public $perPage = 20;

    public function mount()
    {
        // Auto mark all unread notifications as read on view
        $this->markAllAsReadSilently();
    }

    public function hydrate()
    {
        // Auto mark as read on component hydration/updates as well
        $this->markAllAsReadSilently();
    }

    protected function markAllAsReadSilently()
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
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

        return view('livewire.customer.notifications.index', [
            'notifications' => $notifications,
            'totalCount' => $totalCount,
        ]);
    }
}
