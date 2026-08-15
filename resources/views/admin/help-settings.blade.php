@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pengaturan Bantuan</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola nominal minimal bantuan dan biaya administrasi platform.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                Admin Panel
            </span>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <label for="min_help_nominal" class="block text-sm font-semibold text-gray-700 mb-2">
                    Minimal Nominal Bantuan (Rp)
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" wire:model="min_help_nominal" id="min_help_nominal"
                        class="block w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 text-sm font-medium"
                        placeholder="10000" min="0" step="1000">
                </div>
                @error('min_help_nominal') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="mt-1.5 text-xs text-gray-500">Nominal terendah yang dapat diminta customer saat membuat bantuan baru.</p>
            </div>

            <div>
                <label for="admin_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                    Biaya Admin Platform (Rp)
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" wire:model="admin_fee" id="admin_fee"
                        class="block w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 text-sm font-medium"
                        placeholder="0" min="0" step="500">
                </div>
                @error('admin_fee') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="mt-1.5 text-xs text-gray-500">Biaya layanan yang dikenakan per transaksi bantuan.</p>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition duration-150 ease-in-out">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
