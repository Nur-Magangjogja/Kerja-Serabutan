<div class="space-y-5">
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
            'confirm_completion'     => ['label' => 'Customer Konfirmasi Selesai', 'badge' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border border-green-300 dark:border-green-700', 'icon' => '✅'],
            'help_confirmed'         => ['label' => 'Customer Konfirmasi Selesai', 'badge' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border border-green-300 dark:border-green-700', 'icon' => '✅'],
            'cancel_help'            => ['label' => 'Bantuan Dibatalkan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
            'help_cancelled'         => ['label' => 'Bantuan Dibatalkan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800', 'icon' => '❌'],
            'request_partner_cancel' => ['label' => 'Pengajuan Batal oleh Mitra', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800', 'icon' => '⚠️'],
            'auto_complete'          => ['label' => 'Auto-Konfirmasi Selesai', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800', 'icon' => '🤖'],
            'help_reviewed'          => ['label' => 'Ulasan & Rating Diberikan', 'badge' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800', 'icon' => '⭐'],
        ];

        $formatActivity = function ($type) use ($activityMeta) {
            return $activityMeta[$type]['label'] ?? ucwords(str_replace('_', ' ', $type));
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

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>⚡ Aktivitas Mitra & Customer</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Pantau seluruh rekam jejak pekerjaan jasa bantuan antara Mitra dan Customer secara real-time
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Total {{ number_format($activities->total()) }} Aktivitas Pekerjaan
            </span>
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3.5 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shrink-0">
                📋
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Total Log</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3.5 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                📅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Hari Ini</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['today']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3.5 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                👤
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Dari Customer</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['customer_acts']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3.5 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">
                🛵
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Dari Mitra</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['mitra_acts']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3.5 shadow-sm flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                ✅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Pekerjaan Selesai</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['completed_jobs']) }}</p>
            </div>
        </div>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[220px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Pekerjaan / Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Order ID, judul bantuan, customer, mitra, IP..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            {{-- Filter Role Pelaku --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Pelaku Aksi</label>
                <select wire:model.live="roleFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua (Customer & Mitra)</option>
                    <option value="customer">Hanya Customer</option>
                    <option value="mitra">Hanya Mitra</option>
                </select>
            </div>

            {{-- Filter Kota --}}
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Kota / Wilayah</label>
                <select wire:model.live="cityId"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Kota</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
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
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($activities->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    ⏱️
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Aktivitas Pekerjaan Ditemukan</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Belum ada rekaman aktivitas pekerjaan bantuan antara Mitra dan Customer yang sesuai dengan filter pencarian Anda.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Waktu & IP</th>
                            <th class="px-4 py-3">Pelaku Aksi</th>
                            <th class="px-4 py-3">Aksi Pekerjaan</th>
                            <th class="px-4 py-3">Tugas Jasa Bantuan</th>
                            <th class="px-4 py-3">Keterangan & Bukti</th>
                            <th class="px-4 py-3 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach ($activities as $act)
                            @php
                                $u = $act->user;
                                $help = $act->help;
                                $meta = $activityMeta[$act->activity_type] ?? null;
                                $isMitra = ($u?->role === 'mitra');
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                {{-- Waktu & IP --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="font-semibold text-gray-900 dark:text-white text-xs">{{ $act->created_at->format('d M Y') }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ $act->created_at->format('H:i:s') }} WIB</p>
                                    <span class="inline-block font-mono text-[10px] text-gray-400 mt-0.5">
                                        🌐 {{ $act->ip_address ?: '127.0.0.1' }}
                                    </span>
                                </td>

                                {{-- Pelaku Aksi (Customer / Mitra) --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $isMitra ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 border border-amber-300' : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 border border-emerald-300' }}">
                                            {{ strtoupper(substr($u?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-gray-900 dark:text-white text-xs truncate">{{ $u?->name ?? 'User Terhapus' }}</p>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $isMitra ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' }}">
                                                    {{ $u?->role ?? 'User' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-400 truncate">{{ $u?->email }}</p>
                                            @if($u?->city)
                                                <span class="text-[10px] text-gray-400 block truncate">📍 {{ is_object($u->city) ? $u->city->name : $u->city }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Aksi Pekerjaan --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $meta['badge'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        <span>{{ $meta['icon'] ?? '⚡' }}</span>
                                        <span>{{ $formatActivity($act->activity_type) }}</span>
                                    </span>
                                </td>

                                {{-- Tugas Jasa Bantuan --}}
                                <td class="px-4 py-3.5 max-w-xs">
                                    @if($help)
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    {{ $help->order_id ?: ('#HELP-' . $help->id) }}
                                                </span>
                                                <span class="font-bold text-gray-900 dark:text-white text-xs truncate" title="{{ $help->title }}">
                                                    {{ $help->title }}
                                                </span>
                                            </div>

                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                                <span>Tarif: <strong class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</strong></span>
                                                <span>•</span>
                                                <span>Status: <strong class="capitalize">{{ $help->status }}</strong></span>
                                            </div>

                                            <div class="text-[10px] text-gray-400 truncate">
                                                <span>Customer: {{ $help->customer->name ?? $help->user->name ?? '-' }}</span>
                                                @if($help->mitra)
                                                    <span> • Mitra: {{ $help->mitra->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Tidak terhubung dengan ID bantuan spesifik</span>
                                    @endif
                                </td>

                                {{-- Keterangan & Bukti Foto --}}
                                <td class="px-4 py-3.5 max-w-xs">
                                    <p class="text-gray-800 dark:text-gray-200 text-xs leading-relaxed line-clamp-2">
                                        {{ $act->description }}
                                    </p>

                                    @php
                                        $photoToShow = $act->photo ?: ($help?->proof_photo ?: null);
                                    @endphp
                                    @if($photoToShow)
                                        <div class="mt-1.5 flex items-center gap-1.5">
                                            <a href="{{ asset('storage/' . $photoToShow) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-primary-600 dark:text-primary-400 hover:underline font-semibold bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 rounded border border-primary-200 dark:border-primary-800">
                                                <span>📷 Lihat Bukti Foto</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                {{-- Detail Bantuan --}}
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($help)
                                        <button type="button" wire:click="showHelpDetails({{ $help->id }})"
                                            class="px-2.5 py-1 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1"
                                            title="Lihat Detail Bantuan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detail Bantuan</span>
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Modal Detail Bantuan Interaktif ===== --}}
    @if($showHelpModal && $selectedHelp)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity" wire:click="closeHelpDetails"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/40 px-2 py-0.5 rounded border border-primary-200 dark:border-primary-800">
                                {{ $selectedHelp->order_id ?: ('#HELP-' . $selectedHelp->id) }}
                            </span>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                {{ $selectedHelp->title }}
                            </h3>
                        </div>
                        <button type="button" wire:click="closeHelpDetails" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        {{-- Ringkasan Customer & Mitra --}}
                        <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-2">
                            <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[10px]">Pihak Terkait</h4>
                            <div>
                                <p class="text-gray-500">Customer (Pembuat):</p>
                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ $selectedHelp->customer->name ?? $selectedHelp->user->name ?? '-' }} ({{ $selectedHelp->customer->phone ?? '-' }})</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Mitra (Pelaksana):</p>
                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ $selectedHelp->mitra->name ?? 'Belum ada mitra' }} {{ $selectedHelp->mitra ? ('(' . $selectedHelp->mitra->phone . ')') : '' }}</p>
                            </div>
                        </div>

                        {{-- Ringkasan Biaya & Status --}}
                        <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-2">
                            <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[10px]">Status & Pembayaran</h4>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Status Tugas:</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                    {{ $selectedHelp->status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Nilai Bantuan:</span>
                                <span class="font-bold text-emerald-600 text-sm">Rp {{ number_format($selectedHelp->amount ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Kota / Wilayah:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedHelp->city->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi & Alamat --}}
                    <div class="space-y-2 text-xs">
                        <div>
                            <p class="font-semibold text-gray-700 dark:text-gray-300">Deskripsi Bantuan:</p>
                            <p class="text-gray-600 dark:text-gray-400 mt-0.5 bg-gray-50 dark:bg-gray-750 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                {{ $selectedHelp->description ?: 'Tidak ada deskripsi detail' }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700 dark:text-gray-300">Alamat Lengkap:</p>
                            <p class="text-gray-600 dark:text-gray-400 mt-0.5 bg-gray-50 dark:bg-gray-750 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                📍 {{ $selectedHelp->full_address ?: ($selectedHelp->location ?: 'Alamat tidak dicantumkan') }}
                            </p>
                        </div>
                    </div>

                    {{-- Bukti Foto Pengerjaan --}}
                    @if($selectedHelp->proof_photo || $selectedHelp->photo)
                        <div class="space-y-1.5 text-xs">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">Foto & Bukti Pengerjaan:</p>
                            <div class="flex items-center gap-3">
                                @if($selectedHelp->photo)
                                    <div class="text-center">
                                        <img src="{{ asset('storage/' . $selectedHelp->photo) }}" alt="Foto Bantuan" class="w-24 h-24 object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
                                        <span class="text-[10px] text-gray-400 block mt-1">Foto Permintaan</span>
                                    </div>
                                @endif
                                @if($selectedHelp->proof_photo)
                                    <div class="text-center">
                                        <img src="{{ asset('storage/' . $selectedHelp->proof_photo) }}" alt="Bukti Selesai" class="w-24 h-24 object-cover rounded-xl border border-emerald-300 dark:border-emerald-700 shadow-xs">
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block mt-1">Bukti Selesai Mitra</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Modal Footer --}}
                    <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeHelpDetails"
                            class="px-5 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl text-xs transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>