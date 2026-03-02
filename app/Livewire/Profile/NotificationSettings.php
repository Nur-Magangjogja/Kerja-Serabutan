<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class NotificationSettings extends Component
{
    public $generalNotification = true;
    public $sound = true;
    public $soundCall = false;
    public $vibrate = true;
    public $transactionUpdate = false;
    public $expenseReminder = false;
    public $budgetNotifications = false;
    public $lowBalanceAlerts = false;

    public function mount()
    {
        $user = auth()->user();
        // Load user notification preferences from database if needed
        // For now using default values
    }

    public function updateSetting($setting)
    {
        $this->$setting = !$this->$setting;

        // Save to database
        // auth()->user()->update([
        //     'notification_settings' => [
        //         'general' => $this->generalNotification,
        //         'sound' => $this->sound,
        //         // etc...
        //     ]
        // ]);

        session()->flash('message', 'Notification settings updated');
    }

    public function render()
    {
        return view('livewire.profile.notification-settings');
    }
}
