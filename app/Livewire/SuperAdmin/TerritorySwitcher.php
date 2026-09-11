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
        $this->expandedCityIds = [];
    }

    public function updatedSearch()
    {
        $this->expandedCityIds = [];
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
        $this->expandedCityIds = [];
        $this->search = '';

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
        $rawSearch = trim($this->search);
        $cleanSearch = trim(preg_replace('/^(kecamatan|kec\.|kec|kabupaten|kab\.|kab|kota)\s+/i', '', $rawSearch));

        $activeDistrictId = null;
        $activeCityId = null;

        if ($territory['type'] === 'district' && !empty($territory['id'])) {
            $activeDistrictId = (int) $territory['id'];
            $activeCityId = (int) District::where('id', $activeDistrictId)->value('city_id');
        } elseif ($territory['type'] === 'city' && !empty($territory['id'])) {
            $activeCityId = (int) $territory['id'];
        }

        $expandedIds = $this->expandedCityIds;
        $query = City::query()
            ->withCount('districts');

        if ($activeCityId) {
            $query->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$activeCityId])
                  ->orderBy('name');
        } else {
            $query->orderBy('name');
        }

        if (!empty($expandedIds)) {
            $query->with(['districts' => function ($q) use ($expandedIds, $activeDistrictId) {
                $q->whereIn('city_id', $expandedIds);
                if ($activeDistrictId) {
                    $q->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$activeDistrictId])
                      ->orderBy('name');
                } else {
                    $q->orderBy('name');
                }
            }]);
        }

        if ($rawSearch !== '') {
            $query->where(function ($q) use ($rawSearch, $cleanSearch) {
                $q->where('name', 'like', "%{$rawSearch}%")
                  ->orWhere('province', 'like', "%{$rawSearch}%");
                
                if ($cleanSearch !== '' && $cleanSearch !== $rawSearch) {
                    $q->orWhere('name', 'like', "%{$cleanSearch}%")
                      ->orWhere('province', 'like', "%{$cleanSearch}%");
                }

                $q->orWhereHas('districts', function ($dq) use ($rawSearch, $cleanSearch) {
                    $dq->where('name', 'like', "%{$rawSearch}%");
                    if ($cleanSearch !== '' && $cleanSearch !== $rawSearch) {
                        $dq->orWhere('name', 'like', "%{$cleanSearch}%");
                    }
                });
            });
        }

        $cities = $query->get();

        return view('livewire.superadmin.territory-switcher', [
            'territory'         => $territory,
            'cities'            => $cities,
            'expandedCityIds'   => $this->expandedCityIds,
            'activeCityId'      => $activeCityId,
            'activeDistrictId'  => $activeDistrictId,
            'searchTerm'        => $rawSearch,
            'cleanSearch'       => $cleanSearch,
            'totalCities'       => City::count(),
            'totalDistricts'    => District::count(),
        ]);
    }
}
