<?php

namespace App\Livewire\SuperAdmin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Help;

#[Layout('layouts.superadmin')]
class Approved extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
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
        app(\App\Services\HelpCancellationService::class)->cancelByPartnerUnilaterally($help, auth()->user(), 'Ditolak oleh SuperAdmin');
        session()->flash('message', 'Bantuan ditolak dan dana escrow dikembalikan 100% ke pemohon.');
    }

    public function render()
    {
        $approvedStatuses = array_merge(Help::activeStatuses(), [
            Help::STATUS_MENUNGGU_MITRA,
            Help::STATUS_WAITING_CONFIRMATION,
            Help::STATUS_SELESAI,
        ]);

        $query = Help::query()
            ->with(['customer', 'mitra', 'city', 'district'])
            ->whereIn('status', $approvedStatuses);

        $currentUser = auth()->user();
        $territory = $currentUser ? $currentUser->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
        if ($territory['type'] === 'district' && $territory['id']) {
            $dId = (int) $territory['id'];
            $query->where(function ($q) use ($dId) {
                $q->where('district_id', $dId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('district_id', $dId));
            });
        } elseif ($territory['type'] === 'city' && $territory['id']) {
            $cId = (int) $territory['id'];
            $districtIds = $currentUser ? $currentUser->getEffectiveSuperadminDistrictIds() : [];
            $query->where(function ($q) use ($cId, $districtIds) {
                $q->where('city_id', $cId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('city_id', $cId));
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $districtIds));
                }
            });
        }

        $helps = $query
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.superadmin.helps.approved', compact('helps'));
    }
}
