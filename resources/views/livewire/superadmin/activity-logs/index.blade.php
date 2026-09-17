@php
    $title = 'Activity Logs';
    $breadcrumb = 'Super Admin / Activity Logs';

    $actionBadges = [
        'login'              => ['label' => 'Login Berhasil', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800', 'icon' => '🔑'],
        'login_failed'       => ['label' => 'Login Gagal', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '⚠️'],
        'logout'             => ['label' => 'Logout', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600', 'icon' => '🚪'],
        'topup_approval'     => ['label' => 'Setujui Topup', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800', 'icon' => '💳'],
        'topup_rejected'     => ['label' => 'Tolak Topup', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
        'topup_request'      => ['label' => 'Pengajuan Topup', 'class' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800', 'icon' => '💵'],
        'ktp_verified'       => ['label' => 'Verifikasi KTP Disetujui', 'class' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800', 'icon' => '🪪'],
        'ktp_rejected'       => ['label' => 'Verifikasi KTP Ditolak', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '🚫'],
        'withdraw_approved'  => ['label' => 'Pencairan Disetujui', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800', 'icon' => '💸'],
        'withdraw_rejected'  => ['label' => 'Pencairan Ditolak', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
        'partner_blocked'    => ['label' => 'Pengguna Diblokir', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border border-rose-300', 'icon' => '🔒'],
        'partner_unblocked'  => ['label' => 'Buka Blokir', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border border-emerald-300', 'icon' => '🔓'],
        'greylist_add'       => ['label' => 'Masuk Daftar Abu-Abu', 'class' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300', 'icon' => '📋'],
        'greylist_remove'    => ['label' => 'Pulihkan dari Abu-Abu', 'class' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300', 'icon' => '🛡️'],
        'warning_issued'     => ['label' => 'Terbit Surat Peringatan (SP)', 'class' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-300', 'icon' => '⚠️'],
        'shadow_ban_enabled' => ['label' => 'Shadow Ban Diaktifkan', 'class' => 'bg-rose-100 text-rose-900 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300', 'icon' => '🚫'],
        'shadow_ban_disabled'=> ['label' => 'Shadow Ban Dicabut', 'class' => 'bg-teal-100 text-teal-900 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-300', 'icon' => '🔓'],
        'report_created'     => ['label' => 'Laporan Aduan Dibuat', 'class' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800', 'icon' => '📢'],
        'help_created'       => ['label' => 'Bantuan Dibuat', 'class' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800', 'icon' => '📝'],
        'help_taken'         => ['label' => 'Pekerjaan Diambil', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800', 'icon' => '🤝'],
        'service_completed'  => ['label' => 'Pekerjaan Selesai', 'class' => 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-300 border border-green-200 dark:border-green-800', 'icon' => '✅'],
    ];

    $formatActionBadge = function ($action) use ($actionBadges) {
        return $actionBadges[$action] ?? [
            'label' => ucwords(str_replace('_', ' ', $action)),
            'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600',
            'icon'  => '📌',
        ];
    };
@endphp

<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>🛡️ Activity Logs</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Audit rekam jejak aktivitas seluruh pengguna sistem (Super Admin, Admin, Customer, dan Mitra)
            </p>
        </div>
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            Memuat data...
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5 sm:gap-3">
        @php
        $statCards = [
            ['label' => 'Total Log',           'value' => $stats['total_logs'],    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'bg' => 'bg-blue-50 dark:bg-blue-900/40', 'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Hari Ini',            'value' => $stats['today_logs'],    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/40', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Admin & Super Admin', 'value' => $stats['admin_logs'],    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-violet-50 dark:bg-violet-900/40', 'color' => 'text-violet-600 dark:text-violet-400'],
            ['label' => 'Customer',            'value' => $stats['customer_logs'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'bg' => 'bg-sky-50 dark:bg-sky-900/40', 'color' => 'text-sky-600 dark:text-sky-400'],
            ['label' => 'Mitra',               'value' => $stats['mitra_logs'],    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'bg' => 'bg-amber-50 dark:bg-amber-900/40', 'color' => 'text-amber-600 dark:text-amber-400'],
        ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 {{ $card['bg'] }} rounded-xl hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 {{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $card['label'] }}</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">{{ number_format($card['value']) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ===== MAIN NAVIGATION TABS (DIREKTORI PENGGUNA VS DAFTAR AKTIVITAS) ===== --}}
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
    <div class="space-y-5">
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
                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'all' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Semua
                    </button>
                    <button type="button" wire:click="$set('userRoleFilter', 'customer')"
                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'customer' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Customer
                    </button>
                    <button type="button" wire:click="$set('userRoleFilter', 'mitra')"
                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'mitra' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Mitra
                    </button>
                    <button type="button" wire:click="$set('userRoleFilter', 'admin_superadmin')"
                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer {{ $userRoleFilter === 'admin_superadmin' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-gray-600 dark:text-gray-400' }}">
                        Admin
                    </button>
                </div>

                {{-- Filter Kota --}}
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
                $isAdmin = in_array($u->role, ['admin', 'super_admin', 'superadmin']);
                $lastAct = $u->latestActivityLog;
                $hasAct = !empty($u->last_activity_at);
                $badgeInfo = $lastAct ? $formatActionBadge($lastAct->action) : null;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                {{-- User Profile Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($u->profile_photo ?? $u->photo)
                            <img src="{{ asset('storage/' . ($u->profile_photo ?? $u->photo)) }}" alt="{{ $u->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-xl {{ $isAdmin ? 'bg-violet-600 text-white' : ($isMitra ? 'bg-purple-600 text-white' : 'bg-primary-600 text-white') }} flex items-center justify-center font-bold text-sm shrink-0">
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

                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 {{ $isAdmin ? 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800' : ($isMitra ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800') }}">
                        {{ ucwords(str_replace('_', ' ', $u->role)) }}
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
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $badgeInfo['class'] }}">
                                <span>{{ $badgeInfo['icon'] }}</span>
                                <span>{{ $badgeInfo['label'] }}</span>
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
    {{-- TAB 2: DAFTAR SELURUH LOG ALIRAN AKTIVITAS (STREAMS)                  --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'streams')
    <div class="space-y-4">
        {{-- Selected User Banner --}}
        @if($selectedUserId)
        <div class="p-3.5 bg-primary-50 dark:bg-primary-950/50 border border-primary-200 dark:border-primary-800 rounded-2xl flex items-center justify-between gap-3 text-xs sm:text-sm text-primary-900 dark:text-primary-200 shadow-xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-lg">👤</span>
                <span class="truncate">Menampilkan aktivitas khusus untuk pelaku: <strong>{{ $selectedUserName ?? 'User #' . $selectedUserId }}</strong></span>
            </div>
            <button type="button" wire:click="clearUserFilter"
                class="px-3 py-1.5 bg-white dark:bg-gray-800 text-primary-700 dark:text-primary-300 hover:bg-primary-600 hover:text-white rounded-xl font-bold text-xs border border-primary-300 dark:border-primary-700 shadow-2xs transition shrink-0 cursor-pointer">
                ✕ Tampilkan Semua Pengguna
            </button>
        </div>
        @endif

        {{-- Filter Toolbar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
            <div class="flex flex-wrap items-end gap-3">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[200px]">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, aksi, deskripsi, IP..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                {{-- Role Filter --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Filter Role</label>
                    <select wire:model.live="roleFilter"
                        class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="all">Semua Role</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                        <option value="mitra">Mitra</option>
                    </select>
                </div>

                {{-- Action Filter --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Filter Aksi</label>
                    <select wire:model.live="actionFilter"
                        class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="all">Semua Tipe Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}">{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom"
                        class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo"
                        class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                {{-- Reset --}}
                <button wire:click="clearFilters"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </button>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200/80 dark:border-gray-700">
                            <th class="px-4 py-3.5">Waktu & IP</th>
                            <th class="px-4 py-3.5">Pengguna</th>
                            <th class="px-4 py-3.5">Role</th>
                            <th class="px-4 py-3.5">Aksi</th>
                            <th class="px-4 py-3.5">Deskripsi</th>
                            <th class="px-4 py-3.5 text-right">Detail Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($logs as $log)
                            @php
                                $badge = $formatActionBadge($log->action);
                                $agentInfo = $this->parseUserAgent($log->user_agent);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                {{-- Waktu & IP --}}
                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5">
                                        <span>🌐 {{ $log->ip_address ?? '127.0.0.1' }}</span>
                                        <span>•</span>
                                        <span>{{ $agentInfo['device'] }}</span>
                                    </div>
                                </td>

                                {{-- Pengguna --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($log->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <button type="button" wire:click="filterByUser({{ $log->user->id }}, '{{ addslashes($log->user->name) }}')"
                                                    class="font-bold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 text-left transition cursor-pointer" title="Saring khusus user ini">
                                                    {{ $log->user->name }}
                                                </button>
                                                <div class="text-[10px] text-gray-400">{{ $log->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Sistem / Anonim</span>
                                    @endif
                                </td>

                                {{-- Role --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php
                                        $uRole = $log->user->role ?? 'system';
                                        $roleStyles = [
                                            'super_admin' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                            'superadmin'  => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                            'admin'       => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                            'customer'    => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                                            'mitra'       => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                            'system'      => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $roleStyles[$uRole] ?? $roleStyles['system'] }}">
                                        {{ ucwords(str_replace('_', ' ', $uRole)) }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badge['class'] }}">
                                        <span>{{ $badge['icon'] }}</span>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>

                                {{-- Deskripsi --}}
                                <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300">
                                    <div class="line-clamp-2" title="{{ $log->description }}">
                                        {{ $log->description ?? '-' }}
                                    </div>
                                </td>

                                {{-- Detail Info Modal Button --}}
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    <button wire:click="showProperties({{ $log->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/50 hover:bg-primary-100 dark:hover:bg-primary-900/60 rounded-xl border border-primary-200/80 dark:border-primary-800 transition cursor-pointer shadow-2xs">
                                        <span>🔍 Detail Info</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                    <div class="text-3xl mb-2">📋</div>
                                    <p class="font-medium text-sm">Tidak ada log aktivitas yang ditemukan.</p>
                                    <p class="text-xs mt-1">Coba sesuaikan kata kunci pencarian atau filter yang dipilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Logs Pagination --}}
            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                    {{ $logs->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ===== Modal Detail Informasi Nyata Aktivitas ===== --}}
    @if($showPropertiesModal && $selectedLog)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in"
         wire:keydown.escape.window="closePropertiesModal">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl w-full max-w-2xl overflow-hidden animate-scale-up"
             @click.away="$wire.closePropertiesModal()">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/70 dark:bg-gray-750/50">
                <div class="flex items-center gap-2.5">
                    @php
                        $mBadge = $formatActionBadge($selectedLog->action);
                    @endphp
                    <div class="w-9 h-9 rounded-2xl bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 flex items-center justify-center text-lg font-bold">
                        {{ $mBadge['icon'] }}
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Detail Riwayat Aktivitas</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">ID Log #{{ $selectedLog->id }} • {{ $selectedLog->created_at->translatedFormat('l, d F Y - H:i:s') }} WIB</p>
                    </div>
                </div>
                <button wire:click="closePropertiesModal" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                {{-- Info Pelaku & Aksi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-400 block mb-1">Pelaku Aksi</span>
                        <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $selectedLog->user->name ?? 'Sistem Otomatis' }}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ $selectedLog->user->email ?? '-' }}</div>
                        <span class="inline-block mt-1 px-2 py-0.5 text-[9px] font-bold rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300">
                            Role: {{ ucfirst($selectedLog->user->role ?? 'system') }}
                        </span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-400 block mb-1">Aksi & Event</span>
                        <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1">
                            <span>{{ $mBadge['icon'] }}</span>
                            <span>{{ $mBadge['label'] }}</span>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Kode: <code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">{{ $selectedLog->action }}</code></div>
                    </div>
                </div>

                {{-- Deskripsi Aktivitas --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Keterangan Aktivitas:</span>
                    <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">{{ $selectedLog->description ?? '-' }}</p>
                </div>

                {{-- Info Target User / Help jika ada --}}
                @if($targetUser || $targetHelp)
                <div class="p-3.5 bg-sky-50/70 dark:bg-sky-950/40 rounded-2xl border border-sky-100 dark:border-sky-900/60 space-y-2">
                    <span class="text-xs font-bold text-sky-900 dark:text-sky-200 flex items-center gap-1.5">
                        <span>🎯</span> Subjek / Target yang Terlibat
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @if($targetUser)
                        <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-sky-200/60 dark:border-sky-800">
                            <span class="text-[10px] text-gray-400 block">Pengguna Sasaran:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $targetUser->name }}</span>
                            <span class="text-[11px] text-gray-500 block">{{ $targetUser->email }} ({{ ucfirst($targetUser->role) }})</span>
                        </div>
                        @endif
                        @if($targetHelp)
                        <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-sky-200/60 dark:border-sky-800">
                            <span class="text-[10px] text-gray-400 block">Pesanan / Tugas Bantuan:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $targetHelp->order_id }}</span>
                            <span class="text-[11px] text-gray-500 block line-clamp-1">{{ $targetHelp->title }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Info Jaringan & Browser --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-2">Informasi Jaringan & Perangkat:</span>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="p-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block">Alamat IP:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $selectedLog->ip_address ?? '127.0.0.1' }}</span>
                        </div>
                        <div class="p-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block">Perangkat:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $parsedAgent['device'] }}</span>
                        </div>
                        <div class="p-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block">Browser / OS:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $parsedAgent['browser'] }} • {{ $parsedAgent['os'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end bg-gray-50/50 dark:bg-gray-750/30">
                <button wire:click="closePropertiesModal"
                    class="px-5 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-xl border border-gray-200 dark:border-gray-600 transition shadow-2xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
