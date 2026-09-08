<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class TerritorySwitcher extends Component
{
    protected $listeners = [
        'admin-district-changed' => '$refresh',
        'admin-city-changed'     => '$refresh',
    ];

    public function selectDistrict($districtId)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return;
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

        $managedDistricts = $user->getAdminDistricts();

        if ($managedDistricts->count() <= 1) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $activeDistrictFilter = $user->getActiveAdminDistrictFilter();
        $activeLabel          = $user->active_admin_district_label;

        return view('livewire.admin.territory-switcher', [
            'managedDistricts'     => $managedDistricts,
            'activeDistrictFilter' => $activeDistrictFilter,
            'activeLabel'          => $activeLabel,
        ]);
    }
}
