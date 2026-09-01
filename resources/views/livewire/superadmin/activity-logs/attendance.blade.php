@php
    $title = 'Attendance Logs';
    $breadcrumb = 'Super Admin / Attendance Logs';
@endphp

<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log Kehadiran (Attendance Logs)</h1>
            <p class="text-sm text-gray-500 mt-1">Pemantauan aktivitas presensi, check-in, dan log kehadiran user/mitra.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.activity.logs') }}" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 text-sm font-medium rounded-xl hover:bg-blue-100 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Semua Log Aktivitas
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Log Kehadiran</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Kehadiran Hari Ini</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['today']) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Cari user, email, atau deskripsi..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <select wire:model.live="roleFilter" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="all">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="mitra">Mitra</option>
                    <option value="customer">Customer</option>
                </select>
            </div>

            <div>
                <input wire:model.live="dateFrom" type="date"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500"
                    placeholder="Dari Tanggal">
            </div>

            <div>
                <input wire:model.live="dateTo" type="date"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500"
                    placeholder="Sampai Tanggal">
            </div>
        </div>

        @if($search || $roleFilter !== 'all' || $dateFrom || $dateTo)
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end">
                <button wire:click="clearFilters" class="text-xs text-red-600 hover:text-red-700 font-medium">
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-100">
                        <th class="px-6 py-3.5">Waktu</th>
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Aksi / Aktivitas</th>
                        <th class="px-6 py-3.5">Deskripsi</th>
                        <th class="px-6 py-3.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/75 transition">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">
                                {{ $log->created_at?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $log->user->name ?? 'Guest / Sistem' }}</div>
                                <div class="text-xs text-gray-400">{{ $log->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if(optional($log->user)->role === 'mitra')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">Mitra</span>
                                @elseif(optional($log->user)->role === 'admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700">Admin</span>
                                @elseif(optional($log->user)->role === 'super_admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700">Super Admin</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst(optional($log->user)->role ?? 'User') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-blue-50 text-blue-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs max-w-sm truncate">
                                {{ $log->description ?: '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-400">
                                {{ $log->ip_address ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                Belum ada catatan log kehadiran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            {{ $logs->links('vendor.pagination.superadmin') }}
        </div>
    </div>
</div>

