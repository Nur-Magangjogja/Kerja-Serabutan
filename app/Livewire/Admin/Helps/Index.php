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
    public $districtFilter = 'all';
    public $cityFilter = 'all'; // Backward compatibility alias
    public $perPage = 10;
    public $selectedHelpId = null;
    public $showDetailModal = false;

    protected $listeners = [
        'admin-district-changed' => 'onAdminDistrictChanged',
        'admin-city-changed'     => 'onAdminDistrictChanged',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->districtFilter = auth()->user()?->getActiveAdminDistrictFilter() ?? 'all';
        $this->cityFilter = $this->districtFilter;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter()
    {
        auth()->user()?->setActiveAdminDistrictFilter($this->districtFilter);
        $this->cityFilter = $this->districtFilter;
        $this->dispatch('admin-district-changed', districtId: $this->districtFilter);
        $this->resetPage();
    }

    public function updatedCityFilter()
    {
        $this->districtFilter = $this->cityFilter;
        $this->updatedDistrictFilter();
    }

    public function filterByStatus(string $status)
    {
        $this->statusFilter = ($status === 'all') ? '' : $status;
        $this->resetPage();
    }

    public function setDistrictFilter(string $district)
    {
        $this->districtFilter = $district;
        $this->cityFilter = $district;
        auth()->user()?->setActiveAdminDistrictFilter($district);
        $this->dispatch('admin-district-changed', districtId: $district);
        $this->resetPage();
    }

    public function setCityFilter(string $city)
    {
        $this->setDistrictFilter($city);
    }

    public function onAdminDistrictChanged($districtId = null)
    {
        $this->districtFilter = auth()->user()?->getActiveAdminDistrictFilter() ?? 'all';
        $this->cityFilter = $this->districtFilter;
        $this->resetPage();
    }

    public function onAdminCityChanged($cityId = null)
    {
        $this->onAdminDistrictChanged($cityId);
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
        
        // District Resolution for Admin
        $allowedDistrictIds = ($admin && $admin->role === 'admin') ? $admin->getAdminDistrictIds() : [];
        $managedDistricts = ($admin && $admin->role === 'admin') ? $admin->getAdminDistricts() : collect();
        if ($admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
            $activeDistrictIds = $admin->getEffectiveAdminDistrictIds();
        } else {
            $activeDistrictIds = [];
        }

        // Base Query
        $query = Help::query()
            ->with(['customer', 'customer.district', 'customer.city', 'mitra', 'district', 'city'])
            ->when(!empty($activeDistrictIds), function ($q) use ($activeDistrictIds) {
                $q->where(function ($sq) use ($activeDistrictIds) {
                    $sq->whereIn('district_id', $activeDistrictIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds));
                });
            })
            ->when(empty($activeDistrictIds) && $admin && $admin->role === 'admin', function ($q) {
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

        // Statistics - filtered by active district scope
        $statsQuery = Help::query();
        if (!empty($activeDistrictIds)) {
            $statsQuery->where(function ($sq) use ($activeDistrictIds) {
                $sq->whereIn('district_id', $activeDistrictIds)
                  ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds));
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

        $selectedHelp = $this->selectedHelpId ? Help::with(['customer', 'mitra', 'district', 'city', 'rating'])->find($this->selectedHelpId) : null;
        $helpActivities = $this->selectedHelpId ? \App\Models\PartnerActivity::with('user')->where('help_id', $this->selectedHelpId)->orderBy('created_at', 'asc')->get() : collect();

        return view('livewire.admin.helps.index', [
            'helps'            => $helps,
            'managedDistricts' => $managedDistricts,
            'managedCities'    => $managedDistricts, // Backward compatibility
            'districtFilter'   => $this->districtFilter,
            'cityFilter'       => $this->districtFilter,
            'totalHelps'       => $totalHelps,
            'pendingHelps'     => $pendingHelps,
            'activeHelps'      => $activeHelps,
            'completedHelps'   => $completedHelps,
            'cancelledHelps'   => $cancelledHelps,
            'selectedHelp'     => $selectedHelp,
            'helpActivities'   => $helpActivities,
        ]);
    }
}
