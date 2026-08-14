<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function startRegistration(): void
    {
        // Redirect ke step 1 untuk registrasi multi-step
        $this->redirect(route('register.choose-role'), navigate: true);
    }
}; ?>

<div>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Akun Baru</h1>
        <p class="text-sm text-gray-600 mt-1">Lengkapi data untuk verifikasi</p>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold text-gray-900 mb-3">Yang Perlu Disiapkan:</h3>
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-bold text-sm">1</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Data KTP</h4>
                        <p class="text-xs text-gray-600">NIK, nama lengkap, alamat sesuai KTP</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-sm">2</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Foto KTP</h4>
                        <p class="text-xs text-gray-600">Foto KTP yang jelas</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 font-bold text-sm">3</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Foto Selfie + KTP</h4>
                        <p class="text-xs text-gray-600">Foto selfie sambil memegang KTP</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-orange-600 font-bold text-sm">4</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Email & Password</h4>
                        <p class="text-xs text-gray-600">Email aktif untuk login</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
            <h4 class="font-bold text-blue-900">Informasi Penting</h4>
            <p class="mt-1">Data yang Anda berikan akan digunakan untuk verifikasi identitas. Pastikan benar dan sesuai KTP.</p>
        </div>

        <div class="space-y-3">
            <button wire:click="startRegistration" type="button" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3.5 rounded-full shadow transition">Mulai Pendaftaran</button>

            <div class="text-center">
                <p class="text-sm text-gray-600">Sudah punya akun? <a href="{{ route('login') }}" wire:navigate class="text-primary-600 font-semibold">Login</a></p>
            </div>
        </div>
    </div>
</div>