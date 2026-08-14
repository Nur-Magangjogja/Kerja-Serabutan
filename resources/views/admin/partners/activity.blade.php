@extends('layouts.admin')

@section('content')

    <div class="p-8 space-y-6">

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
                'mitra' => ['label' => 'Mitra', 'badge' => 'bg-primary-50 text-primary-700'],
                'customer' => ['label' => 'Customer', 'badge' => 'bg-amber-100 text-amber-800'],
                'other' => ['label' => 'Internal', 'badge' => 'bg-gray-100 text-gray-700'],
            ];

            // Activity meta and helpers (defined early so views can use them)
            $activityMeta = [
                // Login / Logout
                'login' => ['label' => 'Login Berhasil', 'badge' => 'bg-blue-100 text-blue-700', 'icon' => '🔐'],
                'login_failed' => ['label' => 'Login Gagal', 'badge' => 'bg-red-100 text-red-700', 'icon' => '❌'],
                'logout' => ['label' => 'Logout', 'badge' => 'bg-blue-50 text-blue-700', 'icon' => '📤'],

                // Bantuan
                'take_help' => ['label' => 'Ambil Bantuan', 'badge' => 'bg-emerald-100 text-emerald-700', 'icon' => '📥'],
                'help_started' => ['label' => 'Mulai Kerjakan Bantuan', 'badge' => 'bg-emerald-100 text-emerald-700', 'icon' => '▶️'],
                'help_completed' => ['label' => 'Selesaikan Bantuan', 'badge' => 'bg-emerald-200 text-emerald-800', 'icon' => '✔️'],
                'help_cancelled' => ['label' => 'Batalkan Bantuan', 'badge' => 'bg-yellow-100 text-yellow-800', 'icon' => '⏹️'],
                'help_created' => ['label' => 'Customer Membuat Bantuan', 'badge' => 'bg-sky-100 text-sky-700', 'icon' => '🆘'],
                'help_reviewed' => ['label' => 'Customer Menilai Bantuan', 'badge' => 'bg-indigo-100 text-indigo-700', 'icon' => '⭐'],

                // Profil (ungu)
                'profile_updated' => ['label' => 'Update Data Diri', 'badge' => 'bg-purple-100 text-purple-700', 'icon' => '📝'],
                'ktp_reuploaded' => ['label' => 'Upload Ulang KTP', 'badge' => 'bg-purple-100 text-purple-700', 'icon' => '📤'],
                'phone_changed' => ['label' => 'Mengubah Nomor Telepon', 'badge' => 'bg-purple-100 text-purple-700', 'icon' => '📱'],
                'password_changed' => ['label' => 'Mengubah Password', 'badge' => 'bg-purple-100 text-purple-700', 'icon' => '🔑'],

                // Finansial
                'balance_topup' => ['label' => 'Top Up Saldo', 'badge' => 'bg-orange-100 text-orange-700', 'icon' => '💳'],
                'balance_withdraw' => ['label' => 'Tarik Saldo', 'badge' => 'bg-orange-100 text-orange-700', 'icon' => '🏧'],
                'balance_deducted' => ['label' => 'Pengurangan Saldo', 'badge' => 'bg-orange-50 text-orange-700', 'icon' => '➖'],

                // Berbahaya
                'security_bruteforce' => ['label' => 'Banyak Login Gagal', 'badge' => 'bg-red-100 text-red-700', 'icon' => '⚠️'],
                'security_location_anomaly' => ['label' => 'Lokasi Mencurigakan', 'badge' => 'bg-red-100 text-red-700', 'icon' => '📍'],
                'security_outdated_app' => ['label' => 'Aplikasi Versi Lama', 'badge' => 'bg-red-100 text-red-700', 'icon' => '⬇️'],

                // KTP (hijau & merah)
                'ktp_verified' => ['label' => 'KTP Diverifikasi', 'badge' => 'bg-green-100 text-green-700', 'icon' => '🛂'],
                'ktp_rejected' => ['label' => 'KTP Ditolak', 'badge' => 'bg-red-100 text-red-700', 'icon' => '✖️'],
            ];

            $formatActivity = function ($type) use ($activityMeta) {
                if (isset($activityMeta[$type])) {
                    return $activityMeta[$type]['label'];
                }

                return ucwords(str_replace('_', ' ', $type));
            };

            $detectDevice = function ($userAgent) {
                if (!$userAgent) {
                    return ['label' => 'Unknown Device', 'badge' => 'bg-gray-100 text-gray-600'];
                }

                $ua = strtolower($userAgent);

                if (str_contains($ua, 'android') || str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
                    return ['label' => 'Mobile Browser', 'badge' => 'bg-green-100 text-green-700'];
                }

                if (str_contains($ua, 'windows')) {
                    return ['label' => 'Chrome Windows', 'badge' => 'bg-blue-100 text-blue-700'];
                }

                if (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh')) {
                    return ['label' => 'MacOS Browser', 'badge' => 'bg-blue-50 text-blue-700'];
                }

                if (str_contains($ua, 'linux')) {
                    return ['label' => 'Linux Browser', 'badge' => 'bg-blue-50 text-blue-700'];
                }

                return ['label' => 'Unknown Device', 'badge' => 'bg-gray-100 text-gray-600'];
            };
        @endphp

        <!-- Top stat cards and live feed removed as requested -->



        <!-- Filter Bar -->
        <div class="mb-6">
            <form method="GET" action="{{ route('admin.partners.activity') }}"
                class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-700">Filter Aktivitas</p>
                    @if (request()->hasAny(['search', 'type', 'start_date', 'end_date']))
                        <a href="{{ route('admin.partners.activity') }}"
                            class="text-[11px] text-gray-400 hover:text-gray-600 underline">Reset filter</a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Aktivitas</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama/email mitra, deskripsi, atau IP"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Aktivitas</label>
                        <select name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="all">Semua Aktivitas</option>
                            @foreach ($activityTypes as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                        <div class="flex space-x-2">
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                                class="w-1/2 px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                                class="w-1/2 px-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <p class="text-[11px] text-gray-400">Gunakan kombinasi pencarian, jenis aktivitas, dan periode tanggal
                        untuk mempersempit log aktivitas.</p>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Aktivitas</h2>
                    <p class="text-xs text-gray-500 mt-1">Menampilkan aktivitas terbaru mitra, customer, serta aksi penting
                        lainnya.</p>
                </div>
                <div class="flex items-center space-x-3">
                    @if (!$activities->isEmpty())
                        <p class="text-[11px] text-gray-500 mr-2 hidden md:block">Total {{ $activities->total() }} aktivitas
                            pada halaman ini.</p>
                    @endif
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.partners.activity.export.csv', request()->query()) }}"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-full text-[11px] text-gray-700 hover:bg-gray-50">
                            CSV
                        </a>
                        <a href="{{ route('admin.partners.activity.export.excel', request()->query()) }}"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-full text-[11px] text-gray-700 hover:bg-gray-50">
                            Excel
                        </a>
                        <a href="{{ route('admin.partners.activity.export.print', request()->query()) }}" target="_blank"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-full text-[11px] text-gray-700 hover:bg-gray-50">
                            PDF / Print
                        </a>
                    </div>
                </div>
            </div>

            @if ($activities->isEmpty())
                @php $totalActivitiesCount = \App\Models\PartnerActivity::count(); @endphp

                @if ($totalActivitiesCount > 0)
                    <div class="px-6 py-6">
                        <div class="mb-4 text-center">
                            <p class="text-gray-700 font-semibold">Filter saat ini menyembunyikan hasil.</p>
                            <p class="text-sm text-gray-500">Terdapat <strong>{{ $totalActivitiesCount }}</strong> aktivitas di
                                database, namun filter/periode saat ini tidak menampilkan baris apapun.</p>
                            <p class="text-xs text-gray-400 mt-2">Coba klik <a href="{{ route('admin.partners.activity') }}"
                                    class="underline">Reset filter</a> atau hapus parameter tanggal/jenis di URL.</p>
                        </div>

                        @php
                            $peek = \App\Models\PartnerActivity::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
                        @endphp

                        <div class="bg-white border border-gray-100 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-2">Contoh 5 aktivitas terakhir (peek):</div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-gray-500 uppercase">
                                        <tr>
                                            <th class="pb-2 text-left">Waktu</th>
                                            <th class="pb-2 text-left">User</th>
                                            <th class="pb-2 text-left">Jenis</th>
                                            <th class="pb-2 text-left">Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700">
                                        @foreach($peek as $p)
                                            <tr class="border-t border-gray-50">
                                                <td class="py-2 text-xs">{{ optional($p->created_at)->format('Y-m-d H:i:s') }}</td>
                                                <td class="py-2 text-xs">{{ optional($p->user)->name ?? '-' }}</td>
                                                <td class="py-2 text-xs">{{ $p->activity_type }}</td>
                                                <td class="py-2 text-xs">
                                                    {{ \Illuminate\Support\Str::limit($p->description ?? '-', 80) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 8H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 10.414V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">Belum ada aktivitas mitra.</p>
                        <p class="text-gray-400 text-xs mt-1">Aktivitas login dan aksi lain akan muncul di sini secara
                            otomatis.</p>
                    </div>
                @endif
            @else
                <div class="px-6 py-3 border-b border-gray-100">
                    <div class="flex flex-wrap gap-2 items-center text-xs">
                        <span class="text-gray-400 mr-2 uppercase tracking-wide">Filter Role Cepat:</span>
                        <button type="button" data-role-filter-btn data-role-filter="all"
                            class="role-chip inline-flex items-center px-3 py-1 rounded-full border text-[11px] font-semibold bg-gray-900 text-white">
                            Semua ({{ $totalOnPage }})
                        </button>
                        <button type="button" data-role-filter-btn data-role-filter="mitra"
                            class="role-chip inline-flex items-center px-3 py-1 rounded-full border text-[11px] font-semibold bg-gray-50 text-gray-700">
                            Mitra ({{ $roleCounts['mitra'] }})
                        </button>
                        <button type="button" data-role-filter-btn data-role-filter="customer"
                            class="role-chip inline-flex items-center px-3 py-1 rounded-full border text-[11px] font-semibold bg-gray-50 text-gray-700">
                            Customer ({{ $roleCounts['customer'] }})
                        </button>
                        <button type="button" data-role-filter-btn data-role-filter="other"
                            class="role-chip inline-flex items-center px-3 py-1 rounded-full border text-[11px] font-semibold bg-gray-50 text-gray-700">
                            Lainnya ({{ $roleCounts['other'] }})
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Aktivitas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Deskripsi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User Agent
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider">Detail
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($activities as $a)
                                @php
                                    $roleKey = optional($a->user)?->isMitra() ? 'mitra' : (optional($a->user)?->isCustomer() ? 'customer' : 'other');
                                    $roleInfo = $roleMeta[$roleKey] ?? $roleMeta['other'];
                                @endphp
                                <tr class="hover:bg-gray-50" data-role-row data-role="{{ $roleKey }}"
                                    data-activity-type="{{ $a->activity_type }}">
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-900 text-sm font-medium">
                                        @if($a->user)
                                            <a href="{{ route('admin.users.show', $a->user) }}"
                                                class="text-primary-600 hover:text-primary-700 hover:underline">
                                                {{ $a->user->name }}
                                            </a>
                                            <p class="text-[11px] text-gray-500">{{ $a->user->email }}</p>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-700 text-sm">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold {{ $roleInfo['badge'] }}">
                                            {{ $roleInfo['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-700 text-sm">
                                        @php
                                            $meta = $activityMeta[$a->activity_type] ?? null;
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold {{ $meta['badge'] ?? 'bg-gray-100 text-gray-700' }}">
                                            @if (!empty($meta['icon']))
                                                <span class="mr-1">{{ $meta['icon'] }}</span>
                                            @endif
                                            {{ $formatActivity($a->activity_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 text-xs">
                                        {{ $a->description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-700 text-xs font-mono">
                                        {{ $a->ip_address ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-500 text-[11px]">
                                        @php
                                            $device = $detectDevice($a->user_agent);
                                        @endphp
                                        <div class="flex flex-col space-y-1">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $device['badge'] }}">
                                                {{ $device['label'] }}
                                            </span>
                                            <span>{{ \Illuminate\Support\Str::limit($a->user_agent, 60) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600 text-xs">
                                        {{ $a->created_at->diffForHumans() }}
                                        <span class="text-gray-400">({{ $a->created_at->format('Y-m-d H:i:s') }} WIB)</span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-gray-600 text-xs">
                                        <a href="{{ route('admin.partners.activity', array_merge(request()->query(), ['activity_id' => $a->id])) }}"
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 rounded-full hover:bg-gray-50">
                                            <span class="mr-1">Detail</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($activities->hasPages())
                    @php
                        $p = $activities;
                        $current = $p->currentPage();
                        $last = $p->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-center">
                        <nav class="inline-flex items-center space-x-2" role="navigation" aria-label="Pagination">
                            {{-- Previous --}}
                            @if($current > 1)
                                <a href="{{ $p->url($current - 1) }}" class="inline-flex items-center px-3 py-1.5 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                    &larr; Prev
                                </a>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 border rounded-md text-sm text-gray-400">&larr; Prev</span>
                            @endif

                            {{-- First + ellipsis --}}
                            @if($start > 1)
                                <a href="{{ $p->url(1) }}" class="inline-flex items-center px-3 py-1.5 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50">1</a>
                                @if($start > 2)
                                    <span class="px-2 text-sm text-gray-500">…</span>
                                @endif
                            @endif

                            {{-- Page numbers --}}
                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span aria-current="page" class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white border rounded-md text-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $p->url($i) }}" class="inline-flex items-center px-3 py-1.5 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50">{{ $i }}</a>
                                @endif
                            @endfor

                            {{-- Ellipsis + last --}}
                            @if($end < $last)
                                @if($end < $last - 1)
                                    <span class="px-2 text-sm text-gray-500">…</span>
                                @endif
                                <a href="{{ $p->url($last) }}" class="inline-flex items-center px-3 py-1.5 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50">{{ $last }}</a>
                            @endif

                            {{-- Next --}}
                            @if($current < $last)
                                <a href="{{ $p->url($current + 1) }}" class="inline-flex items-center px-3 py-1.5 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50">Next &rarr;</a>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 border rounded-md text-sm text-gray-400">Next &rarr;</span>
                            @endif
                        </nav>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($selectedActivity)
        <div class="fixed inset-0 bg-black bg-opacity-40 flex justify-end z-40">
            <div class="w-full max-w-md bg-white h-full shadow-2xl border-l border-gray-200 flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Detail Aktivitas</h2>
                        <p class="text-xs text-gray-500 mt-1">Investigasi singkat aktivitas mitra.</p>
                    </div>
                    <a href="{{ route('admin.partners.activity', request()->except('activity_id')) }}"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                <div class="px-6 py-4 flex-1 overflow-y-auto space-y-6 text-sm">
                    @if ($selectedActivity && $suspicious['flag'])
                        <div class="border border-amber-200 bg-amber-50 rounded-xl px-4 py-3 flex items-start space-x-3">
                            <div class="mt-0.5">
                                <span class="text-lg">⚠️</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-amber-800">Aktivitas mencurigakan — kemungkinan akun
                                    diretas.</p>
                                <ul class="mt-2 text-[11px] text-amber-900 list-disc ml-4 space-y-1">
                                    @foreach ($suspicious['reasons'] as $reason)
                                        <li>{{ $reason }}</li>
                                    @endforeach
                                </ul>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.partners.toggle', $selectedActivity->user_id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-full text-[11px] font-semibold bg-red-600 text-white hover:bg-red-700">
                                            Blokir Mitra
                                        </button>
                                    </form>

                                    <form method="POST"
                                        action="{{ route('admin.partners.activity.reset_sessions', $selectedActivity->user_id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-full text-[11px] font-semibold bg-gray-800 text-white hover:bg-gray-900">
                                            Reset Sesi Login
                                        </button>
                                    </form>

                                    <form method="POST"
                                        action="{{ route('admin.partners.activity.reset_password', $selectedActivity->user_id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-full text-[11px] font-semibold bg-blue-600 text-white hover:bg-blue-700">
                                            Reset Password Otomatis
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 mb-2">Informasi Utama</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Nama Mitra</dt>
                                <dd class="font-medium text-gray-900">{{ $selectedActivity->user->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Aktivitas</dt>
                                <dd class="font-medium text-gray-900 flex items-center space-x-2">
                                    @php
                                        $meta = $activityMeta[$selectedActivity->activity_type] ?? null;
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold {{ $meta['badge'] ?? 'bg-gray-100 text-gray-700' }}">
                                        @if (!empty($meta['icon']))
                                            <span class="mr-1">{{ $meta['icon'] }}</span>
                                        @endif
                                        {{ $formatActivity($selectedActivity->activity_type) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Deskripsi</dt>
                                <dd class="mt-1 text-gray-800 text-xs whitespace-pre-line">
                                    {{ $selectedActivity->description ?? '-' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">IP Address</dt>
                                <dd class="font-mono text-gray-900">{{ $selectedActivity->ip_address ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">User Agent / Device</dt>
                                @php
                                    $device = $detectDevice($selectedActivity->user_agent ?? null);
                                @endphp
                                <dd class="mt-1 text-[11px] text-gray-700 leading-snug space-y-1">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $device['badge'] }}">
                                        {{ $device['label'] }}
                                    </span>
                                    <div>{{ $selectedActivity->user_agent ?? '-' }}</div>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Waktu</dt>
                                <dd class="text-gray-900 text-xs">
                                    {{ $selectedActivity->created_at->diffForHumans() }}
                                    <span class="text-gray-500">({{ $selectedActivity->created_at->format('Y-m-d H:i:s') }}
                                        WIB)</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 mb-2">Lokasi</h3>
                        <p class="text-[11px] text-gray-400">Lokasi GPS belum tersedia di log. Jika nanti ditambahkan, bisa
                            ditampilkan di sini.</p>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 mb-2">Aktivitas 24 Jam Terakhir</h3>
                        @if ($recentActivities->isEmpty())
                            <p class="text-[11px] text-gray-400">Tidak ada aktivitas lain dari mitra ini dalam 24 jam sebelum
                                aktivitas ini.</p>
                        @else
                            <ul class="space-y-2 text-[11px] text-gray-700">
                                @foreach ($recentActivities as $ra)
                                    <li class="flex justify-between">
                                        <div>
                                            @php
                                                $meta = $activityMeta[$ra->activity_type] ?? null;
                                            @endphp
                                            <span class="font-medium inline-flex items-center">
                                                @if (!empty($meta['icon']))
                                                    <span class="mr-1">{{ $meta['icon'] }}</span>
                                                @endif
                                                {{ $formatActivity($ra->activity_type) }}
                                            </span>
                                            <span class="text-gray-500"> · {{ $ra->description ?? '-' }}</span>
                                        </div>
                                        <span class="text-gray-400 ml-2 whitespace-nowrap">
                                            {{ $ra->created_at->diffForHumans($selectedActivity->created_at, true) }}
                                            ({{ $ra->created_at->format('Y-m-d H:i:s') }} WIB)
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('[data-role-filter-btn]');
            const rows = document.querySelectorAll('[data-role-row]');

            if (!buttons.length || !rows.length) {
                return;
            }

            const setActiveButton = (current) => {
                buttons.forEach((btn) => {
                    btn.classList.remove('bg-gray-900', 'text-white', 'border-gray-900');
                    btn.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-200');
                });

                current.classList.add('bg-gray-900', 'text-white', 'border-gray-900');
                current.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-200');
            };

            const applyRoleFilter = (role) => {
                rows.forEach((row) => {
                    const shouldShow = role === 'all' || row.dataset.role === role;
                    row.classList.toggle('hidden', !shouldShow);
                });
            };

            if (buttons[0]) {
                setActiveButton(buttons[0]);
                applyRoleFilter(buttons[0].dataset.roleFilter || 'all');
            }

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