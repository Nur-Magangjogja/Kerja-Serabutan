<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Help;

class HelpDetailModal extends Component
{
    public $open = false;
    public $helpId;
    public $help;

    protected $listeners = [
        'openHelp' => 'openModal',
    ];

    public function openModal($id)
    {
        $this->helpId = $id;
        $this->help = Help::with(['user', 'city', 'category', 'ratings'])->find($id);

        if ($this->help) {
            $this->open = true;
        }
    }

    public function close()
    {
        $this->open = false;
        $this->help = null;
        $this->helpId = null;
    }

    public function render()
    {
        return view('livewire.help-detail-modal');
    }
}
