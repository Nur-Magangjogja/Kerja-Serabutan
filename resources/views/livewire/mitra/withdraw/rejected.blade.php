@extends('layouts.mitra')

@section('content')
    <div class="max-w-md mx-auto p-6">
        <div class="bg-white rounded shadow p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>

            <h1 class="mt-4 text-2xl font-semibold">Penarikan Ditolak</h1>
            <p class="mt-2 text-sm text-gray-600">Permintaan penarikan Anda tidak dapat diproses. Berikut detailnya.</p>

            <div class="mt-4 text-left bg-gray-50 border border-gray-100 rounded p-4">
                <div class="flex justify-between text-sm text-gray-600">
                    <div>Jumlah</div>
                    <div class="font-semibold">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>Bank</div>
                    <div class="font-semibold">{{ strtoupper($withdraw->bank_code) }}</div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>Rekening</div>
                    <div class="font-semibold">{{ $withdraw->account_number }}</div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>Tanggal Diproses</div>
                    <div class="font-semibold">{{ optional($withdraw->processed_at)->format('Y-m-d H:i') }}</div>
                </div>
                <div class="mt-4 text-sm text-gray-700">
                    <div class="font-medium">Alasan Pembatalan</div>
                    <div class="mt-1 text-gray-600">{{ $withdraw->description ?? 'Tidak ada catatan tambahan dari admin.' }}
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('mitra.withdraw.history') }}"
                    class="flex-1 inline-block px-4 py-2 bg-primary-600 text-white rounded text-center">Lihat Riwayat</a>
                <a href="{{ route('mitra.withdraw.form', ['force' => 1]) }}"
                    class="flex-1 inline-block px-4 py-2 bg-gray-200 text-gray-700 rounded text-center">Kembali
                </a>
            </div>
        </div>
    </div>
@endsection