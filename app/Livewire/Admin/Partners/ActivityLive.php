<?php

namespace App\Livewire\Admin\Partners;

use Livewire\Component;
use App\Models\Help;
use App\Models\User;
use App\Models\PartnerActivity;

class ActivityLive extends Component
{
    public $mitraActive = 0;
    public $pendingHelps = 0;
    public $inProgress = 0;
    public $feed = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Filter by admin's city if user is admin
        $mitraQuery = User::where('role', 'mitra')->where('status', 'active');
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $mitraQuery->where('city_id', auth()->user()->city_id);
        }
        $this->mitraActive = $mitraQuery->count();

        // Filter helps by users from admin's city
        $pendingQuery = Help::available();
        $inProgressQuery = Help::taken();
        
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $pendingQuery->whereHas('customer', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
            $inProgressQuery->whereHas('customer', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $this->pendingHelps = $pendingQuery->count();
        $this->inProgress = $inProgressQuery->count();

        // Filter partner activities by admin's city
        $feedQuery = PartnerActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $feedQuery->whereHas('user', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $this->feed = $feedQuery->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'user' => $a->user ? $a->user->name : '-',
                    'type' => $a->activity_type,
                    'desc' => $a->description,
                    'time' => $a->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.partners.activity-live');
    }
}
