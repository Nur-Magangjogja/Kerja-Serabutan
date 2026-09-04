<?php

namespace App\Livewire\SuperAdmin\Cities;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\City;
use App\Models\User;
use App\Models\Province;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $cityId;

    // helper for delete modal display
    public $deletingCityName = null;

    // form fields
    public $name = '';
    public $province = '';
    public $province_id = null;
    public $admin_id = null;
    public $is_active = true;
    public $deleteId = null;
    // detail modal + chart data
    public $showDetailModal = false;
    public $detailCityId = null;
    public $detailCityName = null;
    public $detailProvince = null;
    public $detailStats = [
        'customers' => 0,
        'mitras' => 0,
        'total_users' => 0,
        'districts_count' => 0,
        'capacity_status' => 'open',
        'is_active' => true,
    ];
    public $chartDays = 30;
    public $chartLabels = [];
    public $chartCustomerData = [];
    public $chartMitraData = [];

    // provinces management
    public $provinces = [];
    public $showProvinceModal = false;
    public $provinceName = '';
    public $provinceEditId = null;
    public $showProvinceDeleteModal = false;
    public $deleteProvinceId = null;
    public $deletingProvinceName = null;
    public $filterProvinceId = null;

    // Capacity & Supply-Demand Management
    public $showCapacityModal = false;
    public $capacityCityId = null;
    public $capacityCityName = null;
    public $overrideStatus = 'open';
    public $overrideHours = 24;
    public $overrideNotes = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'province', 'province_id', 'admin_id', 'is_active', 'cityId', 'editMode']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function editCity($id)
    {
        $city = City::findOrFail($id);
        $this->cityId = $city->id;
        $this->name = $city->name;
        $this->province = $city->province;
        $this->province_id = $city->province_id;
        $this->admin_id = $city->admin_id;
        $this->is_active = $city->is_active;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $city = City::find($id);
        $this->deletingCityName = $city ? $city->name : null;
        $this->showDeleteModal = true;
    }

    public function toggleStatus($id)
    {
        $city = City::findOrFail($id);
        $city->update(['is_active' => !$city->is_active]);
        session()->flash('message', 'Status kota berhasil diubah');
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'admin_id' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ];

        // if province_id chosen, validate it; otherwise require free-text province
        if ($this->province_id) {
            $rules['province_id'] = 'exists:provinces,id';
        } else {
            $rules['province'] = 'required|string|max:255';
        }

        $validated = $this->validate($rules);

        // Ensure selected user is actually an admin
        $admin = User::find($validated['admin_id']);
        if (!$admin || $admin->role !== 'admin') {
            $this->addError('admin_id', 'Pilih user dengan role admin sebagai pengelola kota');
            return;
        }

        if ($this->editMode && $this->cityId) {
            $city = City::findOrFail($this->cityId);
            $oldAdminId = $city->admin_id;
            // ensure province name is stored for backward compatibility
            if ($this->province_id) {
                $prov = Province::find($this->province_id);
                if ($prov) $validated['province'] = $prov->name;
                $validated['province_id'] = $this->province_id;
            }

            $city->update($validated);

            // If admin changed, clear old admin's city_id
            if ($oldAdminId && $oldAdminId !== $city->admin_id) {
                $oldAdmin = User::find($oldAdminId);
                if ($oldAdmin) {
                    $oldAdmin->city_id = null;
                    $oldAdmin->save();
                }
            }

            // assign new admin's city_id
            $admin->city_id = $city->id;
            $admin->save();

            session()->flash('message', 'Kota berhasil diperbarui');
        } else {
            if ($this->province_id) {
                $prov = Province::find($this->province_id);
                if ($prov) $validated['province'] = $prov->name;
                $validated['province_id'] = $this->province_id;
            }

            $city = City::create($validated);

            // assign admin to this new city
            $admin->city_id = $city->id;
            $admin->save();

            session()->flash('message', 'Kota berhasil dibuat dan admin ditetapkan');
        }

        $this->showModal = false;
        $this->reset(['name', 'province', 'province_id', 'admin_id', 'is_active', 'cityId', 'editMode']);
    }

    /** Provinces management */
    public function openProvinceModal($id = null)
    {
        if ($id) {
            $prov = Province::findOrFail($id);
            $this->provinceEditId = $prov->id;
            $this->provinceName = $prov->name;
        } else {
            $this->provinceEditId = null;
            $this->provinceName = '';
        }
        $this->showProvinceModal = true;
    }

    public function saveProvince()
    {
        $this->validate(['provinceName' => 'required|string|max:255']);

        if ($this->provinceEditId) {
            $prov = Province::findOrFail($this->provinceEditId);
            $prov->update(['name' => $this->provinceName]);
            session()->flash('message', 'Provinsi diperbarui');
        } else {
            Province::create(['name' => $this->provinceName]);
            session()->flash('message', 'Provinsi dibuat');
        }

        $this->showProvinceModal = false;
        $this->provinceEditId = null;
        $this->provinceName = '';
    }

    public function confirmDeleteProvince($id)
    {
        $this->deleteProvinceId = $id;
        $prov = Province::find($id);
        $this->deletingProvinceName = $prov ? $prov->name : null;
        $this->showProvinceDeleteModal = true;
    }

    public function deleteProvince()
    {
        if ($this->deleteProvinceId) {
            $prov = Province::findOrFail($this->deleteProvinceId);
            // detach province from cities (keep province name for compatibility)
            City::where('province_id', $prov->id)->update(['province_id' => null]);
            $prov->delete();
            session()->flash('message', 'Provinsi dihapus');
        }

        $this->showProvinceDeleteModal = false;
        $this->deleteProvinceId = null;
        $this->deletingProvinceName = null;
    }

    public function toggleProvinceStatus($id)
    {
        $prov = Province::findOrFail($id);
        $prov->update(['is_active' => !$prov->is_active]);
        session()->flash('message', 'Status provinsi berhasil diubah');
    }

    public function selectProvince($id = null)
    {
        if ($this->filterProvinceId === $id) {
            $this->filterProvinceId = null;
        } else {
            $this->filterProvinceId = $id;
        }
        $this->resetPage();
    }

    public function deleteCity()
    {
        if ($this->deleteId) {
            $city = City::findOrFail($this->deleteId);
            // unset admin's city relationship if set
            if ($city->admin_id) {
                $admin = User::find($city->admin_id);
                if ($admin) {
                    $admin->city_id = null;
                    $admin->save();
                }
            }
            $city->delete();
            session()->flash('message', 'Kota berhasil dihapus');
        }

        // reset delete helpers
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deletingCityName = null;
    }

    /**
     * Open detail modal for a city and load stats and chart data on-demand
     */
    public function openDetailModal($cityId)
    {
        $city = City::with(['capacity', 'districts'])->find($cityId);
        if (!$city) {
            session()->flash('error', 'Kota tidak ditemukan');
            return;
        }

        $this->detailCityId = $city->id;
        $this->detailCityName = $city->name;
        $this->detailProvince = $city->province;
        $this->chartDays = 30;

        // Query total active user counts by role (fast indexed single query)
        $userCounts = User::where('city_id', $cityId)
            ->where('status', 'active')
            ->selectRaw("role, count(*) as count")
            ->groupBy('role')
            ->pluck('count', 'role');

        $customersCount = (int) ($userCounts['customer'] ?? 0);
        $mitrasCount = (int) ($userCounts['mitra'] ?? 0);

        $this->detailStats = [
            'customers' => $customersCount,
            'mitras' => $mitrasCount,
            'total_users' => $customersCount + $mitrasCount,
            'districts_count' => $city->districts ? $city->districts->count() : 0,
            'capacity_status' => $city->capacity?->getEffectiveStatus() ?? 'open',
            'is_active' => (bool) $city->is_active,
        ];

        $this->loadChartData($city->id);
        $this->showDetailModal = true;
    }

    /**
     * Change chart timeframe (7, 14, 30, 90 days)
     */
    public function setChartDays($days)
    {
        $this->chartDays = in_array((int)$days, [7, 14, 30, 90]) ? (int)$days : 30;
        if ($this->detailCityId) {
            $this->loadChartData($this->detailCityId);
        }
    }

    /**
     * Load chart data for a city using a single aggregate query
     */
    public function loadChartData($cityId)
    {
        $days = $this->chartDays;
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        // 1 single query grouped by date and role
        $records = User::query()
            ->selectRaw("DATE(created_at) as date_val, role, COUNT(*) as count")
            ->where('city_id', $cityId)
            ->where('status', 'active')
            ->whereIn('role', ['customer', 'mitra'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date_val', 'role')
            ->get();

        $indexedData = [];
        foreach ($records as $r) {
            $indexedData[$r->date_val][$r->role] = (int) $r->count;
        }

        $this->chartLabels = [];
        $this->chartCustomerData = [];
        $this->chartMitraData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dateObj = now()->subDays($i);
            $dateKey = $dateObj->format('Y-m-d');
            $this->chartLabels[] = $dateObj->format('d M');
            $this->chartCustomerData[] = $indexedData[$dateKey]['customer'] ?? 0;
            $this->chartMitraData[] = $indexedData[$dateKey]['mitra'] ?? 0;
        }

        // Dispatch browser event so JS can render the chart
        $this->dispatch('city-chart-ready', [
            'labels' => $this->chartLabels,
            'customers' => $this->chartCustomerData,
            'mitras' => $this->chartMitraData,
        ]);
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailCityId = null;
        $this->detailCityName = null;
        $this->detailProvince = null;
        $this->detailStats = [
            'customers' => 0,
            'mitras' => 0,
            'total_users' => 0,
            'districts_count' => 0,
            'capacity_status' => 'open',
            'is_active' => true,
        ];
        $this->chartLabels = [];
        $this->chartCustomerData = [];
        $this->chartMitraData = [];
        $this->dispatch('city-chart-closed');
    }

    public function evaluateCityCapacity($cityId)
    {
        $city = City::findOrFail($cityId);
        app(\App\Services\SupplyDemandService::class)->evaluateCapacity($city);
        session()->flash('message', "Kapasitas & metrik kota {$city->name} berhasil diperbarui.");
    }

    public function openCapacityModal($cityId)
    {
        $city = City::with('capacity')->findOrFail($cityId);
        $this->capacityCityId   = $city->id;
        $this->capacityCityName = $city->name;
        $this->overrideStatus   = $city->capacity?->admin_override_status ?? $city->capacity?->capacity_status ?? 'open';
        $this->overrideHours    = 24;
        $this->overrideNotes    = $city->capacity?->admin_override_notes ?? '';
        $this->showCapacityModal = true;
    }

    public function saveCapacityOverride()
    {
        if (!$this->capacityCityId) return;

        $city = City::findOrFail($this->capacityCityId);
        $until = $this->overrideHours > 0 ? now()->addHours($this->overrideHours) : null;

        app(\App\Services\SupplyDemandService::class)->setAdminOverride(
            $city,
            auth()->user(),
            $this->overrideStatus,
            $until,
            $this->overrideNotes
        );

        $this->showCapacityModal = false;
        session()->flash('message', "Override kapasitas untuk {$city->name} berhasil disimpan.");
    }

    public function clearCapacityOverride($cityId)
    {
        $city = City::findOrFail($cityId);
        app(\App\Services\SupplyDemandService::class)->clearAdminOverride($city);
        session()->flash('message', "Override kapasitas {$city->name} berhasil dihapus (kembali ke auto-manage).");
    }

    public function render()
    {
        $loadDistricts = Schema::hasTable('districts');

        $citiesQuery = City::query()
            ->with('capacity')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('province', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterProvinceId, function ($q) {
                $q->where('province_id', $this->filterProvinceId);
            })
            ->withCount('users')
            ->latest();

        if ($loadDistricts) {
            $citiesQuery->with(['districts' => function ($q) { $q->orderBy('name'); }]);
        }

        $cities = $citiesQuery->paginate($this->perPage);

        // Show all admins in dropdown (superadmin can reassign any admin)
        $admins = User::where('role', 'admin')->get();

        // Load provinces for sidebar/dropdowns (guard table existence)
        if (Schema::hasTable('provinces')) {
            $provinces = Province::orderBy('name')->get();
        } else {
            $provinces = collect();
        }

        $this->provinces = $provinces;

        return view('livewire.superadmin.cities.index', compact('cities', 'admins', 'provinces', 'loadDistricts'));
    }
}

