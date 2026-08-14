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

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:20'],
        'address' => ['nullable', 'string', 'max:500'],
        'city_id' => ['nullable', 'exists:cities,id'],
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->city_id = $user->city_id;
    }

    public function updateProfileInformation()
    {
        $this->validate();

        $user = Auth::user();

        // Check if email changed and already exists
        if (
            $this->email !== $user->email &&
            \App\Models\User::where('email', $this->email)->exists()
        ) {
            $this->addError('email', 'Email already taken.');
            return;
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city_id' => $this->city_id,
        ]);

        session()->flash('message', 'Profile updated successfully!');
    }

    public function render()
    {
        $cities = \App\Models\City::where('is_active', true)->orderBy('name')->get();
        return view('livewire.profile.update-profile-information-form', compact('cities'));
    }
}
