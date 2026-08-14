<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use Livewire\Component;

class Show extends Component
{
    public Help $help;

    public function mount($id)
    {
        $this->help = Help::with(['city', 'user', 'mitra'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.customer.helps.show');
    }
}
