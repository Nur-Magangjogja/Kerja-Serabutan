<?php
namespace App\Livewire\Mitra\Profile;

use Livewire\Component;
use App\Models\City;
use Livewire\Attributes\On;

class Edit extends Component
{
    public bool $showModal = false;
    public ?string $name = null;
    public ?string $phone = null;
    public ?int $city_id = null;
    public ?string $bio = null;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:40',
        'city_id' => 'nullable|exists:cities,id',
        'bio' => 'nullable|string|max:1000',
    ];

    #[On('openEditProfile')]
    public function openModal(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
        $this->city_id = $user->city_id ?? null;
        $this->bio = $user->bio ?? '';
        $this->showModal = true;
    }

    #[On('closeEditProfile')]
    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $user->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'city_id' => $this->city_id,
            'bio' => $this->bio,
        ]);

        $this->showModal = false;

        $this->emit('profile-updated');
        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        $cities = City::orderBy('name')->get();

        return view('livewire.mitra.profile.edit', compact('cities'));
    }
}
