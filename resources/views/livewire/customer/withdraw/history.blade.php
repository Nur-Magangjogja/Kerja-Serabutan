@extends('layouts.app')

@section('content')
<div class="min-h-screen text-gray-900 dark:text-gray-100 pb-20">
    <!-- Header Bar -->
    <div class="px-4 py-3.5 bg-gradient-to-r from-[#0098e7] via-[#0077cc] to-[#0060b0] text-white shadow-xs rounded-b-2xl">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.withdraw.form') }}" class="p-2 -ml-1 hover:bg-white/15 rounded-xl transition cursor-pointer" aria-label="Kembali ke Form">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="text-center">
                <h1 class="text-sm font-bold tracking-tight">Riwayat Tarik Saldo</h1>
                <p class="text-[11px] text-white/80">Daftar permintaan pencairan dana Anda</p>
            </div>

            <a href="{{ route('customer.dashboard') }}" class="p-2 -mr-1 hover:bg-white/15 rounded-xl transition cursor-pointer" title="Dashboard" aria-label="Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-xl mx-auto px-4 pt-4 space-y-4">
        <!-- Saldo Ringkasan -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider block">Saldo Tersedia</span>
                <div class="text-lg font-black text-primary-600 dark:text-sky-400 mt-0.5">
                    Rp {{ number_format($user->balance ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <a href="{{ route('customer.withdraw.form', ['force' => 1]) }}" class="px-3 py-2 bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800/60 text-primary-600 dark:text-sky-400 hover:bg-primary-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tarik Dana</span>
            </a>
        </div>

        @if(session('status'))
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-xl shadow-xs text-xs">
                {{ session('status') }}
            </div>
        @endif

        <!-- Daftar Riwayat -->
        <div class="space-y-3">
            @forelse($history as $item)
                @php
                    $isSuccess = $item->status === \App\Models\WithdrawRequest::STATUS_SUCCESS;
                    $isPending = $item->status === \App\Models\WithdrawRequest::STATUS_PENDING;
                    $isProcessing = $item->status === \App\Models\WithdrawRequest::STATUS_PROCESSING;
                    $isFailed = $item->status === \App\Models\WithdrawRequest::STATUS_FAILED;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold {{ $isSuccess ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600' : ($isFailed ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600') }}">
                                {{ $isSuccess ? '✓' : ($isFailed ? '✕' : '⏳') }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white">
                                    {{ $item->bank_code }} • {{ $item->account_number }}
                                </div>
                                <div class="text-[11px] text-gray-400 dark:text-gray-500">
                                    {{ $item->description ? str_replace('A/N: ', '', $item->description) : '-' }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($isSuccess)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                Berhasil
                            </span>
                        @elseif($isFailed)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                Dibatalkan
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                                Menunggu
                            </span>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-gray-50 dark:border-gray-700/60 flex items-center justify-between">
                        <div class="text-[11px] text-gray-400 dark:text-gray-500">
                            {{ $item->created_at->translatedFormat('d M Y, H:i') }} WIB
                        </div>
                        <div class="text-sm font-black text-gray-900 dark:text-white">
                            - Rp {{ number_format($item->amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-100 dark:border-gray-700 text-center space-y-3">
                    <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                        📭
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat Tarik Saldo</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Anda belum pernah melakukan pengajuan penarikan dana.</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('customer.withdraw.form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition">
                            Tarik Saldo Sekarang
                        </a>
                    </div>
                </div>
            @endforelse

            <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                {{ $history->links('vendor.pagination.superadmin') }}
            </div>
        </div>
    </div>
</div>
@endsection
