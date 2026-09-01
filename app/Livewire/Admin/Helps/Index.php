<?php

namespace App\Livewire\Admin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Help;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $cityFilter = 'all';
    public $perPage = 10;
    public $selectedHelpId = null;
    public $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'cityFilter' => ['except' => 'all'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedCityFilter()
    {
        $this->resetPage();
    }

    public function filterByStatus(string $status)
    {
        $this->statusFilter = ($status === 'all') ? '' : $status;
        $this->resetPage();
    }

    public function setCityFilter(string $city)
    {
        $this->cityFilter = $city;
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function viewHelp($id)
    {
        $this->selectedHelpId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedHelpId = null;
    }

    public function approveHelp($id)
    {
        $help = Help::findOrFail($id);
        $help->update(['status' => Help::STATUS_MENUNGGU_MITRA]);
        session()->flash('message', 'Bantuan berhasil disetujui');
    }

    public function rejectHelp($id)
    {
        $help = Help::findOrFail($id);
        if ($help->escrow_status === Help::ESCROW_STATUS_HELD) {
            app(\App\Services\HelpTransactionService::class)->autoCancelExpiredHelp($help, 'Ditolak oleh Admin Regional');
        } else {
            $help->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
            ]);
        }
        session()->flash('message', 'Bantuan ditolak dan dana escrow dikembalikan 100% ke saldo pemohon.');
    }

    public function render()
    {
        $admin = auth()->user();
        
        // Multi-City Resolution
        $allowedCityIds = ($admin && $admin->role === 'admin') ? $admin->getAdminCityIds() : [];
        $managedCities = ($admin && $admin->role === 'admin') ? $admin->getAdminCities() : collect();

        if ($this->cityFilter !== 'all' && in_array((int) $this->cityFilter, $allowedCityIds, true)) {
            $activeCityIds = [(int) $this->cityFilter];
        } else {
            $activeCityIds = $allowedCityIds;
        }

        // Base Query
        $query = Help::query()
            ->with(['customer', 'customer.city', 'mitra', 'city'])
            ->when(!empty($activeCityIds), function ($q) use ($activeCityIds) {
                $q->where(function ($sq) use ($activeCityIds) {
                    $sq->whereIn('city_id', $activeCityIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('city_id', $activeCityIds));
                });
            })
            ->when(empty($activeCityIds) && $admin && $admin->role === 'admin', function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sq) {
                    $sq->where('title', 'like', '%' . $this->search . '%')
                       ->orWhere('description', 'like', '%' . $this->search . '%')
                       ->orWhere('order_id', 'like', '%' . $this->search . '%')
                       ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->statusFilter !== '', function ($q) {
                $filter = strtolower(trim($this->statusFilter));

                if (in_array($filter, ['pending', 'menunggu', 'menunggu_mitra'])) {
                    $q->whereIn('status', ['pending', 'menunggu_mitra', 'menunggu']);
                } elseif (in_array($filter, ['active', 'aktif', 'in_progress', 'sedang_diproses'])) {
                    $q->whereIn('status', [
                        'active', 'taken', 'memperoleh_mitra', 'sedang_diproses',
                        'in_progress', 'partner_on_the_way', 'partner_arrived',
                        'waiting_customer_confirmation'
                    ]);
                } elseif (in_array($filter, ['completed', 'selesai'])) {
                    $q->whereIn('status', ['completed', 'selesai']);
                } elseif (in_array($filter, ['cancelled', 'dibatalkan', 'rejected', 'ditolak'])) {
                    $q->whereIn('status', ['cancelled', 'dibatalkan', 'rejected']);
                } else {
                    $q->where('status', $filter);
                }
            });

        $helps = $query->latest()->paginate($this->perPage);

        // Statistics - filtered by active city scope
        $statsQuery = Help::query();
        if (!empty($activeCityIds)) {
            $statsQuery->where(function ($sq) use ($activeCityIds) {
                $sq->whereIn('city_id', $activeCityIds)
                  ->orWhereHas('customer', fn($cq) => $cq->whereIn('city_id', $activeCityIds));
            });
        } elseif ($admin && $admin->role === 'admin') {
            $statsQuery->whereRaw('1 = 0');
        }

        $totalHelps = (clone $statsQuery)->count();
        $pendingHelps = (clone $statsQuery)->whereIn('status', ['pending', 'menunggu_mitra', 'menunggu'])->count();
        $activeHelps = (clone $statsQuery)->whereIn('status', [
            'active', 'taken', 'memperoleh_mitra', 'sedang_diproses',
            'in_progress', 'partner_on_the_way', 'partner_arrived',
            'waiting_customer_confirmation'
        ])->count();
        $completedHelps = (clone $statsQuery)->whereIn('status', ['completed', 'selesai'])->count();
        $cancelledHelps = (clone $statsQuery)->whereIn('status', ['cancelled', 'dibatalkan', 'rejected'])->count();

        $selectedHelp = $this->selectedHelpId ? Help::with(['customer', 'mitra', 'city', 'rating'])->find($this->selectedHelpId) : null;
        $helpActivities = $this->selectedHelpId ? \App\Models\PartnerActivity::with('user')->where('help_id', $this->selectedHelpId)->orderBy('created_at', 'asc')->get() : collect();

        return view('livewire.admin.helps.index', compact(
            'helps',
            'managedCities',
            'totalHelps',
            'pendingHelps',
            'activeHelps',
            'completedHelps',
            'cancelledHelps',
            'selectedHelp',
            'helpActivities'
        ));
    }
}
