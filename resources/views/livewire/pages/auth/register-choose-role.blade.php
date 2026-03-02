<?php

use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function choose($role): void
    {
        // Normalize accepted roles
        $allowed = ['customer', 'mitra'];
        $role = in_array($role, $allowed) ? $role : 'customer';

        Session::put('registration_role', $role);

        // Clear any previous registration UUID to start fresh
        Session::forget('registration_uuid');

        $this->redirect(route('register.step1'), navigate: true);
    }
}; ?>

<div class="w-full">
    <div class="bg-white rounded-2xl p-8 w-full shadow-lg max-w-md mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Peran Anda</h2>
        <p class="text-sm text-gray-600 mb-6">Pilih apakah Anda mendaftar sebagai Customer atau Mitra.</p>

        <div class="grid grid-cols-2 gap-4">
            <button wire:click="choose('customer')" type="button"
                class="py-4 px-3 bg-gray-50 rounded-xl border hover:border-primary-500 transition text-center">
                <div class="font-bold text-gray-900">Customer</div>
                <div class="text-xs text-gray-600 mt-1">Menggunakan layanan</div>
            </button>

            <button wire:click="choose('mitra')" type="button"
                class="py-4 px-3 bg-gray-50 rounded-xl border hover:border-primary-500 transition text-center">
                <div class="font-bold text-gray-900">Mitra</div>
                <div class="text-xs text-gray-600 mt-1">Menjadi penyedia layanan</div>
            </button>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-primary-600">Sudah punya akun? Login</a>
        </div>
    </div>
</div>