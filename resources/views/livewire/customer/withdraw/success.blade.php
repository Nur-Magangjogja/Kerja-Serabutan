@extends('layouts.app')

@section('content')
<div class="min-h-screen text-gray-900 dark:text-gray-100 flex items-center justify-center px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 max-w-md w-full border border-gray-100 dark:border-gray-700 shadow-xl text-center space-y-5">
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mx-auto text-3xl shadow-sm">
            ✓
        </div>

        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Penarikan Dana Berhasil!</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Dana Anda telah berhasil ditransfer oleh Admin ke rekening / e-wallet tujuan.
            </p>
        </div>

        <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/80 text-left space-y-2.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-400">Nominal Penarikan</span>
                <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Bank / E-Wallet</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $withdraw->bank_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">No. Rekening / Akun</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $withdraw->account_number }}</span>
            </div>
            @if($withdraw->description)
            <div class="flex justify-between">
                <span class="text-gray-400">Penerima</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ str_replace('A/N: ', '', $withdraw->description) }}</span>
            </div>
            @endif
            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-400">Waktu Selesai</span>
                <span class="text-gray-600 dark:text-gray-300">{{ optional($withdraw->processed_at ?? $withdraw->updated_at)->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <a href="{{ route('customer.dashboard') }}" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow-sm block text-center">
                Kembali ke Dashboard
            </a>
            <a href="{{ route('customer.withdraw.history') }}" class="w-full py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition block text-center">
                Lihat Riwayat Penarikan
            </a>
        </div>
    </div>
</div>
@endsection
