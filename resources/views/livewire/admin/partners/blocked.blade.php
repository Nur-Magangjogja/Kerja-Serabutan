<div class="space-y-5">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
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
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Akun Diblokir</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola dan buka akses blokir akun mitra dan customer secara real-time</p>
        </div>
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($blockedUsers->total()) }} Akun Diblokir
        </span>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, atau no HP..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Filter Peran</label>
                <select wire:model.live="roleFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Peran</option>
                    <option value="mitra">🛵 Mitra</option>
                    <option value="customer">👤 Customer</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($blockedUsers->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    🛡️
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Akun Diblokir</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Saat ini tidak ada user yang berstatus diblokir permanen.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Kontak & Wilayah</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Waktu Terdaftar</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach ($blockedUsers as $user)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-200 text-xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                            <span class="text-[10px] text-gray-400">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->role === 'mitra' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $user->role === 'mitra' ? '🛵 Mitra' : '👤 Customer' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="text-xs text-gray-800 dark:text-gray-200">{{ $user->phone ?: '-' }}</p>
                                    <span class="text-[10px] text-gray-400">{{ $user->city?->name ?: '-' }}</span>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300">
                                        🚫 Diblokir
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-400 text-[11px]">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button type="button" wire:click="toggleBlock({{ $user->id }})" wire:confirm="Buka blokir untuk akun {{ $user->name }}?"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                        🔓 Buka Blokir
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $blockedUsers->links() }}
            </div>
        @endif
    </div>
</div>
