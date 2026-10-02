<?php
namespace App\Livewire\Mitra\Profile;

use Livewire\Component;
use App\Models\City;
use App\Models\District;
use Livewire\Attributes\On;

class Edit extends Component
{
    public bool $showModal = false;
    public ?string $name = null;
    public ?string $phone = null;
    public ?int $city_id = null;
    public ?int $district_id = null;
    public ?string $kecamatan = null;
    public array $districtsList = [];
    public ?string $bio = null;

    protected array $rules = [
        'name' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s\.\'\-]+$/u'],
        'phone' => 'nullable|string|max:40',
        'bio' => 'nullable|string|max:1000',
    ];

    protected array $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.min' => 'Nama lengkap minimal 3 karakter.',
        'name.max' => 'Nama lengkap maksimal 255 karakter.',
        'name.regex' => 'Nama lengkap hanya boleh berisi huruf alfabet, spasi, tanda hubung (-), titik (.), dan tanda petik (\'). Angka dan simbol khusus tidak diperbolehkan.',
    ];

    #[On('openEditProfile')]
    public function openModal(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
        $this->city_id = $user->city_id ?? null;
        $this->district_id = $user->district_id ?? null;
        $this->kecamatan = $user->district?->name ?? $user->kecamatan;
        $this->bio = $user->bio ?? '';

        if ($this->city_id) {
            $this->districtsList = app(\App\Services\CitySearchService::class)->getDistrictsByCity((int) $this->city_id);
        } else {
            $this->districtsList = [];
        }

        $this->showModal = true;
    }

    public function updatedCityId($value): void
    {
        // Locked identity territory: ignore self-service modifications
    }

    public function updatedDistrictId($value): void
    {
        // Locked identity territory: ignore self-service modifications
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

        // STRICT SECURITY: Territory fields are locked identity and cannot be edited by self-service
        $user->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'bio' => $this->bio,
        ]);

        // Keep local state in sync with immutable user identity
        $this->city_id = $user->city_id;
        $this->district_id = $user->district_id;
        $this->kecamatan = $user->district?->name ?? $user->kecamatan;

        $this->showModal = false;

        $this->dispatch('profile-updated');
        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        $cities = City::orderBy('name')->get();

        return view('livewire.mitra.profile.edit', compact('cities'));
    }
}

