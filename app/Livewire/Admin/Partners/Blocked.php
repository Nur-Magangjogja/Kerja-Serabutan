<?php

namespace App\Livewire\Admin\Partners;

use App\Models\User;
use App\Models\PartnerActivity;
use Livewire\Component;
use Livewire\WithPagination;

class Blocked extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleBlock($userId)
    {
        $user = User::findOrFail($userId);
        $newStatus = ($user->status === 'blocked') ? 'active' : 'blocked';
        $user->update(['status' => $newStatus]);

        PartnerActivity::create([
            'user_id' => $user->id,
            'activity_type' => ($newStatus === 'blocked') ? 'partner_blocked' : 'partner_unblocked',
            'description' => ($newStatus === 'blocked')
                ? "Akun {$user->name} diblokir oleh Admin " . auth()->user()->name
                : "Blokir akun {$user->name} dibuka oleh Admin " . auth()->user()->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        session()->flash('success', ($newStatus === 'blocked')
            ? "Akun {$user->name} berhasil diblokir."
            : "Blokir pada akun {$user->name} berhasil dibuka."
        );
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $query = User::where('status', 'blocked')->with('city')->latest();

        if (! $isSuperAdmin) {
            $managedCityIds = $admin->managedCities()->pluck('cities.id')->toArray();
            if (!empty($managedCityIds)) {
                $query->whereIn('city_id', $managedCityIds);
            }
        }

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if (!empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $blockedUsers = $query->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.blocked', [
            'blockedUsers' => $blockedUsers,
        ])->layout($layout);
    }
}
