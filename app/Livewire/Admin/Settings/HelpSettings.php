<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;

#[Layout('layouts.admin')]
class HelpSettings extends Component
{
    public $min_help_nominal;
    public $platform_commission_rate;
    public $admin_fee;

    protected function rules()
    {
        return [
            'min_help_nominal' => 'required|numeric|min:0',
            'platform_commission_rate' => 'required|numeric|min:0|max:100',
            'admin_fee' => 'nullable|numeric|min:0',
        ];
    }

    public function mount()
    {
        $this->min_help_nominal = (int) AppSetting::get('min_help_nominal', 20000);
        $this->platform_commission_rate = (float) AppSetting::get('platform_commission_rate', 10);
        $this->admin_fee = (float) AppSetting::get('admin_fee', 0);
    }

    public function save()
    {
        $this->validate();

        AppSetting::set('min_help_nominal', (string) $this->min_help_nominal);
        AppSetting::set('platform_commission_rate', (string) $this->platform_commission_rate);
        if ($this->admin_fee !== null) {
            AppSetting::set('admin_fee', (string) $this->admin_fee);
        }

        session()->flash('message', 'Pengaturan bantuan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.settings.help-settings');
    }
}
