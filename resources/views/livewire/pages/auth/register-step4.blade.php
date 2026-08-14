<?php

use App\Models\Registration;
use App\Models\User;
use App\Models\City;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public $step1_data;
    public $step2_data;
    public $step3_data;
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $agree_terms = false;

    public function mount()
    {
        // Cek apakah registration UUID ada
        $uuid = Session::get('registration_uuid');
        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration || !$registration->ktp_photo_path || !$registration->selfie_photo_path) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Load step data from registration record
        $this->step1_data = $registration->only([
            'nik',
            'full_name',
            'place_of_birth',
            'date_of_birth',
            'gender',
            'address',
            'rt',
            'rw',
            'kelurahan',
            'kecamatan',
            'city',
            'province',
            'religion',
            'marital_status',
            'occupation'
        ]);

        $this->step2_data = ['ktp_photo_path' => $registration->ktp_photo_path];
        $this->step3_data = ['selfie_photo_path' => $registration->selfie_photo_path];
        // Prefill email if user previously entered it
        $this->email = $registration->email ?? $this->email;
    }



    public function complete(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree_terms' => ['accepted'],
        ]);

        // Ambil registration
        $uuid = Session::get('registration_uuid');
        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Gabungkan semua data
        $userData = [
            'name' => $registration->full_name,
            'email' => $validated['email'],
            'role' => $registration->role ?? 'customer',
            'password' => Hash::make($validated['password']),
            'nik' => $registration->nik,
            'place_of_birth' => $registration->place_of_birth,
            'date_of_birth' => $registration->date_of_birth,
            'gender' => $registration->gender,
            'address' => $registration->address,
            'rt' => $registration->rt,
            'rw' => $registration->rw,
            'kelurahan' => $registration->kelurahan,
            'kecamatan' => $registration->kecamatan,
            'city' => $registration->city,
            'province' => $registration->province,
            'religion' => $registration->religion,
            'marital_status' => $registration->marital_status,
            'occupation' => $registration->occupation,
            'ktp_photo' => $registration->ktp_photo_path,
            'selfie_photo' => $registration->selfie_photo_path,
        ];

        // Buat user baru tetapi jangan login — akun perlu verifikasi admin
        $userData['status'] = 'inactive';
        $userData['verified'] = false;

        $user = User::create($userData);

        // Jika nama kota pada registration sesuai dengan record di tabel cities,
        // set relasi city_id agar user otomatis terkait dengan admin kota tersebut.
        try {
            if (!empty($registration->city_id)) {
                $user->city_id = $registration->city_id;
                $user->save();
            } elseif (!empty($registration->city)) {
                $city = City::whereRaw('LOWER(name) = ?', [strtolower($registration->city)])->first();
                if ($city) {
                    $user->city_id = $city->id;
                    $user->save();
                }
            }
        } catch (\Exception $e) {
            // jika terjadi error mapping city, jangan ganggu proses pendaftaran
        }

        event(new Registered($user));

        // Tandai registration menunggu verifikasi admin
        $registration->update([
            'status' => 'pending_verification',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Hapus UUID session
        Session::forget('registration_uuid');

        // Clear client-side saved draft for step4 (email)
        $this->dispatch('clear-registration-step4');

        // Redirect to a success/awaiting-verification page
        $this->redirect(route('registration.success'), navigate: true);
    }

    public function previousStep(): void
    {
        $this->redirect(route('register.step3'), navigate: true);
    }

    public function editStep($step): void
    {
        $this->redirect(route("register.step{$step}"), navigate: true);
    }
}; ?>

            <div class="w-full">
                <div class="bg-white rounded-2xl p-6 w-full shadow-lg max-w-md mx-auto">
                    <div class="flex items-center justify-between mb-4">
                        <button wire:click="previousStep" class="text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-bold text-gray-900">Verifikasi Data</h1>
                        <div class="w-6"></div>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
                    </div>
                    <p class="text-sm text-gray-500 mb-4 text-center">Step 4 of 4</p>

                    <form wire:submit="complete" class="flex flex-col space-y-4">
                        <p class="text-sm text-gray-600 mb-2">Periksa kembali data Anda sebelum melanjutkan</p>

                        <!-- Data KTP Summary -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Data Pribadi
                                </h3>
                                <button type="button" wire:click="editStep(1)"
                                    class="text-primary-600 text-sm font-semibold">Edit</button>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">NIK:</span>
                                    <span class="font-semibold text-gray-900">{{ $step1_data['nik'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-semibold text-gray-900">{{ $step1_data['full_name'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">TTL:</span>
                                    <span class="font-semibold text-gray-900">{{ $step1_data['place_of_birth'] }},
                                        {{ date('d/m/Y', strtotime($step1_data['date_of_birth'])) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jenis Kelamin:</span>
                                    <span class="font-semibold text-gray-900">{{ $step1_data['gender'] }}</span>
                                </div>
                                <div class="pt-2 border-t">
                                    <span class="text-gray-600 block mb-1">Alamat:</span>
                                    <span class="font-semibold text-gray-900 text-xs">
                                        {{ $step1_data['address'] }}, RT {{ $step1_data['rt'] }}/RW {{ $step1_data['rw'] }},
                                        Kel. {{ $step1_data['kelurahan'] }}, Kec. {{ $step1_data['kecamatan'] }},
                                        {{ $step1_data['city'] }}, {{ $step1_data['province'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Foto KTP -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Foto KTP
                                </h3>
                                <button type="button" wire:click="editStep(2)"
                                    class="text-primary-600 text-sm font-semibold">Edit</button>
                            </div>
                            <img src="{{ Storage::url($step2_data['ktp_photo_path']) }}" alt="KTP" class="w-full rounded-lg">
                        </div>

                        <!-- Foto Selfie -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Foto Selfie dengan KTP
                                </h3>
                                <button type="button" wire:click="editStep(3)"
                                    class="text-primary-600 text-sm font-semibold">Edit</button>
                            </div>
                            <img src="{{ Storage::url($step3_data['selfie_photo_path']) }}" alt="Selfie" class="w-full rounded-lg">
                        </div>

                        <!-- Email & Password -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                                Akun Login
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-2">Email *</label>
                                    <input wire:model="email" id="email" type="email" placeholder="example@email.com"
                                        class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-2">Password *</label>
                                    <input wire:model="password" id="password" type="password" placeholder="Min. 8 karakter"
                                        class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition">
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-xs font-semibold text-gray-700 mb-2">Konfirmasi Password *</label>
                                    <input wire:model="password_confirmation" id="password_confirmation" type="password"
                                        placeholder="Ketik ulang password"
                                        class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input wire:model="agree_terms" type="checkbox" class="w-5 h-5 text-primary-500 rounded mt-0.5">
                                <span class="text-sm text-gray-700 flex-1">
                                    Saya menyetujui <a href="#" class="text-primary-600 font-semibold">Syarat & Ketentuan</a>
                                    serta <a href="#" class="text-primary-600 font-semibold">Kebijakan Privasi</a> yang berlaku
                                </span>
                            </label>
                            <x-input-error :messages="$errors->get('agree_terms')" class="mt-2" />
                        </div>

                        <!-- Complete Button -->
                        <div class="pt-2 pb-2">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-50">
                                <span wire:loading.remove>Selesai & Daftar</span>
                                <span wire:loading>Mendaftarkan akun...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                (function () {
                    const key = 'registration_step4_email';
                    const el = document.getElementById('email');
                    // Load saved email (if any) into input
                    window.addEventListener('DOMContentLoaded', () => {
                        try {
                            const val = localStorage.getItem(key);
                            if (val !== null && el) {
                                el.value = val;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        } catch (e) { }
                    });

                    // Save on input
                    if (el) {
                        el.addEventListener('input', (ev) => {
                            try { localStorage.setItem(key, ev.target.value); } catch (e) { }
                        });
                    }

                    // Clear saved draft when Livewire triggers event
                    document.addEventListener('livewire:load', function () {
                        if (window.Livewire) {
                            window.Livewire.on('clear-registration-step4', () => {
                                try { localStorage.removeItem(key); } catch (e) { }
                            });
                        }
                    });
                })();
            </script>
        </div>
    </div>
</div>