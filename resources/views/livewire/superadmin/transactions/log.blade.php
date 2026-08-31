@php
    $title = 'Financial Report & Analytics';
@endphp

<div class="py-2 max-w-full overflow-x-hidden space-y-6">

    {{-- ===== 1. Page Header & Export Controls ===== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Financial Report & Analytics
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        Periode Aktif: <strong class="text-primary-600 dark:text-primary-400">{{ $periodLabel }}</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- Export Action Buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <!-- 1. CSV Export -->
            <button wire:click="exportCsv" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-xs hover:shadow cursor-pointer disabled:opacity-50">
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
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-xs hover:shadow cursor-pointer disabled:opacity-50">
                <svg wire:loading.remove wire:target="exportXlsx" class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg wire:loading wire:target="exportXlsx" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Excel</span>
            </button>

            <!-- 3. PDF Export -->
            <button wire:click="exportPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-xs hover:shadow cursor-pointer disabled:opacity-50">
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

    {{-- ===== 2. Main Navigation Tabs & LIVEWIRE PERIOD CONTROLS (BULAN & TAHUN) ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
        
        {{-- Mode Tabs --}}
        <div class="inline-flex bg-gray-200/70 dark:bg-gray-800 p-1.5 rounded-2xl gap-1.5 w-full sm:w-auto">
            {{-- Tab 1: Overview & Charts --}}
            <button type="button" wire:click="setTab('overview')"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === 'overview' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Ikhtisar & Grafik</span>
            </button>

            {{-- Tab 2: User Financial Directory --}}
            <button type="button" wire:click="setTab('users')"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === 'users' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Direktori Pengguna</span>
            </button>

            {{-- Tab 3: All Transaction Streams --}}
            <button type="button" wire:click="setTab('streams')"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === 'streams' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Aliran Transaksi</span>
                @if($selectedUserId)
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                @endif
            </button>
        </div>

        {{-- Livewire Periode Selector (PILIH BULAN & TAHUN REAKTIF) --}}
        <div class="flex items-center gap-2.5 bg-white dark:bg-gray-800 p-2 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-2xs">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1.5 pl-1.5">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Pilih Periode:
            </span>

            {{-- Dropdown Bulan --}}
            <select wire:model.live="selectedMonth"
                class="text-xs font-bold py-1.5 pl-2.5 pr-7 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 cursor-pointer">
                <option value="all">Semua Bulan</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>

            {{-- Dropdown Tahun --}}
            <select wire:model.live="selectedYear"
                class="text-xs font-bold py-1.5 pl-2.5 pr-7 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 cursor-pointer">
                <option value="all">Semua Tahun</option>
                @foreach($availableYears as $yr)
                    <option value="{{ $yr }}">Tahun {{ $yr }}</option>
                @endforeach
            </select>

            {{-- Live Loading Indicator --}}
            <div wire:loading wire:target="selectedMonth, selectedYear" class="flex items-center text-primary-600 dark:text-primary-400 text-xs gap-1 pr-1">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                <span class="text-[11px] font-semibold hidden sm:inline">Memuat...</span>
            </div>
        </div>

    </div>

    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: IKHTISAR & ANALISIS GRAFIK (OVERVIEW & CHARTS) --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'overview')
    <div class="space-y-6 animate-fade-in">
        
        {{-- Section 1: Hero Metric Summary Sesuai Bulan & Tahun Terpilih vs Total All-Time --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Card 1: Deposit Masuk (Top Up) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs relative overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-700 transition">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Deposit Masuk (Top Up)</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2 truncate">
                    Rp {{ number_format($periodTopup, 0, ',', '.') }}
                </div>
                <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Sepanjang Masa:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Rp {{ number_format($allTimeTopup, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Card 2: Kas Komisi Platform --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs relative overflow-hidden group hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Komisi Platform (Kas)</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mb-2 truncate">
                    Rp {{ number_format($periodPlatformFee, 0, ',', '.') }}
                </div>
                <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Sepanjang Masa:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Rp {{ number_format($allTimePlatformFee, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Card 3: Earning Bersih Mitra --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs relative overflow-hidden group hover:border-purple-300 dark:hover:border-purple-700 transition">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Earning Bersih Mitra</span>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mb-2 truncate">
                    Rp {{ number_format($periodEarning, 0, ',', '.') }}
                </div>
                <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Sepanjang Masa:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Rp {{ number_format($allTimeEarning, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Card 4: Penarikan Dana (Withdraw) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs relative overflow-hidden group hover:border-rose-300 dark:hover:border-rose-700 transition">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pencairan Dana (Withdraw)</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mb-2 truncate">
                    Rp {{ number_format($periodWithdraw, 0, ',', '.') }}
                </div>
                <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Sepanjang Masa:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Rp {{ number_format($allTimeWithdraw, 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        {{-- Secondary Metric Badges Sesuai Periode --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl p-3 flex flex-col">
                <span class="text-amber-800 dark:text-amber-300 font-semibold">🔒 Escrow Lock (Holding):</span>
                <span class="font-bold text-sm text-amber-900 dark:text-amber-200 mt-0.5">Rp {{ number_format($periodEscrow, 0, ',', '.') }}</span>
                <span class="text-[10px] text-amber-700 dark:text-amber-400 mt-1">All-Time: Rp {{ number_format($allTimeEscrow, 0, ',', '.') }}</span>
            </div>
            <div class="bg-cyan-50/70 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800/60 rounded-xl p-3 flex flex-col">
                <span class="text-cyan-800 dark:text-cyan-300 font-semibold">↩️ Refund (Pengembalian):</span>
                <span class="font-bold text-sm text-cyan-900 dark:text-cyan-200 mt-0.5">Rp {{ number_format($periodRefund, 0, ',', '.') }}</span>
                <span class="text-[10px] text-cyan-700 dark:text-cyan-400 mt-1">All-Time: Rp {{ number_format($allTimeRefund, 0, ',', '.') }}</span>
            </div>
            <div class="bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl p-3 flex flex-col">
                <span class="text-rose-800 dark:text-rose-300 font-semibold">🛠️ WD Mitra vs Customer:</span>
                <span class="font-bold text-sm text-rose-900 dark:text-rose-200 mt-0.5">Rp {{ number_format($periodWithdrawMitra, 0, ',', '.') }}</span>
                <span class="text-[10px] text-rose-700 dark:text-rose-400 mt-1">Customer: Rp {{ number_format($periodWithdrawCustomer, 0, ',', '.') }}</span>
            </div>
            <div class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 flex flex-col">
                <span class="text-gray-600 dark:text-gray-400 font-semibold">📊 Total Log Transaksi:</span>
                <span class="font-bold text-sm text-gray-900 dark:text-white mt-0.5">{{ number_format($periodTransactions) }} baris</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">All-Time: {{ number_format($allTimeCount) }} baris</span>
            </div>
        </div>

        {{-- Section 2: 100% REAKTIF LIVEWIRE MULTI-TIPE FINANCIAL CHART --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-200/80 dark:border-gray-700 shadow-xs space-y-4"
             x-data="financialChartController()">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/80 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                        Grafik Analisis Multi-Tipe Transaksi (Livewire Reactive)
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Menampilkan grafik seluruh tipe transaksi untuk periode <strong class="text-primary-600 dark:text-primary-400">{{ $periodLabel }}</strong>
                    </p>
                </div>
            </div>

            {{-- Multi-Dataset Toggle Chips --}}
            <div class="flex items-center gap-2 flex-wrap text-xs pt-1">
                <span class="text-gray-400 dark:text-gray-500 font-semibold mr-1">Filter Tipe Transaksi di Grafik:</span>
                
                <button type="button" @click="toggleDataset('topup')"
                    :class="activeDatasets.topup ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border-emerald-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Deposit (Top Up)
                </button>

                <button type="button" @click="toggleDataset('platform_fee')"
                    :class="activeDatasets.platform_fee ? 'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 border-blue-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Komisi Platform
                </button>

                <button type="button" @click="toggleDataset('earning')"
                    :class="activeDatasets.earning ? 'bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-200 border-purple-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                    Earning Mitra
                </button>

                <button type="button" @click="toggleDataset('withdraw')"
                    :class="activeDatasets.withdraw ? 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border-rose-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    Withdraw
                </button>

                <button type="button" @click="toggleDataset('escrow_lock')"
                    :class="activeDatasets.escrow_lock ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border-amber-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    Escrow Lock
                </button>

                <button type="button" @click="toggleDataset('refund')"
                    :class="activeDatasets.refund ? 'bg-cyan-100 dark:bg-cyan-900/60 text-cyan-800 dark:text-cyan-200 border-cyan-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-400 border-transparent opacity-60'"
                    class="px-3 py-1.5 rounded-xl font-bold border transition flex items-center gap-1.5 cursor-pointer">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                    Refund
                </button>

                <div class="ml-auto flex items-center gap-1">
                    <button type="button" @click="toggleAllDatasets(true)" class="px-2.5 py-1 text-[11px] font-semibold text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 cursor-pointer">
                        Pilih Semua
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" @click="toggleAllDatasets(false)" class="px-2.5 py-1 text-[11px] font-semibold text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 cursor-pointer">
                        Kosongkan
                    </button>
                </div>
            </div>

            {{-- Chart Canvas Container --}}
            <div class="w-full bg-gray-50 dark:bg-gray-900/80 rounded-2xl p-4 sm:p-5 border border-gray-200/80 dark:border-gray-700/80 h-72 sm:h-84 min-h-[300px]" wire:ignore>
                <canvas id="superadminMultiFinancialChart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Section 3: Tabel Rekapitulasi Arus Kas Bulanan --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Catatan & Rekapitulasi Pemasukan Bulanan
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Rincian akumulasi transaksi per bulan beserta net cashflow
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                            <th class="px-4 py-3 text-left">Bulan / Tahun</th>
                            <th class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">Top Up Masuk</th>
                            <th class="px-4 py-3 text-right text-blue-600 dark:text-blue-400">Komisi Platform</th>
                            <th class="px-4 py-3 text-right text-purple-600 dark:text-purple-400">Earning Mitra</th>
                            <th class="px-4 py-3 text-right text-rose-600 dark:text-rose-400">Withdraw</th>
                            <th class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">Escrow Lock</th>
                            <th class="px-4 py-3 text-right text-cyan-600 dark:text-cyan-400">Refund</th>
                            <th class="px-4 py-3 text-right font-bold">Net Arus Kas Masuk</th>
                            <th class="px-4 py-3 text-center">Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($monthlyBreakdownRows as $mRow)
                        @php
                            $monthCarbon = \Carbon\Carbon::createFromDate($mRow->year, $mRow->month, 1);
                            $netCash = ($mRow->topup + $mRow->platform_fee) - ($mRow->withdraw + $mRow->refund);
                            $isCurrentMonth = ($mRow->year == now()->year && $mRow->month == now()->month);
                            $isSelectedMonth = ($this->selectedYear == $mRow->year && $this->selectedMonth == $mRow->month);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition {{ $isSelectedMonth ? 'bg-primary-50/50 dark:bg-primary-950/30 font-bold border-l-4 border-l-primary-600' : ($isCurrentMonth ? 'bg-gray-50 dark:bg-gray-800 font-medium' : '') }}">
                            <td class="px-4 py-3 text-gray-900 dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span>{{ $monthCarbon->translatedFormat('F Y') }}</span>
                                @if($isCurrentMonth)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300">
                                        Bulan Ini
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($mRow->topup, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($mRow->platform_fee, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-purple-600 dark:text-purple-400">
                                Rp {{ number_format($mRow->earning, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($mRow->withdraw, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($mRow->escrow_lock, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-cyan-600 dark:text-cyan-400">
                                Rp {{ number_format($mRow->refund, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap {{ $netCash >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $netCash >= 0 ? '+' : '' }}Rp {{ number_format($netCash, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-semibold">
                                {{ number_format($mRow->total_count) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat transaksi bulanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif


    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: DIREKTORI KEUANGAN PENGGUNA (USER FINANCIAL DIRECTORY) --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'users')
    <div class="space-y-5 animate-fade-in">
        
        {{-- User Search & Role Filter Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Menu Direktori Keuangan Pengguna
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Pilih pengguna untuk meninjau secara mendalam seluruh tipe & aliran dana yang dilakukannya
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative flex-1 sm:w-64">
                    <input wire:model.live.debounce.300ms="userSearch" type="text" placeholder="Cari nama, email, no HP..."
                        class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <div class="inline-flex bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl gap-1">
                    <button type="button" wire:click="$set('userRole', 'all')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRole === 'all' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Semua
                    </button>
                    <button type="button" wire:click="$set('userRole', 'customer')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRole === 'customer' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Customer
                    </button>
                    <button type="button" wire:click="$set('userRole', 'mitra')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRole === 'mitra' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Mitra
                    </button>
                </div>
            </div>
        </div>

        {{-- Users Grid Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($users as $u)
            @php
                $userBalanceVal = (float) ($u->balance ? (is_object($u->balance) ? $u->balance->balance : $u->balance) : 0);
                $isMitra = $u->role === 'mitra';
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                
                {{-- User Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($u->selfie_photo)
                            <img src="{{ asset('storage/' . $u->selfie_photo) }}" alt="{{ $u->name }}" class="w-11 h-11 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        @else
                            <div class="w-11 h-11 rounded-xl {{ $isMitra ? 'bg-purple-600 text-white' : 'bg-primary-600 text-white' }} flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate" title="{{ $u->name }}">{{ $u->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</p>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 {{ $isMitra ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </div>

                {{-- User Financial Summary Mini-Grid --}}
                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-3 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block">Saldo Saat Ini:</span>
                        <span class="font-bold text-sm {{ $userBalanceVal < 0 ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">
                            Rp {{ number_format($userBalanceVal, 0, ',', '.') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block">Total Transaksi:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                            {{ $u->transactions_count ?? 0 }} baris
                        </span>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-semibold">Deposit / Top Up:</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-300">
                            Rp {{ number_format($u->total_topup ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                        <span class="text-[10px] text-rose-600 dark:text-rose-400 block font-semibold">Withdraw Dicairkan:</span>
                        <span class="font-bold text-rose-700 dark:text-rose-300">
                            Rp {{ number_format($u->total_withdraw ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Open Streams Button --}}
                <button type="button" wire:click="filterByUser({{ $u->id }}, '{{ addslashes($u->name) }}')"
                    class="w-full py-2.5 px-4 rounded-xl bg-primary-50 dark:bg-primary-950/50 hover:bg-primary-600 text-primary-700 dark:text-primary-300 hover:text-white font-bold text-xs transition flex items-center justify-center gap-2 border border-primary-200 dark:border-primary-800/60 hover:border-primary-600 cursor-pointer shadow-2xs">
                    <span>Lihat Aliran Dana Pengguna</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>

            </div>
            @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Tidak ada data pengguna yang sesuai dengan pencarian.</p>
            </div>
            @endforelse
        </div>

        {{-- Users Pagination --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200/80 dark:border-gray-700 shadow-xs">
            {{ $users->links('vendor.pagination.superadmin') }}
        </div>

    </div>
    @endif


    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 3: LOG ALIRAN TRANSAKSI (ALL TRANSACTION STREAMS) --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'streams')
    <div class="space-y-4 animate-fade-in">
        
        {{-- Filter Box & Active User Banner --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Active User Filter Notification --}}
            @if($selectedUserId)
            <div class="bg-primary-50 dark:bg-primary-950/60 border-b border-primary-100 dark:border-primary-800/80 px-5 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-semibold text-primary-900 dark:text-primary-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary-600 animate-pulse"></span>
                    <span>Menampilkan mutasi & aliran dana khusus untuk: <strong>{{ $selectedUserName ?? 'User #' . $selectedUserId }}</strong></span>
                </div>
                <button type="button" wire:click="clearUserFilter"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 text-xs font-bold hover:bg-rose-50 cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Hapus Filter Pengguna</span>
                </button>
            </div>
            @endif

            {{-- Filter Bar --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    
                    {{-- Search --}}
                    <div class="relative lg:col-span-2">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari ref, bantuan #id, request code, user..."
                            class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Type Filter --}}
                    <div>
                        <select wire:model.live="type"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="all">Semua Tipe Transaksi</option>
                            <option value="topup">Top Up (Deposit Masuk)</option>
                            <option value="platform_fee">Komisi Platform (Kas)</option>
                            <option value="earning">Earning Mitra (Gaji Masuk)</option>
                            <option value="withdraw">Withdraw (Pencairan)</option>
                            <option value="escrow_lock">Escrow Lock (Holding)</option>
                            <option value="refund">Refund (Pengembalian)</option>
                            <option value="cancellation">Pembatalan Tugas</option>
                            <option value="deduction">Potongan (Deduction)</option>
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <select wire:model.live="status"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="all">Semua Status</option>
                            <option value="completed">Completed / Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected / Failed</option>
                        </select>
                    </div>

                    {{-- Per Page --}}
                    <div>
                        <select wire:model.live="perPage"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="10">10 / halaman</option>
                            <option value="15">15 / halaman</option>
                            <option value="30">30 / halaman</option>
                            <option value="50">50 / halaman</option>
                            <option value="100">100 / halaman</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table Streams --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Pengguna / Entitas</th>
                            <th class="px-4 py-3 text-left">Tipe & Aliran Dana</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Referensi</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($transactions as $t)
                        @php
                        $typeBadge = match($t->type ?? '') {
                            'topup'        => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60', 'label' => '↓ Deposit (Top Up)'],
                            'platform_fee' => ['bg' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/60', 'label' => '🏢 Komisi Platform'],
                            'earning'      => ['bg' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800/60', 'label' => '🤝 Earning Mitra'],
                            'withdraw'     => ['bg' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/60', 'label' => '↑ Withdraw'],
                            'escrow_lock'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/60', 'label' => '🔒 Escrow Lock'],
                            'refund'       => ['bg' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-800/60', 'label' => '↩️ Refund 100%'],
                            'cancellation', 'penalty' => ['bg' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-300 dark:border-red-700/60', 'label' => '⚠️ Pembatalan'],
                            'deduction'    => ['bg' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600', 'label' => 'Potongan'],
                            default        => ['bg' => 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200', 'label' => ucfirst($t->type ?? '-')],
                        };

                        $statusBadge = match($t->status ?? 'completed') {
                            'approved', 'success', 'ok', 'completed' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                            'pending', 'waiting_approval'            => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            'rejected', 'failed'                     => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                            default                                  => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                        };

                        $isPositive = in_array($t->type, ['topup', 'earning', 'platform_fee', 'refund']);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ optional($t->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if($t->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($t->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <button type="button" wire:click="filterByUser({{ $t->user->id }}, '{{ addslashes($t->user->name) }}')"
                                            class="font-bold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 text-left truncate block cursor-pointer">
                                            {{ $t->user->name }}
                                        </button>
                                        <span class="text-[11px] text-gray-400 truncate block">{{ $t->user->email }}</span>
                                    </div>
                                </div>
                                @elseif($t->type === 'platform_fee')
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        🏢
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 dark:text-white block">Kas Platform</span>
                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold block">Pendapatan Perusahaan</span>
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $typeBadge['bg'] }}">
                                    {{ $typeBadge['label'] }}
                                </span>
                                @if($t->description)
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-1 max-w-[260px]" title="{{ $t->description }}">
                                        {{ $t->description }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-sm whitespace-nowrap {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $isPositive ? '+' : '-' }} Rp {{ number_format($t->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <div class="flex flex-col gap-1 items-start">
                                    @if(!empty($t->reference_id))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                            Bantuan #{{ $t->reference_id }}
                                        </span>
                                    @endif
                                    @if(!empty($t->request_code))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                            KODE: {{ $t->request_code }}
                                        </span>
                                    @endif
                                    @if(empty($t->reference_id) && empty($t->request_code))
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusBadge }}">
                                    {{ ucfirst($t->status ?? 'completed') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada transaksi yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Pagination --}}
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $transactions->links('vendor.pagination.superadmin') }}
            </div>
        </div>

    </div>
    @endif

</div>

<script>
function financialChartController() {
    return {
        activeDatasets: {
            topup: true,
            platform_fee: true,
            earning: true,
            withdraw: true,
            escrow_lock: false,
            refund: false
        },
        chartInstance: null,
        init() {
            this.$nextTick(() => {
                this.renderChart();
            });
            this.$wire.$watch('chartData', () => {
                this.renderChart();
            });
            window.addEventListener('theme-changed', () => {
                this.renderChart();
            });
        },
        toggleDataset(key) {
            this.activeDatasets[key] = !this.activeDatasets[key];
            this.renderChart();
        },
        toggleAllDatasets(state) {
            Object.keys(this.activeDatasets).forEach(k => this.activeDatasets[k] = state);
            this.renderChart();
        },
        renderChart() {
            const canvas = document.getElementById('superadminMultiFinancialChart');
            if (!canvas) return;

            if (typeof Chart === 'undefined') {
                setTimeout(() => this.renderChart(), 100);
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const dataObj = this.$wire.get('chartData') || { labels: [] };
            const labels = dataObj.labels || [];

            const datasets = [
                {
                    key: 'topup',
                    label: 'Deposit (Top Up)',
                    data: dataObj.topup || [],
                    borderColor: '#10b981',
                    backgroundColor: isDark ? 'rgba(16, 185, 129, 0.4)' : 'rgba(16, 185, 129, 0.25)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.topup
                },
                {
                    key: 'platform_fee',
                    label: 'Komisi Platform (Kas)',
                    data: dataObj.platform_fee || [],
                    borderColor: '#2563eb',
                    backgroundColor: isDark ? 'rgba(37, 99, 235, 0.45)' : 'rgba(37, 99, 235, 0.3)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.platform_fee
                },
                {
                    key: 'earning',
                    label: 'Earning Mitra',
                    data: dataObj.earning || [],
                    borderColor: '#8b5cf6',
                    backgroundColor: isDark ? 'rgba(139, 92, 246, 0.4)' : 'rgba(139, 92, 246, 0.25)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.earning
                },
                {
                    key: 'withdraw',
                    label: 'Withdraw',
                    data: dataObj.withdraw || [],
                    borderColor: '#f43f5e',
                    backgroundColor: isDark ? 'rgba(244, 63, 94, 0.4)' : 'rgba(244, 63, 94, 0.25)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.withdraw
                },
                {
                    key: 'escrow_lock',
                    label: 'Escrow Lock',
                    data: dataObj.escrow_lock || [],
                    borderColor: '#f59e0b',
                    backgroundColor: isDark ? 'rgba(245, 158, 11, 0.35)' : 'rgba(245, 158, 11, 0.2)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.escrow_lock
                },
                {
                    key: 'refund',
                    label: 'Refund',
                    data: dataObj.refund || [],
                    borderColor: '#06b6d4',
                    backgroundColor: isDark ? 'rgba(6, 182, 212, 0.35)' : 'rgba(6, 182, 212, 0.2)',
                    borderWidth: 2,
                    borderRadius: 4,
                    hidden: !this.activeDatasets.refund
                }
            ].filter(d => !d.hidden);

            if (Chart.getChart) {
                const existing = Chart.getChart(canvas);
                if (existing) {
                    try { existing.destroy(); } catch(e) {}
                }
            }
            if (this.chartInstance) {
                try { this.chartInstance.destroy(); } catch(e) {}
                this.chartInstance = null;
            }

            const ctx = canvas.getContext('2d');
            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#e2e8f0',
                            cornerRadius: 8,
                            callbacks: {
                                label: function(c) {
                                    const v = c.raw ?? c.parsed?.y ?? 0;
                                    return c.dataset.label + ': Rp ' + Number(v).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                autoSkip: true,
                                maxRotation: 45,
                                font: { size: 11, weight: '500' },
                                color: isDark ? '#9ca3af' : '#6b7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { 
                                    if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + 'M';
                                    if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + 'K';
                                    return 'Rp ' + Number(v).toLocaleString('id-ID'); 
                                },
                                font: { size: 11, weight: '500' },
                                color: isDark ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(156, 163, 175, 0.15)'
                            }
                        }
                    }
                }
            });
        }
    };
}
</script>
