<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function continueToDashboard(): void
    {
        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="bg-gradient-to-br from-green-400 via-green-500 to-green-600 flex flex-col overflow-hidden shadow-2xl"
    style="height: 100vh; max-height: 100vh;">

    <!-- Success Animation Container -->
    <div class="flex-1 flex flex-col bg-gray-50 rounded-t-[2.5rem] overflow-y-auto px-6 py-8">
        <div class="flex-1 flex flex-col items-center justify-center text-center">
            <!-- Success Icon -->
            <div class="mb-6 relative">
                <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center animate-bounce">
                    <svg class="w-20 h-20 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <!-- Confetti Effect -->
                <div class="absolute -top-4 -left-4 text-4xl animate-ping">🎉</div>
                <div class="absolute -top-4 -right-4 text-4xl animate-ping" style="animation-delay: 0.1s;">🎊</div>
                <div class="absolute -bottom-4 -left-4 text-4xl animate-ping" style="animation-delay: 0.2s;">✨</div>
                <div class="absolute -bottom-4 -right-4 text-4xl animate-ping" style="animation-delay: 0.3s;">🌟</div>
            </div>

            <!-- Success Message -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Registrasi Berhasil!</h1>
            <p class="text-gray-600 mb-8 max-w-md">
                Akun Anda telah berhasil dibuat. Saat ini akun Anda sedang menunggu verifikasi dari admin. Anda akan
                dapat login setelah admin menyetujui akun Anda.
            </p>

            <!-- Success Details -->
            <div class="bg-white rounded-2xl p-6 shadow-lg mb-8 max-w-md w-full">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <h3 class="font-semibold text-gray-900 text-sm">Data KTP Tersimpan</h3>
                            <p class="text-xs text-gray-600">Informasi identitas Anda aman</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <h3 class="font-semibold text-gray-900 text-sm">Foto Terverifikasi</h3>
                            <p class="text-xs text-gray-600">KTP dan selfie sudah diupload</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <h3 class="font-semibold text-gray-900 text-sm">Menunggu Verifikasi</h3>
                            <p class="text-xs text-gray-600">Akun akan aktif setelah disetujui admin</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8 max-w-md w-full">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1 text-left">
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Langkah Selanjutnya</h4>
                        <p class="text-xs text-blue-800">Tunggu email konfirmasi dari admin atau coba login dalam 1x24
                            jam. Setelah disetujui, Anda dapat menggunakan aplikasi untuk mendapatkan atau memberikan
                            bantuan.</p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('login') }}"
                class="block w-full max-w-md bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition text-center">
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
    <style>
        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-5%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }

            50% {
                transform: translateY(0);
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }
    </style>
</div>