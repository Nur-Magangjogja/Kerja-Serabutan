<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;

#[Layout('layouts.blank')]
class HelpSettings extends Component
{
    public $min_help_nominal;
    public $admin_fee;

    protected function rules()
    {
        return [
            'min_help_nominal' => 'required|numeric|min:0',
            'admin_fee' => 'required|numeric|min:0',
        ];
    }

    public function mount()
    {
        $this->min_help_nominal = (int) AppSetting::get('min_help_nominal', 10000);
        $this->admin_fee = (float) AppSetting::get('admin_fee', 0);
    }

    public function save()
    {
        $this->validate();

        AppSetting::set('min_help_nominal', (string) $this->min_help_nominal);
        AppSetting::set('admin_fee', (string) $this->admin_fee);

        session()->flash('message', 'Pengaturan bantuan berhasil disimpan.');
    }

    public function render()
    {
        return view('admin.help-settings');
    }
}
