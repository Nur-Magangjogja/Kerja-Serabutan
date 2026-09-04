<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.superadmin')]
#[Title('Pengaturan - Tema Tampilan')]
class Appearance extends Component
{
    public function render()
    {
        return view('livewire.superadmin.settings.appearance');
    }
}
