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

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Akun Diblokir</h1>
                @if(!$isSuperAdmin && auth()->user() && auth()->user()->active_admin_city_label)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Wilayah: {{ auth()->user()->active_admin_city_label }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola, blokir pengguna bermasalah, dan buka akses blokir akun mitra & customer</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-2 rounded-xl">
                Total {{ number_format($blockedUsers->total()) }} Akun Diblokir
            </span>
            <button type="button" wire:click="openBlockModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <span>Blokir Pengguna (Mitra / Customer)</span>
            </button>
        </div>
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
                    Saat ini tidak ada user yang berstatus diblokir permanen. Anda dapat memblokir akun yang melanggar aturan melalui tombol di atas.
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
                                    <span class="text-[10px] text-gray-400">{{ $user->city_name ?: '-' }}</span>
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

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $blockedUsers->links('vendor.pagination.superadmin') }}
            </div>
        @endif
    </div>

    {{-- ===== Modal Blokir Pengguna (Mitra & Customer) ===== --}}
    @if($showBlockModal)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closeBlockModal">
        <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white">Blokir Pengguna (Mitra / Customer)</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Pilih akun aktif yang akan dinonaktifkan aksesnya</p>
                    </div>
                </div>
                <button wire:click="closeBlockModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-6 max-h-[80vh] overflow-y-auto space-y-4">
                {{-- Filter Peran Target --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Pilih Kategori Peran Pengguna</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="$set('targetRole', 'all')"
                            class="py-2 px-3 text-xs font-bold rounded-xl border transition cursor-pointer {{ $targetRole === 'all' ? 'bg-primary-50 dark:bg-primary-950/50 border-primary-500 text-primary-700 dark:text-primary-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                            Semua
                        </button>
                        <button type="button" wire:click="$set('targetRole', 'mitra')"
                            class="py-2 px-3 text-xs font-bold rounded-xl border transition cursor-pointer {{ $targetRole === 'mitra' ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-500 text-emerald-700 dark:text-emerald-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                            🛵 Mitra Saja
                        </button>
                        <button type="button" wire:click="$set('targetRole', 'customer')"
                            class="py-2 px-3 text-xs font-bold rounded-xl border transition cursor-pointer {{ $targetRole === 'customer' ? 'bg-blue-50 dark:bg-blue-950/50 border-blue-500 text-blue-700 dark:text-blue-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                            👤 Customer Saja
                        </button>
                    </div>
                </div>

                {{-- Input Pencarian User --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Cari Akun yang Ingin Diblokir</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" wire:model.live.debounce.250ms="userSearch"
                            placeholder="Ketik nama, email, atau nomor HP pengguna..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                {{-- Hasil Daftar Pengguna Aktif --}}
                <div class="space-y-1.5 max-h-48 overflow-y-auto border border-gray-100 dark:border-gray-700 rounded-2xl p-2 bg-gray-50/50 dark:bg-gray-900/40">
                    @if($availableUsers->isEmpty())
                        <div class="py-6 text-center text-xs text-gray-400">
                            Tidak ditemukan akun aktif yang sesuai pencarian.
                        </div>
                    @else
                        @foreach($availableUsers as $u)
                            <div wire:click="selectUserForBlock({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->role }}', '{{ addslashes($u->email) }}')"
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-center justify-between gap-3 {{ $selectedUserId === $u->id ? 'bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700/60 border border-transparent' }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-200 text-xs shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="flex items-center gap-1.5">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $u->name }}</p>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase {{ $u->role === 'mitra' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                                {{ $u->role === 'mitra' ? 'Mitra' : 'Customer' }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-gray-400 truncate">{{ $u->email }} • {{ $u->city_name ?? 'Semua Kota' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if($selectedUserId === $u->id)
                                        <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                    @else
                                        <span class="text-[10px] font-bold text-gray-400 hover:text-rose-600">Pilih</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                @error('selectedUserId') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror

                {{-- Info Pengguna Terpilih --}}
                @if($selectedUserId)
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-2xl flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] text-rose-500 font-bold block">Akun Terpilih untuk Diblokir:</span>
                            <span class="font-black text-rose-900 dark:text-rose-200">{{ $selectedUserName }} ({{ ucfirst($selectedUserRole) }})</span>
                            <span class="text-[11px] text-rose-700/80 dark:text-rose-300/80 block">{{ $selectedUserEmail }}</span>
                        </div>
                        <button type="button" wire:click="$set('selectedUserId', null)" class="text-xs text-rose-600 hover:underline font-bold">Ganti</button>
                    </div>
                @endif

                {{-- Input Alasan Pemblokiran --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Alasan Pemblokiran Akun <span class="text-rose-500">*</span></label>
                    <textarea wire:model="blockReason" rows="3"
                        placeholder="Contoh: Pelanggaran berat kode etik / indikasi akun fiktif / aduan penipuan dari pengguna lain..."
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    @error('blockReason') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                <button type="button" wire:click="closeBlockModal"
                    class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" wire:click="submitBlockUser"
                    class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition cursor-pointer shadow-xs flex items-center gap-1.5">
                    <span>🚫 Konfirmasi Blokir Akun</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
