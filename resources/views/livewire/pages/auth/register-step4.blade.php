<?php

use App\Models\City;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public $step1_data = [];
    public $step2_data = [];
    public $step3_data = [];
    public bool $agree_declaration = false;

    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && !$user->hasVerifiedEmail()) {
                $this->redirect(route('verification.notice'), navigate: true);
                return;
            }
        }

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$uuid && Auth::check()) {
            $reg = Registration::where('email', Auth::user()->email)->latest()->first();
            if ($reg) {
                $uuid = $reg->uuid;
                Session::put('registration_uuid', $uuid);
            }
        }

        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }
        Session::put('registration_uuid', $uuid);

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration || empty($registration->ktp_photo_path) || empty($registration->selfie_photo_path)) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        $this->step1_data = $registration->only([
            'nik',
            'full_name',
            'phone',
            'gender',
            'city',
            'province'
        ]);

        $this->step2_data = ['ktp_photo_path' => $registration->ktp_photo_path];
        $this->step3_data = ['selfie_photo_path' => $registration->selfie_photo_path];
    }

    public function complete(): void
    {
        $this->validate([
            'agree_declaration' => ['accepted'],
        ], [
            'agree_declaration.accepted' => 'Anda harus menyatakan bahwa seluruh data identitas dan dokumen yang diunggah adalah benar.',
        ]);

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        $registration = $uuid ? Registration::where('uuid', $uuid)->first() : null;
        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        $user = Auth::user();

        // Jika belum ada user aktif di sesi, cari berdasarkan email pada registration
        if (!$user && !empty($registration->email)) {
            $user = User::where('email', $registration->email)->first();
        }

        if ($user) {
            // Cek kembali keunikan NIK sebelum menyimpan ke user
            $duplicateUser = User::where('nik', $registration->nik)->where('id', '!=', $user->id)->exists();
            if ($duplicateUser) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'agree_declaration' => 'Nomor NIK ' . $registration->nik . ' sudah terdaftar pada akun lain. Setiap pengguna hanya dapat memiliki 1 akun.',
                ]);
            }

            $cityId = $registration->city_id;
            if (!$cityId && !empty($registration->city)) {
                $c = City::whereRaw('LOWER(name) = ?', [strtolower($registration->city)])->first();
                $cityId = $c?->id;
            }

            $user->update([
                'nik'            => $registration->nik,
                'name'           => $registration->full_name ?: $user->name,
                'phone'          => $registration->phone ?: $user->phone,
                'gender'         => $registration->gender,
                'city'           => $registration->city,
                'city_id'        => $cityId,
                'province'       => $registration->province,
                'ktp_photo'      => $registration->ktp_photo_path,
                'ktp_path'       => $registration->ktp_photo_path,
                'selfie_photo'   => $registration->selfie_photo_path,
                'status'         => 'inactive',
                'verified'       => false,
            ]);

            // Inisialisasi saldo jika belum ada
            try {
                \App\Models\UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00]);
            } catch (\Throwable $e) {
                // ignore
            }

            // Kirim notifikasi ke admin regional terkait pengajuan KTP baru
            try {
                $admins = User::where('role', 'admin')
                    ->when($user->city_id, fn($q) => $q->where('city_id', $user->city_id))
                    ->where('status', 'active')
                    ->get();
                if ($admins->isEmpty()) {
                    $admins = User::where('role', 'admin')->where('status', 'active')->get();
                }
                foreach ($admins as $adm) {
                    $adm->notify(new \App\Notifications\NewKtpVerificationNotification($user));
                }
            } catch (\Throwable $e) {
                Log::warning('[Registration] Gagal kirim notifikasi verifikasi KTP ke admin: ' . $e->getMessage());
            }
        }

        // Tandai status registrasi menjadi pending verifikasi admin
        $registration->update([
            'status' => 'pending_verification',
            'email'  => $user?->email ?? $registration->email,
        ]);

        // Bersihkan cookies & session sementara
        Session::forget('registration_uuid');
        Session::forget('registration_role');
        Cookie::queue(Cookie::forget('registration_uuid'));
        Cookie::queue(Cookie::forget('registration_role'));
        Cookie::queue(Cookie::forget('registration_step1_draft'));

        // Logout pengguna agar tidak langsung masuk ke dashboard dan harus login manual setelah diverifikasi
        Auth::logout();
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

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
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Langkah 4 dari 4</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Konfirmasi & Kirim Verifikasi</h2>
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
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
            Periksa kembali kelengkapan data diri dan dokumen identitas Anda sebelum mengajukan verifikasi ke tim admin.
        </p>

        <!-- 1. Data Akun Terdaftar -->
        @if(auth()->user())
        <div class="bg-primary-50/60 dark:bg-primary-950/40 border border-primary-100 dark:border-primary-800/80 rounded-2xl p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-600 text-white font-bold flex items-center justify-center text-sm shrink-0 shadow-xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-[10px] font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wider block">Akun Terdaftar:</span>
                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">{{ auth()->user()->email }}</h4>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-700 shadow-2xs">
                {{ ucfirst(auth()->user()->role) }}
            </span>
        </div>
        @endif

        <!-- 2. Data KTP Summary -->
        <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Data Pribadi Sesuai KTP
                </h3>
                <button type="button" wire:click="editStep(1)" class="text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                    Edit
                </button>
            </div>
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">NIK (16 Digit):</span>
                    <span class="font-semibold text-gray-900 dark:text-white font-mono">{{ $step1_data['nik'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nama Lengkap:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['full_name'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">No. HP / WhatsApp:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['phone'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Jenis Kelamin:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $step1_data['gender'] ?? '-' }}</span>
                </div>
                <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400 block mb-0.5">Alamat KTP:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200 text-xs leading-relaxed">
                        {{ $step1_data['city'] ?? '-' }}, {{ $step1_data['province'] ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Foto Dokumen KTP & Selfie -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Foto e-KTP -->
            <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-gray-200/60 dark:border-gray-700/60">
                    <h3 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span>📷</span> Foto e-KTP
                    </h3>
                    <button type="button" wire:click="editStep(2)" class="text-primary-600 dark:text-sky-400 text-[11px] font-bold hover:underline cursor-pointer">
                        Ubah
                    </button>
                </div>
                @if(!empty($step2_data['ktp_photo_path']))
                    <img src="{{ Storage::url($step2_data['ktp_photo_path']) }}" alt="Foto KTP" class="w-full h-36 rounded-xl border border-gray-200 dark:border-gray-700 object-cover">
                @else
                    <div class="w-full h-36 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-xs text-gray-400">
                        Belum ada foto
                    </div>
                @endif
            </div>

            <!-- Foto Selfie KTP -->
            <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-gray-200/60 dark:border-gray-700/60">
                    <h3 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span>🤳</span> Foto Selfie + KTP
                    </h3>
                    <button type="button" wire:click="editStep(3)" class="text-primary-600 dark:text-sky-400 text-[11px] font-bold hover:underline cursor-pointer">
                        Ubah
                    </button>
                </div>
                @if(!empty($step3_data['selfie_photo_path']))
                    <img src="{{ Storage::url($step3_data['selfie_photo_path']) }}" alt="Foto Selfie" class="w-full h-36 rounded-xl border border-gray-200 dark:border-gray-700 object-cover">
                @else
                    <div class="w-full h-36 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center text-xs text-gray-400">
                        Belum ada foto
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. Pernyataan & Deklarasi -->
        <div class="bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="agree_declaration" type="checkbox" class="w-4 h-4 text-emerald-600 rounded border-gray-300 dark:border-gray-600 mt-0.5 focus:ring-emerald-500">
                <span class="text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-relaxed flex-1">
                    Saya menyatakan dengan sesungguhnya bahwa seluruh data identitas diri dan foto dokumen KTP yang saya lampirkan adalah <strong>benar, sah, dan milik saya pribadi</strong> untuk keperluan verifikasi akun di platform SayaBantu.
                </span>
            </label>
            @error('agree_declaration')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-2 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="pt-3 pb-2 flex items-center gap-3">
            <button type="button" wire:click="previousStep"
                class="px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </button>

            <button type="submit" wire:loading.attr="disabled"
                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                <svg wire:loading wire:target="complete" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="complete">Kirim Pengajuan Verifikasi KTP</span>
                <span wire:loading wire:target="complete">Mengirim Pengajuan...</span>
                <svg wire:loading.remove wire:target="complete" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </div>
    </form>
</div>