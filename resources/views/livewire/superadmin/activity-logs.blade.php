@php
    $title = 'Activity Logs';
    $breadcrumb = 'Super Admin / Activity Logs';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Activity Logs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau seluruh aktivitas pengguna dalam sistem</p>
        </div>
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            Memuat...
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5 sm:gap-3 mb-5">
        @php
        $statCards = [
            ['label' => 'Total',     'value' => $stats['total_logs'],    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'bg' => 'bg-blue-50 dark:bg-blue-900/40', 'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Hari Ini',  'value' => $stats['today_logs'],    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/40', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Admin',     'value' => $stats['admin_logs'],    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-violet-50 dark:bg-violet-900/40', 'color' => 'text-violet-600 dark:text-violet-400'],
            ['label' => 'Customer',  'value' => $stats['customer_logs'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'bg' => 'bg-amber-50 dark:bg-amber-900/40', 'color' => 'text-amber-600 dark:text-amber-400'],
            ['label' => 'Mitra',     'value' => $stats['mitra_logs'],    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'bg' => 'bg-cyan-50 dark:bg-cyan-900/40', 'color' => 'text-cyan-600 dark:text-cyan-400'],
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
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari user, aksi, deskripsi..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Role Filter --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Role</label>
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
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Aksi</label>
                <select wire:model.live="actionFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Aksi</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Dari</label>
                <input type="date" wire:model.live="dateFrom"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Sampai</label>
                <input type="date" wire:model.live="dateTo"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Reset --}}
            <button wire:click="clearFilters"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($logs as $log)
                    @php
                    $roleConfig = [
                        'super_admin' => 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400',
                        'admin'       => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                        'customer'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                        'mitra'       => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                    ];
                    $rc = $roleConfig[$log->user->role ?? ''] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-xs flex-shrink-0">
                                    {{ substr($log->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $log->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $log->user->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $rc }}">
                                {{ ucfirst(str_replace('_', ' ', $log->user->role ?? 'unknown')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $log->activity_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</p>
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            <code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded">{{ $log->ip_address ?? '—' }}</code>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada activity log</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba ubah filter atau lakukan pencarian lain</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
