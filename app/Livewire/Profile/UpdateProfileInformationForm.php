<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UpdateProfileInformationForm extends Component
{
    public $name;
    public $email;
    public $phone;
    public $city_id;
    public $city;
    public $province;
    public string $cityQuery = '';
    public array $searchResults = [];

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\s\-]+$/'],
        'city_id' => ['nullable', 'exists:cities,id'],
        'city' => ['required', 'string', 'max:100'],
        'province' => ['required', 'string', 'max:100'],
    ];

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
        'phone.min' => 'Nomor HP minimal 9 digit.',
        'phone.max' => 'Nomor HP maksimal 20 digit.',
        'phone.regex' => 'Format nomor HP tidak valid.',
        'city.required' => 'Kota / Kabupaten wajib diisi.',
        'province.required' => 'Provinsi wajib diisi.',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->city_id = $user->city_id;
        $this->city = $user->city;
        $this->province = $user->province;

        if ($this->city_id) {
            $cityRec = \App\Models\City::find($this->city_id);
            if ($cityRec) {
                $this->cityQuery = $cityRec->name . ($cityRec->province ? " — {$cityRec->province}" : '');
                $this->city = $cityRec->name;
                $this->province = $cityRec->province ?? $this->province;
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
            $this->city = $q;
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
            $this->city = $city->name;
            $this->province = $city->province;
        }
        $this->searchResults = [];
        $this->resetErrorBag('city');
        $this->resetErrorBag('province');
        $this->resetErrorBag('city_id');
    }

    public function clearCity(): void
    {
        $this->city_id = null;
        $this->cityQuery = '';
        $this->city = '';
        $this->searchResults = [];
    }

    public function updateProfileInformation()
    {
        // If user manually typed city query without selecting from dropdown
        if (empty($this->city) && !empty($this->cityQuery)) {
            $this->city = $this->cityQuery;
        }

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

        $cityName = $this->city;
        $provinceName = $this->province;
        if ($this->city_id) {
            $cityRec = \App\Models\City::find($this->city_id);
            if ($cityRec) {
                $cityName = $cityRec->name;
                $provinceName = $cityRec->province ?: $provinceName;
            }
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city_id' => $this->city_id,
            'city' => $cityName,
            'province' => $provinceName,
        ]);

        session()->flash('message', 'Profil Anda berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.profile.update-profile-information-form');
    }
}

