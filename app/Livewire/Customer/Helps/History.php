<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $title = 'Riwayat Bantuan';
    
    public $selectedHelp = null;
    public $selectedHelpData = null;
    public $showModal = false;

    public function showHelpDetail($helpId)
    {
        $help = Help::with(['user', 'city', 'mitra'])->find($helpId);
        
        if (!$help) {
            $this->selectedHelp = null;
            $this->selectedHelpData = null;
            $this->showModal = false;
            return;
        }

        $this->selectedHelp = $help;
        $this->selectedHelpData = [
            'id' => $help->id,
            'title' => $help->title,
            'description' => $help->description,
            'equipment_provided' => $help->equipment_provided,
            'amount' => $help->amount,
            'admin_fee' => $help->admin_fee,
            'total_amount' => $help->total_amount,
            'photo' => $help->photo,
            'location' => $help->location,
            'full_address' => $help->full_address,
            'latitude' => $help->latitude,
            'longitude' => $help->longitude,
            'status' => $help->status,
            'user_name' => $help->user?->name,
            'user_phone' => $help->user?->phone,
            'city_name' => $help->city?->name,
            'mitra_name' => $help->mitra?->name,
            'mitra_phone' => $help->mitra?->phone,
            'created_at' => $help->created_at?->format('d M Y, H:i'),
            'created_at_human' => $help->created_at?->diffForHumans(),
            'completed_at' => $help->updated_at?->format('d M Y, H:i'),
        ];
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedHelp = null;
        $this->selectedHelpData = null;
    }

    public function render()
    {
        $completedHelps = Help::where('user_id', auth()->id())
            ->whereIn('status', ['selesai', 'completed'])
            ->with([
                'user',
                'city',
                'mitra',
                'rating',
                'ratings'
            ])
            ->latest()
            ->paginate(10);

        return view('livewire.customer.helps.history', [
            'completedHelps' => $completedHelps
        ])->layout('layouts.app');
    }
}
