<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $title = 'Riwayat Bantuan';
    
    public $statusFilter = 'all'; // all, selesai, dibatalkan
    public $search = '';

    public $selectedHelp = null;
    public $selectedHelpData = null;
    public $showModal = false;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search'       => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = in_array($status, ['all', 'selesai', 'dibatalkan'], true) ? $status : 'all';
        $this->resetPage();
    }

    public function showHelpDetail($helpId)
    {
        $help = Help::where('user_id', auth()->id())
            ->with(['user', 'city', 'district', 'mitra', 'ratings', 'cancelRequests', 'latestCancelRequest'])
            ->find($helpId);
        
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
            'district_name' => $help->district?->name,
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
        $userId = auth()->id();

        // Status terminal yang masuk ke riwayat: Selesai & Dibatalkan
        $selesaiStatuses = [Help::STATUS_SELESAI, 'completed'];
        $batalStatuses   = [Help::STATUS_DIBATALKAN, 'batal', 'cancelled', 'canceled'];
        $historyStatuses = array_merge($selesaiStatuses, $batalStatuses);

        // Hitung statistik khusus untuk customer yang sedang login (terisolasi per individu)
        $baseStatsQuery = Help::where('user_id', $userId);
        $totalSelesai   = (clone $baseStatsQuery)->whereIn('status', $selesaiStatuses)->count();
        $totalBatal     = (clone $baseStatsQuery)->whereIn('status', $batalStatuses)->count();
        $totalSpent     = (clone $baseStatsQuery)->whereIn('status', $selesaiStatuses)->sum('amount');
        $totalHistory   = $totalSelesai + $totalBatal;

        // Query riwayat tugas customer
        $query = Help::where('user_id', $userId)
            ->with([
                'user',
                'city',
                'district',
                'mitra',
                'rating',
                'ratings',
                'latestCancelRequest',
                'cancelRequests' => fn($q) => $q->latest(),
                'latestPartnerReport',
                'reports' => fn($q) => $q->latest(),
            ]);

        if ($this->statusFilter === 'selesai') {
            $query->whereIn('status', $selesaiStatuses);
        } elseif ($this->statusFilter === 'dibatalkan') {
            $query->whereIn('status', $batalStatuses);
        } else {
            $query->whereIn('status', $historyStatuses);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%")
                  ->orWhereHas('mitra', fn($mq) => $mq->where('name', 'like', "%{$s}%"));
            });
        }

        $helps = $query->latest('updated_at')->paginate(10);

        return view('livewire.customer.helps.history', [
            'helps'        => $helps,
            'totalHistory' => $totalHistory,
            'totalSelesai' => $totalSelesai,
            'totalBatal'   => $totalBatal,
            'totalSpent'   => $totalSpent,
        ])->layout('layouts.app');
    }
}

