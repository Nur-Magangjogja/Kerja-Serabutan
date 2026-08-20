@extends('layouts.admin')

@section('page-title', 'Pengaturan')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <!-- Sub-navigation tabs -->
    <x-admin-settings-nav />

    <!-- Card: Pengaturan Tema Tampilan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-8 shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-200 flex flex-col items-center justify-center text-center space-y-4 w-full">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Pilih Mode Tampilan</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sesuaikan tema panel admin sesuai kenyamanan Anda.</p>
        </div>
        <div class="w-full flex justify-center">
            <x-theme-switcher />
        </div>
    </div>
</div>
@endsection
