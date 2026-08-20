@php
    $title = 'Akun Anda Ditolak';
@endphp

@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12">
    <div class="w-full max-w-xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-xl font-semibold text-gray-900">Akun Anda Ditolak</h1>
                    <p class="text-sm text-gray-600 mt-1">Maaf — registrasi Anda tidak disetujui oleh admin.</p>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 border border-gray-100 rounded-md p-4">
                <h3 class="text-sm font-semibold text-gray-700">Alasan Penolakan</h3>
                <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ $registration->rejection_reason ?? 'Tidak ada alasan diberikan oleh admin.' }}</div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center sm:items-center justify-end gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-md shadow-sm">Kembali</a>
                {{-- @if (Route::has('contact'))
                    <a href="{{ route('contact') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow">Hubungi Admin</a>
                @else
                    <a href="{{ url('/') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow">Hubungi Admin</a>
                @endif --}}
            </div>

            <p class="mt-4 text-xs text-gray-500">Jika Anda merasa ini keliru, silakan hubungi admin untuk klarifikasi.</p>
        </div>
    </div>
</div>
@endsection
