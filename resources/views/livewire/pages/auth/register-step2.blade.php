<?php

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
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && !$user->hasVerifiedEmail()) {
                $this->redirect(route('verification.notice'), navigate: true);
                return;
            }
        }

        // Cek apakah step 1 sudah selesai (diperiksa via registration_uuid di session atau cookie)
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$uuid && \Illuminate\Support\Facades\Auth::check()) {
            $reg = Registration::where('email', \Illuminate\Support\Facades\Auth::user()->email)->latest()->first();
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
        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

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

        try {
            if ($this->ktp_photo && method_exists($this->ktp_photo, 'temporaryUrl')) {
                $this->preview_url = $this->ktp_photo->temporaryUrl();
            }
        } catch (\Throwable $e) {
            // ignore preview temporaryUrl failure
        }
    }

    public function removePhoto(): void
    {
        $this->ktp_photo = null;
        $this->preview_url = null;
        $this->iteration++;

        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if ($uuid) {
            $registration = Registration::where('uuid', $uuid)->first();
            if ($registration) {
                if ($registration->ktp_photo_path) {
                    Storage::disk('public')->delete($registration->ktp_photo_path);
                }
                $registration->update([
                    'ktp_photo_path' => null,
                ]);
            }
        }

        if (\Illuminate\Support\Facades\Auth::check()) {
            \Illuminate\Support\Facades\Auth::user()->update([
                'ktp_photo' => null,
                'ktp_path' => null,
            ]);
        }
    }

    public function nextStep(): void
    {
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        $registration = $uuid ? Registration::where('uuid', $uuid)->first() : null;

        if ($this->ktp_photo) {
            $this->validate([
                'ktp_photo' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ], [
                'ktp_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
                'ktp_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
                'ktp_photo.max' => 'Ukuran foto maksimal 2MB',
            ]);

            // Hapus foto lama jika ada
            if ($registration && $registration->ktp_photo_path) {
                Storage::disk('public')->delete($registration->ktp_photo_path);
            }

            // Simpan file ke storage
            $path = $this->ktp_photo->store('ktp-photos', 'public');

            if ($registration) {
                $registration->update([
                    'ktp_photo_path' => $path,
                    'status' => 'in_progress',
                ]);
            }

            if (\Illuminate\Support\Facades\Auth::check()) {
                \Illuminate\Support\Facades\Auth::user()->update([
                    'ktp_photo' => $path,
                    'ktp_path' => $path,
                ]);
            }
        } elseif (!$registration || empty($registration->ktp_photo_path)) {
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
        $this->redirect(route('register.step1'), navigate: true);
    }
}; ?>

<div class="space-y-5">
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
            <input wire:model="ktp_photo" id="ktp_photo" type="file"
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
                    <!-- Preview Image -->
                    <div class="relative bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-lg border-2 border-primary-500">
                        <img src="{{ $preview_url }}" alt="Preview KTP" class="w-full max-h-72 object-contain bg-gray-950/5 dark:bg-gray-950/40">
                        
                        <!-- Overlay Action Toolbar -->
                        <div class="p-3 bg-gray-50/95 dark:bg-gray-800/95 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Foto KTP Terpasang</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Tombol Ganti Foto -->
                                <label for="ktp_photo"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>Ganti Foto</span>
                                </label>

                                <!-- Tombol Hapus Foto -->
                                <button type="button" wire:click="removePhoto"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-100 hover:bg-rose-200 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-semibold transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus</span>
                                </button>
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
                        <h4 class="text-xs sm:text-sm font-bold text-blue-900 dark:text-blue-200 mb-1.5">Tips Upload Foto KTP:</h4>
                        <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
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
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700/80 rounded-lg overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('images/Sample-Ktp.png') }}" alt="Contoh Foto KTP yang Baik" class="w-full h-full object-cover">
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
    </form>
</div>