<div class="space-y-5">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';

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
            'help_cancelled' => ['label' => 'Bantuan Dibatalkan', 'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'],
            'partner_blocked' => ['label' => 'Mitra Diblokir', 'badge' => 'bg-rose-100 dark:bg-rose-900/50 text-rose-800 dark:text-rose-300'],
            'partner_unblocked' => ['label' => 'Blokir Mitra Dibuka', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],
            'profile_updated' => ['label' => 'Profil Diperbarui', 'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
            'password_changed' => ['label' => 'Password Diubah', 'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'],
            'session_reset' => ['label' => 'Sesi Direset Admin', 'badge' => 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'],
            'password_reset_by_admin' => ['label' => 'Password Direset Admin', 'badge' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
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

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Aktivitas Mitra & Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau rekaman audit aktivitas pengguna sistem secara real-time</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
                Total {{ number_format($activities->total()) }} Log Aktivitas
            </span>
        </div>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Aktivitas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama/email mitra, deskripsi, atau IP..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Tipe Aktivitas</label>
                <select wire:model.live="activityType"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Tipe Aktivitas</option>
                    @foreach($activityMeta as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

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
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($activities->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    ⏱️
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Log Aktivitas</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Belum ada aktivitas yang tercatat sesuai dengan parameter pencarian Anda.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Tipe Aktivitas</th>
                            <th class="px-4 py-3">Deskripsi & Perangkat</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3 text-right">Aksi Akun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach ($activities as $act)
                            @php $u = $act->user; @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-200 text-xs shrink-0">
                                            {{ strtoupper(substr($u?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $u?->name ?? 'User Terhapus' }}</p>
                                            <span class="text-[10px] text-gray-400">{{ $u?->email }}</span>
                                            @if($u?->city)
                                                <span class="text-[10px] text-gray-400 block">• {{ is_object($u->city) ? $u->city->name : $u->city }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php $meta = $activityMeta[$act->activity_type] ?? null; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $meta['badge'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $formatActivity($act->activity_type) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 max-w-xs">
                                    <p class="text-gray-800 dark:text-gray-200 text-xs line-clamp-2">{{ $act->description }}</p>
                                    @if($act->user_agent)
                                        <span class="text-[10px] text-gray-400 line-clamp-1 mt-0.5" title="{{ $act->user_agent }}">
                                            🌐 {{ $act->user_agent }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[11px] text-gray-500">
                                    {{ $act->ip_address ?: '-' }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-400 text-[11px]">
                                    {{ $act->created_at->format('d M Y • H:i:s') }}
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($u)
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" wire:click="resetSession({{ $u->id }})" wire:confirm="Reset seluruh sesi aktif untuk pengguna ini?"
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                                Reset Sesi
                                            </button>
                                            <button type="button" wire:click="resetPassword({{ $u->id }})" wire:confirm="Reset password pengguna ini ke default (password123)?"
                                                class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                                Reset Sandi
                                            </button>
                                        </div>
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
</div>