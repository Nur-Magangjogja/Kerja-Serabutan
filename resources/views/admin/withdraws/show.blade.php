@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Permintaan Withdraw #{{ $withdraw->id }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Informasi lengkap penarikan dana mitra</p>
        </div>
        <a href="{{ route('admin.withdraws.index') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    {{-- ===== Main Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">ID Withdraw</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white font-mono">#{{ $withdraw->id }}</p>
            </div>
            @php
            $stClass = match($withdraw->status) {
                'pending'    => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                'processing' => 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400',
                'success'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                default      => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
            };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $stClass }}">
                {{ ucfirst($withdraw->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <p class="text-xs text-gray-400 dark:text-gray-500">Mitra</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5">{{ $withdraw->user?->name ?? '—' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">ID User: #{{ $withdraw->user_id }}</p>
            </div>

            <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <p class="text-xs text-gray-400 dark:text-gray-500">Saldo Saat Ini</p>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">Rp {{ number_format($withdraw->user?->balance ?? 0, 0, ',', '.') }}</p>
            </div>

            <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <p class="text-xs text-gray-400 dark:text-gray-500">Jumlah Permintaan</p>
                <p class="text-xl font-bold text-primary-600 dark:text-primary-400 mt-0.5">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl space-y-2 text-sm">
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Bank & Rekening Tujuan</p>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $withdraw->bank_code }} • {{ $withdraw->account_number }}</p>
            </div>
            @if($withdraw->description)
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Keterangan</p>
                <p class="text-gray-700 dark:text-gray-300">{{ $withdraw->description }}</p>
            </div>
            @endif
        </div>

        <div class="text-xs text-gray-400 dark:text-gray-500 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
            <span>Dibuat: {{ optional($withdraw->created_at)->format('d M Y, H:i') }} WIB</span>
            @if($withdraw->processed_at)
                <span>Diproses: {{ $withdraw->processed_at->format('d M Y, H:i') }} WIB</span>
            @endif
            @if($withdraw->external_id)
                <span>Ref: <code class="font-mono text-gray-600 dark:text-gray-400">{{ $withdraw->external_id }}</code></span>
            @endif
        </div>
    </div>
</div>
@endsection