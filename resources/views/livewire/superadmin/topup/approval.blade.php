@php
    $title = 'Manajemen & Approval Top-Up Saldo';
    $breadcrumb = 'Super Admin / Approval Top-Up';
@endphp

<div x-data="approvalModal()" @confirm-approve.window="openFromEvent($event)">
    <div wire:poll.15s.visible>
        {{-- ===== Page Header ===== --}}
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen & Approval Top-Up Saldo</h1>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Verifikasi, approve, dan kelola pembatalan request top-up customer seluruh Indonesia</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-xs">
                <svg class="w-3.5 h-3.5 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>
                <span class="font-semibold">Auto-refresh 15 dtk</span>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl text-sm shadow-xs">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-2xl text-sm shadow-xs">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- ===== Unified Status Filter & Controls Toolbar ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 mb-5 shadow-xs space-y-3.5">
            {{-- Top Row: Status Filter Tabs with Counts --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                {{-- Menunggu --}}
                <button wire:click="filterByStatus('waiting_approval')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'waiting_approval' ? 'bg-amber-500 text-white shadow-sm ring-2 ring-amber-400/20' : 'bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60' }}">
                    <span class="w-2 h-2 rounded-full {{ $filterStatus === 'waiting_approval' ? 'bg-white' : 'bg-amber-500' }} animate-pulse"></span>
                    <span>Menunggu (Pending)</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold {{ $filterStatus === 'waiting_approval' ? 'bg-white/25 text-white' : 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300' }}">
                        {{ $totalPending }}
                    </span>
                </button>

                {{-- Disetujui --}}
                <button wire:click="filterByStatus('completed')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'completed' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-500/20' : 'bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60' }}">
                    <span class="w-2 h-2 rounded-full {{ $filterStatus === 'completed' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span>Disetujui / Selesai</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold {{ $filterStatus === 'completed' ? 'bg-white/25 text-white' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' }}">
                        {{ $totalCompleted }}
                    </span>
                </button>

                {{-- Dibatalkan / Fraud --}}
                <button wire:click="filterByStatus('cancelled')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'cancelled' ? 'bg-purple-600 text-white shadow-sm ring-2 ring-purple-500/20' : 'bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60' }}">
                    <span class="w-2 h-2 rounded-full {{ $filterStatus === 'cancelled' ? 'bg-white' : 'bg-purple-500' }}"></span>
                    <span>Dibatalkan / Fraud</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold {{ $filterStatus === 'cancelled' ? 'bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300' }}">
                        {{ $totalCancelled }}
                    </span>
                </button>

                {{-- Ditolak --}}
                <button wire:click="filterByStatus('rejected')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-sm ring-2 ring-rose-500/20' : 'bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60' }}">
                    <span class="w-2 h-2 rounded-full {{ $filterStatus === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                    <span>Ditolak</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold {{ $filterStatus === 'rejected' ? 'bg-white/25 text-white' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300' }}">
                        {{ $totalRejected }}
                    </span>
                </button>

                {{-- Semua --}}
                <button wire:click="filterByStatus('all')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm' : 'bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60' }}">
                    <span>Semua Request</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold {{ $filterStatus === 'all' ? 'bg-white/25 dark:bg-slate-900/20 text-white dark:text-slate-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $totalAll }}
                    </span>
                </button>
            </div>

            {{-- Bottom Row: Search & City Filter Controls --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                {{-- Search Bar --}}
                <div class="relative flex-1 min-w-0">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama customer, email, kode request, no. telepon..."
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-gray-800 outline-none transition">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    @if($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-0.5 rounded-md transition" title="Hapus pencarian">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>

                {{-- City Filter --}}
                @if(isset($cities) && $cities->count() > 0)
                    <div class="relative flex-shrink-0 w-full sm:w-auto">
                        <select wire:model.live="cityFilter"
                            class="w-full sm:w-auto py-2 pl-3.5 pr-8 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 outline-none transition cursor-pointer shadow-2xs">
                            <option value="all">Semua Wilayah (Nasional)</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== Table Card ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden">#</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Wilayah</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Kode Request</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Total Bayar</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">No. Pengguna</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Waktu</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500 hidden">#{{ $transaction->id }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-9 w-9 flex-shrink-0">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-[#0098e7] to-[#0060b0] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                                {{ substr($transaction->user->name ?? 'U', 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $transaction->user->name ?? ($transaction->customer_name ?? 'User') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $transaction->customer_email ?? ($transaction->user->email ?? '—') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php
                                        $txUser = $transaction->user;
                                        $txCityName = $txUser?->city_name ?? (is_object($txUser?->city) ? $txUser?->city?->name : ($txUser?->city ?? null));
                                    @endphp
                                    @if($txCityName)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                            <svg class="w-3.5 h-3.5 text-primary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $txCityName }}</span>
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Semua Wilayah</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded font-mono">{{ $transaction->request_code ?? '#' . $transaction->id }}</code>
                                </td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <p class="text-sm font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-xs font-semibold">
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $transaction->customer_phone ?? ($transaction->user->phone ?? '—') }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($transaction->status === 'waiting_approval' || $transaction->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                    @elseif($transaction->status === 'completed' || $transaction->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            Disetujui
                                        </span>
                                    @elseif($transaction->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50">
                                            Dibatalkan / Fraud
                                        </span>
                                    @elseif($transaction->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-xs">
                                    <p class="text-gray-700 dark:text-gray-200 font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                                    @if ($transaction->approvedBy)
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Oleh: {{ $transaction->approvedBy->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Lihat Bukti -->
                                        <button type="button" wire:click="viewDetail({{ $transaction->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition cursor-pointer" title="Lihat Bukti & Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>

                                        @if($transaction->status === 'waiting_approval' || $transaction->status === 'pending')
                                            <!-- Tolak -->
                                            <button type="button" wire:click="openRejectModal({{ $transaction->id }})"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition cursor-pointer" title="Tolak Request">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                            <!-- Approve -->
                                            <button type="button" wire:loading.attr="disabled"
                                                data-id="{{ $transaction->id }}"
                                                data-name="{{ $transaction->user->name ?? ($transaction->customer_name ?? 'User') }}"
                                                data-amount="{{ 'Rp ' . number_format($transaction->amount, 0, ',', '.') }}"
                                                @click.prevent="openFromEl($event)"
                                                class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition cursor-pointer" title="Setujui (Approve)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        @elseif($transaction->status === 'completed' || $transaction->status === 'approved')
                                            <!-- Batalkan Approval (Kasus Barcode Salah / Penipuan / Fraud) -->
                                            <button type="button" wire:click="openCancelApprovalModal({{ $transaction->id }})"
                                                wire:loading.attr="disabled"
                                                class="px-2 py-1 rounded-lg text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800 transition cursor-pointer flex items-center gap-1"
                                                title="Batalkan top-up dan tarik kembali saldo (Kasus Barcode Palsu / Penipuan)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                <span>Batalkan</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700/60 flex items-center justify-center mb-3 text-gray-400">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak Ada Transaksi</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tidak ditemukan transaksi top-up pada filter ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $transactions->links('vendor.pagination.superadmin') }}
            </div>
        </div>
    </div>

    {{-- ===== Detail Modal ===== --}}
    @if ($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative border border-gray-100 dark:border-gray-700" @click.stop>
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4 rounded-t-3xl z-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Detail Transaksi Top-Up</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">ID: #{{ $selectedTransaction->id }} • {{ $selectedTransaction->request_code ?? 'Manual' }}</p>
                    </div>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-700/40 rounded-2xl p-4 grid grid-cols-2 gap-4 border border-gray-100 dark:border-gray-700/60">
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Customer</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $selectedTransaction->user->name ?? $selectedTransaction->customer_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedTransaction->customer_email ?? $selectedTransaction->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Kota / Lokasi</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $selectedTransaction->user->city_name ?? (is_object($selectedTransaction->user->city ?? null) ? $selectedTransaction->user->city->name : ($selectedTransaction->user->city ?? '—')) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Nominal Top-Up</p>
                            <p class="text-base font-bold text-gray-900 dark:text-white">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</p>
                            @if(($selectedTransaction->admin_fee ?? 0) > 0)
                                <p class="text-[11px] text-gray-400 mt-0.5">Fee: Rp {{ number_format($selectedTransaction->admin_fee, 0, ',', '.') }}</p>
                            @else
                                <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">Bebas Pajak (Rp 0)</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Total Bayar</p>
                            <p class="text-base font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($selectedTransaction->total_payment, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Via {{ strtoupper($selectedTransaction->payment_method ?? 'QRIS') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Status Saat Ini</p>
                            <p class="text-sm font-bold {{ $selectedTransaction->status === 'completed' ? 'text-emerald-600' : ($selectedTransaction->status === 'cancelled' ? 'text-purple-600' : ($selectedTransaction->status === 'rejected' ? 'text-rose-600' : 'text-amber-600')) }}">
                                {{ strtoupper($selectedTransaction->status) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Waktu Pengajuan</p>
                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $selectedTransaction->created_at->format('d M Y, H:i:s') }}</p>
                        </div>
                    </div>

                    @if($selectedTransaction->rejection_reason)
                        <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl">
                            <p class="text-xs font-bold text-rose-800 dark:text-rose-300">Catatan / Alasan:</p>
                            <p class="text-xs text-rose-700 dark:text-rose-400 mt-0.5">{{ $selectedTransaction->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Proof Image -->
                    @if ($selectedTransaction->proof_of_payment)
                        <div>
                            <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">Bukti Transfer Customer:</p>
                            <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-black/5 dark:bg-black/20 text-center p-2">
                                <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}"
                                    class="w-full max-h-96 object-contain rounded-xl mx-auto" alt="Bukti Transfer"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2218%22 fill=%22%239ca3af%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
                            </div>
                        </div>
                    @else
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/30 rounded-2xl text-center text-gray-400 text-xs">
                            Bukti transfer tidak diunggah atau menggunakan pembayaran instan.
                        </div>
                    @endif

                    <!-- Actions in Modal -->
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-2.5">
                        @if($selectedTransaction->status === 'waiting_approval' || $selectedTransaction->status === 'pending')
                            <button type="button" 
                                wire:click="approve({{ $selectedTransaction->id }})" 
                                wire:loading.attr="disabled"
                                wire:target="approve({{ $selectedTransaction->id }})"
                                class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl transition cursor-pointer shadow-xs disabled:opacity-50 flex items-center justify-center gap-1.5">
                                <svg wire:loading wire:target="approve({{ $selectedTransaction->id }})" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg wire:loading.remove wire:target="approve({{ $selectedTransaction->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span wire:loading.remove wire:target="approve({{ $selectedTransaction->id }})">Setujui (Approve)</span>
                                <span wire:loading wire:target="approve({{ $selectedTransaction->id }})">Menyetujui...</span>
                            </button>
                            <button type="button" wire:click="openRejectModal({{ $selectedTransaction->id }})" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl transition cursor-pointer shadow-xs">
                                Tolak Request
                            </button>
                        @elseif($selectedTransaction->status === 'completed' || $selectedTransaction->status === 'approved')
                            <button type="button" wire:click="openCancelApprovalModal({{ $selectedTransaction->id }})" wire:loading.attr="disabled"
                                class="w-full px-4 py-2.5 text-xs sm:text-sm font-bold bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                <span>Batalkan Top-Up & Tarik Kembali Saldo (Kasus Penipuan / Barcode Salah)</span>
                            </button>
                        @endif
                        <button type="button" wire:click="closeModal"
                            class="w-full px-4 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Reject Modal ===== --}}
    @if ($showRejectModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[110] flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-3xl shadow-2xl w-full max-w-md p-6 z-[120] border border-gray-100 dark:border-gray-700" @click.stop>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold">Tolak Request Top-Up</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedTransaction->user->name ?? 'Customer' }} • Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                <form wire:submit.prevent="reject" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea wire:model="rejectionReason" rows="3"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500 transition"
                            placeholder="Contoh: Bukti transfer tidak terbaca / nominal transfer tidak sesuai..."></textarea>
                        @error('rejectionReason') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-xs font-bold bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition cursor-pointer shadow-xs">
                            Tolak Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== Cancel Approval Modal (Fraud / Barcode Salah) ===== --}}
    @if ($showCancelApprovalModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[110] flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-3xl shadow-2xl w-full max-w-lg p-6 z-[120] border border-rose-200 dark:border-rose-900/60" @click.stop>
                <div class="flex items-start gap-3.5 mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-rose-600 dark:text-rose-400">Batalkan Approval & Tarik Saldo</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Penanganan Kasus Barcode Palsu, Fiktif, atau Penipuan</p>
                    </div>
                </div>

                <div class="mb-4 p-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/80 rounded-2xl text-xs text-rose-900 dark:text-rose-300 space-y-1.5">
                    <p class="font-bold">⚠️ PERINGATAN SISTEM:</p>
                    <p>Anda akan membatalkan top-up untuk customer <strong>{{ $selectedTransaction->user->name ?? 'Customer' }}</strong> sebesar <strong>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</strong>.</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-800/90 dark:text-rose-400/90 pt-1">
                        <li>Saldo customer sebesar Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }} akan <strong>ditarik / dipotong kembali</strong>.</li>
                        <li>Status transaksi diubah menjadi <strong>Dibatalkan / Fraud</strong>.</li>
                        <li>Notifikasi resmi pembatalan akan dikirimkan ke akun customer.</li>
                    </ul>
                </div>
                
                <form wire:submit.prevent="cancelApproval" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                            Alasan Pembatalan / Indikasi Fraud <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="cancellationReason" rows="3"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500 transition"
                            placeholder="Contoh: Barcode salah / Bukti transfer palsu / Mutasi bank fiktif..."></textarea>
                        @error('cancellationReason') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            Tutup
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-xs font-extrabold bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition cursor-pointer shadow-xs flex items-center justify-center gap-1.5">
                            <span wire:loading.remove wire:target="cancelApproval">Ya, Batalkan & Tarik Saldo</span>
                            <span wire:loading wire:target="cancelApproval">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Approval Confirmation Modal (Alpine) -->
    <template x-teleport="body">
        <div x-cloak x-show="show" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" @click="close()" aria-hidden="true"></div>

            <div x-transition class="relative w-full max-w-sm mx-auto">
                <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 relative">
                        <button @click="close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="flex flex-col items-center text-center gap-3">
                            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <h3 class="text-base font-bold">Konfirmasi Approval Top-Up</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pastikan dana telah masuk ke mutasi rekening sebelum menyetujui:</p>

                            <div class="w-full bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60">
                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="name"></div>
                                <div class="text-base font-extrabold text-primary-600 dark:text-primary-400 mt-0.5" x-text="amount"></div>
                            </div>

                            <div class="mt-2 flex w-full items-center justify-between gap-2.5">
                                <button @click="close()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer transition">Batal</button>
                                <button @click="$wire.approve(id); close()" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 cursor-pointer shadow-xs transition">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
        function approvalModal() {
            return {
                show: false,
                id: null,
                name: '',
                amount: '',
                openFromEvent(e) {
                    const d = e.detail || {};
                    this.id = d.id ?? null;
                    this.name = d.name ?? '';
                    this.amount = d.amount ?? '';
                    this.show = true;
                },
                openFromEl(e) {
                    const el = e.currentTarget || e.target;
                    const id = el.dataset?.id ?? null;
                    const name = el.dataset?.name ?? '';
                    const amount = el.dataset?.amount ?? '';
                    this.id = id;
                    this.name = name;
                    this.amount = amount;
                    this.show = true;
                },
                close() {
                    this.show = false;
                    this.id = null;
                }
            }
        }
    </script>
</div>

