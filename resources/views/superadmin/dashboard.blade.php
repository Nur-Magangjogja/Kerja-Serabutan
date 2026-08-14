@php
    // Ensure variables exist to avoid undefined errors
    $stats = $stats ?? [];
    $recentUsers = $recentUsers ?? collect();
    $recentHelps = $recentHelps ?? collect();
    // Ensure chart data exists to avoid Blade parsing issues when using @json
    $userChart = $userChart ?? [
        'daily' => ['labels' => [], 'data' => []],
        'monthly' => ['labels' => [], 'data' => []],
        'yearly' => ['labels' => [], 'data' => []],
    ];
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-8">
        <!-- Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">Super Admin Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">Halo, {{ auth()->user()->name }} — berikut ringkasan sistem.</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
                <div class="relative">
                    @php $unreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0; @endphp
                    <a href="{{ route('customer.notifications.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center" aria-label="Notifications">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($unreadNotifications > 0)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold text-white bg-red-600 rounded-full">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg,#eef2ff,#e0f2fe);">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-2-2h-3l-2-2H10L8 6H5a2 2 0 0 0-2 2v8"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Total Pengguna</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($stats['total_users'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500">Semua pengguna</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg,#fff7ed,#ffedd5);">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Customer</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($stats['total_customers'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500">Pengguna customer</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg,#ecfdf5,#bbf7d0);">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Total Kota</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($stats['total_cities'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500">Kota terdaftar</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg,#eff6ff,#dbeafe);">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Mitra</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($stats['total_mitras'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500">Pengguna mitra</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" style="background: linear-gradient(135deg,#ecfeff,#bbf7d0);">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Admin</div>
                    <div class="text-lg font-bold text-gray-900">{{ number_format($stats['total_admins'] ?? 0) }}</div>
                    <div class="text-sm text-gray-500">Admin & Super Admin</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Data Pengguna Terakhir</h2>
                    <div class="text-xs text-gray-500">Ringkasan singkat</div>
                </div>
                <div class="bg-gray-50 rounded-md border border-gray-100 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm text-gray-500">Grafik pendaftaran pengguna</div>
                        <div class="flex items-center gap-2">
                            <div id="chartRangeTabs" role="tablist" class="inline-flex bg-white rounded-md border border-gray-200 shadow-sm">
                                <button type="button" data-range="daily" class="chart-range-tab px-3 py-1.5 text-sm rounded-l-md border-r transition-all duration-300 ease-out">Harian</button>
                                <button type="button" data-range="monthly" class="chart-range-tab px-3 py-1.5 text-sm border-r transition-all duration-300 ease-out">Bulanan</button>
                                <button type="button" data-range="yearly" class="chart-range-tab px-3 py-1.5 text-sm rounded-r-md transition-all duration-300 ease-out">Tahunan</button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full transition-all duration-500 ease-out" id="chartContainer">
                        <canvas id="usersChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Ringkasan</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Total Pengguna</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($stats['total_users'] ?? 0) }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Customer</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($stats['total_customers'] ?? 0) }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Mitra</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($stats['total_mitras'] ?? 0) }}</div>
                    </div>
                    <hr class="my-2" />
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Total Kota</span>
                        <span class="font-medium">{{ number_format($stats['total_cities'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Total Kategori</span>
                        <span class="font-medium">{{ number_format($stats['total_categories'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <!-- Aksi Cepat -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Aksi Cepat</h2>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <a href="{{ route('superadmin.users') }}" class="group bg-gradient-to-br from-blue-50 to-blue-100/50 hover:from-blue-100 hover:to-blue-200/50 rounded-xl p-4 border border-blue-200/50 transition-all duration-300 hover:shadow-md hover:scale-105 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Kelola User</h3>
                        <p class="text-xs text-gray-600">Manajemen semua pengguna</p>
                    </a>

                    <a href="{{ route('superadmin.cities') }}" class="group bg-gradient-to-br from-emerald-50 to-emerald-100/50 hover:from-emerald-100 hover:to-emerald-200/50 rounded-xl p-4 border border-emerald-200/50 transition-all duration-300 hover:shadow-md hover:scale-105 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Kelola Kota</h3>
                        <p class="text-xs text-gray-600">Manajemen kota layanan</p>
                    </a>

                    <a href="{{ route('superadmin.settings.help') }}" class="group bg-gradient-to-br from-yellow-50 to-yellow-100/50 hover:from-yellow-100 hover:to-yellow-200/50 rounded-xl p-4 border border-yellow-200/50 transition-all duration-300 hover:shadow-md hover:scale-105 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.79 0 1.5.3 2.04.78L20 14v6a1 1 0 01-1 1h-6l-5.22-5.22A4 4 0 1112 8z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Pengaturan Bantuan</h3>
                        <p class="text-xs text-gray-600">Atur minimum & biaya admin</p>
                    </a>

                    <!-- Categories quick action removed -->

                    <!-- Verifikasi KTP quick action removed -->
                </div>
            </div>

            <!-- Pengguna Terbaru -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Pengguna Terbaru</h2>
                </div>
                @if($recentUsers->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 rounded-full bg-gray-100 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Belum ada pengguna baru</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentUsers as $u)
                            <div class="group hover:bg-gray-50 rounded-lg p-3 -mx-3 transition-all duration-200 border border-transparent hover:border-gray-200">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0 text-white font-bold text-sm shadow-sm">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('superadmin.users') }}" class="text-sm font-bold text-gray-900 hover:text-blue-600 transition-colors block truncate">{{ $u->name }}</a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500 truncate">{{ $u->email }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">{{ ucfirst($u->role) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 whitespace-nowrap">{{ $u->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Permintaan Terbaru -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Permintaan Terbaru</h2>
                </div>
                @if($recentHelps->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 rounded-full bg-gray-100 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Belum ada permintaan</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentHelps as $h)
                            <div class="group hover:bg-gray-50 rounded-lg p-3 -mx-3 transition-all duration-200 border border-transparent hover:border-gray-200">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <a href="#" class="text-sm font-bold text-gray-900 hover:text-rose-600 transition-colors line-clamp-2 leading-snug mb-2">{{ $h->title }}</a>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs text-gray-500">oleh {{ optional($h->user)->name ?? '—' }}</span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $h->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($h->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') }}">
                                                @if($h->status === 'pending')
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                                @elseif($h->status === 'active')
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ ucfirst($h->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent helps table (full width) -->
        <div class="grid grid-cols-1 mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Permintaan Bantuan Terbaru</h3>
                    </div>
                    <!-- 'Lihat semua' link to Moderasi Bantuan removed -->
                </div>

                @if($recentHelps->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="pb-3 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="pb-3 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Judul</th>
                                    <th class="pb-3 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="pb-3 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Pengguna</th>
                                    <th class="pb-3 px-4 text-xs font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentHelps as $help)
                                    <tr class="group hover:bg-gray-50 transition-colors duration-150">
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 font-bold text-sm">
                                                {{ $help->id }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <a href="#" class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2 leading-relaxed">
                                                {{ $help->title }}
                                            </a>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $help->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($help->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') }}">
                                                @if($help->status === 'pending')
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                                @elseif($help->status === 'completed')
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ ucfirst($help->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                    {{ strtoupper(substr(optional($help->user)->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-700">{{ optional($help->user)->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2 text-gray-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="text-sm">{{ $help->created_at->diffForHumans() }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mx-auto flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Belum ada permintaan</h4>
                        <p class="text-sm text-gray-500">Permintaan bantuan akan muncul di sini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($userChart);

            const ctx = document.getElementById('usersChart').getContext('2d');
            let usersChart = null;

            function renderRange(range) {
                // Fade out and scale down animation
                const chartContainer = document.getElementById('chartContainer');
                chartContainer.style.opacity = '0';
                chartContainer.style.transform = 'translateY(20px) scale(0.98)';
                
                setTimeout(() => {
                    // choose max ticks limit based on range
                    let maxTicks = 12;
                    if (range === 'daily') maxTicks = 10;
                    if (range === 'monthly') maxTicks = 12;
                    if (range === 'yearly') maxTicks = 6;

                    // create gradient for bars
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, 'rgba(59,130,246,0.95)');
                    gradient.addColorStop(1, 'rgba(59,130,246,0.6)');

                    const cfg = {
                        type: 'bar',
                        data: {
                            labels: chartData[range].labels,
                            datasets: [{
                                label: 'Pendaftaran',
                                data: chartData[range].data,
                                backgroundColor: gradient,
                                borderColor: 'rgba(59,130,246,1)',
                                borderWidth: 1,
                                hoverBackgroundColor: 'rgba(59,130,246,0.95)',
                                borderRadius: 6,
                                maxBarThickness: 36
                            }]
                        },
                        options: {
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: {
                                        size: 13,
                                        weight: '600'
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    callbacks: {
                                        label: function(ctx) {
                                            const v = ctx.raw ?? ctx.parsed?.y ?? 0;
                                            return 'Pendaftaran: ' + (typeof v === 'number' ? v.toLocaleString() : v);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { 
                                    stacked: false, 
                                    ticks: { 
                                        maxRotation: 45, 
                                        minRotation: 0, 
                                        autoSkip: true, 
                                        maxTicksLimit: maxTicks 
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: { 
                                    beginAtZero: true, 
                                    ticks: { 
                                        precision: 0, 
                                        stepSize: 1, 
                                        callback: function(value){ 
                                            return Number(value).toLocaleString(); 
                                        } 
                                    }, 
                                    grid: { 
                                        color: 'rgba(15,23,42,0.04)' 
                                    } 
                                }
                            },
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { 
                                mode: 'index', 
                                intersect: false 
                            },
                            layout: { 
                                padding: { 
                                    top: 6, 
                                    bottom: 2, 
                                    left: 0, 
                                    right: 0 
                                } 
                            },
                            animation: {
                                duration: 1200,
                                easing: 'easeOutCubic',
                                delay: (context) => {
                                    let delay = 0;
                                    if (context.type === 'data' && context.mode === 'default') {
                                        delay = context.dataIndex * 80;
                                    }
                                    return delay;
                                },
                                y: {
                                    from: (ctx) => {
                                        if (ctx.type === 'data') {
                                            return ctx.chart.scales.y.getPixelForValue(0);
                                        }
                                    }
                                }
                            }
                        }
                    };

                    if (usersChart) {
                        usersChart.destroy();
                    }
                    usersChart = new Chart(ctx, cfg);
                    
                    // Fade in and scale up animation
                    setTimeout(() => {
                        chartContainer.style.opacity = '1';
                        chartContainer.style.transform = 'translateY(0) scale(1)';
                    }, 100);
                }, 200);
            }

            // tabs instead of selector
            const tabs = document.querySelectorAll('.chart-range-tab');
            const validRanges = ['daily','monthly','yearly'];
            let initialRange = 'daily';
            const saved = localStorage.getItem('superadmin.usersChart.range');
            if (saved && validRanges.includes(saved)) initialRange = saved;

            function setActive(range) {
                tabs.forEach(t => {
                    if (t.dataset.range === range) {
                        t.classList.add('bg-blue-600','text-white','border-blue-600');
                        t.classList.remove('text-gray-600','bg-white','border-gray-200');
                    } else {
                        t.classList.remove('bg-blue-600','text-white','border-blue-600');
                        t.classList.add('text-gray-600','bg-white','border-gray-200');
                    }
                });
            }

            if (tabs.length) {
                tabs.forEach(tab => {
                    tab.addEventListener('click', function () {
                        const r = tab.dataset.range;
                        if (!validRanges.includes(r)) return;
                        setActive(r);
                        localStorage.setItem('superadmin.usersChart.range', r);
                        renderRange(r);
                    });
                });
                // set initial active and render
                setActive(initialRange);
                renderRange(initialRange);
            } else {
                renderRange('daily');
            }
        });
    </script>

</div>