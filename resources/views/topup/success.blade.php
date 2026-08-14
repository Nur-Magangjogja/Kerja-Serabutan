@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
            <div class="text-center">
                @if($transaction)
                    <div class="mx-auto w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold mb-1">Pembayaran Berhasil</h1>
                    <p class="text-sm text-gray-500 mb-4">Topup Anda telah berhasil diproses.</p>

                    <div class="text-left bg-gray-50 p-3 rounded mb-4">
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Order ID</span>
                            <span class="font-medium">{{ $transaction->order_id }}</span>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-600">
                            <span>Jumlah</span>
                            <span class="font-bold">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-600">
                            <span>Status</span>
                            <span class="text-sm text-green-600">{{ ucfirst($transaction->status) }}</span>
                        </div>
                    </div>
                @else
                    <div class="mx-auto w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold mb-1">Transaksi Tidak Ditemukan</h1>
                    <p class="text-sm text-gray-500 mb-4">Order ID: {{ $order_id ?? '-' }}</p>
                @endif

                <div class="space-y-2">
                    <a href="{{ route('dashboard') }}"
                        class="block w-full py-2 px-4 bg-primary-500 text-white text-center rounded">Kembali ke
                        Dashboard</a>
                    <a href="{{ route('customer.transactions.index') }}"
                        class="block w-full py-2 px-4 border border-gray-200 text-center rounded">Lihat Transaksi</a>
                </div>
            </div>
        </div>
    </div>

@endsection