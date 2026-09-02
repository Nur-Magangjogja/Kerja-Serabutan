@php
    $stats = $stats ?? [];
    $recentUsers = $recentUsers ?? collect();
    $recentTransactions = $recentTransactions ?? collect();
    $recentHelps = $recentHelps ?? collect();
    $userChart = $userChart ?? [
        'daily'   => ['labels' => [], 'data' => []],
        'monthly' => ['labels' => [], 'data' => []],
        'yearly'  => ['labels' => [], 'data' => []],
    ];
    $title = 'Dashboard';
@endphp

<div>
    {{-- ===== Welcome Header ===== --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat Datang, {{ auth()->user()->name ?? 'Super Admin' }} 👋</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }} &mdash; Berikut ringkasan sistem saat ini.</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-medium text-emerald-700 dark:text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Sistem Aktif
        </div>
    </div>

    {{-- Pending Alerts Grid (Optimized equal height & grid layout) --}}
    @if((isset($stats['pending_topups']) && $stats['pending_topups'] > 0) || (isset($stats['pending_withdraws']) && $stats['pending_withdraws'] > 0) || (isset($stats['pending_verifications']) && $stats['pending_verifications'] > 0))
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 mb-6">
        {{-- Card 1: Verifikasi KTP --}}
        @if(isset($stats['pending_verifications']) && $stats['pending_verifications'] > 0)
        <a href="{{ route('superadmin.verifications') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-indigo-100/80 dark:group-hover:bg-indigo-900/40 group-hover:border-indigo-300 dark:group-hover:border-indigo-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-indigo-950 dark:text-indigo-200 truncate">{{ $stats['pending_verifications'] }} Verifikasi KTP Menunggu</h3>
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
        @if(isset($stats['pending_topups']) && $stats['pending_topups'] > 0)
        <a href="{{ route('superadmin.topup.approvals') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-amber-100/80 dark:group-hover:bg-amber-900/40 group-hover:border-amber-300 dark:group-hover:border-amber-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-amber-950 dark:text-amber-200 truncate">{{ $stats['pending_topups'] }} Top-Up Menunggu Approval</h3>
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
        @if(isset($stats['pending_withdraws']) && $stats['pending_withdraws'] > 0)
        <a href="{{ route('superadmin.withdraws.index') }}" wire:navigate class="block group h-full">
            <div class="h-full bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 rounded-2xl p-4 flex items-center justify-between gap-3.5 group-hover:bg-blue-100/80 dark:group-hover:bg-blue-900/40 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-all duration-200 shadow-xs hover:shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-blue-950 dark:text-blue-200 truncate">{{ $stats['pending_withdraws'] }} Tarik Saldo Menunggu Transfer</h3>
                        <p class="text-xs text-blue-700/80 dark:text-blue-300/80 mt-0.5 truncate">Pengajuan penarikan dana mitra perlu ditransfer</p>
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

    {{-- ===== Stat Cards (Optimized 5 Cards) ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 mb-6">
        {{-- Total Pengguna --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 flex items-center gap-3.5 shadow-xs hover:shadow-md transition-all duration-200 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 hidden sm:flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Total Pengguna</p>
                <p class="text-lg sm:text-xl font-black text-gray-900 dark:text-white truncate">{{ number_format($stats['total_users'] ?? 0) }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate hidden sm:block">Semua akun</p>
            </div>
        </div>

        {{-- Customer --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 flex items-center gap-3.5 shadow-xs hover:shadow-md transition-all duration-200 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 hidden sm:flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Customer</p>
                <p class="text-lg sm:text-xl font-black text-amber-600 dark:text-amber-400 truncate">{{ number_format($stats['total_customers'] ?? 0) }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate hidden sm:block">Pengguna aktif</p>
            </div>
        </div>

        {{-- Total Kota --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 flex items-center gap-3.5 shadow-xs hover:shadow-md transition-all duration-200 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 hidden sm:flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Total Kota</p>
                <p class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 truncate">{{ number_format($stats['total_cities'] ?? 0) }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate hidden sm:block">Kota terdaftar</p>
            </div>
        </div>

        {{-- Mitra --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 flex items-center gap-3.5 shadow-xs hover:shadow-md transition-all duration-200 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 hidden sm:flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Mitra</p>
                <p class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 truncate">{{ number_format($stats['total_mitras'] ?? 0) }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate hidden sm:block">Mitra kerja</p>
            </div>
        </div>

        {{-- Admin --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 flex items-center gap-3.5 shadow-xs hover:shadow-md transition-all duration-200 min-w-0 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-violet-50 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 hidden sm:flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">Admin</p>
                <p class="text-lg sm:text-xl font-black text-violet-600 dark:text-violet-400 truncate">{{ number_format($stats['total_admins'] ?? 0) }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate hidden sm:block">Admin & SuperAdmin</p>
            </div>
        </div>
    </div>

    {{-- ===== Chart + Ringkasan ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        {{-- Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs p-4 sm:p-5 lg:col-span-2 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white truncate">Grafik Pendaftaran Pengguna</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">Statistik pertumbuhan pengguna per periode</p>
                </div>
                <div id="chartRangeTabs" role="tablist" class="inline-flex bg-gray-100 dark:bg-gray-700/60 rounded-xl p-1 gap-1 w-full sm:w-auto justify-between sm:justify-start flex-shrink-0">
                    <button type="button" data-range="daily"   class="chart-range-tab flex-1 sm:flex-none text-center px-3 py-1.5 text-xs font-bold rounded-lg transition">Harian</button>
                    <button type="button" data-range="monthly" class="chart-range-tab flex-1 sm:flex-none text-center px-3 py-1.5 text-xs font-bold rounded-lg transition">Bulanan</button>
                    <button type="button" data-range="yearly"  class="chart-range-tab flex-1 sm:flex-none text-center px-3 py-1.5 text-xs font-bold rounded-lg transition">Tahunan</button>
                </div>
            </div>
            <div class="w-full min-w-0 h-64 sm:h-72 relative" id="chartContainer" wire:ignore>
                <canvas id="usersChart"></canvas>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Ringkasan Platform
                    </h3>
                    <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 px-2 py-0.5 rounded-md">Realtime</span>
                </div>
                <div class="space-y-2.5">
                    @foreach([
                        ['label' => 'Total Pengguna', 'value' => number_format($stats['total_users'] ?? 0),     'color' => 'bg-blue-500',    'badge' => 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/50'],
                        ['label' => 'Customer',       'value' => number_format($stats['total_customers'] ?? 0), 'color' => 'bg-amber-500',   'badge' => 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50'],
                        ['label' => 'Total Kota',     'value' => number_format($stats['total_cities'] ?? 0),    'color' => 'bg-emerald-500', 'badge' => 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50'],
                        ['label' => 'Mitra',          'value' => number_format($stats['total_mitras'] ?? 0),    'color' => 'bg-indigo-500',  'badge' => 'text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/50'],
                        ['label' => 'Total Admin',    'value' => number_format($stats['total_admins'] ?? 0),    'color' => 'bg-violet-500',  'badge' => 'text-violet-700 dark:text-violet-300 bg-violet-50 dark:bg-violet-950/50'],
                    ] as $item)
                    <div class="flex items-center justify-between py-2 px-2.5 rounded-xl hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full {{ $item['color'] }} flex-shrink-0 shadow-2xs"></span>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">{{ $item['label'] }}</span>
                        </div>
                        <span class="text-xs font-black px-2.5 py-0.5 rounded-lg {{ $item['badge'] }} border border-transparent dark:border-white/5 shadow-2xs">
                            {{ $item['value'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="pt-3 mt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                <span>Cakupan Sistem</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">Seluruh Wilayah</span>
            </div>
        </div>
    </div>

    {{-- ===== Quick Actions + Recent Users + Recent Transactions ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        {{-- Aksi Cepat --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Aksi Cepat
            </h2>
            <div class="grid grid-cols-2 gap-2">
                @php
                $quickActions = [
                    ['href' => route('superadmin.users'),            'label' => 'Kelola User',     'color' => 'blue',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
                    ['href' => route('superadmin.cities'),           'label' => 'Kelola Kota',     'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>'],
                    ['href' => route('superadmin.settings.help'),     'label' => 'Pengaturan',      'color' => 'amber',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ['href' => route('superadmin.topup.approvals'),  'label' => 'Top-Up',          'color' => 'violet',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                    ['href' => route('superadmin.withdraws.index'),   'label' => 'Withdraw',        'color' => 'rose',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                    ['href' => route('superadmin.transactions.log'), 'label' => 'Laporan',         'color' => 'teal',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                ];
                $colorMapQA = [
                    'blue'    => ['bg' => 'bg-blue-50/90 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50',    'icon' => 'text-blue-600 dark:text-blue-400',    'hover' => 'hover:bg-blue-100/90 dark:hover:bg-blue-900/60'],
                    'emerald' => ['bg' => 'bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'hover' => 'hover:bg-emerald-100/90 dark:hover:bg-emerald-900/60'],
                    'amber'   => ['bg' => 'bg-amber-50/90 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50',   'icon' => 'text-amber-600 dark:text-amber-400',   'hover' => 'hover:bg-amber-100/90 dark:hover:bg-amber-900/60'],
                    'violet'  => ['bg' => 'bg-violet-50/90 dark:bg-violet-950/40 border border-violet-100 dark:border-violet-900/50', 'icon' => 'text-violet-600 dark:text-violet-400', 'hover' => 'hover:bg-violet-100/90 dark:hover:bg-violet-900/60'],
                    'rose'    => ['bg' => 'bg-rose-50/90 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/50',    'icon' => 'text-rose-600 dark:text-rose-400',    'hover' => 'hover:bg-rose-100/90 dark:hover:bg-rose-900/60'],
                    'teal'    => ['bg' => 'bg-teal-50/90 dark:bg-teal-950/40 border border-teal-100 dark:border-teal-900/50',    'icon' => 'text-teal-600 dark:text-teal-400',    'hover' => 'hover:bg-teal-100/90 dark:hover:bg-teal-900/60'],
                ];
                @endphp
                @foreach($quickActions as $qa)
                @php $c = $colorMapQA[$qa['color']]; @endphp
                <a href="{{ $qa['href'] }}" wire:navigate class="flex flex-col items-center gap-2 p-3 rounded-lg {{ $c['bg'] }} {{ $c['hover'] }} group">
                    <div class="w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $qa['icon'] !!}</svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 text-center leading-tight">{{ $qa['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Pengguna Terbaru --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengguna Terbaru
            </h2>
            @if($recentUsers->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pengguna baru</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($recentUsers as $u)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0
                        {{ $u->role === 'customer' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400' :
                          ($u->role === 'mitra'    ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400' :
                                                     'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400') }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Transaksi Keuangan Terbaru --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Transaksi Keuangan Terbaru
                </h2>
                <a href="{{ route('superadmin.transactions.log') }}" class="text-xs text-primary-600 dark:text-sky-400 hover:underline font-medium flex items-center gap-0.5">
                    Lihat Semua
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @if($recentTransactions->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($recentTransactions as $trx)
                @php
                $isTopup = ($trx->type ?? '') === 'topup';
                $isWithdraw = ($trx->type ?? '') === 'withdraw';
                $typeBadge = match($trx->type ?? '') {
                    'topup'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                    'withdraw' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                    default    => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                };
                $statusBadge = match(strtolower($trx->status ?? 'ok')) {
                    'approved', 'success', 'ok' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
                    'pending'                   => 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                    'rejected', 'failed'        => 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400',
                    default                     => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                };
                @endphp
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 {{ $isTopup ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400' : ($isWithdraw ? 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400') }}">
                            @if($isTopup)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @elseif($isWithdraw)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $trx->user_display_name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ optional($trx->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                        <p class="text-xs sm:text-sm font-bold {{ $isTopup ? 'text-emerald-600 dark:text-emerald-400' : ($isWithdraw ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white') }}">
                            {{ $isTopup ? '+' : ($isWithdraw ? '-' : '') }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </p>
                        <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.2 rounded {{ $statusBadge }} mt-0.5">
                            {{ ucfirst($trx->status ?? 'Success') }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ===== Permintaan Bantuan Table (Full Width) ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Permintaan Bantuan Terbaru
            </h3>
        </div>

        @if($recentHelps->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($recentHelps as $help)
                    @php
                    $s = $help->status;
                    $badge = match($s) {
                        'pending', 'menunggu_mitra' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                        'active', 'sedang_diproses', 'taken', 'memperoleh_mitra', 'in_progress', 'waiting_customer_confirmation' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                        'completed', 'selesai' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                        'dibatalkan', 'cancelled', 'rejected' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400',
                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                    };
                    $statusLabel = match($s) {
                        'menunggu_mitra' => 'Menunggu Mitra',
                        'taken', 'memperoleh_mitra' => 'Diambil Mitra',
                        'sedang_diproses', 'in_progress' => 'Diproses',
                        'waiting_customer_confirmation' => 'Konfirmasi Selesai',
                        'selesai', 'completed' => 'Selesai',
                        'dibatalkan', 'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', $s)),
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-5 py-3.5">
                            <span class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs inline-flex items-center justify-center">{{ $help->id }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-800 dark:text-gray-100 line-clamp-1">{{ $help->title }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr(optional($help->user)->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 truncate">{{ optional($help->user)->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ $help->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 mx-auto flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada permintaan bantuan</p>
        </div>
        @endif
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    let usersChart = null;
    let observer = null;

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
        }, 60);
    }

    function initUsersChart() {
        const canvas = document.getElementById('usersChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const chartData = @json($userChart);
        const isDark = () => document.documentElement.classList.contains('dark');

        function getColors() {
            return {
                gridColor : isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
                tickColor : isDark() ? '#9ca3af' : '#6b7280',
                bar       : isDark() ? 'rgba(99,102,241,0.85)' : 'rgba(59,130,246,0.85)',
                barHover  : isDark() ? 'rgba(129,140,248,0.95)' : 'rgba(59,130,246,0.95)',
                border    : isDark() ? 'rgba(129,140,248,1)' : 'rgba(59,130,246,1)',
            };
        }

        function renderRange(range) {
            const container = document.getElementById('chartContainer');
            if (container) {
                container.style.opacity = '1';
                container.style.transform = 'none';
            }

            const c = getColors();
            const labels = chartData[range]?.labels || [];
            const data = chartData[range]?.data || [];

            // If chart exists, update smoothly
            if (usersChart && usersChart.ctx && document.getElementById('usersChart')) {
                usersChart.data.labels = labels;
                if (usersChart.data.datasets && usersChart.data.datasets[0]) {
                    usersChart.data.datasets[0].data = data;
                    usersChart.data.datasets[0].backgroundColor = c.bar;
                    usersChart.data.datasets[0].hoverBackgroundColor = c.barHover;
                    usersChart.data.datasets[0].borderColor = c.border;
                }
                if (usersChart.options && usersChart.options.scales) {
                    if (usersChart.options.scales.x && usersChart.options.scales.x.ticks) {
                        usersChart.options.scales.x.ticks.color = c.tickColor;
                    }
                    if (usersChart.options.scales.y && usersChart.options.scales.y.ticks) {
                        usersChart.options.scales.y.ticks.color = c.tickColor;
                    }
                }
                usersChart.update('none');
                return;
            }

            const cfg = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendaftaran',
                        data: data,
                        backgroundColor: c.bar,
                        hoverBackgroundColor: c.barHover,
                        borderColor: c.border,
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 36,
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark() ? '#1e293b' : 'rgba(0,0,0,0.85)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => 'Pendaftaran: ' + Number(ctx.raw ?? (ctx.parsed ? ctx.parsed.y : 0) ?? 0).toLocaleString()
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 12, color: c.tickColor },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: c.tickColor, callback: v => Number(v).toLocaleString() },
                            grid: { color: c.gridColor }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false
                }
            };

            // Destroy old chart on canvas if it exists
            if (typeof Chart !== 'undefined' && Chart.getChart) {
                const existing = Chart.getChart(canvas) || Chart.getChart('usersChart');
                if (existing) {
                    try { existing.destroy(); } catch(e) {}
                }
            }
            if (usersChart) {
                try { usersChart.destroy(); } catch(e) {}
                usersChart = null;
            }
            if (window.usersChartInstance) {
                try { window.usersChartInstance.destroy(); } catch(e) {}
                window.usersChartInstance = null;
            }

            usersChart = new Chart(ctx, cfg);
            window.usersChartInstance = usersChart;
        }

        const tabs = document.querySelectorAll('.chart-range-tab');
        const validRanges = ['daily', 'monthly', 'yearly'];
        let initialRange = localStorage.getItem('superadmin.usersChart.range') || 'daily';
        if (!validRanges.includes(initialRange)) initialRange = 'daily';

        function setActive(range) {
            tabs.forEach(t => {
                if (t.dataset.range === range) {
                    t.className = 'chart-range-tab flex-1 sm:flex-none text-center px-2.5 sm:px-3 py-1.5 sm:py-1 text-xs font-medium rounded-md bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-xs';
                } else {
                    t.className = 'chart-range-tab flex-1 sm:flex-none text-center px-2.5 sm:px-3 py-1.5 sm:py-1 text-xs font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200';
                }
            });
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const r = tab.dataset.range;
                if (!validRanges.includes(r)) return;
                setActive(r);
                localStorage.setItem('superadmin.usersChart.range', r);
                renderRange(r);
            });
        });

        // Immediately render and activate initial range on load
        setActive(initialRange);
        renderRange(initialRange);

        let lastKnownDark = isDark();
        if (!observer) {
            observer = new MutationObserver(() => {
                const currentDark = isDark();
                if (currentDark !== lastKnownDark) {
                    lastKnownDark = currentDark;
                    if (usersChart) renderRange(localStorage.getItem('superadmin.usersChart.range') || 'daily');
                }
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }
    }

    function safeInit() {
        waitForChart(initUsersChart);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInit);
    } else {
        safeInit();
    }
    document.addEventListener('livewire:navigated', safeInit);

    window.addEventListener('theme-changed', function() {
        if (usersChart && document.getElementById('usersChart')) {
            const currentRange = localStorage.getItem('superadmin.usersChart.range') || 'daily';
            initUsersChart();
        }
    });
})();
</script>
