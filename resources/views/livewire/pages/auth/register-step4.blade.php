<?php

use App\Models\Registration;
use App\Models\User;
use App\Models\City;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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
        // Cek apakah registration UUID ada (session atau cookie)
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }
        Session::put('registration_uuid', $uuid);

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration || !$registration->ktp_photo_path || !$registration->selfie_photo_path) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Load step data from registration record
        $this->step1_data = $registration->only([
            'nik',
            'full_name',
            'phone',
            'place_of_birth',
            'date_of_birth',
            'gender',
            'address',
            'rt',
            'rw',
            'kelurahan',
            'kecamatan',
            'city',
            'province'
        ]);

        $this->step2_data = ['ktp_photo_path' => $registration->ktp_photo_path];
        $this->step3_data = ['selfie_photo_path' => $registration->selfie_photo_path];
        // Prefill email if user previously entered it
        $this->email = $registration->email ?? (request()->cookie('registration_step4_email') ?? $this->email);
    }

    public function updatedEmail($value): void
    {
        if (!empty($value)) {
            Cookie::queue('registration_step4_email', $value, 60 * 24 * 7);
        }
    }

    public function complete(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree_terms' => ['accepted'],
        ]);

        // Ambil registration
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        $registration = $uuid ? Registration::where('uuid', $uuid)->first() : null;
        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Gabungkan semua data
        $userData = [
            'name' => $registration->full_name,
            'email' => $validated['email'],
            'phone' => $registration->phone,
            'role' => $registration->role ?? (request()->cookie('registration_role') ?? 'customer'),
            'password' => Hash::make($validated['password']),
            'nik' => $registration->nik,
            'place_of_birth' => $registration->place_of_birth,
            'date_of_birth' => $registration->date_of_birth,
            'gender' => $registration->gender,
            'address' => $registration->address,
            'rt' => $registration->rt !== null ? (int) $registration->rt : null,
            'rw' => $registration->rw !== null ? (int) $registration->rw : null,
            'kelurahan' => $registration->kelurahan,
            'kecamatan' => $registration->kecamatan,
            'city' => $registration->city,
            'province' => $registration->province,
            'ktp_photo' => $registration->ktp_photo_path,
            'selfie_photo' => $registration->selfie_photo_path,
        ];

        // Buat user baru tetapi jangan login — akun perlu verifikasi admin
        $userData['status'] = 'inactive';
        $userData['verified'] = false;

        $user = User::create($userData);

        // Inisialisasi saldo user baru
        try {
            \App\Models\UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00]);
        } catch (\Throwable $e) {
            // ignore balance init error
        }

        $isWaitingList = false;

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

            // Tracking waiting list jika pendaftar adalah mitra di kota berstatus closed
            if ($user->role === 'mitra' && $user->city_id) {
                $cityCap = \App\Models\CityCapacity::where('city_id', $user->city_id)->first();
                if ($cityCap && $cityCap->isClosed()) {
                    $cityCap->increment('waiting_list_count');
                    $isWaitingList = true;
                }
            }
        } catch (\Exception $e) {
            // jika terjadi error mapping city, jangan ganggu proses pendaftaran
        }

        event(new Registered($user));

        // Kirim notifikasi ke Admin regional terkait untuk Verifikasi KTP
        try {
            $cityId = $user->city_id;
            $admins = User::where('role', 'admin')
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->where('status', 'active')
                ->get();
            if ($admins->isEmpty()) {
                $admins = User::where('role', 'admin')->where('status', 'active')->get();
            }
            foreach ($admins as $adm) {
                $adm->notify(new \App\Notifications\NewKtpVerificationNotification($user));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Registration] Gagal kirim notifikasi verifikasi KTP ke admin: ' . $e->getMessage());
        }

        // Tandai registration menunggu verifikasi admin atau antrean waiting list
        $regStatus = $isWaitingList ? 'waiting_list' : 'pending_verification';
        $registration->update([
            'status'   => $regStatus,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Hapus UUID session & cookies
        Session::forget('registration_uuid');
        Session::forget('registration_role');
        Cookie::queue(Cookie::forget('registration_uuid'));
        Cookie::queue(Cookie::forget('registration_role'));
        Cookie::queue(Cookie::forget('registration_step1_draft'));
        Cookie::queue(Cookie::forget('registration_step4_email'));

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

<div class="space-y-5">
    <!-- Step Header -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
        <div>
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Langkah Terakhir</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Verifikasi & Buat Akun</h2>
        </div>
        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center border border-emerald-200 dark:border-emerald-800">
            4/4
        </div>
    </div>

    <!-- Progress Indicator Pills -->
    <div class="grid grid-cols-4 gap-1.5 mb-2">
        <div class="h-1.5 rounded-full bg-emerald-600"></div>
        <div class="h-1.5 rounded-full bg-emerald-600"></div>
        <div class="h-1.5 rounded-full bg-emerald-600"></div>
        <div class="h-1.5 rounded-full bg-emerald-600"></div>
    </div>

    <form wire:submit="complete" class="flex flex-col space-y-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Periksa kembali ringkasan data Anda dan tentukan email serta kata sandi akun.</p>

        <!-- Data KTP Summary -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Data Pribadi
                </h3>
                <button type="button" wire:click="editStep(1)" class="text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                    Edit
                </button>
            </div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">NIK:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['nik'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nama Lengkap:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['full_name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">No. HP / WhatsApp:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['phone'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['place_of_birth'] }}, {{ date('d/m/Y', strtotime($step1_data['date_of_birth'])) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Jenis Kelamin:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['gender'] }}</span>
                </div>
                <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400 block mb-0.5">Alamat:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200 text-xs leading-relaxed">
                        {{ $step1_data['address'] }}, RT {{ $step1_data['rt'] }}/RW {{ $step1_data['rw'] }}, Kel. {{ $step1_data['kelurahan'] }}, Kec. {{ $step1_data['kecamatan'] }}, {{ $step1_data['city'] }}, {{ $step1_data['province'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Foto KTP Summary -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" clip-rule="evenodd" />
                    </svg>
                    Foto e-KTP
                </h3>
                <button type="button" wire:click="editStep(2)" class="text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                    Edit
                </button>
            </div>
            <img src="{{ Storage::url($step2_data['ktp_photo_path']) }}" alt="KTP" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 max-h-48 object-cover">
        </div>

        <!-- Foto Selfie Summary -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" clip-rule="evenodd" />
                    </svg>
                    Foto Selfie + KTP
                </h3>
                <button type="button" wire:click="editStep(3)" class="text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                    Edit
                </button>
            </div>
            <img src="{{ Storage::url($step3_data['selfie_photo_path']) }}" alt="Selfie" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 max-h-48 object-cover">
        </div>

        <!-- Email & Password Form -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white mb-3 flex items-center gap-2 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                Informasi Akun Masuk
            </h3>
            <div class="space-y-3">
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                    <input wire:model="email" id="email" type="email" placeholder="nama@email.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kata Sandi <span class="text-red-500">*</span></label>
                    <input wire:model="password" id="password" type="password" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password" placeholder="Ketik ulang kata sandi"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="agree_terms" type="checkbox" class="w-4 h-4 text-primary-500 rounded border-gray-300 dark:border-gray-600 mt-0.5 focus:ring-primary-500">
                <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed flex-1">
                    Saya menyetujui <a href="#" class="text-primary-600 dark:text-sky-400 font-semibold hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-primary-600 dark:text-sky-400 font-semibold hover:underline">Kebijakan Privasi</a> platform.
                </span>
            </label>
            <x-input-error :messages="$errors->get('agree_terms')" />
        </div>

                        <!-- Actions -->
                        <!-- Actions -->
                        <div class="pt-4 pb-2 flex items-center gap-3">
                            <button type="button" wire:click="previousStep"
                                class="px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Kembali</span>
                            </button>

                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                                <span wire:loading.remove>Selesai & Buat Akun</span>
                                <span wire:loading class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Mendaftarkan...
                                </span>
                            </button>
                        </div>
                    </form>

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