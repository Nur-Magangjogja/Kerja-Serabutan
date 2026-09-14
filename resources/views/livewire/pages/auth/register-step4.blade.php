<?php

use App\Livewire\Actions\CancelRegistration;
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
        $user = Auth::user();

        if ($user) {
            if (in_array($user->role ?? '', ['admin', 'super_admin', 'superadmin'])) {
                $route = in_array($user->role, ['super_admin', 'superadmin']) ? 'superadmin.dashboard' : 'admin.dashboard';
                $this->redirect(route($route), navigate: true);
                return;
            }

            if ($user->verified && $user->status === 'active') {
                $route = $user->role === 'mitra' ? 'mitra.dashboard' : 'customer.dashboard';
                $this->redirect(route($route), navigate: true);
                return;
            }
        }

        if ($user && !$user->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        $userEmail = $user ? strtolower(trim($user->email)) : null;

        $registration = null;
        if ($userEmail) {
            $registration = Registration::where('email', $userEmail)->latest()->first();
        }

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$registration && $uuid) {
            $found = Registration::where('uuid', $uuid)->first();
            if ($found && $userEmail && strtolower(trim($found->email ?? '')) === $userEmail) {
                $registration = $found;
            }
        }

        if (!$registration || empty($registration->ktp_photo_path) || empty($registration->selfie_photo_path)) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        Session::put('registration_uuid', $registration->uuid);
        \Illuminate\Support\Facades\Cookie::queue('registration_uuid', $registration->uuid, 60 * 24);

        $this->step1_data = $registration->only([
            'nik',
            'full_name',
            'phone',
            'gender',
            'district_id',
            'kecamatan',
            'city_id',
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

        $user = Auth::user();
        $userEmail = $user ? strtolower(trim($user->email)) : null;

        $registration = null;
        if ($userEmail) {
            $registration = Registration::where('email', $userEmail)->latest()->first();
        }

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$registration && $uuid) {
            $found = Registration::where('uuid', $uuid)->first();
            if ($found && $userEmail && strtolower(trim($found->email ?? '')) === $userEmail) {
                $registration = $found;
            }
        }

        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

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

            $districtId = $registration->district_id;
            $kecamatanName = $registration->kecamatan;
            if (!$kecamatanName && $districtId) {
                $distRec = \App\Models\District::find($districtId);
                $kecamatanName = $distRec?->name;
            }

            $user->update([
                'nik'            => $registration->nik,
                'name'           => $registration->full_name ?: $user->name,
                'phone'          => $registration->phone ?: $user->phone,
                'gender'         => $registration->gender,
                'district_id'    => $districtId,
                'kecamatan'      => $kecamatanName,
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

            // Kirim notifikasi ke admin regional terkait pengajuan KTP baru (berdasarkan kecamatan / kota)
            try {
                $admins = User::where('role', 'admin')
                    ->when($user->district_id, function($q) use ($user) {
                        $q->where(function($sq) use ($user) {
                            $sq->where('district_id', $user->district_id)
                               ->orWhereHas('managedDistricts', fn($dq) => $dq->where('districts.id', $user->district_id));
                        });
                    }, function($q) use ($user) {
                        $q->when($user->city_id, fn($cq) => $cq->where('city_id', $user->city_id));
                    })
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
        Cookie::queue(Cookie::forget('sb_register_draft'));
        Cookie::queue(Cookie::forget('sb_register_leave_time'));

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

    public function cancelRegistration(CancelRegistration $cancelRegistration): void
    {
        $cancelRegistration();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="space-y-5" x-data="{ confirmCancelModal: false, previewModalImage: null, previewModalTitle: '' }">
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
                <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 space-y-1">
                    <span class="text-gray-500 dark:text-gray-400 block text-xs">Wilayah Domisili / Operasional:</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block font-medium">Kecamatan (Patokan Utama)</span>
                            <span class="font-bold text-primary-700 dark:text-sky-400 truncate block">{{ $step1_data['kecamatan'] ?? ($step1_data['district_id'] ? 'Kecamatan Terpilih' : '-') }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block font-medium">Kota / Kabupaten</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $step1_data['city'] ?? '-' }}</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 pt-0.5">Provinsi: {{ $step1_data['province'] ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- 3. Foto Dokumen KTP & Selfie (Responsive, Aspect-aware, Lightbox zoom) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- Foto e-KTP -->
            <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                    <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span></span> Foto e-KTP
                    </h3>
                    <button type="button" wire:click="editStep(2)" class="inline-flex items-center gap-1 text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Ubah</span>
                    </button>
                </div>
                
                @if(!empty($step2_data['ktp_photo_path']))
                    <div class="relative group cursor-pointer w-full h-44 sm:h-52 bg-slate-900/5 dark:bg-black/40 rounded-xl border border-gray-200 dark:border-gray-700/80 overflow-hidden flex items-center justify-center p-2.5 transition-all"
                         @click="previewModalImage = '{{ asset('storage/' . $step2_data['ktp_photo_path']) }}'; previewModalTitle = 'Foto e-KTP'">
                        <img src="{{ asset('storage/' . $step2_data['ktp_photo_path']) }}" 
                             alt="Foto KTP" 
                             class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xs transition-transform duration-200 group-hover:scale-[1.03]">
                        <div class="absolute inset-0 bg-gray-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-white text-xs font-semibold backdrop-blur-2xs rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                            </svg>
                            <span>Klik untuk perbesar</span>
                        </div>
                    </div>
                @else
                    <div class="w-full h-44 sm:h-52 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center text-xs text-gray-400 gap-1">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Belum ada foto</span>
                    </div>
                @endif
            </div>

            <!-- Foto Selfie KTP -->
            <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                    <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span></span> Foto Selfie + KTP
                    </h3>
                    <button type="button" wire:click="editStep(3)" class="inline-flex items-center gap-1 text-primary-600 dark:text-sky-400 text-xs font-bold hover:underline cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Ubah</span>
                    </button>
                </div>
                
                @if(!empty($step3_data['selfie_photo_path']))
                    <div class="relative group cursor-pointer w-full h-44 sm:h-52 bg-slate-900/5 dark:bg-black/40 rounded-xl border border-gray-200 dark:border-gray-700/80 overflow-hidden flex items-center justify-center p-2.5 transition-all"
                         @click="previewModalImage = '{{ asset('storage/' . $step3_data['selfie_photo_path']) }}'; previewModalTitle = 'Foto Selfie + KTP'">
                        <img src="{{ asset('storage/' . $step3_data['selfie_photo_path']) }}" 
                             alt="Foto Selfie" 
                             class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xs transition-transform duration-200 group-hover:scale-[1.03]">
                        <div class="absolute inset-0 bg-gray-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-white text-xs font-semibold backdrop-blur-2xs rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                            </svg>
                            <span>Klik untuk perbesar</span>
                        </div>
                    </div>
                @else
                    <div class="w-full h-44 sm:h-52 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center text-xs text-gray-400 gap-1">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Belum ada foto</span>
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

        <!-- Cancel Registration Link / Button -->
        <div class="pt-2 text-center border-t border-gray-100 dark:border-gray-750 mt-4">
            <button type="button" 
                @click="confirmCancelModal = true"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors py-1.5 px-3 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Batalkan Pembuatan Akun & Masuk</span>
            </button>
        </div>
    </form>

    <!-- Lightbox Modal untuk Preview Foto Step 4 (Alpine.js) -->
    <div x-show="previewModalImage" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="preview-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur Overlay -->
        <div x-show="previewModalImage"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm transition-opacity"
             @click="previewModalImage = null"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div x-show="previewModalImage"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 text-left shadow-2xl border border-gray-100 dark:border-gray-700 transition-all sm:my-8 w-full max-w-lg p-5 sm:p-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-3">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white" id="preview-modal-title" x-text="previewModalTitle"></h3>
                    <button type="button" 
                            @click="previewModalImage = null"
                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-center max-h-[70vh] overflow-hidden bg-gray-950/5 dark:bg-black/50 rounded-2xl p-2 sm:p-3">
                    <img :src="previewModalImage" :alt="previewModalTitle" class="max-w-full max-h-[65vh] w-auto h-auto object-contain rounded-xl shadow-md">
                </div>

                
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Pembatalan Pendaftaran (Alpine.js) -->
    <div x-show="confirmCancelModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur Overlay -->
        <div x-show="confirmCancelModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="confirmCancelModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="confirmCancelModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl border border-gray-100 dark:border-gray-700 transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7">
                
                <div class="flex items-start gap-4">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 sm:mx-0 shadow-xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="text-left flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">
                            Batalkan Pembuatan Akun?
                        </h3>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pengajuan pendaftaran akun ini? Semua berkas dan data yang diisi akan dihapus dan Anda akan dialihkan kembali ke halaman <strong>Masuk (Login)</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <button type="button" 
                        @click="confirmCancelModal = false"
                        class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 transition cursor-pointer">
                        Lanjutkan Pengisian
                    </button>
                    <button type="button" 
                        wire:click="cancelRegistration"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 text-xs font-bold shadow-sm transition active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <svg wire:loading wire:target="cancelRegistration" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="cancelRegistration">Ya, Batalkan & Keluar</span>
                        <span wire:loading wire:target="cancelRegistration">Membatalkan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>