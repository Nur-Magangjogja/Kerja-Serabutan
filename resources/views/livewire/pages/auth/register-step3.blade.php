<?php

use App\Models\Registration;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component {
    use WithFileUploads;

    public $selfie_photo;
    public $preview_url = null;

    public function mount()
    {
        // Cek apakah step 1 dan 2 sudah selesai (via registration record)
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }
        Session::put('registration_uuid', $uuid);

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration || !$registration->ktp_photo_path) {
            // KTP belum diupload, kembali ke step1
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }
    }

    public function updatedSelfiePhoto()
    {
        $this->validate([
            'selfie_photo' => 'image|mimes:jpg,jpeg,png|max:2048', // 2MB Max
        ], [
            'selfie_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'selfie_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
            'selfie_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        try {
            $this->preview_url = ($this->selfie_photo && method_exists($this->selfie_photo, 'temporaryUrl') && $this->selfie_photo->isPreviewable()) ? $this->selfie_photo->temporaryUrl() : null;
        } catch (\Throwable $e) {
            $this->preview_url = null;
        }
    }

    public function nextStep(): void
    {
        $this->validate([
            'selfie_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'selfie_photo.required' => 'Foto selfie wajib diupload',
            'selfie_photo.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'selfie_photo.mimes' => 'Format foto harus PNG, JPG, atau JPEG',
            'selfie_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        // Simpan file ke storage
        $path = $this->selfie_photo->store('selfie-photos', 'public');

        // Update registration record
        $uuid = Session::get('registration_uuid');
        $registration = Registration::where('uuid', $uuid)->first();
        if ($registration) {
            $registration->update([
                'selfie_photo_path' => $path,
                'status' => 'in_progress',
            ]);
        }

        $this->redirect(route('register.step4'), navigate: true);
    }

    public function previousStep(): void
    {
        $this->redirect(route('register.step2'), navigate: true);
    }
}; ?>

<div class="space-y-5">
    <!-- Step Header -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
        <div>
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-primary-600 dark:text-sky-400">Langkah 3 dari 4</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Foto Selfie + KTP</h2>
        </div>
        <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-sky-400 font-bold text-xs flex items-center justify-center border border-primary-200 dark:border-primary-800">
            3/4
        </div>
    </div>

    <!-- Progress Indicator Pills -->
    <div class="grid grid-cols-4 gap-1.5 mb-2">
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <form wire:submit="nextStep" class="space-y-4">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Unggah foto selfie sambil memegang e-KTP Anda untuk verifikasi keaslian akun.</p>

                <!-- Upload Area -->
                <div class="mb-6">
                    @if ($preview_url)
                        <!-- Preview Image -->
                        <div class="relative bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-lg border-2 border-primary-500">
                            <img src="{{ $preview_url }}" alt="Preview Selfie" class="w-full h-auto">
                            <button type="button" wire:click="$set('selfie_photo', null); $set('preview_url', null)"
                                class="absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white rounded-xl p-2 shadow-lg transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <!-- Upload Placeholder -->
                        <label for="selfie_photo" class="block cursor-pointer group">
                            <div class="bg-gray-50/70 dark:bg-gray-900/60 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 group-hover:border-primary-500 dark:group-hover:border-primary-500 transition-all p-7 text-center">
                                <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-950/70 text-primary-600 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-1">Upload Foto Selfie</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Klik untuk mengambil foto selfie memegang KTP</p>
                                <span class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                                    Ambil Foto Selfie
                                </span>
                            </div>
                            <input wire:model="selfie_photo" id="selfie_photo" type="file" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" capture="user" class="hidden">
                        </label>
                    @endif

                    <x-input-error :messages="$errors->get('selfie_photo')" />

                    @if ($selfie_photo && !$preview_url)
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

                <!-- Important Alert -->
                <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-xs sm:text-sm font-bold text-amber-900 dark:text-amber-200 mb-1.5">PENTING:</h4>
                            <ul class="text-xs text-amber-800 dark:text-amber-300 space-y-1">
                                <li>• <strong>Wajah Anda harus terlihat jelas</strong> dan tidak tertutup masker / kacamata hitam.</li>
                                <li>• <strong>Pegang KTP di dekat wajah Anda</strong> tanpa menutupi tulisan NIK & data penting.</li>
                                <li>• Pastikan pencahayaan cukup dan foto tidak berbayang.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tips Alert -->
                <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-2xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-xs sm:text-sm font-bold text-blue-900 dark:text-blue-200 mb-1.5">Tips Pengambilan:</h4>
                            <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                                <li>• Gunakan kamera depan dengan pencahayaan langsung ke wajah.</li>
                                <li>• Hindari penggunaan filter kecantikan agar identitas tetap otentik.</li>
                                <li>• Format yang didukung: JPG, JPEG, PNG (Maksimal 2MB).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Example Images -->
                <div class="bg-gray-50/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">Panduan Contoh Foto Selfie:</p>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Good Example -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border-2 border-emerald-500/70 dark:border-emerald-600/70">
                            <div class="aspect-square bg-emerald-50 dark:bg-emerald-950/40 rounded-lg overflow-hidden flex items-center justify-center mb-2">
                                <img src="{{ asset('images/Sample-Ktp-Person-Valid.png') }}" alt="Contoh Selfie KTP Benar" class="w-full h-full object-cover">
                            </div>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold text-center">✓ Benar</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center mt-0.5">Wajah & KTP jelas</p>
                        </div>

                        <!-- Bad Example -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border-2 border-red-500/70 dark:border-red-600/70">
                            <div class="aspect-square bg-red-50 dark:bg-red-950/40 rounded-lg overflow-hidden flex items-center justify-center mb-2">
                                <img src="{{ asset('images/Sample-Ktp-Person-Failed.png') }}" alt="Contoh Selfie KTP Salah" class="w-full h-full object-cover">
                            </div>
                            <p class="text-xs text-red-600 dark:text-red-400 font-bold text-center">✗ Salah</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center mt-0.5">Buram atau gelap</p>
                        </div>
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

                <button type="submit" wire:loading.attr="disabled" :disabled="!$wire.selfie_photo"
                    class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2">
                    <span wire:loading.remove>Lanjutkan ke Langkah 4</span>
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