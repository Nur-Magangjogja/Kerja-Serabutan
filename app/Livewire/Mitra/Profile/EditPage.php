<?php
namespace App\Livewire\Mitra\Profile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\City;

#[Layout('components.mitra-layout')]
class EditPage extends Component
{
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?int $city_id = null;
    public ?string $address = null;
    public ?string $bio = null;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:40',
        'city_id' => 'nullable|exists:cities,id',
        'address' => 'nullable|string|max:500',
        'bio' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->city_id = $user->city_id ?? null;
        $this->address = $user->address ?? '';
        $this->bio = $user->bio ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        // If email changed, ensure uniqueness
        if ($this->email !== $user->email && \App\Models\User::where('email', $this->email)->exists()) {
            $this->addError('email', 'Email sudah terdaftar.');
            return;
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city_id' => $this->city_id,
            'address' => $this->address,
            'bio' => $this->bio,
        ]);

        $this->emit('profile-updated');
        session()->flash('message', 'Profil berhasil diperbarui.');

        $this->redirectRoute('mitra.profile');
    }

    public function render()
    {
        $cities = City::orderBy('name')->get();
        return view('livewire.mitra.profile.edit-page', compact('cities'));
    }
}
