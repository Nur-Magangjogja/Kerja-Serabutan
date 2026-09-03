<div class="space-y-6">
    {{-- ===== Page Header & Month Selector ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-4 pb-2 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ringkasan aktivitas operasional dan tren seluruh data di wilayah wewenang Anda</p>
        </div>

        {{-- Premium Optimized Month Selector --}}
        <div class="flex items-center gap-1.5" x-data="{ isOpen: false }" @click.away="isOpen = false" wire:ignore.self>
            {{-- Quick Prev Month Button --}}
            <button type="button" wire:click="prevMonth" wire:loading.attr="disabled"
                class="p-2 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 shadow-2xs transition cursor-pointer disabled:opacity-50" 
                title="Bulan Sebelumnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            {{-- Main Dropdown Popover Button --}}
            <div class="relative">
                <button type="button" @click="isOpen = !isOpen"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-400 rounded-2xl px-4 py-2 shadow-2xs transition cursor-pointer group">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-950/80 dark:to-primary-900/40 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-200/60 dark:border-primary-800/60 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <span class="text-[10px] font-extrabold text-gray-400 dark:text-gray-400 uppercase tracking-wider block leading-none">Bulan Update</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $periodLabel }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary-600 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </button>

                {{-- Popover Menu --}}
                <div x-cloak x-show="isOpen" 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 p-3 z-50 overflow-hidden">
                    
                    {{-- Quick Action Buttons --}}
                    <div class="grid grid-cols-2 gap-1.5 pb-2.5 mb-2.5 border-b border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="setCurrentMonth(); isOpen = false"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer {{ !$isAllPeriod && $selectedMonth === now()->format('Y-m') ? 'bg-primary-600 text-white shadow-xs' : 'bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ !$isAllPeriod && $selectedMonth === now()->format('Y-m') ? 'bg-white' : 'bg-emerald-500 animate-pulse' }}"></span>
                            Bulan Ini
                        </button>

                        <button type="button" wire:click="setAllPeriod(); isOpen = false"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer {{ $isAllPeriod ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-xs' : 'bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Semua Periode
                        </button>
                    </div>

                    {{-- History Months List --}}
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider px-2 mb-1.5">Riwayat 12 Bulan Terakhir</p>
                    <div class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                        @foreach($availableMonths as $m)
                            <button type="button" wire:click="setMonth('{{ $m['key'] }}'); isOpen = false"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition text-left cursor-pointer {{ $selectedMonth === $m['key'] ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 font-bold border border-primary-200 dark:border-primary-800' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $m['is_current'] ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    {{ $m['label'] }}
                                </span>
                                @if($selectedMonth === $m['key'])
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Quick Next Month Button --}}
            <button type="button" wire:click="nextMonth" wire:loading.attr="disabled"
                class="p-2 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 shadow-2xs transition cursor-pointer disabled:opacity-50" 
                title="Bulan Berikutnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- ===== Top Stat Cards Grid ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2.5 sm:gap-3">
        {{-- Total Bantuan --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 16V8a2 2 0 00-2-2h-3l-2-2H10L8 6H5a2 2 0 00-2 2v8"/><rect x="3" y="8" width="18" height="10" rx="2" ry="2" fill="none"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total Bantuan</p>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white truncate">{{ number_format($totalHelps) }}</p>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Pending</p>
                <p class="text-base sm:text-lg font-extrabold text-amber-600 dark:text-amber-400 truncate">{{ number_format($pendingHelps) }}</p>
            </div>
        </div>

        {{-- Sedang Aktif --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Sedang Aktif</p>
                <p class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400 truncate">{{ number_format($activeHelps) }}</p>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Selesai</p>
                <p class="text-base sm:text-lg font-extrabold text-cyan-600 dark:text-cyan-400 truncate">{{ number_format($completedHelps) }}</p>
            </div>
        </div>

        {{-- Dibatalkan --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Dibatalkan</p>
                <p class="text-base sm:text-lg font-extrabold text-rose-600 dark:text-rose-400 truncate">{{ number_format($cancelledHelps) }}</p>
            </div>
        </div>

        {{-- KTP Pending --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">KTP Pending</p>
                <p class="text-base sm:text-lg font-extrabold text-orange-600 dark:text-orange-400 truncate">{{ number_format($pendingVerifications) }}</p>
            </div>
        </div>
    </div>

    {{-- Pending Alerts Grid (Optimized equal height & grid layout) --}}
    @if((isset($pendingVerifications) && $pendingVerifications > 0) || (isset($pendingTopups) && $pendingTopups > 0) || (isset($pendingWithdraws) && $pendingWithdraws > 0))
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
        {{-- Card 1: Verifikasi KTP --}}
        @if(isset($pendingVerifications) && $pendingVerifications > 0)
        <a href="{{ route('admin.verifications') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-indigo-100/80 dark:group-hover:bg-indigo-900/40 group-hover:border-indigo-300 dark:group-hover:border-indigo-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-indigo-950 dark:text-indigo-200 truncate">{{ $pendingVerifications }} Verifikasi KTP Menunggu</h3>
                        <p class="text-xs text-indigo-700/80 dark:text-indigo-300/80 mt-0.5 truncate">Tinjau dokumen identitas pendaftar baru</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 dark:text-indigo-300 group-hover:text-indigo-900 dark:group-hover:text-indigo-100 flex-shrink-0 group-hover:translate-x-1 transition-all">
                    Proses <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endif

        {{-- Card 2: Top-Up Approval --}}
        @if(isset($pendingTopups) && $pendingTopups > 0)
        <a href="{{ route('admin.topup.approvals') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-amber-100/80 dark:group-hover:bg-amber-900/40 group-hover:border-amber-300 dark:group-hover:border-amber-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-amber-950 dark:text-amber-200 truncate">{{ $pendingTopups }} Top-Up Menunggu Approval</h3>
                        <p class="text-xs text-amber-700/80 dark:text-amber-300/80 mt-0.5 truncate">Verifikasi bukti transfer deposit customer</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-300 group-hover:text-amber-900 dark:group-hover:text-amber-100 flex-shrink-0 group-hover:translate-x-1 transition-all">
                    Proses <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endif

        {{-- Card 3: Withdraw / Tarik Saldo --}}
        @if(isset($pendingWithdraws) && $pendingWithdraws > 0)
        <a href="{{ route('admin.withdraws.index') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-blue-100/80 dark:group-hover:bg-blue-900/40 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-blue-950 dark:text-blue-200 truncate">{{ $pendingWithdraws }} Tarik Saldo Menunggu Transfer</h3>
                        <p class="text-xs text-blue-700/80 dark:text-blue-300/80 mt-0.5 truncate">Pengajuan penarikan dana mitra perlu diproses</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 dark:text-blue-300 group-hover:text-blue-900 dark:group-hover:text-blue-100 flex-shrink-0 group-hover:translate-x-1 transition-all">
                    Proses <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endif
    </div>
    @endif

    {{-- ===== Unified Multi-Metric Chart + Ringkasan Operasional ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Unified Multi-Metric Chart --}}
        <div class="lg:col-span-2 min-w-0 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-6 flex flex-col justify-between overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white truncate">Tren Aktivitas Operasional Gabungan</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">
                            Grafik terintegrasi seluruh data wilayah • <strong class="text-primary-600 dark:text-primary-400">{{ $periodLabel }}</strong>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs text-primary-600 dark:text-primary-400 font-bold bg-primary-50 dark:bg-primary-900/30 px-3 py-1 rounded-xl border border-primary-100 dark:border-primary-800/60 shadow-2xs shrink-0">
                            {{ $isAllPeriod ? 'Data Akumulasi' : 'Filter Bulanan' }}
                        </span>
                        {{-- Mobile Scroll Indicator --}}
                        <span class="sm:hidden text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100/80 dark:bg-gray-700/60 px-2 py-0.5 rounded-lg inline-flex items-center gap-1 shadow-2xs">
                            <svg class="w-3 h-3 text-primary-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Geser grafik
                        </span>
                    </div>
                </div>

                {{-- Interactive Multi-Metric Legend Toggle Buttons (Scrollable on mobile, wrapped on desktop) --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-2 pt-1 custom-scrollbar flex-nowrap sm:flex-wrap" id="chartLegendContainer">
                    <button type="button" onclick="toggleAdminChartDataset(0)" data-dataset-index="0"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border border-sky-200/70 dark:border-sky-800/70 hover:bg-sky-100 dark:hover:bg-sky-900/60 transition-all cursor-pointer shadow-2xs hover:scale-105 active:scale-95 shrink-0"
                        title="Klik untuk menyembunyikan / menampilkan data Total Bantuan">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500 shadow-xs shrink-0"></span>
                        <span class="whitespace-nowrap">Total Bantuan ({{ number_format($totalHelps) }})</span>
                    </button>
                    <button type="button" onclick="toggleAdminChartDataset(1)" data-dataset-index="1"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/70 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-all cursor-pointer shadow-2xs hover:scale-105 active:scale-95 shrink-0"
                        title="Klik untuk menyembunyikan / menampilkan data Bantuan Selesai">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs shrink-0"></span>
                        <span class="whitespace-nowrap">Selesai ({{ number_format($completedHelps) }})</span>
                    </button>
                    <button type="button" onclick="toggleAdminChartDataset(2)" data-dataset-index="2"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/70 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition-all cursor-pointer shadow-2xs hover:scale-105 active:scale-95 shrink-0"
                        title="Klik untuk menyembunyikan / menampilkan data Bantuan Dibatalkan">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-xs shrink-0"></span>
                        <span class="whitespace-nowrap">Dibatalkan ({{ number_format($cancelledHelps) }})</span>
                    </button>
                    <button type="button" onclick="toggleAdminChartDataset(3)" data-dataset-index="3"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/70 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition-all cursor-pointer shadow-2xs hover:scale-105 active:scale-95 shrink-0"
                        title="Klik untuk menyembunyikan / menampilkan data Pendaftaran KTP">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs shrink-0"></span>
                        <span class="whitespace-nowrap">Pendaftaran KTP ({{ number_format($pendingVerifications) }})</span>
                    </button>
                </div>
            </div>

            {{-- Responsive Chart Scroll Wrapper with Automated Overflow --}}
            <div class="w-full overflow-x-auto custom-scrollbar mt-2 -mx-1 px-1 sm:mx-0 sm:px-0" id="adminChartScrollWrapper">
                <div id="adminUnifiedChartContainer" 
                     class="w-full min-w-[560px] sm:min-w-0 flex-1 min-h-[260px] sm:min-h-[300px] h-64 sm:h-76 relative"
                     data-labels="{{ json_encode($chartLabels) }}"
                     data-total="{{ json_encode($chartHelpsData) }}"
                     data-completed="{{ json_encode($chartCompletedData) }}"
                     data-cancelled="{{ json_encode($chartCancelledData) }}"
                     data-verifications="{{ json_encode($chartVerificationsData) }}"
                >
                    <div class="absolute inset-0 w-full h-full" wire:ignore>
                        <canvas id="adminUnifiedChartCanvas" class="w-full h-full block" style="max-height: 100% !important; height: 100% !important; width: 100% !important;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Operasional (Fully Synchronized) --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Ringkasan Operasional</h3>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        {{ $periodLabel }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    {{-- Total Bantuan --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Total Bantuan</span>
                        </div>
                        <span class="text-sm font-black text-gray-900 dark:text-white">{{ number_format($totalHelps) }}</span>
                    </div>

                    {{-- Pending Moderasi --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="font-medium text-amber-800 dark:text-amber-300">Pending Moderasi</span>
                        </div>
                        <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ number_format($pendingHelps) }}</span>
                    </div>

                    {{-- Sedang Aktif --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-emerald-800 dark:text-emerald-300">Sedang Aktif</span>
                        </div>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ number_format($activeHelps) }}</span>
                    </div>

                    {{-- Selesai --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-cyan-50/60 dark:bg-cyan-950/30 border border-cyan-100 dark:border-cyan-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            <span class="font-medium text-cyan-800 dark:text-cyan-300">Selesai / Sukses</span>
                        </div>
                        <span class="text-sm font-black text-cyan-600 dark:text-cyan-400">{{ number_format($completedHelps) }}</span>
                    </div>

                    {{-- Dibatalkan --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="font-medium text-rose-800 dark:text-rose-300">Dibatalkan</span>
                        </div>
                        <span class="text-sm font-black text-rose-600 dark:text-rose-400">{{ number_format($cancelledHelps) }}</span>
                    </div>

                    {{-- KTP Menunggu Verifikasi --}}
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-orange-50/60 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/40">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            <span class="font-medium text-orange-800 dark:text-orange-300">KTP Menunggu Verifikasi</span>
                        </div>
                        <span class="text-sm font-black text-orange-600 dark:text-orange-400">{{ number_format($pendingVerifications) }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Mitra Wilayah</span>
                <span class="text-xs font-extrabold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-800/60 px-3 py-1 rounded-xl shadow-2xs">
                    {{ number_format($totalAllMitras) }} Mitra Terdaftar
                </span>
            </div>
        </div>
    </div>

    {{-- ===== Permintaan Bantuan Terbaru (Pada Periode Ini) ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Permintaan Bantuan Terbaru</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Daftar bantuan terbaru di wilayah Anda pada periode {{ $periodLabel }}</p>
            </div>
            <a href="{{ route('admin.helps') }}" wire:navigate class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center gap-1">
                Lihat semua di Manajemen Bantuan <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if(isset($latestHelps) && $latestHelps->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer / Pemohon</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($latestHelps as $help)
                        @php
                        $stClass = match($help->status ?? '') {
                            'pending', 'menunggu_mitra' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            'active', 'taken', 'memperoleh_mitra', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'sedang_diproses' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            'completed', 'selesai' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            'cancelled', 'dibatalkan', 'rejected' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">#{{ $help->order_id ?? $help->id }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $help->title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ optional($help->user)->name ?? (optional($help->customer)->name ?? '—') }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $help->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-800 dark:text-gray-100">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                {{ $help->created_at ? $help->created_at->translatedFormat('d M Y, H:i') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak ada data bantuan</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Tidak ditemukan permintaan bantuan pada periode {{ $periodLabel }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Native Chart.js Multi-Dataset Unified Integration --}}
    <script>
    (function() {
        let adminChart = null;
        if (!Array.isArray(window._adminChartVisibility) || window._adminChartVisibility.length < 4) {
            window._adminChartVisibility = [true, true, true, true];
        }

        function syncLegendButtonsUI() {
            const container = document.getElementById('chartLegendContainer');
            if (!container) return;
            const buttons = container.querySelectorAll('button[data-dataset-index]');
            buttons.forEach(btn => {
                const idx = parseInt(btn.getAttribute('data-dataset-index'), 10);
                if (isNaN(idx)) return;
                const isVisible = (window._adminChartVisibility[idx] !== false);
                if (isVisible) {
                    btn.classList.remove('opacity-40', 'line-through', 'grayscale');
                    btn.setAttribute('aria-pressed', 'true');
                } else {
                    btn.classList.add('opacity-40', 'line-through', 'grayscale');
                    btn.setAttribute('aria-pressed', 'false');
                }
            });
        }

        function waitForChart(callback, maxAttempts = 50) {
            if (typeof Chart !== 'undefined') {
                callback();
                return;
            }
            let attempts = 0;
            const timer = setInterval(() => {
                attempts++;
                if (typeof Chart !== 'undefined') {
                    clearInterval(timer);
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(timer);
                }
            }, 50);
        }

        function initAdminChart() {
            const container = document.getElementById('adminUnifiedChartContainer');
            const canvas = document.getElementById('adminUnifiedChartCanvas');
            if (!container || !canvas) return;

            const isDark = document.documentElement.classList.contains('dark');
            let labels = [], totalHelps = [], completedHelps = [], cancelledHelps = [], verifications = [];
            try {
                labels = JSON.parse(container.getAttribute('data-labels') || '[]');
                totalHelps = JSON.parse(container.getAttribute('data-total') || '[]');
                completedHelps = JSON.parse(container.getAttribute('data-completed') || '[]');
                cancelledHelps = JSON.parse(container.getAttribute('data-cancelled') || '[]');
                verifications = JSON.parse(container.getAttribute('data-verifications') || '[]');
            } catch (e) {
                console.error('Failed to parse chart data:', e);
            }

            const datasets = [
                {
                    label: 'Total Bantuan',
                    data: totalHelps,
                    borderColor: '#0ea5e9',
                    backgroundColor: isDark ? 'rgba(14,165,233,0.15)' : 'rgba(14,165,233,0.08)',
                    pointBackgroundColor: '#0ea5e9',
                    pointHoverBackgroundColor: '#0284c7',
                    pointRadius: labels.length > 20 ? 2 : 3.5,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    hidden: (window._adminChartVisibility[0] === false)
                },
                {
                    label: 'Bantuan Selesai',
                    data: completedHelps,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#10b981',
                    pointHoverBackgroundColor: '#059669',
                    pointRadius: labels.length > 20 ? 2 : 3,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.35,
                    hidden: (window._adminChartVisibility[1] === false)
                },
                {
                    label: 'Bantuan Dibatalkan',
                    data: cancelledHelps,
                    borderColor: '#f43f5e',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#f43f5e',
                    pointHoverBackgroundColor: '#e11d48',
                    pointRadius: labels.length > 20 ? 2 : 3,
                    borderWidth: 2,
                    borderDash: [4, 4],
                    fill: false,
                    tension: 0.35,
                    hidden: (window._adminChartVisibility[2] === false)
                },
                {
                    label: 'Pendaftaran KTP',
                    data: verifications,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#f59e0b',
                    pointHoverBackgroundColor: '#d97706',
                    pointRadius: labels.length > 20 ? 2 : 3,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.35,
                    hidden: (window._adminChartVisibility[3] === false)
                }
            ];

            // If chart exists on this active canvas, update it smoothly without zoom/ballooning
            if (adminChart && adminChart.ctx && document.getElementById('adminUnifiedChartCanvas') === adminChart.canvas) {
                adminChart.data.labels = labels;
                datasets.forEach((ds, idx) => {
                    const isVis = (window._adminChartVisibility[idx] !== false);
                    ds.hidden = !isVis;
                    adminChart.setDatasetVisibility(idx, isVis);
                });
                adminChart.data.datasets = datasets;
                if (adminChart.options && adminChart.options.scales) {
                    if (adminChart.options.scales.x && adminChart.options.scales.x.ticks) {
                        adminChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                        adminChart.options.scales.x.ticks.maxTicksLimit = window.innerWidth < 640 ? 8 : 14;
                    }
                    if (adminChart.options.scales.y && adminChart.options.scales.y.ticks) {
                        adminChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                    }
                }
                adminChart.update('none');
                adminChart.resize();
                syncLegendButtonsUI();

                const scrollWrapper = container.closest('.overflow-x-auto');
                if (scrollWrapper && window.innerWidth < 640) {
                    setTimeout(() => {
                        scrollWrapper.scrollLeft = scrollWrapper.scrollWidth;
                    }, 50);
                }
                return;
            }

            // Clean up previous instances
            if (adminChart) {
                try { adminChart.destroy(); } catch(e) {}
                adminChart = null;
            }
            if (window._adminUnifiedChartInstance) {
                try { window._adminUnifiedChartInstance.destroy(); } catch(e) {}
                window._adminUnifiedChartInstance = null;
            }
            if (typeof Chart !== 'undefined') {
                const existing = Chart.getChart(canvas);
                if (existing) {
                    try { existing.destroy(); } catch(e) {}
                }
            }

            const config = {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 50,
                    devicePixelRatio: window.devicePixelRatio || 1,
                    layout: {
                        padding: {
                            top: 8,
                            bottom: 8,
                            left: 0,
                            right: 8
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: { duration: 250 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#1f2937' : '#ffffff',
                            titleColor: isDark ? '#f3f4f6' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? '#374151' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
                            callbacks: {
                                title: function(items) {
                                    return '📅 Tanggal: ' + items[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: isDark ? '#9ca3af' : '#6b7280',
                                font: { size: 10 },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: window.innerWidth < 640 ? 8 : 14
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' },
                            ticks: { color: isDark ? '#9ca3af' : '#6b7280', precision: 0, font: { size: 11 } }
                        }
                    }
                }
            };

            try {
                adminChart = new Chart(canvas.getContext('2d'), config);
                window._adminUnifiedChartInstance = adminChart;
            } catch(err) {
                console.warn('Canvas reset required for Admin Chart:', err);
                const parent = canvas.parentNode;
                if (parent) {
                    const newCanvas = document.createElement('canvas');
                    newCanvas.id = 'adminUnifiedChartCanvas';
                    newCanvas.className = 'w-full h-full block';
                    newCanvas.style.maxHeight = '100%';
                    newCanvas.style.height = '100%';
                    newCanvas.style.width = '100%';
                    parent.replaceChild(newCanvas, canvas);
                    adminChart = new Chart(newCanvas.getContext('2d'), config);
                    window._adminUnifiedChartInstance = adminChart;
                }
            }

            syncLegendButtonsUI();

            // Auto-scroll on mobile to latest dates on load
            const scrollWrapper = container.closest('.overflow-x-auto');
            if (scrollWrapper && window.innerWidth < 640) {
                setTimeout(() => {
                    scrollWrapper.scrollLeft = scrollWrapper.scrollWidth;
                }, 100);
            }

            // Setup ResizeObserver for smooth minimize/maximize & sidebar animation
            if (window.ResizeObserver && container) {
                if (window._adminChartResizeObs) {
                    try { window._adminChartResizeObs.disconnect(); } catch(e) {}
                }
                window._adminChartResizeObs = new ResizeObserver(() => {
                    if (adminChart && adminChart.ctx) {
                        adminChart.resize();
                    }
                });
                window._adminChartResizeObs.observe(container);
            }
        }

        window.toggleAdminChartDataset = function(datasetIndex) {
            if (!Array.isArray(window._adminChartVisibility)) {
                window._adminChartVisibility = [true, true, true, true];
            }
            window._adminChartVisibility[datasetIndex] = !window._adminChartVisibility[datasetIndex];

            const chart = window._adminUnifiedChartInstance || adminChart;
            if (chart) {
                chart.setDatasetVisibility(datasetIndex, window._adminChartVisibility[datasetIndex]);
                chart.update();
            }
            syncLegendButtonsUI();
        };

        function safeInit() {
            waitForChart(() => {
                initAdminChart();
                syncLegendButtonsUI();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', safeInit);
        } else {
            safeInit();
        }

        document.addEventListener('livewire:navigated', safeInit);
        window.addEventListener('admin-city-changed', () => setTimeout(safeInit, 50));
        window.addEventListener('chart-refresh', () => setTimeout(safeInit, 50));
        window.addEventListener('theme-changed', safeInit);

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (adminChart && adminChart.ctx) {
                    adminChart.resize();
                }
                syncLegendButtonsUI();
            }, 100);
        });

        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => {
                    if (component.name === 'admin.dashboard.index') {
                        setTimeout(safeInit, 50);
                    }
                });
            });
        });
    })();
    </script>
</div>
