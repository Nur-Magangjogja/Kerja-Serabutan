<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class TerritorySwitcher extends Component
{
    protected $listeners = ['admin-city-changed' => '$refresh'];

    public function selectCity($cityId)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return;
        }

        $user->setActiveAdminCityFilter((string) $cityId);
        $this->dispatch('admin-city-changed', cityId: $cityId);
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

        $managedCities = $user->getAdminCities();
        if ($managedCities->count() <= 1) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $activeFilter = $user->getActiveAdminCityFilter();
        $activeLabel  = $user->active_admin_city_label;

        return view('livewire.admin.territory-switcher', [
            'managedCities' => $managedCities,
            'activeFilter'  => $activeFilter,
            'activeLabel'   => $activeLabel,
        ]);
    }
}
