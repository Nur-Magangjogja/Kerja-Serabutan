<?php

use App\Livewire\Actions\CancelRegistration;
use App\Models\Registration;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component {
    use WithFileUploads;

    public $ktp_photo;
    public $preview_url = null;
    public int $iteration = 1;

    public function mount()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

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

        // Cari record registrasi strictly milik user yang sedang aktif
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
            Session::forget('registration_uuid');
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('registration_uuid'));
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        Session::put('registration_uuid', $registration->uuid);
        \Illuminate\Support\Facades\Cookie::queue('registration_uuid', $registration->uuid, 60 * 24);

        if (!empty($registration->ktp_photo_path)) {
            $this->preview_url = asset('storage/' . $registration->ktp_photo_path);
        }
    }

    public function updatedKtpPhoto()
    {
        $this->validate([
            'ktp_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048', // 2MB Max
        ], [
            'ktp_photo.required' => 'Silakan pilih file foto KTP Anda.',
            'ktp_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'ktp_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
            'ktp_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        $userEmail = $user ? strtolower(trim($user->email)) : null;

        $registration = null;
        if ($userEmail) {
            $registration = Registration::where('email', $userEmail)->latest()->first();
        }

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$registration && $uuid) {
            $found = Registration::where('uuid', $uuid)->first();
            if ($found && (!$userEmail || strtolower(trim($found->email ?? '')) === $userEmail)) {
                $registration = $found;
            }
        }

        if ($registration && $this->ktp_photo) {
            // Hapus foto lama jika ada
            if ($registration->ktp_photo_path && Storage::disk('public')->exists($registration->ktp_photo_path)) {
                Storage::disk('public')->delete($registration->ktp_photo_path);
            }

            // Simpan file ke storage secara instan agar tidak hilang saat refresh
            $path = $this->ktp_photo->store('ktp-photos', 'public');

            $registration->update([
                'ktp_photo_path' => $path,
                'status' => 'in_progress',
            ]);

            if ($user) {
                $user->update([
                    'ktp_photo' => $path,
                    'ktp_path' => $path,
                ]);
            }

            $this->preview_url = asset('storage/' . $path);
            $this->reset('ktp_photo');
            $this->iteration++;
        }
    }

    public function nextStep(): void
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $userEmail = $user ? strtolower(trim($user->email)) : null;

        $registration = null;
        if ($userEmail) {
            $registration = Registration::where('email', $userEmail)->latest()->first();
        }

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$registration && $uuid) {
            $found = Registration::where('uuid', $uuid)->first();
            if ($found && (!$userEmail || strtolower(trim($found->email ?? '')) === $userEmail)) {
                $registration = $found;
            }
        }

        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        if ($this->ktp_photo) {
            $this->validate([
                'ktp_photo' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ], [
                'ktp_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
                'ktp_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
                'ktp_photo.max' => 'Ukuran foto maksimal 2MB',
            ]);

            if ($registration->ktp_photo_path && Storage::disk('public')->exists($registration->ktp_photo_path)) {
                Storage::disk('public')->delete($registration->ktp_photo_path);
            }

            $path = $this->ktp_photo->store('ktp-photos', 'public');

            $registration->update([
                'ktp_photo_path' => $path,
                'status' => 'in_progress',
            ]);

            if ($user) {
                $user->update([
                    'ktp_photo' => $path,
                    'ktp_path' => $path,
                ]);
            }
        } elseif (empty($registration->ktp_photo_path)) {
            $this->validate([
                'ktp_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ], [
                'ktp_photo.required' => 'Foto KTP wajib diupload.',
            ]);
            return;
        }

        $this->redirect(route('register.step3'), navigate: true);
    }

    public function previousStep(): void
    {
        $this->redirect(route('register.step1'));
    }

    public function cancelRegistration(CancelRegistration $cancelRegistration): void
    {
        $cancelRegistration();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="space-y-5" x-data="{ confirmCancelModal: false }">
    <!-- Step Header -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
        <div>
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-primary-600 dark:text-sky-400">Langkah 2 dari 4</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Upload Foto KTP</h2>
        </div>
        <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-sky-400 font-bold text-xs flex items-center justify-center border border-primary-200 dark:border-primary-800">
            2/4
        </div>
    </div>

    <!-- Progress Indicator Pills -->
    <div class="grid grid-cols-4 gap-1.5 mb-2">
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <form wire:submit="nextStep" class="space-y-4">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Unggah foto e-KTP Anda dengan jelas dan pencahayaan yang cukup.</p>

            <!-- Hidden File Input (Always in DOM with key to allow clean re-upload) -->
            <input wire:model.live="ktp_photo" id="ktp_photo" type="file"
                accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg"
                class="hidden"
                wire:key="ktp-photo-input-{{ $iteration }}">

            <!-- Upload Area -->
            <div class="mb-6">
                <!-- Loading State during File Upload -->
                <div wire:loading wire:target="ktp_photo" class="w-full mb-3">
                    <div class="p-4 bg-primary-50/90 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 rounded-2xl flex items-center justify-center gap-3 text-primary-700 dark:text-primary-300 shadow-xs">
                        <svg class="animate-spin h-5 w-5 text-primary-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs sm:text-sm font-semibold">Mengunggah dan memproses foto KTP...</span>
                    </div>
                </div>

                @if ($preview_url)
                    <!-- Preview Image Card (Responsive, Aspect-aware, No Crop) -->
                    <div class="relative bg-slate-900/5 dark:bg-black/40 rounded-2xl overflow-hidden shadow-md border-2 border-primary-500/80 transition-all">
                        <div class="relative w-full min-h-[200px] sm:min-h-[250px] max-h-[380px] sm:max-h-[440px] flex items-center justify-center p-2.5 sm:p-4 overflow-hidden">
                            <img src="{{ $preview_url }}" alt="Preview KTP" class="max-w-full max-h-[350px] sm:max-h-[410px] w-auto h-auto object-contain rounded-xl shadow-xs transition-transform duration-200">
                        </div>
                        
                        <!-- Overlay Action Toolbar -->
                        <div class="p-3 bg-white/95 dark:bg-gray-800/95 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Foto KTP Terpasang</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Tombol Ganti Foto -->
                                <label for="ktp_photo"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>Ganti Foto</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Upload Placeholder -->
                    <label for="ktp_photo" class="block cursor-pointer group">
                        <div class="bg-gray-50/70 dark:bg-gray-900/60 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 group-hover:border-primary-500 dark:group-hover:border-primary-500 transition-all p-7 text-center">
                            <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-950/70 text-primary-600 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-1">Upload Foto KTP</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Klik untuk memilih foto dari perangkat Anda</p>
                            <span class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                                Pilih Foto KTP
                            </span>
                        </div>
                    </label>
                @endif

                <x-input-error :messages="$errors->get('ktp_photo')" />
            </div>

            <!-- Tips Alert -->
            <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-2xl p-4 mb-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-xs sm:text-sm font-bold text-white-900 dark:text-white-200 mb-1.5">Tips Upload Foto KTP:</h4>
                        <ul class="text-xs text-white-800 dark:text-white-300 space-y-1">
                            <li>• Pastikan foto KTP terlihat jelas, fokus, dan tidak buram.</li>
                            <li>• Semua teks & angka NIK di KTP harus terbaca dengan baik.</li>
                            <li>• Hindari pantulan kilau cahaya lampu atau bayangan gelap.</li>
                            <li>• Format yang didukung: JPG, JPEG, PNG (Maksimal 2MB).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Example Image -->
            <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Contoh Foto KTP yang Baik:</p>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border-2 border-emerald-500/70 dark:border-emerald-600/70">
                    <div class="w-full min-h-[160px] max-h-[220px] bg-gray-100 dark:bg-gray-700/80 rounded-lg overflow-hidden flex items-center justify-center p-2">
                        <img src="{{ asset('images/Sample-Ktp.png') }}" alt="Contoh Foto KTP yang Baik" class="max-w-full max-h-[200px] w-auto h-auto object-contain rounded">
                    </div>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-2 text-center flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Jelas & Terbaca Sempurna
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions (Tombol Navigasi Bawah) -->
        <div class="pt-6 pb-2 flex items-center gap-3">
            <button type="button" wire:click="previousStep"
                class="px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </button>

            <button type="submit"
                wire:loading.attr="disabled"
                @disabled(!$preview_url && !$ktp_photo)
                class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2">
                <!-- Spinner loading tepat di sebelah teks tombol -->
                <svg wire:loading wire:target="nextStep, ktp_photo" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span wire:loading.remove wire:target="nextStep, ktp_photo">Lanjutkan ke Langkah 3</span>
                <span wire:loading wire:target="nextStep">Menyimpan...</span>
                <span wire:loading wire:target="ktp_photo">Memproses Foto...</span>

                <svg wire:loading.remove wire:target="nextStep, ktp_photo" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
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
                                Apakah Anda yakin ingin membatalkan pendaftaran ini? Seluruh data diri dan dokumen foto KTP yang diunggah akan dihapus dan Anda akan dialihkan kembali ke halaman <strong>Masuk (Login)</strong>.
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