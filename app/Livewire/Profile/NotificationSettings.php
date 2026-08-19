<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class NotificationSettings extends Component
{
    public $help_updates = true;
    public $chat_messages = true;
    public $transactions = true;
    public $sound_enabled = true;
    public $auto_mark_read = false;
    public $auto_cleanup_read = false;

    public $unreadCount = 0;
    public $readCount = 0;
    public $totalCount = 0;

    public function mount()
    {
        $this->loadSettings();
        $this->refreshCounts();
    }

    public function loadSettings()
    {
        $user = auth()->user();
        if ($user) {
            $settings = $user->notification_settings ?? [];
            $this->help_updates = $settings['help_updates'] ?? true;
            $this->chat_messages = $settings['chat_messages'] ?? true;
            $this->transactions = $settings['transactions'] ?? true;
            $this->sound_enabled = $settings['sound_enabled'] ?? true;
            $this->auto_mark_read = $settings['auto_mark_read'] ?? false;
            $this->auto_cleanup_read = $settings['auto_cleanup_read'] ?? false;
        }
    }

    public function refreshCounts()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->unreadCount = $user->unreadNotifications()->count();
            $this->readCount = $user->notifications()->whereNotNull('read_at')->count();
            $this->totalCount = $user->notifications()->count();
        }
    }

    public function updateSetting($setting)
    {
        if (!in_array($setting, ['help_updates', 'chat_messages', 'transactions', 'sound_enabled', 'auto_mark_read', 'auto_cleanup_read'])) {
            return;
        }

        $this->$setting = !$this->$setting;

        if (auth()->check()) {
            $user = auth()->user();
            $settings = $user->notification_settings ?? [];
            $settings[$setting] = $this->$setting;
            $user->notification_settings = $settings;
            $user->save();
        }

        session()->flash('message', 'Pengaturan notifikasi berhasil diperbarui');
    }

    public function markAllAsRead()
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
            $this->refreshCounts();
            session()->flash('message', 'Semua notifikasi berhasil ditandai telah dibaca');
        }
    }

    public function deleteReadNotifications()
    {
        if (auth()->check()) {
            $deleted = auth()->user()->notifications()->whereNotNull('read_at')->delete();
            $this->refreshCounts();
            session()->flash('message', "{$deleted} notifikasi yang sudah dibaca berhasil dibersihkan");
        }
    }

    public function deleteAllNotifications()
    {
        if (auth()->check()) {
            auth()->user()->notifications()->delete();
            $this->refreshCounts();
            session()->flash('message', 'Seluruh riwayat notifikasi berhasil dihapus');
        }
    }

    public function render()
    {
        return view('livewire.profile.notification-settings');
    }
}
