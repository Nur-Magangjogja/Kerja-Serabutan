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
        $uuid = Session::get('registration_uuid');
        if (!$uuid) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

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
            'selfie_photo' => 'image|max:2048', // 2MB Max
        ]);

        $this->preview_url = $this->selfie_photo->temporaryUrl();
    }

    public function nextStep(): void
    {
        $this->validate([
            'selfie_photo' => ['required', 'image', 'max:2048'],
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

<div class="w-full">
    <div class="bg-white rounded-2xl p-6 w-full shadow-lg max-w-md mx-auto">
        <div class="flex items-center justify-between mb-4">
            <button wire:click="previousStep" class="text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="text-lg font-bold text-gray-900">Foto Selfie</h1>
            <div class="w-6"></div>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
        </div>
        <p class="text-sm text-gray-500 mb-4 text-center">Step 3 of 4</p>

        <form wire:submit="nextStep" class="">
            <div>
                <p class="text-sm text-gray-600 mb-4">Upload foto selfie sambil memegang KTP Anda</p>

                <!-- Upload Area -->
                <div class="mb-6">
                    @if ($preview_url)
                        <!-- Preview Image -->
                        <div class="relative bg-white rounded-2xl overflow-hidden shadow-lg border-2 border-primary-500">
                            <img src="{{ $preview_url }}" alt="Preview Selfie" class="w-full h-auto">
                            <button type="button" wire:click="$set('selfie_photo', null); $set('preview_url', null)"
                                class="absolute top-3 right-3 bg-red-500 text-white rounded-full p-2 shadow-lg hover:bg-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <!-- Upload Placeholder -->
                        <label for="selfie_photo" class="block cursor-pointer">
                            <div
                                class="bg-white rounded-2xl border-2 border-dashed border-gray-300 hover:border-primary-500 transition p-8">
                                <div class="text-center">
                                    <div
                                        class="mx-auto w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-primary-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-700 mb-2">Upload Foto Selfie</h3>
                                    <p class="text-sm text-gray-500 mb-4">Klik untuk mengambil atau memilih foto</p>
                                    <div
                                        class="inline-block bg-primary-500 text-white px-6 py-2 rounded-full text-sm font-semibold">
                                        Ambil Foto
                                    </div>
                                </div>
                            </div>
                            <input wire:model="selfie_photo" id="selfie_photo" type="file" accept="image/*" capture="user"
                                class="hidden">
                        </label>
                    @endif

                    <x-input-error :messages="$errors->get('selfie_photo')" class="mt-2" />

                    @if ($selfie_photo && !$preview_url)
                        <div class="mt-3 text-center">
                            <div class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses...
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Important Info -->
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-orange-900 mb-2">PENTING!</h4>
                            <ul class="text-xs text-orange-800 space-y-1">
                                <li>• <strong>Wajah Anda harus terlihat jelas</strong></li>
                                <li>• <strong>Pegang KTP di samping wajah Anda</strong></li>
                                <li>• <strong>Data di KTP harus terbaca dengan jelas</strong></li>
                                <li>• Pastikan tidak ada orang lain dalam foto</li>
                                <li>• Gunakan latar belakang yang bersih</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-blue-900 mb-2">Tips Foto Selfie:</h4>
                            <ul class="text-xs text-blue-800 space-y-1">
                                <li>• Ambil foto di tempat yang terang</li>
                                <li>• Pastikan wajah dan KTP tidak blur</li>
                                <li>• Pegang KTP di dekat wajah Anda</li>
                                <li>• Hindari menggunakan filter atau edit foto</li>
                                <li>• Format: JPG, PNG (Max 2MB)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Example Images -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Good Example -->
                    <div class="bg-white rounded-xl p-3 border-2 border-green-500">
                        <div
                            class="aspect-square bg-gradient-to-br from-green-100 to-green-200 rounded-lg flex items-center justify-center mb-2">
                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-xs text-green-700 font-bold text-center">✓ Benar</p>
                        <p class="text-xs text-gray-600 text-center mt-1">Wajah & KTP jelas</p>
                    </div>

                    <!-- Bad Example -->
                    <div class="bg-white rounded-xl p-3 border-2 border-red-500">
                        <div
                            class="aspect-square bg-gradient-to-br from-red-100 to-red-200 rounded-lg flex items-center justify-center mb-2">
                            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <p class="text-xs text-red-700 font-bold text-center">✗ Salah</p>
                        <p class="text-xs text-gray-600 text-center mt-1">Blur atau gelap</p>
                    </div>
                </div>
            </div>

            <!-- Next Button -->
            <div class="pt-6 pb-2">
                <button type="submit" wire:loading.attr="disabled" :disabled="!$wire.selfie_photo"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Lanjutkan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>