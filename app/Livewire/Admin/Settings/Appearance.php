<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Pengaturan - Tema Tampilan')]
class Appearance extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.appearance');
    }
}
