@php
    $title = 'Activity Logs';
    $breadcrumb = 'Super Admin / Activity Logs';

    $actionBadges = [
        'login'             => ['label' => 'Login Berhasil', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800', 'icon' => '🔑'],
        'login_failed'      => ['label' => 'Login Gagal', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '⚠️'],
        'logout'            => ['label' => 'Logout', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300', 'icon' => '🚪'],
        'topup_approval'    => ['label' => 'Setujui Topup', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800', 'icon' => '💳'],
        'topup_rejected'    => ['label' => 'Tolak Topup', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
        'ktp_verified'      => ['label' => 'Verifikasi KTP Disetujui', 'class' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800', 'icon' => '🪪'],
        'ktp_rejected'      => ['label' => 'Verifikasi KTP Ditolak', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '🚫'],
        'withdraw_approved' => ['label' => 'Pencairan Disetujui', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800', 'icon' => '💸'],
        'withdraw_rejected' => ['label' => 'Pencairan Ditolak', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
        'partner_blocked'   => ['label' => 'Pengguna Diblokir', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200', 'icon' => '🔒'],
        'partner_unblocked' => ['label' => 'Buka Blokir', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200', 'icon' => '🔓'],
        'greylist_add'      => ['label' => 'Masuk Daftar Abu-Abu', 'class' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300', 'icon' => '📋'],
        'warning_issued'    => ['label' => 'Terbit Surat Peringatan (SP)', 'class' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-300', 'icon' => '⚠️'],
        'shadow_ban_enabled'=> ['label' => 'Shadow Ban Diaktifkan', 'class' => 'bg-rose-100 text-rose-900 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300', 'icon' => '🚫'],
        'shadow_ban_disabled'=> ['label' => 'Shadow Ban Dicabut', 'class' => 'bg-teal-100 text-teal-900 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-300', 'icon' => '🔓'],
        'greylist_remove'   => ['label' => 'Pulihkan dari Abu-Abu', 'class' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300', 'icon' => '🛡️'],
        'report_created'    => ['label' => 'Laporan Aduan Dibuat', 'class' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800', 'icon' => '📢'],
        'help_created'      => ['label' => 'Bantuan Dibuat', 'class' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800', 'icon' => '📝'],
        'service_completed' => ['label' => 'Pekerjaan Selesai', 'class' => 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-300 border border-green-200 dark:border-green-800', 'icon' => '✅'],
    ];

    $formatActionBadge = function ($action) use ($actionBadges) {
        return $actionBadges[$action] ?? [
            'label' => ucwords(str_replace('_', ' ', $action)),
            'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'icon'  => '📌',
        ];
    };
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
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
            Memuat...
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5 sm:gap-3 mb-5">
        @php
        $statCards = [
            ['label' => 'Total Log',           'value' => $stats['total_logs'],    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'bg' => 'bg-blue-50 dark:bg-blue-900/40', 'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Hari Ini',            'value' => $stats['today_logs'],    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/40', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Admin & Super Admin', 'value' => $stats['admin_logs'],    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-violet-50 dark:bg-violet-900/40', 'color' => 'text-violet-600 dark:text-violet-400'],
            ['label' => 'Customer',            'value' => $stats['customer_logs'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/40', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Mitra',               'value' => $stats['mitra_logs'],    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'bg' => 'bg-amber-50 dark:bg-amber-900/40', 'color' => 'text-amber-600 dark:text-amber-400'],
        ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
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

    {{-- ===== Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-4 mb-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, aksi, deskripsi, IP..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            {{-- Role Filter --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Filter Role</label>
                <select wire:model.live="roleFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
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
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Tipe Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Reset --}}
            <button wire:click="clearFilters"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3">Waktu & IP</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Data Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                    @forelse($logs as $log)
                        @php
                            $u = $log->user;
                            $role = $u->role ?? 'guest';
                            $roleBadges = [
                                'super_admin' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/60 dark:text-violet-200 border border-violet-300',
                                'admin'       => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 border border-blue-300',
                                'customer'    => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border border-emerald-300',
                                'mitra'       => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300',
                            ];
                            $rb = $roleBadges[$role] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                            $badgeInfo = $formatActionBadge($log->action);
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                            {{-- Waktu & IP --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <p class="font-semibold text-gray-900 dark:text-white text-xs">{{ $log->created_at->format('d M Y') }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }} WIB</p>
                                <span class="inline-block font-mono text-[10px] text-gray-400 mt-0.5">
                                    🌐 {{ $log->ip_address ?: '-' }}
                                </span>
                            </td>

                            {{-- Pengguna --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ in_array($role, ['admin', 'super_admin']) ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50' : ($role === 'mitra' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50') }}">
                                        {{ strtoupper(substr($u?->name ?? ($log->user_id ? 'U' : 'S'), 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 dark:text-white text-xs truncate">
                                            {{ $u?->name ?? ($log->user_id ? "User #{$log->user_id}" : 'Sistem') }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ $u?->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rb }}">
                                    {{ str_replace('_', ' ', $role) }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeInfo['class'] }}">
                                    <span>{{ $badgeInfo['icon'] }}</span>
                                    <span>{{ $badgeInfo['label'] }}</span>
                                </span>
                            </td>

                            {{-- Deskripsi --}}
                            <td class="px-4 py-3.5 max-w-sm">
                                <p class="text-gray-800 dark:text-gray-200 text-xs leading-relaxed line-clamp-2">
                                    {{ $log->description ?: '—' }}
                                </p>
                            </td>

                            {{-- Data Payload / Properties --}}
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                @if(!empty($log->properties))
                                    <button type="button" wire:click="showProperties({{ $log->id }})"
                                        class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] font-medium transition cursor-pointer inline-flex items-center gap-1">
                                        <span>🔍 Detail Data</span>
                                    </button>
                                @else
                                    <span class="text-gray-400 text-[11px]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3 text-2xl">
                                        📋
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Activity Log Ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau lakukan pencarian lain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Modal Detail Informasi Nyata Aktivitas ===== --}}
    @if($showPropertiesModal && $selectedLog)
        @php
            $props = $selectedLog->properties ?? [];
            $reason = $props['reason'] ?? ($props['alasan'] ?? ($props['note'] ?? ($props['message'] ?? null)));
            $targetRole = $props['role'] ?? ($targetUser?->role ?? null);
            $actionLabel = ucwords(str_replace(['_', '-'], ' ', $selectedLog->action));
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity" wire:click="closePropertiesModal"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm">
                                📋
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Detail Riwayat Aktivitas</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                                        #{{ $selectedLog->id }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $selectedLog->created_at->translatedFormat('d F Y, H:i:s') }} ({{ $selectedLog->created_at->diffForHumans() }})
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closePropertiesModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 flex items-center justify-center transition cursor-pointer">
                            ✕
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        {{-- Ringkasan Aksi & Pelaku --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] font-bold uppercase text-gray-400 block mb-0.5">Tindakan / Aksi</span>
                                <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $actionLabel }}</p>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $selectedLog->action }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] font-bold uppercase text-gray-400 block mb-0.5">Pelaku Tindakan</span>
                                <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $selectedLog->user->name ?? 'Sistem / Anonim' }}</p>
                                <span class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold">{{ ucfirst(str_replace('_', ' ', $selectedLog->user->role ?? 'System')) }}</span>
                            </div>
                        </div>

                        {{-- Deskripsi Aktivitas --}}
                        <div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Keterangan Aktivitas:</span>
                            <div class="p-3 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 rounded-2xl text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                                {{ $selectedLog->description ?: 'Tidak ada deskripsi detail' }}
                            </div>
                        </div>

                        {{-- Info Pengguna Target (Jika Ada) --}}
                        @if($targetUser || !empty($props['target_user_id']))
                            <div class="p-3.5 bg-rose-50/60 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 rounded-2xl space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold text-rose-800 dark:text-rose-300 uppercase tracking-wider">
                                        👤 Pengguna yang Ditargetkan
                                    </span>
                                    @if($targetRole)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $targetRole === 'mitra' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $targetRole === 'mitra' ? '🛵 Mitra' : '👤 Customer' }}
                                        </span>
                                    @endif
                                </div>
                                @if($targetUser)
                                    <div class="flex items-center gap-2.5 pt-1">
                                        <div class="w-8 h-8 rounded-full bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($targetUser->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $targetUser->name }}</p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $targetUser->email }} • {{ $targetUser->phone ?: '-' }} • {{ $targetUser->city_name ?: 'Semua Wilayah' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        ID Pengguna Target: <strong class="font-mono">#{{ $props['target_user_id'] }}</strong>
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Info Pesanan / Bantuan Target (Jika Ada) --}}
                        @if($targetHelp || !empty($props['help_id']))
                            <div class="p-3.5 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/50 rounded-2xl space-y-1.5">
                                <span class="text-[11px] font-extrabold text-amber-800 dark:text-amber-300 uppercase tracking-wider block">
                                    📦 Tugas / Bantuan Terkait
                                </span>
                                @if($targetHelp)
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $targetHelp->title }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Order ID: <strong class="font-mono">{{ $targetHelp->order_id ?: '#'.$targetHelp->id }}</strong> • Nominal: <span class="font-bold text-emerald-600">Rp {{ number_format($targetHelp->amount, 0, ',', '.') }}</span></p>
                                @else
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        ID Bantuan: <strong class="font-mono">#{{ $props['help_id'] ?? $props['reference_id'] }}</strong>
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Alasan / Catatan Aksi (Jika Ada) --}}
                        @if($reason)
                            <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-2xl">
                                <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 block mb-1">📝 Alasan / Catatan Aksi:</span>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 italic">"{{ $reason }}"</p>
                            </div>
                        @endif

                        {{-- Detail Perangkat & Jaringan --}}
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-2">
                            <span class="text-[10px] font-bold uppercase text-gray-400 block">🌐 Informasi Perangkat & Akses</span>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Perangkat & OS:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $parsedAgent['os'] ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Browser:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $parsedAgent['browser'] ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Tipe Akses:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $parsedAgent['device'] ?? 'Desktop' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Alamat IP:</span>
                                    <span class="font-mono text-gray-800 dark:text-gray-200 font-semibold">{{ $selectedLog->ip_address ?: '127.0.0.1' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Toggle Lihat Data Teknis / JSON Asli --}}
                        @if(!empty($props))
                            <div class="pt-1">
                                <button type="button" wire:click="toggleRawJson"
                                    class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-semibold flex items-center gap-1 cursor-pointer">
                                    <span>{{ $showRawJson ? '▼ Sembunyikan Data Teknis (JSON)' : '▶ Lihat Data Teknis Asli (JSON Debug)' }}</span>
                                </button>
                                @if($showRawJson)
                                    <div class="mt-2 space-y-2 animate-in fade-in duration-100">
                                        <pre class="bg-gray-900 text-emerald-400 p-3 rounded-2xl text-[11px] overflow-x-auto font-mono max-h-48 border border-gray-800">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @if($selectedLog->user_agent)
                                            <p class="text-[10px] text-gray-400 font-mono bg-gray-900 text-gray-300 p-2 rounded-xl border border-gray-800 break-all">
                                                {{ $selectedLog->user_agent }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="button" wire:click="closePropertiesModal"
                            class="px-5 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl text-xs transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
            </div>
        </div>
    @endif
</div>


