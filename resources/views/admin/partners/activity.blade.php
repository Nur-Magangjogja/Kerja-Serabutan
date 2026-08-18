@extends('layouts.admin')

@section('content')
<div class="space-y-5">
    @php
        $collection = ($activities instanceof \Illuminate\Pagination\AbstractPaginator) ? collect($activities->items()) : collect($activities);
        $roleCounts = [
            'mitra' => $collection->filter(fn($a) => optional($a->user)?->isMitra())->count(),
            'customer' => $collection->filter(fn($a) => optional($a->user)?->isCustomer())->count(),
            'other' => $collection->filter(fn($a) => $a->user && !$a->user->isMitra() && !$a->user->isCustomer())->count(),
        ];
        $totalOnPage = $collection->count();
        $topTypes = $collection->groupBy('activity_type')->map(fn($items) => $items->count())->sortDesc()->take(4);
        $roleMeta = [
            'mitra' => ['label' => 'Mitra', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],
            'customer' => ['label' => 'Customer', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'],
            'other' => ['label' => 'Internal', 'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
        ];

        $activityMeta = [
            'login' => ['label' => 'Login Berhasil', 'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
            'login_failed' => ['label' => 'Login Gagal', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
            'logout' => ['label' => 'Logout', 'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
            'help_created' => ['label' => 'Customer Buat Bantuan', 'badge' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400'],
            'take_help' => ['label' => 'Ambil Bantuan', 'badge' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400'],
            'partner_on_the_way' => ['label' => 'Mitra Menuju Lokasi', 'badge' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400'],
            'partner_arrived' => ['label' => 'Mitra Tiba di Lokasi', 'badge' => 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'],
            'help_started' => ['label' => 'Mulai Pekerjaan', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],
            'help_completed' => ['label' => 'Pekerjaan Selesai (Bukti Terkirim)', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300'],
            'help_confirmed' => ['label' => 'Customer Konfirmasi Selesai', 'badge' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300'],
            'help_cancelled' => ['label' => 'Batalkan Bantuan', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400'],
            'help_reviewed' => ['label' => 'Customer Beri Ulasan', 'badge' => 'bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400'],
            'profile_updated' => ['label' => 'Update Profil', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
            'ktp_reuploaded' => ['label' => 'Upload Ulang KTP', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
            'phone_changed' => ['label' => 'Ubah No. Telepon', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
            'password_changed' => ['label' => 'Ubah Password', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
            'balance_topup' => ['label' => 'Top Up Saldo', 'badge' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
            'balance_withdraw' => ['label' => 'Tarik Saldo', 'badge' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
            'balance_deducted' => ['label' => 'Pengurangan Saldo', 'badge' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
            'security_bruteforce' => ['label' => 'Banyak Login Gagal', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
            'security_location_anomaly' => ['label' => 'Lokasi Mencurigakan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
            'security_outdated_app' => ['label' => 'Aplikasi Lama', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
            'ktp_verified' => ['label' => 'KTP Diverifikasi', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],
            'ktp_rejected' => ['label' => 'KTP Ditolak', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
        ];

        $formatActivity = function ($type) use ($activityMeta) {
            return $activityMeta[$type]['label'] ?? ucwords(str_replace('_', ' ', $type));
        };

        $detectDevice = function ($userAgent) {
            if (!$userAgent) return ['label' => 'Unknown Device', 'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'];
            $ua = strtolower($userAgent);
            if (str_contains($ua, 'android') || str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
                return ['label' => 'Mobile Browser', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'];
            }
            if (str_contains($ua, 'windows')) {
                return ['label' => 'Chrome Windows', 'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'];
            }
            if (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh')) {
                return ['label' => 'MacOS Browser', 'badge' => 'bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400'];
            }
            if (str_contains($ua, 'linux')) {
                return ['label' => 'Linux Browser', 'badge' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400'];
            }
            return ['label' => 'Unknown Device', 'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'];
        };
    @endphp

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Aktivitas Mitra & Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau rekaman audit aktivitas pengguna sistem</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.partners.activity.export.csv', request()->query()) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('admin.partners.activity.export.excel', request()->query()) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </a>
            <a href="{{ route('admin.partners.activity.export.print', request()->query()) }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                PDF / Print
            </a>
        </div>
    </div>

    {{-- ===== Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <form method="GET" action="{{ route('admin.partners.activity') }}" class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Aktivitas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama/email mitra, deskripsi, atau IP..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Jenis Aktivitas</label>
                <select name="type"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Aktivitas</option>
                    @foreach ($activityTypes as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'type', 'start_date', 'end_date']))
                <a href="{{ route('admin.partners.activity') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        {{-- Quick Role Chips --}}
        @if(!$activities->isEmpty())
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-2 items-center text-xs">
            <span class="text-gray-400 uppercase tracking-wider font-semibold mr-1">Filter Cepat:</span>
            <button type="button" data-role-filter-btn data-role-filter="all"
                class="role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-primary-600 text-white border-primary-600">
                Semua ({{ $totalOnPage }})
            </button>
            <button type="button" data-role-filter-btn data-role-filter="mitra"
                class="role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100">
                Mitra ({{ $roleCounts['mitra'] }})
            </button>
            <button type="button" data-role-filter-btn data-role-filter="customer"
                class="role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100">
                Customer ({{ $roleCounts['customer'] }})
            </button>
            <button type="button" data-role-filter-btn data-role-filter="other"
                class="role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100">
                Lainnya ({{ $roleCounts['other'] }})
            </button>
        </div>
        @endif

        @if ($activities->isEmpty())
            <div class="px-4 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada aktivitas ditemukan</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba sesuaikan filter pencarian atau tanggal</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Deskripsi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">IP / Device</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($activities as $a)
                            @php
                                $roleKey = optional($a->user)?->isMitra() ? 'mitra' : (optional($a->user)?->isCustomer() ? 'customer' : 'other');
                                $roleInfo = $roleMeta[$roleKey] ?? $roleMeta['other'];
                                $meta = $activityMeta[$a->activity_type] ?? ['label' => $formatActivity($a->activity_type), 'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'];
                                $device = $detectDevice($a->user_agent ?? null);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150" data-role-row data-role="{{ $roleKey }}">
                                <td class="px-4 py-3.5">
                                    @if($a->user)
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($a->user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.users.show', $a->user) }}" class="font-semibold text-gray-800 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 truncate block">
                                                    {{ $a->user->name }}
                                                </a>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $a->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleInfo['badge'] }}">
                                        {{ $roleInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $meta['badge'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                    <div class="flex items-center gap-2">
                                        @if($a->photo)
                                            <a href="{{ asset('storage/' . $a->photo) }}" target="_blank" class="w-8 h-8 rounded-lg overflow-hidden border border-emerald-300 flex-shrink-0 hover:opacity-80 transition shadow-xs" title="Lihat Foto Bukti">
                                                <img src="{{ asset('storage/' . $a->photo) }}" alt="Foto" class="w-full h-full object-cover">
                                            </a>
                                        @endif
                                        <p class="max-w-xs truncate text-xs" title="{{ $a->description }}">{{ $a->description ?? '—' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell">
                                    <p class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ $a->ip_address ?? '-' }}</p>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold {{ $device['badge'] }} mt-0.5">
                                        {{ $device['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ optional($a->created_at)->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('admin.partners.activity', array_merge(request()->query(), ['detail' => $a->id])) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    {{ $activities->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Detail Modal when query 'detail' is present --}}
    @if(isset($selectedActivity) && $selectedActivity)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <a href="{{ route('admin.partners.activity', request()->except('detail')) }}" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></a>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full z-10 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Detail Aktivitas #{{ $selectedActivity->id }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($selectedActivity->created_at)->format('d M Y, H:i:s') }} WIB</p>
                </div>
                <a href="{{ route('admin.partners.activity', request()->except('detail')) }}" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 text-xs">
                <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl space-y-2 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between"><span class="text-gray-400">User:</span> <span class="font-semibold text-gray-800 dark:text-gray-200">{{ optional($selectedActivity->user)->name ?? '—' }} ({{ optional($selectedActivity->user)->email ?? '—' }})</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Role:</span> <span class="font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst(optional($selectedActivity->user)->role ?? '—') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Tipe:</span> <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $formatActivity($selectedActivity->activity_type) }}</span></div>
                    @if($selectedActivity->help_id)
                        <div class="flex justify-between"><span class="text-gray-400">Terkait Bantuan:</span> <span class="font-bold text-indigo-600 dark:text-indigo-400">#{{ $selectedActivity->help_id }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-gray-400">Deskripsi:</span> <span class="font-medium text-gray-700 dark:text-gray-300 text-right">{{ $selectedActivity->description ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">IP:</span> <span class="font-mono text-gray-700 dark:text-gray-300">{{ $selectedActivity->ip_address ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">User Agent:</span> <span class="text-gray-700 dark:text-gray-300 text-right max-w-xs truncate">{{ $selectedActivity->user_agent ?? '—' }}</span></div>
                </div>

                @if($selectedActivity->photo)
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Bukti Aktivitas / Pengerjaan:
                        </span>
                        <a href="{{ asset('storage/' . $selectedActivity->photo) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . $selectedActivity->photo) }}" alt="Foto Bukti" class="w-full max-h-64 object-contain rounded-lg bg-black/5 dark:bg-black/30 border border-gray-200 dark:border-gray-600 hover:opacity-95 transition cursor-pointer">
                        </a>
                    </div>
                @endif
            </div>

                @if(isset($recentActivities) && !$recentActivities->isEmpty())
                <div class="space-y-2">
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aktivitas 24 Jam Terakhir</h4>
                    <div class="space-y-1.5">
                        @foreach($recentActivities as $ra)
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $formatActivity($ra->activity_type) }}</span>
                                <span class="text-gray-400"> · {{ $ra->description ?? '-' }}</span>
                            </div>
                            <span class="text-gray-400 whitespace-nowrap">{{ $ra->created_at->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <a href="{{ route('admin.partners.activity', request()->except('detail')) }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Tutup
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('[data-role-filter-btn]');
        const rows = document.querySelectorAll('[data-role-row]');
        if (!buttons.length || !rows.length) return;

        const setActiveButton = (current) => {
            buttons.forEach((btn) => {
                btn.className = 'role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100';
            });
            current.className = 'role-chip px-3 py-1 rounded-full border text-xs font-semibold bg-primary-600 text-white border-primary-600';
        };

        const applyRoleFilter = (role) => {
            rows.forEach((row) => {
                const shouldShow = role === 'all' || row.dataset.role === role;
                row.classList.toggle('hidden', !shouldShow);
            });
        };

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const role = btn.dataset.roleFilter || 'all';
                setActiveButton(btn);
                applyRoleFilter(role);
            });
        });
    });
</script>
@endpush
@endsection