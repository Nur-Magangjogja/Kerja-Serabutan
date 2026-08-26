<?php

namespace App\Livewire\Admin\Partners;

use App\Models\City;
use App\Models\PartnerActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Activity extends Component
{
    use WithPagination;

    public $search = '';
    public $activityType = 'all';
    public $cityId = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'activityType' => ['except' => 'all'],
        'cityId' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActivityType()
    {
        $this->resetPage();
    }

    public function updatingCityId()
    {
        $this->resetPage();
    }

    public function resetSession($userId)
    {
        $user = User::findOrFail($userId);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        session()->flash('success', "Seluruh sesi aktif untuk mitra {$user->name} berhasil di-reset.");
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        $user->update([
            'password' => Hash::make('password123'),
        ]);
        session()->flash('success', "Password mitra {$user->name} telah di-reset ke default: 'password123'.");
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $query = PartnerActivity::with(['user.city'])->latest();

        if (! $isSuperAdmin) {
            $managedCityIds = $admin->managedCities()->pluck('cities.id')->toArray();
            if (!empty($managedCityIds)) {
                $query->whereHas('user', function ($q) use ($managedCityIds) {
                    $q->whereIn('city_id', $managedCityIds);
                });
            }
        }

        if ($this->activityType !== 'all') {
            $query->where('activity_type', $this->activityType);
        }

        if ($this->cityId !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('city_id', $this->cityId));
        }

        if (!empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('activity_type', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            });
        }

        $activities = $query->paginate(15);
        $cities = City::orderBy('name')->get();
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.activity', [
            'activities' => $activities,
            'cities' => $cities,
        ])->layout($layout);
    }
}
