<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UpdateProfileInformationForm extends Component
{
    public $name;
    public $email;
    public $phone;
    public $address;
    public $city_id;
    public string $cityQuery = '';
    public array $searchResults = [];

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\s\-]+$/'],
        'city_id' => ['required', 'exists:cities,id'],
        'address' => ['required', 'string', 'max:500'],
    ];

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
        'phone.min' => 'Nomor HP minimal 9 digit.',
        'phone.max' => 'Nomor HP maksimal 20 digit.',
        'phone.regex' => 'Format nomor HP tidak valid.',
        'city_id.required' => 'Kota/Kabupaten wajib dipilih dari hasil pencarian.',
        'city_id.exists' => 'Kota/Kabupaten yang dipilih tidak valid.',
        'address.required' => 'Alamat lengkap wajib diisi.',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->city_id = $user->city_id;

        if ($this->city_id) {
            $cityRec = \App\Models\City::find($this->city_id);
            if ($cityRec) {
                $this->cityQuery = $cityRec->name . ($cityRec->province ? " — {$cityRec->province}" : '');
            }
        } elseif (!empty($user->city)) {
            $this->cityQuery = $user->city;
        }
    }

    public function updatedCityQuery($value): void
    {
        $q = trim((string) $value);
        if (empty($q) || strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = \App\Models\City::where(function($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('province', 'like', "%{$q}%");
            })
            ->where('is_active', true)
            ->select('id', 'name', 'province')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function setCityId($id): void
    {
        $this->city_id = $id;
        $city = \App\Models\City::find($id);
        if ($city) {
            $this->cityQuery = $city->name . ($city->province ? " — {$city->province}" : '');
        }
        $this->searchResults = [];
        $this->resetErrorBag('city_id');
    }

    public function clearCity(): void
    {
        $this->city_id = null;
        $this->cityQuery = '';
        $this->searchResults = [];
    }

    public function updateProfileInformation()
    {
        $this->validate();

        $user = Auth::user();

        // Check if email changed and already exists
        if (
            $this->email !== $user->email &&
            \App\Models\User::where('email', $this->email)->where('id', '!=', $user->id)->exists()
        ) {
            $this->addError('email', 'Email sudah digunakan oleh akun lain.');
            return;
        }

        $cityName = null;
        if ($this->city_id) {
            $cityRec = \App\Models\City::find($this->city_id);
            $cityName = $cityRec?->name;
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city_id' => $this->city_id,
            'city' => $cityName ?? $user->city,
        ]);

        session()->flash('message', 'Profil Anda berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.profile.update-profile-information-form');
    }
}
