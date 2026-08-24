@extends('layouts.app')

@section('content')
<div class="min-h-screen text-gray-900 dark:text-gray-100 flex items-center justify-center px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 max-w-md w-full border border-gray-100 dark:border-gray-700 shadow-xl text-center space-y-5">
        <div class="w-16 h-16 bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto text-3xl shadow-sm">
            ✕
        </div>

        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Pengajuan Penarikan Dibatalkan</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Permintaan penarikan dana tidak dapat diproses oleh Admin. Saldo Anda aman dan tidak terpotong.
            </p>
        </div>

        <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/80 text-left space-y-2.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-400">Nominal Pengajuan</span>
                <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Tujuan</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $withdraw->bank_code }} • {{ $withdraw->account_number }}</span>
            </div>
            @if($withdraw->description)
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-400 block mb-0.5">Catatan Admin:</span>
                <p class="text-gray-700 dark:text-gray-300 italic">{{ $withdraw->description }}</p>
            </div>
            @endif
        </div>

        <div class="space-y-2 pt-2">
            <a href="{{ route('customer.withdraw.form', ['force' => 1]) }}" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow-sm block text-center">
                Ajukan Penarikan Baru
            </a>
            <a href="{{ route('customer.dashboard') }}" class="w-full py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition block text-center">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
