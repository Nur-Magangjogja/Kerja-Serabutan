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

    public function mount()
    {
        // Cek apakah step 1 sudah selesai (diperiksa via registration_uuid)
        $uuid = Session::get('registration_uuid');
        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }
    }

    public function updatedKtpPhoto()
    {
        $this->validate([
            'ktp_photo' => 'image|mimes:jpg,jpeg,png|max:2048', // 2MB Max
        ], [
            'ktp_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'ktp_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
            'ktp_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        try {
            $this->preview_url = ($this->ktp_photo && method_exists($this->ktp_photo, 'temporaryUrl') && $this->ktp_photo->isPreviewable()) ? $this->ktp_photo->temporaryUrl() : null;
        } catch (\Throwable $e) {
            $this->preview_url = null;
        }
    }

    public function nextStep(): void
    {
        $this->validate([
            'ktp_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'ktp_photo.required' => 'Foto KTP wajib diupload',
            'ktp_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'ktp_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
            'ktp_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        // Simpan file ke storage
        $path = $this->ktp_photo->store('ktp-photos', 'public');

        // Update registration record
        $uuid = Session::get('registration_uuid');
        $registration = Registration::where('uuid', $uuid)->first();
        if ($registration) {
            $registration->update([
                'ktp_photo_path' => $path,
                'status' => 'in_progress',
            ]);
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

                <!-- Upload Area -->
                <div class="mb-6">
                    @if ($preview_url)
                        <!-- Preview Image -->
                        <div class="relative bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-lg border-2 border-primary-500">
                            <img src="{{ $preview_url }}" alt="Preview KTP" class="w-full h-auto">
                            <button type="button" wire:click="$set('ktp_photo', null); $set('preview_url', null)"
                                class="absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white rounded-xl p-2 shadow-lg transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
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
                            <input wire:model="ktp_photo" id="ktp_photo" type="file" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" class="hidden">
                        </label>
                    @endif

                    <x-input-error :messages="$errors->get('ktp_photo')" />

                    @if ($ktp_photo && !$preview_url)
                        <div class="mt-3 text-center">
                            <div class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                <svg class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses foto...
                            </div>
                        </div>
                    @endif
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
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700/80 rounded-lg flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
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

            <!-- Actions -->
            <div class="pt-6 pb-2 flex items-center gap-3">
                <button type="button" wire:click="previousStep"
                    class="px-5 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali</span>
                </button>

                <button type="submit" wire:loading.attr="disabled" :disabled="!$wire.ktp_photo"
                    class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2">
                    <span wire:loading.remove>Lanjutkan ke Langkah 3</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
</div>