<?php

namespace App\Livewire\Admin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Help;
use App\Models\District;
use App\Models\City;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
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
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($this->districtFilter);
            $admin->setActiveAdminCityFilter($this->districtFilter);
        }
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
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($district);
            $admin->setActiveAdminCityFilter($district);
        }
        $this->dispatch('admin-district-changed', districtId: $district);
        $this->resetPage();
    }

    public function setCityFilter(string $city)
    {
        $this->setDistrictFilter($city);
    }

    public function onAdminDistrictChanged($districtId = null)
    {
        $admin = auth()->user();
        $target = (string) ($districtId ?? ($admin ? ($admin->getActiveAdminDistrictFilter() !== 'all' ? $admin->getActiveAdminDistrictFilter() : $admin->getActiveAdminCityFilter()) : 'all'));
        
        $changed = ($this->districtFilter !== $target);

        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($target);
            $admin->setActiveAdminCityFilter($target);
        }
        $this->districtFilter = $target;
        $this->cityFilter = $target;

        if ($changed) {
            $this->resetPage();
        }
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
        $help->update([
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
        ]);
        session()->flash('message', 'Bantuan berhasil disetujui dan dibuka ke pool mitra');
    }

    public function rejectHelp($id)
    {
        $help = Help::findOrFail($id);
        app(\App\Services\HelpCancellationService::class)->cancelByPartnerUnilaterally($help, auth()->user(), 'Ditolak oleh Admin Regional');
        session()->flash('message', 'Bantuan ditolak dan dana escrow dikembalikan 100% ke saldo pemohon.');
    }

    public function render()
    {
        $admin = auth()->user();
        
        // District and City Resolution for Admin
        $allowedDistrictIds = ($admin && $admin->role === 'admin') ? $admin->getAdminDistrictIds() : [];
        $managedDistricts = ($admin && $admin->role === 'admin') ? $admin->getAdminDistricts() : collect();
        $allowedCityIds = ($admin && $admin->role === 'admin') ? $admin->getAdminCityIds() : [];
        $managedCities = ($admin && $admin->role === 'admin') ? $admin->getAdminCities() : collect();

        $activeDistrictIds = [];
        $activeCityIds = [];

        if ($admin && $admin->role === 'admin') {
            $filterValue = $this->districtFilter !== 'all' ? $this->districtFilter : ($this->cityFilter !== 'all' ? $this->cityFilter : 'all');

            if ($filterValue !== 'all') {
                $valInt = (int) $filterValue;
                if (in_array($valInt, $allowedDistrictIds, true)) {
                    $activeDistrictIds = [$valInt];
                } elseif (in_array($valInt, $allowedCityIds, true)) {
                    $activeCityIds = [$valInt];
                    $activeDistrictIds = District::where('city_id', $valInt)->pluck('id')->map('intval')->all();
                } else {
                    $activeDistrictIds = $allowedDistrictIds;
                    $activeCityIds = $allowedCityIds;
                }
            } else {
                $activeDistrictIds = $allowedDistrictIds;
                $activeCityIds = $allowedCityIds;
            }
        }

        // Base Query
        $query = Help::query()
            ->with(['customer', 'customer.district', 'customer.city', 'mitra', 'district', 'city'])
            ->when($admin && $admin->role === 'admin', function ($q) use ($activeDistrictIds, $activeCityIds) {
                if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                    $q->where(function ($sq) use ($activeDistrictIds, $activeCityIds) {
                        $sq->whereIn('district_id', $activeDistrictIds)
                          ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds))
                          ->orWhere(function ($ssq) use ($activeCityIds) {
                              $ssq->whereNull('district_id')
                                 ->whereIn('city_id', $activeCityIds);
                          })
                          ->orWhereHas('customer', function ($cq) use ($activeCityIds) {
                              $cq->whereNull('district_id')
                                 ->whereIn('city_id', $activeCityIds);
                          });
                    });
                } elseif (!empty($activeDistrictIds)) {
                    $q->where(function ($sq) use ($activeDistrictIds) {
                        $sq->whereIn('district_id', $activeDistrictIds)
                          ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds));
                    });
                } elseif (!empty($activeCityIds)) {
                    $q->where(function ($sq) use ($activeCityIds) {
                        $sq->whereIn('city_id', $activeCityIds)
                          ->orWhereHas('customer', fn($cq) => $cq->whereIn('city_id', $activeCityIds));
                    });
                } else {
                    $q->whereRaw('1 = 0');
                }
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
                    $q->where('status', Help::STATUS_MENUNGGU_MITRA);
                } elseif (in_array($filter, ['active', 'aktif', 'in_progress', 'sedang_diproses', 'diproses'])) {
                    $q->whereIn('status', array_merge(Help::activeStatuses(), [Help::STATUS_WAITING_CONFIRMATION]));
                } elseif (in_array($filter, ['completed', 'selesai'])) {
                    $q->where('status', Help::STATUS_SELESAI);
                } elseif (in_array($filter, ['cancelled', 'dibatalkan', 'rejected', 'ditolak'])) {
                    $q->where('status', Help::STATUS_DIBATALKAN);
                } else {
                    $q->where('status', Help::normalizeStatus($filter));
                }
            });

        $helps = $query->latest()->paginate($this->perPage);

        // Statistics - filtered by active district/city scope
        $statsQuery = Help::query();
        if ($admin && $admin->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $statsQuery->where(function ($sq) use ($activeDistrictIds, $activeCityIds) {
                    $sq->whereIn('district_id', $activeDistrictIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds))
                      ->orWhere(function ($ssq) use ($activeCityIds) {
                          $ssq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      })
                      ->orWhereHas('customer', function ($cq) use ($activeCityIds) {
                          $cq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $statsQuery->where(function ($sq) use ($activeDistrictIds) {
                    $sq->whereIn('district_id', $activeDistrictIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds));
                });
            } elseif (!empty($activeCityIds)) {
                $statsQuery->where(function ($sq) use ($activeCityIds) {
                    $sq->whereIn('city_id', $activeCityIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('city_id', $activeCityIds));
                });
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }

        $totalHelps = (clone $statsQuery)->count();
        $pendingHelps = (clone $statsQuery)->where('status', Help::STATUS_MENUNGGU_MITRA)->count();
        $activeHelps = (clone $statsQuery)->whereIn('status', array_merge(Help::activeStatuses(), [Help::STATUS_WAITING_CONFIRMATION]))->count();
        $completedHelps = (clone $statsQuery)->where('status', Help::STATUS_SELESAI)->count();
        $cancelledHelps = (clone $statsQuery)->where('status', Help::STATUS_DIBATALKAN)->count();

        $selectedHelp = $this->selectedHelpId ? Help::with(['customer', 'mitra', 'district.city', 'city', 'rating', 'cancelRequest.customer', 'cancelRequest.partner', 'escrowTransaction'])->find($this->selectedHelpId) : null;
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
