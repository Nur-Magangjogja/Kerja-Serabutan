@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 p-4 sm:p-6">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center">
                @if($transaction)
                    <div class="mx-auto w-16 h-16 bg-emerald-50 dark:bg-emerald-950/60 rounded-full flex items-center justify-center mb-4 border border-emerald-200 dark:border-emerald-800/60">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Pembayaran Berhasil</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Topup Anda telah berhasil diproses.</p>

                    <div class="text-left bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 p-4 rounded-xl mb-4">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                            <span>Order ID</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $transaction->order_id }}</span>
                        </div>
                        <div class="flex justify-between mt-2.5 text-xs text-gray-600 dark:text-gray-400">
                            <span>Jumlah</span>
                            <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mt-2.5 text-xs text-gray-600 dark:text-gray-400">
                            <span>Status</span>
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ ucfirst($transaction->status) }}</span>
                        </div>
                    </div>
                @else
                    <div class="mx-auto w-16 h-16 bg-amber-50 dark:bg-amber-950/60 rounded-full flex items-center justify-center mb-4 border border-amber-200 dark:border-amber-800/60">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Transaksi Tidak Ditemukan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Order ID: {{ $order_id ?? '-' }}</p>
                @endif

                <div class="space-y-2 mt-4">
                    <a href="{{ route('dashboard') }}"
                        class="block w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium text-center rounded-xl shadow-xs">Kembali ke Dashboard</a>
                    <a href="{{ route('customer.transactions.index') }}"
                        class="block w-full py-2.5 px-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 text-center font-medium rounded-xl">Lihat Transaksi</a>
                </div>
            </div>
        </div>
    </div>
@endsection