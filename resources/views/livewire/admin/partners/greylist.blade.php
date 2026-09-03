<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header Section --}}
    <div class="flex items-center justify-between gap-4 flex-wrap bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
        <div>
            <h1 class="text-lg font-extrabold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">⚠️</span>
                <span>Daftar Abu-Abu (Pengawasan & Shadow Ban)</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Kelola daftar user bermasalah (Mitra & Customer), terbitkan Surat Peringatan (SP 1 - 3), dan terapkan Shadow Ban secara real-time.
            </p>
        </div>

        <button type="button" wire:click="openAddModal"
            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Masukkan User Bermasalah</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Pengawasan</span>
            <div class="text-xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $totalGreylist }}</div>
            <span class="text-[10px] text-gray-400">User dalam daftar abu-abu</span>
        </div>

        <div class="bg-rose-50/70 dark:bg-rose-950/40 p-4 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">🚫 Shadow Banned</span>
            <div class="text-xl font-extrabold text-rose-950 dark:text-rose-100 mt-1">{{ $totalShadowBanned }}</div>
            <span class="text-[10px] text-rose-800 dark:text-rose-300">Akses bantuan dibatasi</span>
        </div>

        <div class="bg-amber-50/70 dark:bg-amber-950/40 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">📢 Dalam Peringatan (SP)</span>
            <div class="text-xl font-extrabold text-amber-950 dark:text-amber-100 mt-1">{{ $totalWarning }}</div>
            <span class="text-[10px] text-amber-800 dark:text-amber-300">Menerima surat peringatan</span>
        </div>

        <div class="bg-emerald-50/70 dark:bg-emerald-950/40 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">🛵 Mitra Diawasi</span>
            <div class="text-xl font-extrabold text-emerald-950 dark:text-emerald-100 mt-1">{{ $totalMitra }}</div>
            <span class="text-[10px] text-emerald-800 dark:text-emerald-300">Mitra bermasalah</span>
        </div>

        <div class="bg-blue-50/70 dark:bg-blue-950/40 p-4 rounded-2xl border border-blue-200 dark:border-blue-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">👤 Customer Diawasi</span>
            <div class="text-xl font-extrabold text-blue-950 dark:text-blue-100 mt-1">{{ $totalCustomer }}</div>
            <span class="text-[10px] text-blue-800 dark:text-blue-300">Customer bermasalah</span>
        </div>
    </div>

    {{-- Realtime Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, no HP..."
                    class="w-full px-3.5 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <select wire:model.live="roleFilter" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Peran (Mitra & Customer)</option>
                    <option value="mitra">🛵 Hanya Mitra</option>
                    <option value="customer">👤 Hanya Customer</option>
                </select>
            </div>

            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Status Pengawasan</option>
                    <option value="shadow_banned">🚫 Shadow Banned</option>
                    <option value="warning_only">📢 Memiliki SP (Peringatan)</option>
                    <option value="active_greylist">⚠️ Dalam Daftar Abu-Abu</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        @if($users->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    🛡️
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada User dalam Pengawasan</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Saat ini tidak ada user yang terdaftar dalam Daftar Abu-Abu, Surat Peringatan (SP), ataupun Shadow Ban.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Status Pengawasan</th>
                            <th class="px-4 py-3">Surat Peringatan (SP)</th>
                            <th class="px-4 py-3">Status Akun</th>
                            <th class="px-4 py-3">Histori Terakhir</th>
                            <th class="px-4 py-3 text-right">Aksi Moderasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-200 text-xs shrink-0 overflow-hidden">
                                            @if($user->selfie_photo || $user->photo)
                                                <img src="{{ asset('storage/' . ($user->selfie_photo ?: $user->photo)) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $user->phone ?? $user->email }}</p>
                                            @php
                                                $cityName = $user->city_name ?? (is_object($user->city) ? $user->city->name : ($user->city ?? null));
                                            @endphp
                                            @if($cityName)
                                                <span class="text-[10px] text-gray-400">{{ $cityName }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $user->role === 'mitra' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                        {{ $user->role === 'mitra' ? '🛵 Mitra' : '👤 Customer' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($user->is_shadow_banned)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                🚫 Shadow Banned
                                            </span>
                                            <p class="text-[10px] text-rose-700/80 dark:text-rose-300/80">
                                                {{ $user->role === 'mitra' ? 'Akses tugas dibatasi' : 'Pembuatan order dibatasi' }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                ⚠️ Dalam Pengawasan
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($user->warning_level > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $user->warning_level == 1 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/70 dark:text-yellow-300' : ($user->warning_level == 2 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300') }}">
                                            Surat Peringatan {{ $user->warning_level }} (SP {{ $user->warning_level }})
                                        </span>
                                        @if($user->latest_warning_at)
                                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $user->latest_warning_at->format('d M Y • H:i') }}</p>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($user->status === 'blocked')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            ⛔ Diblokir
                                        </span>
                                    @elseif($user->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            ✓ Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="text-[11px] space-y-0.5">
                                        @php $lastLog = $user->greylistLogs->first(); @endphp
                                        @if($lastLog)
                                            <p class="font-medium text-gray-800 dark:text-gray-200">
                                                {{ ucfirst(str_replace('_', ' ', $lastLog->action)) }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ $lastLog->admin?->name ?? 'Sistem' }} • {{ $lastLog->created_at->diffForHumans() }}
                                            </p>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        {{-- Tombol Detail Moderasi (Alasan & Surat Peringatan hanya di sini) --}}
                                        <button type="button" wire:click="openDetailModal({{ $user->id }})"
                                            class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/50 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer" title="Lihat Detail & Alasan SP">
                                            <span>Detail</span>
                                        </button>

                                        {{-- Beri SP --}}
                                        <button type="button" wire:click="openWarningModal({{ $user->id }})"
                                            class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer" title="Terbitkan Surat Peringatan">
                                            <span>📢 Beri SP</span>
                                        </button>

                                        {{-- Toggle Shadow Ban --}}
                                        <button type="button" wire:click="toggleShadowBan({{ $user->id }})" wire:confirm="{{ $user->is_shadow_banned ? 'Cabut Shadow Ban untuk user ini?' : 'Terapkan Shadow Ban untuk membatasi fitur bantuan user ini?' }}"
                                            class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition border flex items-center gap-1 cursor-pointer {{ $user->is_shadow_banned ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800/60' }}" title="Batasi Akses Fitur">
                                            <span>{{ $user->is_shadow_banned ? '🔓 Buka Ban' : '🚫 Shadow Ban' }}</span>
                                        </button>

                                        {{-- Tombol Blokir: HANYA MUNCUL JIKA USER MENCAPAI SP 3 --}}
                                        @if($user->warning_level >= 3)
                                            @if($user->status === 'blocked')
                                                <span class="px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-lg text-[11px] font-bold" title="Akun telah diblokir">
                                                    ⛔ Diblokir
                                                </span>
                                            @else
                                                <button type="button" wire:click="blockUser({{ $user->id }})" wire:confirm="PERINGATAN: User ini telah mencapai batas SP 3. Apakah Anda yakin ingin MEMBLOKIR akun {{ $user->name }} secara permanen?"
                                                    class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer shadow-xs" title="Blokir Akun Permanen (Khusus SP 3)">
                                                    <span>⛔ Blokir</span>
                                                </button>
                                            @endif
                                        @endif

                                        {{-- Pulihkan Akun --}}
                                        <button type="button" wire:click="removeFromGreylist({{ $user->id }})" wire:confirm="Pulihkan akun ini dan hapus dari Daftar Abu-Abu?"
                                            class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-[11px] font-semibold transition cursor-pointer" title="Pulihkan ke Status Normal">
                                            <span>🔄 Pulihkan</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $users->links('vendor.pagination.superadmin') }}
            </div>
        @endif
    </div>

    {{-- MODAL 1: Tambah User ke Daftar Abu-Abu --}}
    @if($showAddModal)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" wire:click="closeAddModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 z-10">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>⚠️</span> Masukkan User ke Daftar Abu-Abu
                    </h3>
                    <button type="button" wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Cari User (Mitra / Customer)</label>
                        <input type="text" wire:model.live.debounce.300ms="userSearch" placeholder="Ketik minimal 2 karakter nama/email/hp..."
                            class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 placeholder:opacity-60 outline-none focus:ring-2 focus:ring-primary-500">

                        @if(!empty($candidateUsers))
                            <div class="mt-2 border border-gray-200 dark:border-gray-600 rounded-xl max-h-40 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-700 shadow-sm">
                                @foreach($candidateUsers as $cand)
                                    <div wire:click="selectUserForGreylist({{ $cand->id }}, '{{ addslashes($cand->name) }}')"
                                        class="p-2.5 hover:bg-primary-50 dark:hover:bg-primary-950/40 cursor-pointer flex items-center justify-between transition {{ $selectedUserId === $cand->id ? 'bg-primary-50 dark:bg-primary-950/50' : '' }}">
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $cand->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $cand->phone ?? $cand->email }}</p>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $cand->role === 'mitra' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ strtoupper($cand->role) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($selectedUserId)
                            <div class="mt-2 p-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-200 flex items-center justify-between">
                                <span>User Terpilih: <strong>{{ $selectedUserName }}</strong></span>
                                <span class="text-xs">✓</span>
                            </div>
                        @endif
                        @error('selectedUserId') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Tingkat Peringatan Awal</label>
                        <select wire:model.live="addWarningLevel" class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="0">Hanya Pemantauan (Tanpa SP)</option>
                            <option value="1">Surat Peringatan 1 (SP 1 - Teguran Ringan)</option>
                            <option value="2">Surat Peringatan 2 (SP 2 - Peringatan Sedang)</option>
                            <option value="3">Surat Peringatan 3 (SP 3 - Batas Terakhir)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Peninjauan / Pelanggaran</label>
                        <textarea wire:model="addReason" rows="3" placeholder="Contoh: Sering membatalkan tugas sepihak atau perilaku tidak pantas pada pesanan #..."
                            class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 placeholder:opacity-60 outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        @error('addReason') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 bg-rose-50/70 dark:bg-rose-950/30 rounded-xl border border-rose-100 dark:border-rose-900/40 flex items-start gap-2.5">
                        <input type="checkbox" id="shadowBanCheck" wire:model="addApplyShadowBan" class="mt-0.5 rounded text-rose-600 focus:ring-rose-500 cursor-pointer">
                        <label for="shadowBanCheck" class="text-xs text-rose-900 dark:text-rose-200 cursor-pointer">
                            <span class="font-bold block">Terapkan Shadow Ban Langsung</span>
                            <span class="text-[11px] opacity-80">Membatasi user dari fitur pembuatan atau pencarian tugas bantuan tanpa memutus akun.</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeAddModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="submitAddToGreylist" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                        Simpan ke Daftar Abu-Abu
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- MODAL 2: Terbitkan Surat Peringatan (SP) --}}
    @if($showWarningModal)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" wire:click="closeWarningModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 z-10">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>📢</span> Terbitkan Surat Peringatan Resmi (SP)
                    </h3>
                    <button type="button" wire:click="closeWarningModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/60 rounded-xl border border-gray-100 dark:border-gray-600">
                        <span class="text-gray-400 block text-[10px]">Penerima Peringatan:</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $targetUserName }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">SP Saat Ini: SP {{ $currentWarningLevel }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Tingkat Surat Peringatan yang Diterbitkan</label>
                        <select wire:model.live="newWarningLevel" class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="1">SP 1 (Teguran Ringan / Pertama)</option>
                            <option value="2">SP 2 (Peringatan Sedang / Berulang)</option>
                            <option value="3">SP 3 (Peringatan Keras / Batas Terakhir)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Penerbitan SP</label>
                        <input type="text" wire:model="warningReason" placeholder="Misal: Pelanggaran pembatalan pesanan bantuan #..."
                            class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 placeholder:opacity-60 outline-none focus:ring-2 focus:ring-primary-500">
                        @error('warningReason') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Pesan Resmi Peringatan (Akan Muncul di Dashboard User)</label>
                        <textarea wire:model="warningMessage" rows="3" class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        @error('warningMessage') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeWarningModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="submitWarning" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                        Terbitkan SP Resmi
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- MODAL 3: Detail Moderasi Pengguna (Khusus Admin & Superadmin) --}}
    @if($showDetailModal && $detailUser)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] flex flex-col z-10">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl text-base">🛡️</span>
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Detail Moderasi Pengguna</h3>
                            <p class="text-[11px] text-gray-400">Data dan alasan penerbitan SP khusus Admin & Superadmin</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none cursor-pointer">&times;</button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="space-y-4 text-xs overflow-y-auto pr-1 flex-1">
                    {{-- User Profile Card --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-200 text-sm shrink-0 overflow-hidden shadow-xs">
                                @if($detailUser->selfie_photo || $detailUser->photo)
                                    <img src="{{ asset('storage/' . ($detailUser->selfie_photo ?: $detailUser->photo)) }}" alt="{{ $detailUser->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($detailUser->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $detailUser->name }}</h4>
                                <p class="text-gray-400 text-[11px]">{{ $detailUser->phone ?? 'Tanpa HP' }} • {{ $detailUser->email }}</p>
                                @php
                                    $cityName = $detailUser->city_name ?? (is_object($detailUser->city) ? $detailUser->city->name : ($detailUser->city ?? null));
                                @endphp
                                @if($cityName)
                                    <p class="text-[10px] text-gray-400 mt-0.5">📍 {{ $cityName }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $detailUser->role === 'mitra' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                {{ $detailUser->role === 'mitra' ? '🛵 Mitra' : '👤 Customer' }}
                            </span>

                            @if($detailUser->status === 'blocked')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    ⛔ Diblokir
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    ✓ Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Moderation Status Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="p-3 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl border border-amber-200/60 dark:border-amber-800/40">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300 block">Surat Peringatan</span>
                            <div class="text-sm font-extrabold text-amber-950 dark:text-amber-100 mt-0.5">
                                {{ $detailUser->warning_level > 0 ? "SP {$detailUser->warning_level}" : "Normal (Tanpa SP)" }}
                            </div>
                            <span class="text-[10px] text-amber-800/70 dark:text-amber-300/70 block mt-0.5">
                                {{ $detailUser->warning_level == 1 ? 'Teguran Ringan' : ($detailUser->warning_level == 2 ? 'Peringatan Sedang' : ($detailUser->warning_level == 3 ? 'Batas Terakhir' : 'Kepatuhan baik')) }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl border {{ $detailUser->is_shadow_banned ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200/60 dark:border-rose-800/40' : 'bg-gray-50 dark:bg-gray-700/40 border-gray-100 dark:border-gray-600' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider block {{ $detailUser->is_shadow_banned ? 'text-rose-700 dark:text-rose-300' : 'text-gray-400' }}">Status Shadow Ban</span>
                            <div class="text-sm font-extrabold mt-0.5 {{ $detailUser->is_shadow_banned ? 'text-rose-950 dark:text-rose-100' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $detailUser->is_shadow_banned ? '🚫 Aktif (Dibatasi)' : 'Tidak Dibatasi' }}
                            </div>
                            <span class="text-[10px] block mt-0.5 {{ $detailUser->is_shadow_banned ? 'text-rose-800/70 dark:text-rose-300/70' : 'text-gray-400' }}">
                                {{ $detailUser->is_shadow_banned ? ($detailUser->role === 'mitra' ? 'Tidak bisa ambil order' : 'Tidak bisa buat order') : 'Fitur normal' }}
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-600">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Daftar Abu-Abu Sejak</span>
                            <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-0.5">
                                {{ $detailUser->greylisted_at ? $detailUser->greylisted_at->format('d M Y') : '—' }}
                            </div>
                            <span class="text-[10px] text-gray-400 block mt-0.5">
                                {{ $detailUser->greylisted_at ? $detailUser->greylisted_at->diffForHumans() : 'Belum diawasi' }}
                            </span>
                        </div>
                    </div>

                    @if($detailUser->warning_level == 0)
                        {{-- Alasan Masuk Pengawasan (Hanya jika belum ada SP) --}}
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-600 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Alasan Masuk Pengawasan (Daftar Abu-Abu):</span>
                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed font-semibold">
                                {{ $detailUser->greylist_reason ?: 'Tidak ada catatan alasan dari sistem/admin.' }}
                            </p>
                        </div>
                    @endif

                    {{-- Surat Peringatan Terakhir & Pesan Resmi --}}
                    @if($detailUser->warning_level > 0)
                        <div class="p-4 bg-amber-50/80 dark:bg-amber-950/40 rounded-2xl border border-amber-200 dark:border-amber-800/60 space-y-2.5">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-200 flex items-center gap-1.5">
                                    <span>📢</span> Surat Peringatan Resmi Aktif (SP {{ $detailUser->warning_level }})
                                </span>
                                @if($detailUser->latest_warning_at)
                                    <span class="text-[10px] text-amber-700 dark:text-amber-300 font-semibold">
                                        Diterbitkan: {{ $detailUser->latest_warning_at->format('d M Y • H:i') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Alasan Penerbitan SP (Alasan SP terbaru yang menimpa yang lama) --}}
                            <div class="p-2.5 bg-amber-100/70 dark:bg-amber-900/40 rounded-xl border border-amber-200/80 dark:border-amber-800/60">
                                <span class="text-[10px] font-bold text-amber-900 dark:text-amber-200 block uppercase tracking-wider">Alasan Penerbitan SP:</span>
                                <p class="text-xs text-amber-950 dark:text-amber-100 font-bold mt-0.5">
                                    {{ $detailUser->greylist_reason ?: 'Pelanggaran ketentuan operasional platform SayaBantu.' }}
                                </p>
                            </div>

                            <div>
                                <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 block mb-1">Pesan Resmi Peringatan (Tampil di Dashboard User):</span>
                                <div class="p-2.5 bg-white/90 dark:bg-gray-800/90 rounded-xl border border-amber-200/60 dark:border-amber-800/40 text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                                    {{ $detailUser->latest_warning_message ?: 'Pemberitahuan resmi kepatuhan SOP pelayanan SayaBantu.' }}
                                </div>
                            </div>
                            <p class="text-[10px] text-amber-700/80 dark:text-amber-300/80 italic">
                                * Pesan ini tampil di dashboard pengguna untuk transparansi moderasi.
                            </p>
                        </div>
                    @endif

                    {{-- Timeline Histori Moderasi Lengkap --}}
                    <div class="space-y-2 pt-1">
                        <h5 class="font-bold text-gray-900 dark:text-white text-xs flex items-center gap-1.5">
                            <span>📜</span> Riwayat & Log Moderasi (UserGreylistLog)
                        </h5>

                        @if($detailUser->greylistLogs && $detailUser->greylistLogs->isNotEmpty())
                            <div class="space-y-2 border-l-2 border-primary-200 dark:border-primary-800 pl-3 ml-2">
                                @foreach($detailUser->greylistLogs as $log)
                                    <div class="relative bg-gray-50/70 dark:bg-gray-700/30 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600/60 space-y-1">
                                        <div class="flex items-center justify-between flex-wrap gap-1">
                                            <span class="font-bold text-gray-900 dark:text-white text-[11px]">
                                                {{ $log->action_label }}
                                            </span>
                                            <span class="text-[10px] text-gray-400">
                                                {{ $log->created_at->format('d M Y • H:i') }} ({{ $log->created_at->diffForHumans() }})
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-gray-400">
                                            Diproses oleh: <strong class="text-gray-700 dark:text-gray-300">{{ $log->admin?->name ?? 'Sistem Otomatis' }}</strong>
                                        </p>
                                        @if($log->reason)
                                            <p class="text-[11px] text-gray-600 dark:text-gray-300">
                                                <span class="text-gray-400">Alasan:</span> {{ $log->reason }}
                                            </p>
                                        @endif
                                        @if($log->message)
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 bg-white/60 dark:bg-gray-800/60 p-1.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                                💬 {{ $log->message }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl text-center text-gray-400 text-xs">
                                Belum ada riwayat aktivitas moderasi tercatat untuk user ini.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-700 shrink-0">
                    <div>
                        {{-- Tombol Blokir di dalam detail jika user SP 3 --}}
                        @if($detailUser->warning_level >= 3 && $detailUser->status !== 'blocked')
                            <button type="button" wire:click="blockUser({{ $detailUser->id }})" wire:confirm="PERINGATAN: User ini telah mencapai batas SP 3. Apakah Anda yakin ingin MEMBLOKIR akun {{ $detailUser->name }} secara permanen?"
                                class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-xs">
                                <span>⛔ Blokir Akun Permanen</span>
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeDetailModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>

