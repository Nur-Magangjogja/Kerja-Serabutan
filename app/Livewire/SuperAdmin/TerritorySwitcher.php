<?php

namespace App\Livewire\SuperAdmin;

use App\Models\City;
use App\Models\District;
use Livewire\Component;

class TerritorySwitcher extends Component
{
    public $search = '';
    public $expandedCityIds = [];

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function mount()
    {
        $user = auth()->user();
        if ($user) {
            $territory = $user->getActiveSuperadminTerritory();
            if ($territory['type'] === 'district' && $territory['id']) {
                $cityId = District::where('id', $territory['id'])->value('city_id');
                if ($cityId) {
                    $this->expandedCityIds = [(int) $cityId];
                }
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $this->expandedCityIds = [(int) $territory['id']];
            }
        }
    }

    public function toggleExpandCity($cityId)
    {
        $cityId = (int) $cityId;
        if (in_array($cityId, $this->expandedCityIds, true)) {
            $this->expandedCityIds = array_values(array_filter($this->expandedCityIds, fn($id) => $id !== $cityId));
        } else {
            $this->expandedCityIds[] = $cityId;
        }
    }

    public function selectTerritory(string $type, $id = null)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role ?? '', ['super_admin', 'superadmin'])) {
            return;
        }

        $user->setActiveSuperadminTerritory($type, $id);

        $districtId = null;
        $cityId = null;

        if ($type === 'district' && $id) {
            $districtId = (string) (int) $id;
            $cityId = (string) District::where('id', $id)->value('city_id');
        } elseif ($type === 'city' && $id) {
            $cityId = (string) (int) $id;
            $districtId = 'all';
        } else {
            $districtId = 'all';
            $cityId = 'all';
        }

        $this->dispatch('superadmin-territory-changed', [
            'type'       => $type,
            'id'         => $id,
            'districtId' => $districtId,
            'cityId'     => $cityId,
        ]);
        $this->dispatch('admin-district-changed', districtId: $districtId);
        $this->dispatch('admin-city-changed', cityId: $cityId);
        $this->dispatch('chart-refresh');
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role ?? '', ['super_admin', 'superadmin'])) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $territory = $user->getActiveSuperadminTerritory();
        $searchTerm = trim($this->search);

        $query = City::query()
            ->with(['districts' => function ($q) use ($searchTerm) {
                if ($searchTerm !== '') {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('code', 'like', "%{$searchTerm}%");
                }
                $q->orderBy('name');
            }])
            ->withCount('districts')
            ->orderBy('name');

        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('province', 'like', "%{$searchTerm}%")
                  ->orWhereHas('districts', function ($dq) use ($searchTerm) {
                      $dq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('code', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $cities = $query->get();

        // If searching, auto-expand all cities that match
        $effectiveExpanded = $this->expandedCityIds;
        if ($searchTerm !== '') {
            $effectiveExpanded = $cities->pluck('id')->map(fn($id) => (int)$id)->all();
        }

        return view('livewire.superadmin.territory-switcher', [
            'territory'         => $territory,
            'cities'            => $cities,
            'effectiveExpanded' => $effectiveExpanded,
            'totalCities'       => City::count(),
            'totalDistricts'    => District::count(),
        ]);
    }
}
