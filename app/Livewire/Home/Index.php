<?php

namespace App\Livewire\Home;

use App\Models\City;
use App\Models\Help;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $selectedCity = null;
    public $search = '';

    public function render()
    {
        $helps = Help::with(['user', 'city', 'mitra'])
            ->where('status', 'approved')
            ->whereNull('mitra_id')
            ->when($this->selectedCity, function ($query) {
                $query->where('city_id', $this->selectedCity);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $cities = City::where('is_active', true)->get();

        return view('livewire.home.index', [
            'helps' => $helps,
            'cities' => $cities,
        ]);
    }

    public function updatedSelectedCity()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function takeHelp($helpId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isMitra()) {
            session()->flash('error', 'Hanya mitra yang dapat mengambil bantuan.');
            return;
        }

        $help = Help::findOrFail($helpId);

        if ($help->mitra_id) {
            session()->flash('error', 'Bantuan ini sudah diambil oleh mitra lain.');
            return;
        }

        $help->update([
            'mitra_id' => auth()->id(),
            'status' => 'taken',
            'taken_at' => now(),
        ]);

        session()->flash('message', 'Bantuan berhasil diambil! Segera hubungi yang membutuhkan.');
        return redirect()->route('helps.index');
    }
}
