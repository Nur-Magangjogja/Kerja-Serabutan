@php
    $title = 'Financial Report';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Financial Report</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Laporan keuangan dan transaksi sistem</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- 1. CSV Export -->
            <button wire:click="exportCsv" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-700 hover:bg-slate-800 active:bg-slate-900 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs hover:shadow active:scale-[0.98] disabled:opacity-50 cursor-pointer"
                title="Unduh Data Format CSV">
                <svg wire:loading.remove wire:target="exportCsv" class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg wire:loading wire:target="exportCsv" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>CSV</span>
            </button>

            <!-- 2. XLSX Export -->
            <button wire:click="exportXlsx" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs hover:shadow active:scale-[0.98] disabled:opacity-50 cursor-pointer"
                title="Unduh Spreadsheet Format Excel XLSX">
                <svg wire:loading.remove wire:target="exportXlsx, exportExcel" class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg wire:loading wire:target="exportXlsx, exportExcel" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>XLSX</span>
            </button>

            <!-- 3. PDF Export -->
            <button wire:click="exportPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs hover:shadow active:scale-[0.98] disabled:opacity-50 cursor-pointer"
                title="Unduh Laporan Format PDF">
                <svg wire:loading.remove wire:target="exportPdf" class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <svg wire:loading wire:target="exportPdf" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>PDF</span>
            </button>
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
        {{-- Card 1: Top Up --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Deposit Masuk (Top Up)</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($totalTopup, 0, ',', '.') }}</p>
                <p class="text-[10px] text-emerald-600 font-medium truncate">Saldo masuk Customer</p>
            </div>
        </div>

        {{-- Card 2: Platform Fee --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-purple-100 dark:border-purple-900/40 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-purple-700 dark:text-purple-400 font-semibold truncate">Komisi Platform (Kas)</p>
                <p class="text-base sm:text-lg font-bold text-purple-900 dark:text-purple-200 truncate">Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}</p>
                <p class="text-[10px] text-purple-500 font-medium truncate">Pendapatan bersih platform</p>
            </div>
        </div>

        {{-- Card 3: Earning Mitra --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Earning Bersih Mitra</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($totalEarning, 0, ',', '.') }}</p>
                <p class="text-[10px] text-indigo-600 font-medium truncate">Hasil kerja diterima Mitra</p>
            </div>
        </div>

        {{-- Card 4: Withdraw --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Penarikan Dana (Withdraw)</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($totalWithdraw, 0, ',', '.') }}</p>
                <p class="text-[10px] text-rose-600 font-medium truncate">Keluar ke rekening bank Mitra</p>
            </div>
        </div>
    </div>

    {{-- Secondary Metric Pills --}}
    <div class="flex flex-wrap gap-2.5 mb-5 text-xs">
        <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 rounded-xl px-3.5 py-2 flex items-center gap-1.5 font-medium shadow-xs">
            <span>🔒 Total Escrow Lock:</span>
            <span class="font-bold">Rp {{ number_format($totalEscrow, 0, ',', '.') }}</span>
        </div>
        <div class="bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60 rounded-xl px-3.5 py-2 flex items-center gap-1.5 font-medium shadow-xs">
            <span>⚠️ Denda Pelanggaran:</span>
            <span class="font-bold">Rp {{ number_format($totalPenalty, 0, ',', '.') }}</span>
        </div>
        <div class="bg-cyan-50 dark:bg-cyan-900/30 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60 rounded-xl px-3.5 py-2 flex items-center gap-1.5 font-medium shadow-xs">
            <span>↩️ Total Refund:</span>
            <span class="font-bold">Rp {{ number_format($totalRefund, 0, ',', '.') }}</span>
        </div>
        <div class="bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2 flex items-center gap-1.5 font-medium ml-auto shadow-xs">
            <span>📊 Total Log:</span>
            <span class="font-bold">{{ number_format($totalTransactions) }} baris</span>
        </div>
    </div>

    {{-- ===== Filter + Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        {{-- Filter bar --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.debounce.400ms="search" type="text" placeholder="Cari user, email, atau ref..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <select wire:model="type" wire:change="$refresh"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Tipe Transaksi</option>
                    <option value="topup">Top Up (Deposit Masuk)</option>
                    <option value="escrow_lock">Escrow Lock (Dana Ditahan)</option>
                    <option value="earning">Earning Mitra (Bayaran Masuk)</option>
                    <option value="platform_fee">Komisi Platform (Kas Masuk)</option>
                    <option value="withdraw">Withdraw (Pencairan Mitra)</option>
                    <option value="refund">Refund (Pengembalian 100%)</option>
                    <option value="penalty">Denda Pembatalan (Penalty)</option>
                    <option value="deduction">Potongan Biasa (Deduction)</option>
                </select>
                <input wire:model="from" type="date"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <input wire:model="to" type="date"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button wire:click="$refresh"
                    class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors cursor-pointer">
                    Filter
                </button>
                <select wire:model="perPage" wire:change="$refresh"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="10">10 / hal</option>
                    <option value="15">15 / hal</option>
                    <option value="30">30 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
                <div wire:loading class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    Memuat...
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna / Entitas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe & Aliran Dana</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Referensi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($transactions as $t)
                    @php
                    $typeColor = match($t->type ?? '') {
                        'topup'        => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60',
                        'escrow_lock'  => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60',
                        'earning'      => 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60',
                        'platform_fee' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60',
                        'withdraw'     => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60',
                        'refund'       => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60',
                        'penalty'      => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border border-red-300 dark:border-red-700/60',
                        'deduction'    => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60',
                        default        => 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600',
                    };
                    $statusColor = match($t->status ?? 'ok') {
                        'approved', 'success', 'ok', 'completed' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                        'pending', 'waiting_approval'            => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                        'rejected', 'failed'                     => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                        default                                  => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                    };
                    $isPositive = in_array($t->type, ['topup', 'earning', 'platform_fee', 'refund']);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $t->created_at }}</td>
                        <td class="px-4 py-3.5">
                            @if($t->user)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($t->user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $t->user->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $t->user->email }}</p>
                                </div>
                            </div>
                            @elseif($t->type === 'platform_fee')
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    🏢
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-purple-700 dark:text-purple-300 truncate">Kas Platform</p>
                                    <p class="text-[10px] text-gray-400 truncate">Pendapatan Perusahaan</p>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeColor }}">
                                @if($t->type === 'topup')
                                    ↓ Top Up
                                @elseif($t->type === 'escrow_lock')
                                    🔒 Escrow Lock (Holding)
                                @elseif($t->type === 'earning')
                                    🤝 Earning Mitra
                                @elseif($t->type === 'platform_fee')
                                    🏢 Komisi Platform
                                @elseif($t->type === 'withdraw')
                                    ↑ Withdraw
                                @elseif($t->type === 'refund')
                                    ↩️ Refund 100%
                                @elseif($t->type === 'penalty')
                                    ⚠️ Denda Pembatalan
                                @elseif($t->type === 'deduction')
                                    Potongan
                                @else
                                    {{ ucfirst($t->type) }}
                                @endif
                            </span>
                            @if($t->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1 max-w-[280px]" title="{{ $t->description }}">
                                    {{ $t->description }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-bold {{ $isPositive ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $isPositive ? '+' : '-' }} Rp {{ number_format($t->amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <div class="flex flex-col gap-1 items-start min-w-[120px]">
                                {{-- Hanya tampilkan referensi yang bermakna (Bantuan ID dan Kode), sembunyikan order_id internal TOPUP-xxx --}}
                                @if(!empty($t->reference_id))
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                        <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>Bantuan #{{ $t->reference_id }}</span>
                                    </span>
                                @endif

                                @if(!empty($t->request_code))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                        <span class="text-emerald-500 font-bold text-[9px]">KODE</span>
                                        {{ $t->request_code }}
                                    </span>
                                @endif

                                @if(empty($t->reference_id) && empty($t->request_code))
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">{{ ucfirst($t->status ?? 'ok') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada transaksi</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Data transaksi akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $transactions->links('vendor.pagination.superadmin') }}
        </div>
    </div>
</div>
