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
            'ktp_photo' => 'image|max:2048', // 2MB Max
        ]);

        $this->preview_url = $this->ktp_photo->temporaryUrl();
    }

    public function nextStep(): void
    {
        $this->validate([
            'ktp_photo' => ['required', 'image', 'max:2048'],
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

<div class="w-full">
    <div class="bg-white rounded-2xl p-6 w-full shadow-lg max-w-md mx-auto">
        <div class="flex items-center justify-between mb-4">
            <button wire:click="previousStep" class="text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="text-lg font-bold text-gray-900">Upload KTP</h1>
            <div class="w-6"></div>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
        </div>
        <p class="text-sm text-gray-500 mb-4 text-center">Step 2 of 4</p>

        <form wire:submit="nextStep" class="">
            <div>
                <p class="text-sm text-gray-600 mb-4">Upload foto KTP Anda dengan jelas</p>

                <!-- Upload Area -->
                <div class="mb-6">
                    @if ($preview_url)
                        <!-- Preview Image -->
                        <div class="relative bg-white rounded-2xl overflow-hidden shadow-lg border-2 border-primary-500">
                            <img src="{{ $preview_url }}" alt="Preview KTP" class="w-full h-auto">
                            <button type="button" wire:click="$set('ktp_photo', null); $set('preview_url', null)"
                                class="absolute top-3 right-3 bg-red-500 text-white rounded-full p-2 shadow-lg hover:bg-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <!-- Upload Placeholder -->
                        <label for="ktp_photo" class="block cursor-pointer">
                            <div
                                class="bg-white rounded-2xl border-2 border-dashed border-gray-300 hover:border-primary-500 transition p-8">
                                <div class="text-center">
                                    <div
                                        class="mx-auto w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-primary-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-700 mb-2">Upload Foto KTP</h3>
                                    <p class="text-sm text-gray-500 mb-4">Klik untuk memilih foto atau drag & drop</p>
                                    <div
                                        class="inline-block bg-primary-500 text-white px-6 py-2 rounded-full text-sm font-semibold">
                                        Pilih Foto
                                    </div>
                                </div>
                            </div>
                            <input wire:model="ktp_photo" id="ktp_photo" type="file" accept="image/*" class="hidden">
                        </label>
                    @endif

                    <x-input-error :messages="$errors->get('ktp_photo')" class="mt-2" />

                    @if ($ktp_photo && !$preview_url)
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
                            <h4 class="text-sm font-bold text-blue-900 mb-2">Tips Upload Foto KTP:</h4>
                            <ul class="text-xs text-blue-800 space-y-1">
                                <li>• Pastikan foto KTP terlihat jelas dan tidak buram</li>
                                <li>• Semua teks di KTP harus terbaca dengan baik</li>
                                <li>• Hindari pantulan cahaya atau bayangan</li>
                                <li>• Format: JPG, PNG (Max 2MB)</li>
                                <li>• Ambil foto dengan pencahayaan yang cukup</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Example Image -->
                <div class="bg-gray-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-700 mb-3">Contoh Foto KTP yang Baik:</p>
                    <div class="bg-white rounded-lg p-3 border-2 border-green-500">
                        <div
                            class="aspect-video bg-gradient-to-br from-gray-200 to-gray-300 rounded flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <p class="text-xs text-green-600 font-semibold mt-2 text-center">✓ Jelas & Terbaca</p>
                    </div>
                </div>
            </div>

            <!-- Next Button -->
            <div class="pt-6 pb-2">
                <button type="submit" wire:loading.attr="disabled" :disabled="!$wire.ktp_photo"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Lanjutkan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>