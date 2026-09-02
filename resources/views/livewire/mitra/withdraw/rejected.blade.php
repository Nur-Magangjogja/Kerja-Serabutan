@extends('layouts.mitra')

@section('content')
    <div class="max-w-md mx-auto p-4 sm:p-6 pb-24">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 text-center transition-colors">
            <div class="w-16 h-16 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xs">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h1 class="text-xl font-black text-gray-900 dark:text-white">Penarikan Ditolak</h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Permintaan penarikan Anda tidak dapat diproses oleh sistem.</p>

            <div class="mt-5 text-left bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 space-y-2.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Jumlah</span>
                    <span class="font-extrabold text-gray-900 dark:text-white">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Bank</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ strtoupper($withdraw->bank_code) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Rekening</span>
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ $withdraw->account_number }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tanggal Diproses</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ optional($withdraw->processed_at)->format('d M Y, H:i') }} WIB</span>
                </div>
                <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 text-xs">
                    <div class="font-bold text-gray-700 dark:text-gray-300 mb-0.5">Alasan Penolakan:</div>
                    <div class="text-rose-600 dark:text-rose-400 font-medium leading-relaxed">{{ $withdraw->description ?? 'Tidak ada catatan tambahan dari admin.' }}</div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('mitra.withdraw.history') }}"
                    class="flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold text-center transition shadow-xs">Lihat Riwayat</a>
                <a href="{{ route('mitra.withdraw.form', ['force' => 1]) }}"
                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold text-center transition">Ajukan Ulang</a>
            </div>
        </div>
    </div>
@endsection