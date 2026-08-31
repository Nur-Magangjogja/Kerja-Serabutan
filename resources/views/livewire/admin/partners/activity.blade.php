<div class="space-y-6">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';

        $activityMeta = [
            'help_created'           => ['label' => 'Bantuan Dibuat', 'badge' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800', 'icon' => '📝'],
            'take_help'              => ['label' => 'Tugas Diambil Mitra', 'badge' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-800', 'icon' => '🤝'],
            'partner_started_moving' => ['label' => 'Mitra Menuju Lokasi', 'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800', 'icon' => '🛵'],
            'partner_on_the_way'     => ['label' => 'Mitra Menuju Lokasi', 'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800', 'icon' => '🛵'],
            'partner_arrived'        => ['label' => 'Mitra Tiba di Lokasi', 'badge' => 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800', 'icon' => '📍'],
            'service_started'        => ['label' => 'Pelayanan Dalam Proses', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800', 'icon' => '⚡'],
            'help_started'           => ['label' => 'Pelayanan Dalam Proses', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800', 'icon' => '⚡'],
            'service_completed'      => ['label' => 'Pekerjaan Selesai & Kirim Bukti', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700', 'icon' => '📸'],
            'help_completed'         => ['label' => 'Pekerjaan Selesai & Kirim Bukti', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700', 'icon' => '📸'],
            'help_completed_waiting_confirmation' => ['label' => 'Menunggu Konfirmasi Customer', 'badge' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700', 'icon' => '⏳'],
            'confirm_completion'     => ['label' => 'Customer Konfirmasi Selesai', 'badge' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border border-green-300 dark:border-green-700', 'icon' => '✅'],
            'help_confirmed'         => ['label' => 'Customer Konfirmasi Selesai', 'badge' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border border-green-300 dark:border-green-700', 'icon' => '✅'],
            'cancel_help'            => ['label' => 'Bantuan Dibatalkan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
            'help_cancelled'         => ['label' => 'Bantuan Dibatalkan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
            'partner_cancel_executed' => ['label' => 'Batal oleh Mitra (Rematch)', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800', 'icon' => '⚠️'],
            'request_partner_cancel' => ['label' => 'Batal oleh Mitra', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800', 'icon' => '⚠️'],
            'dispute_raised'         => ['label' => 'Pengajuan Sengketa / Komplain', 'badge' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 border border-red-300 dark:border-red-700', 'icon' => '🚨'],
            'warranty_claim_escrow_clawback' => ['label' => 'Klaim Garansi 1x24 Jam', 'badge' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 border border-red-300 dark:border-red-700', 'icon' => '🛡️'],
            'auto_complete'          => ['label' => 'Auto-Konfirmasi Selesai', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800', 'icon' => '🤖'],
            'help_auto_confirmed'    => ['label' => 'Auto-Konfirmasi Selesai', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800', 'icon' => '🤖'],
            'help_reviewed'          => ['label' => 'Ulasan & Rating Diberikan', 'badge' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800', 'icon' => '⭐'],
        ];

        $formatActivity = function ($type) use ($activityMeta) {
            return $activityMeta[$type]['label'] ?? ucwords(str_replace('_', ' ', $type));
        };
        $getBadge = function ($type) use ($activityMeta) {
            return $activityMeta[$type]['badge'] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600';
        };
        $getIcon = function ($type) use ($activityMeta) {
            return $activityMeta[$type]['icon'] ?? '📌';
        };
    @endphp

    {{-- ===== Flash Notification ===== --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== 1. Page Header & Summary Badges ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Aktivitas Mitra & Customer
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        Pantau seluruh rekam jejak aksi pekerjaan jasa bantuan antara Mitra dan Customer secara real-time
                    </p>
                </div>
            </div>
        </div>

        {{-- Quick Stat Pill --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 px-3.5 py-2 rounded-xl flex items-center gap-2 shadow-2xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Total: <strong>{{ number_format($stats['total']) }}</strong> Log Terdata</span>
            </span>
        </div>
    </div>

    {{-- ===== 2. Summary Metric Cards ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shrink-0">
                📋
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Log</p>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                📅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hari Ini</p>
                <p class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['today']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold shrink-0">
                👤
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dari Customer</p>
                <p class="text-base sm:text-lg font-extrabold text-sky-600 dark:text-sky-400">{{ number_format($stats['customer_acts']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                🛵
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dari Mitra</p>
                <p class="text-base sm:text-lg font-extrabold text-purple-600 dark:text-purple-400">{{ number_format($stats['mitra_acts']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold shrink-0">
                ✅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pekerjaan Selesai</p>
                <p class="text-base sm:text-lg font-extrabold text-teal-600 dark:text-teal-400">{{ number_format($stats['completed_jobs']) }}</p>
            </div>
        </div>
    </div>

    {{-- ===== 3. MAIN NAVIGATION TABS (DIREKTORI PENGGUNA VS DAFTAR AKTIVITAS) ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 dark:border-gray-700 pb-3">
        <div class="inline-flex bg-gray-200/70 dark:bg-gray-800 p-1.5 rounded-2xl gap-1.5 w-full sm:w-auto">
            
            {{-- Tab 1: Menu Direktori Pengguna (Pelaku Aksi - Terurut Aktivitas Terakhir) --}}
            <button type="button" wire:click="setTab('directory')"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === 'directory' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Direktori Pelaku Aksi</span>
            </button>

            {{-- Tab 2: Seluruh Aliran Log Aktivitas (Streams) --}}
            <button type="button" wire:click="setTab('streams')"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $tab === 'streams' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Daftar Seluruh Aktivitas</span>
                @if($selectedUserId)
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                @endif
            </button>
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
            <span>Mode Aktif:</span>
            <strong class="text-gray-900 dark:text-white">{{ $tab === 'directory' ? 'Direktori Pengguna (Terurut Terakhir Aktif)' : 'Log Aliran Aktivitas' }}</strong>
        </div>
    </div>


    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: MENU DIREKTORI PENGGUNA (PELAKU AKSI - TERURUT AKTIVITAS TERAKHIR) --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'directory')
    <div class="space-y-5 animate-fade-in">
        
        {{-- Search & Role Filter Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Menu Direktori Pengguna (Pelaku Aksi)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Daftar pengguna otomatis diurutkan berdasarkan <strong>waktu terakhir kali melakukan aktivitas</strong>
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                {{-- Pencarian User --}}
                <div class="relative flex-1 sm:w-64">
                    <input wire:model.live.debounce.300ms="userSearch" type="text" placeholder="Cari nama, email, no HP..."
                        class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                {{-- Filter Role Pelaku --}}
                <div class="inline-flex bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl gap-1">
                    <button type="button" wire:click="$set('userRoleFilter', 'all')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'all' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Semua
                    </button>
                    <button type="button" wire:click="$set('userRoleFilter', 'customer')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'customer' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Customer
                    </button>
                    <button type="button" wire:click="$set('userRoleFilter', 'mitra')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'mitra' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Mitra
                    </button>
                </div>

                {{-- Filter Kota (Jika SuperAdmin) --}}
                @if(count($cities) > 1)
                <div>
                    <select wire:model.live="userCityId"
                        class="py-1.5 pl-3 pr-8 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="all">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        {{-- Users Grid Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($users as $u)
            @php
                $isMitra = $u->role === 'mitra';
                $lastAct = $u->latestPartnerActivity;
                $hasAct = !empty($u->last_activity_at);
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                
                {{-- User Profile Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($u->selfie_photo)
                            <img src="{{ asset('storage/' . $u->selfie_photo) }}" alt="{{ $u->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-xl {{ $isMitra ? 'bg-purple-600 text-white' : 'bg-primary-600 text-white' }} flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate" title="{{ $u->name }}">{{ $u->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</p>
                            @if(!empty($u->city))
                                <span class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                                    <span>📍</span> {{ is_object($u->city) ? $u->city->name : $u->city }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 {{ $isMitra ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </div>

                {{-- Last Activity Box --}}
                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-3.5 space-y-2 border border-gray-100 dark:border-gray-700/60 text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktivitas Terakhir:</span>
                        @if($hasAct)
                            <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400">
                                {{ \Carbon\Carbon::parse($u->last_activity_at)->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-[11px] text-gray-400 italic">Belum ada</span>
                        @endif
                    </div>

                    @if($lastAct)
                        <div class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $getBadge($lastAct->activity_type) }}">
                                <span>{{ $getIcon($lastAct->activity_type) }}</span>
                                <span>{{ $formatActivity($lastAct->activity_type) }}</span>
                            </span>
                            @if($lastAct->description)
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2 mt-1.5" title="{{ $lastAct->description }}">
                                    {{ $lastAct->description }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-[11px] text-gray-400 italic">Pengguna ini belum memiliki rekam aktivitas di sistem.</p>
                    @endif

                    <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-gray-500 dark:text-gray-400">Total Aktivitas:</span>
                        <span class="font-extrabold text-gray-900 dark:text-white">{{ number_format($u->total_activities ?? 0) }} kali</span>
                    </div>
                </div>

                {{-- Action Button --}}
                <button type="button" wire:click="filterByUser({{ $u->id }}, '{{ addslashes($u->name) }}')"
                    class="w-full py-2.5 px-4 rounded-xl bg-primary-50 dark:bg-primary-950/50 hover:bg-primary-600 text-primary-700 dark:text-primary-300 hover:text-white font-bold text-xs transition flex items-center justify-center gap-2 border border-primary-200 dark:border-primary-800/60 hover:border-primary-600 cursor-pointer shadow-2xs">
                    <span>Lihat Log Aktivitas Pengguna</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>

            </div>
            @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Tidak ada pengguna yang cocok dengan pencarian.</p>
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
    {{-- TAB 2: DAFTAR SELURUH LOG ALIRAN AKTIVITAS (STREAMS) --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'streams')
    <div class="space-y-4 animate-fade-in">

        {{-- Filter Box & Active User Banner --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Active User Filter Banner --}}
            @if($selectedUserId)
            <div class="bg-primary-50 dark:bg-primary-950/60 border-b border-primary-100 dark:border-primary-800/80 px-5 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-semibold text-primary-900 dark:text-primary-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary-600 animate-pulse"></span>
                    <span>Menampilkan aktivitas khusus untuk pelaku: <strong>{{ $selectedUserName ?? 'User #' . $selectedUserId }}</strong></span>
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    
                    {{-- Search --}}
                    <div class="relative lg:col-span-2">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul bantuan, pelaku, IP, deskripsi..."
                            class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Role Filter --}}
                    <div>
                        <select wire:model.live="roleFilter"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="all">Semua Pelaku</option>
                            <option value="customer">Customer</option>
                            <option value="mitra">Mitra</option>
                        </select>
                    </div>

                    {{-- Activity Type Filter --}}
                    <div>
                        <select wire:model.live="activityTypeFilter"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="all">Semua Tipe Aksi</option>
                            <option value="help_created">Bantuan Dibuat</option>
                            <option value="take_help">Tugas Diambil Mitra</option>
                            <option value="partner_on_the_way">Mitra Menuju Lokasi</option>
                            <option value="partner_arrived">Mitra Tiba di Lokasi</option>
                            <option value="help_started">Pelayanan Dimulai</option>
                            <option value="help_completed">Pekerjaan Selesai</option>
                            <option value="help_confirmed">Customer Konfirmasi</option>
                            <option value="cancel_help">Bantuan Dibatalkan</option>
                            <option value="partner_cancel_executed">Batal oleh Mitra</option>
                            <option value="dispute_raised">Sengketa / Komplain</option>
                        </select>
                    </div>

                    {{-- City Filter --}}
                    <div>
                        <select wire:model.live="cityId"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="all">Semua Kota</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Per Page --}}
                    <div>
                        <select wire:model.live="perPage"
                            class="w-full py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="15">15 / halaman</option>
                            <option value="30">30 / halaman</option>
                            <option value="50">50 / halaman</option>
                            <option value="100">100 / halaman</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- Activity Streams Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Pelaku Aksi</th>
                            <th class="px-4 py-3 text-left">Jenis Aktivitas</th>
                            <th class="px-4 py-3 text-left">Keterangan / Detail</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Bantuan Terkait</th>
                            <th class="px-4 py-3 text-left hidden xl:table-cell">Info Teknis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($activities as $act)
                        @php
                            $userRole = $act->user->role ?? '-';
                            $isUserMitra = $userRole === 'mitra';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            
                            {{-- Waktu --}}
                            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ optional($act->created_at)->translatedFormat('d M Y, H:i') }}</div>
                                <span class="text-[10px] text-gray-400">{{ optional($act->created_at)->diffForHumans() }}</span>
                            </td>

                            {{-- Pelaku Aksi --}}
                            <td class="px-4 py-3.5">
                                @if($act->user)
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full {{ $isUserMitra ? 'bg-purple-600 text-white' : 'bg-primary-600 text-white' }} font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($act->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <button type="button" wire:click="filterByUser({{ $act->user->id }}, '{{ addslashes($act->user->name) }}')"
                                            class="font-bold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 text-left truncate block cursor-pointer">
                                            {{ $act->user->name }}
                                        </button>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ $isUserMitra ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' }}">
                                                {{ ucfirst($userRole) }}
                                            </span>
                                            @if(!empty($act->user->city))
                                                <span class="text-[10px] text-gray-400 truncate">{{ is_object($act->user->city) ? $act->user->city->name : $act->user->city }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Jenis Aktivitas --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $getBadge($act->activity_type) }}">
                                    <span>{{ $getIcon($act->activity_type) }}</span>
                                    <span>{{ $formatActivity($act->activity_type) }}</span>
                                </span>
                            </td>

                            {{-- Keterangan / Detail --}}
                            <td class="px-4 py-3.5">
                                <p class="text-xs text-gray-800 dark:text-gray-200 line-clamp-2 max-w-sm">
                                    {{ $act->description ?? 'Tidak ada keterangan tambahan.' }}
                                </p>
                                @if($act->photo)
                                    <a href="{{ asset('storage/' . $act->photo) }}" target="_blank"
                                        class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                        <span>📷 Lihat Foto Bukti</span>
                                    </a>
                                @endif
                            </td>

                            {{-- Bantuan Terkait --}}
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                @if($act->help)
                                <div>
                                    <button type="button" wire:click="showHelpDetails({{ $act->help->id }})"
                                        class="font-bold text-primary-600 dark:text-primary-400 hover:underline text-left truncate block cursor-pointer">
                                        {{ $act->help->title }}
                                    </button>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">
                                        #{{ $act->help->id }} &bull; Rp {{ number_format($act->help->price ?? $act->help->amount ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Info Teknis --}}
                            <td class="px-4 py-3.5 hidden xl:table-cell text-gray-400">
                                @if($act->ip_address)
                                    <span class="font-mono text-[10px] block">{{ $act->ip_address }}</span>
                                @else
                                    <span class="text-[10px]">—</span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada log aktivitas yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Pagination --}}
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $activities->links('vendor.pagination.superadmin') }}
            </div>

        </div>

    </div>
    @endif


    {{-- ===== MODAL DETAIL BANTUAN ===== --}}
    @if($showHelpModal && $selectedHelp)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in">
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
            
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300">
                        Bantuan #{{ $selectedHelp->id }}
                    </span>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mt-1">
                        {{ $selectedHelp->title }}
                    </h3>
                </div>
                <button type="button" wire:click="closeHelpDetails" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                    <div>
                        <span class="text-gray-400 block text-[10px]">Customer:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $selectedHelp->customer->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Mitra Bertugas:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $selectedHelp->mitra->name ?? 'Belum ada' }}</strong>
                    </div>
                    <div class="pt-2 border-t border-gray-200/50 dark:border-gray-600/50">
                        <span class="text-gray-400 block text-[10px]">Biaya Jasa:</span>
                        <strong class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedHelp->price ?? $selectedHelp->amount ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div class="pt-2 border-t border-gray-200/50 dark:border-gray-600/50">
                        <span class="text-gray-400 block text-[10px]">Status:</span>
                        <strong class="text-gray-900 dark:text-white">{{ strtoupper($selectedHelp->status) }}</strong>
                    </div>
                </div>

                <div>
                    <span class="text-gray-400 block text-[10px]">Lokasi / Alamat:</span>
                    <p class="text-gray-700 dark:text-gray-300 mt-0.5">{{ $selectedHelp->full_address ?? $selectedHelp->location ?? '-' }}</p>
                </div>

                @if($selectedHelp->description)
                <div>
                    <span class="text-gray-400 block text-[10px]">Deskripsi Pekerjaan:</span>
                    <p class="text-gray-700 dark:text-gray-300 mt-0.5 line-clamp-3">{{ $selectedHelp->description }}</p>
                </div>
                @endif
            </div>

            <div class="pt-2">
                <button type="button" wire:click="closeHelpDetails"
                    class="w-full py-2.5 px-4 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 font-bold text-xs text-gray-800 dark:text-gray-200 transition cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>
    @endif

</div>