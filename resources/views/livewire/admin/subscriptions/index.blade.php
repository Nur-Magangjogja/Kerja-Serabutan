@extends('layouts.admin')

@section('page-title', 'Langganan Mitra')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center max-w-xl mx-auto">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Pengelolaan Langganan</h1>
        <p class="text-sm text-gray-500 mt-2">Sistem saat ini menggunakan skema bagi hasil/biaya admin per transaksi bantuan secara otomatis tanpa biaya langganan bulanan.</p>
        <div class="mt-6">
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
