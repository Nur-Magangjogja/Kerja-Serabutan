<?php

use App\Models\User;
use App\Models\Registration;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'customer'; // customer / mitra
    public bool $agree_terms = false;
    public bool $isSubmitting = false;

    public function register(): void
    {
        if ($this->isSubmitting) {
            return;
        }

        // Otomatis ubah email menjadi huruf kecil dan bersihkan spasi
        $normalizedEmail = strtolower(trim($this->email));
        $this->email = $normalizedEmail;
        $this->name = trim($this->name);

        // Jika email yang didaftarkan sudah ada tapi belum diverifikasi (unverified stub),
        // hapus data unverified lama agar pengguna bisa langsung mendaftar ulang tanpa terkunci
        $existingUnverified = User::where('email', $normalizedEmail)
            ->whereNull('email_verified_at')
            ->first();

        if ($existingUnverified) {
            try {
                Registration::where('email', $normalizedEmail)
                    ->where('status', '!=', 'approved')
                    ->delete();
                $existingUnverified->delete();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Hapus akun unverified lama jika sudah lewat batas waktu 10 menit
        User::purgeExpiredUnverified($normalizedEmail);

        // Hapus akun inactive lama (belum selesai isi data formulir) jika sudah lewat 1x24 jam
        User::purgeExpiredInactive($normalizedEmail);

        $validated = $this->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'ends_with:@gmail.com', 'max:255', 'unique:' . User::class],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'role'        => ['required', 'in:customer,mitra'],
            'agree_terms' => ['accepted'],
        ], [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Alamat email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.ends_with'       => 'Pendaftaran akun wajib menggunakan email Google (@gmail.com).',
            'email.unique'          => 'Alamat email ini sudah terdaftar dan terverifikasi di sistem.',
            'password.required'     => 'Kata sandi wajib diisi.',
            'password.min'          => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
            'role.required'         => 'Silakan pilih peran akun Anda.',
            'agree_terms.accepted'  => 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.',
        ]);

        $this->isSubmitting = true;

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => trim(strtolower($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => 'inactive',
            'verified' => false,
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::warning('[Register] Pengiriman notifikasi email verifikasi mengalami kendala: ' . $e->getMessage());
        }

        // Inisialisasi timestamp pengiriman agar cooldown 2 menit (120 detik) aktif sejak pertama kali mendaftar
        session(['last_verification_sent_at' => now()->timestamp]);

        Auth::login($user);

        $this->redirect(route('verification.notice', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <!-- Header Icon & Title -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-600/20 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Akun Baru</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Buat akun baru dengan email Anda untuk mulai terhubung dengan komunitas SayaBantu.
        </p>
    </div>

    @if (session('error'))
        <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 text-xs flex items-start gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="leading-relaxed">{{ session('error') }}</span>
        </div>
    @endif

    @if (session('message'))
        <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-xs flex items-start gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="leading-relaxed">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Registration Form -->
    <form wire:submit="register" class="space-y-4" x-data="{ showPassword: false, showConfirm: false }">
        
        <!-- 1. Pilihan Peran (Role Selector) -->
        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                Pilih Peran Akun Anda:
            </label>
            <div class="grid grid-cols-2 gap-3">
                <!-- Role: Customer -->
                <button type="button" wire:click="$set('role', 'customer')"
                    class="p-3 rounded-2xl border-2 transition-all flex flex-col items-center text-center gap-1.5 cursor-pointer {{ $role === 'customer' ? 'border-primary-600 bg-primary-50/60 dark:bg-primary-950/40 text-primary-900 dark:text-primary-200 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg {{ $role === 'customer' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                        👤
                    </div>
                    <div>
                        <span class="text-xs font-bold block">Customer</span>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 block leading-tight">Pemberi Tugas</span>
                    </div>
                </button>

                <!-- Role: Mitra -->
                <button type="button" wire:click="$set('role', 'mitra')"
                    class="p-3 rounded-2xl border-2 transition-all flex flex-col items-center text-center gap-1.5 cursor-pointer {{ $role === 'mitra' ? 'border-purple-600 bg-purple-50/60 dark:bg-purple-950/40 text-purple-900 dark:text-purple-200 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg {{ $role === 'mitra' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                        🛠️
                    </div>
                    <div>
                        <span class="text-xs font-bold block">Mitra Jasa</span>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 block leading-tight">Penyedia Layanan</span>
                    </div>
                </button>
            </div>
            @error('role')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 2. Nama Lengkap -->
        <div class="space-y-1">
            <label for="name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                Nama Lengkap
            </label>
            <div class="relative">
                <input wire:model="name" id="name" type="text" required placeholder="Contoh: Budi Santoso"
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            @error('name')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 3. Alamat Email Google (Gmail) -->
        <div class="space-y-1">
            <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                <span>Alamat Email </span>
            </label>
            <div class="relative">
                <input wire:model="email" id="email" type="email" required placeholder="nama@gmail.com"
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            @error('email')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 4. Kata Sandi -->
        <div class="space-y-1">
            <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                Kata Sandi
            </label>
            <div class="relative">
                <input wire:model="password" id="password" :type="showPassword ? 'text' : 'password'" required placeholder="Minimal 8 karakter"
                    class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 5. Konfirmasi Kata Sandi -->
        <div class="space-y-1">
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                Ulangi Kata Sandi
            </label>
            <div class="relative">
                <input wire:model="password_confirmation" id="password_confirmation" :type="showConfirm ? 'text' : 'password'" required placeholder="Ketik ulang kata sandi"
                    class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
        </div>

        <!-- 6. Persetujuan Syarat & Ketentuan -->
        <div class="pt-1">
            <label class="flex items-start gap-2.5 cursor-pointer">
                <input wire:model="agree_terms" type="checkbox"
                    class="mt-0.5 w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                <span class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    Saya menyetujui <a href="#" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Kebijakan Privasi</a> yang berlaku di SayaBantu.
                </span>
            </label>
            @error('agree_terms')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                <svg wire:loading wire:target="register" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="register">Buat Akun & Verifikasi Email</span>
                <span wire:loading wire:target="register">Mendaftarkan Akun...</span>
                <svg wire:loading.remove wire:target="register" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

    </form>

    <!-- Footer Links -->
    <div class="text-center pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Sudah memiliki akun terdaftar? 
            <a href="{{ route('login') }}" class="font-bold text-primary-600 dark:text-primary-400 hover:underline">
                Masuk di sini
            </a>
        </p>
    </div>
</div>