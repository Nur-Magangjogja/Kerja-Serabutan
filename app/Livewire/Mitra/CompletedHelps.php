<?php

namespace App\Livewire\Mitra;

use App\Models\Help;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class CompletedHelps extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'latest';
    public $openRatingHelpId = null;
    // Modal / detail state
    public $showDetailModal = false;
    public $selectedHelpId = null;
    public $selectedHelp = null;
    protected $listeners = [
        'ratingSubmitted' => 'onRatingSubmitted',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        // Show only helps assigned to this mitra that are completed
        $query = Help::query()->where('mitra_id', $user->id)->where('status', 'selesai');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($u) {
                        $u->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->sortBy === 'latest') {
            $query->latest();
        } elseif ($this->sortBy === 'oldest') {
            $query->oldest();
        }

        $helps = $query->with(['user', 'city'])->paginate(15);

        return view('livewire.mitra.helps.completed-helps', [
            'helps' => $helps,
        ]);
    }

    public function toggleRating($helpId)
    {
        // normalize to int to avoid string/int mismatch
        $helpId = (int) $helpId;

        if ($this->openRatingHelpId === $helpId) {
            $this->openRatingHelpId = null;
        } else {
            $this->openRatingHelpId = $helpId;
        }

        // log and dispatch a browser event to help debug client-side
        \Log::info('ToggleRating called', ['helpId' => $helpId, 'open' => $this->openRatingHelpId]);
        $this->dispatch('toggle-rating', helpId: $helpId, open: $this->openRatingHelpId);
    }

    public function showDetail($helpId)
    {
        $helpId = (int) $helpId;
        $this->selectedHelp = Help::with(['user', 'city', 'rating'])->find($helpId);
        if (! $this->selectedHelp) {
            $this->dispatchBrowserEvent('notification', ['type' => 'error', 'message' => 'Data bantuan tidak ditemukan.']);
            return;
        }
        $this->selectedHelpId = $helpId;
        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedHelp = null;
        $this->selectedHelpId = null;
    }

    public function onRatingSubmitted($helpId = null)
    {
        // Close the rating form for the help that was just rated
        if ($helpId && $this->openRatingHelpId === $helpId) {
            $this->openRatingHelpId = null;
        }
        // If the rating was submitted from the detail modal, close it as well
        if ($helpId && $this->selectedHelpId === (int) $helpId) {
            $this->closeDetail();
        }
        // re-render to reflect updated ratings if needed
        $this->resetPage();
    }
}
