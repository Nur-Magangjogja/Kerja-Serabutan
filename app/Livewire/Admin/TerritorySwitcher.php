<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class TerritorySwitcher extends Component
{
    protected $listeners = [
        'admin-district-changed' => '$refresh',
        'admin-city-changed'     => '$refresh',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && $user->role === 'admin') {
            $this->validateSessionFilter($user);
        }
    }

    public function selectDistrict($districtId): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return;
        }

        $allowedDistrictIds = $user->getAdminDistrictIds();
        if ($districtId !== 'all' && !in_array((int) $districtId, $allowedDistrictIds, true)) {
            $districtId = 'all';
        }

        $user->setActiveAdminDistrictFilter((string) $districtId);
        $this->dispatch('admin-district-changed', districtId: $districtId);
        $this->dispatch('admin-city-changed', cityId: $districtId);
        $this->dispatch('chart-refresh');
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $this->validateSessionFilter($user);

        $managedDistricts = $user->getAdminDistricts();

        if ($managedDistricts->count() <= 1) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $activeDistrictFilter = $user->getActiveAdminDistrictFilter();
        $activeLabel          = $user->active_admin_district_label;

        // Group explicitly assigned districts by parent City name for visual grouping
        $groupedDistricts = $managedDistricts->groupBy(function ($d) {
            return $d->city ? $d->city->name : 'Wilayah Lain';
        });

        return view('livewire.admin.territory-switcher', [
            'managedDistricts'     => $managedDistricts,
            'groupedDistricts'     => $groupedDistricts,
            'activeDistrictFilter' => $activeDistrictFilter,
            'activeLabel'          => $activeLabel,
        ]);
    }

    protected function validateSessionFilter($user): void
    {
        $allowedIds = $user->getAdminDistrictIds();
        $sessionFilter = session('admin_active_district_filter');

        if ($sessionFilter !== null && $sessionFilter !== 'all' && !in_array((int) $sessionFilter, $allowedIds, true)) {
            $user->setActiveAdminDistrictFilter('all');
        }
    }
}
