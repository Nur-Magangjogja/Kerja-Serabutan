@php
    $title = 'Manajemen Admin';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola akun admin wilayah dan hak akses kota</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-semibold rounded-xl border border-blue-200/60 dark:border-blue-800">
                Total: {{ $users->total() }} Admin
            </span>
            <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-xs sm:text-sm font-semibold rounded-xl hover:bg-primary-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Admin Baru
            </button>
            <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl text-sm shadow-2xs">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl text-sm shadow-2xs">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama admin, email, no. HP..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="perPage"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 hidden">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">No. HP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Verifikasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Kecamatan Dikelola</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Terdaftar</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($users as $user)
                    @php $isActive = ($user->status === 'active'); @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500 hidden">#{{ $user->id }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate text-xs sm:text-sm">{{ $user->name }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 text-xs hidden md:table-cell">{{ $user->phone ?? '—' }}</td>
                        <td class="px-4 py-3.5">
                            <button type="button" wire:click="toggleVerified({{ $user->id }})" title="Klik untuk ubah verifikasi"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $user->verified ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->verified ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ $user->verified ? 'Terverifikasi' : 'Belum' }}
                            </button>
                        </td>
                        <td class="px-4 py-3.5">
                            <button type="button" wire:click="toggleStatus({{ $user->id }})" title="Klik untuk ubah status"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $isActive ? 'bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-blue-500' : 'bg-rose-500' }}"></span>
                                {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            @if($user->managedDistricts && $user->managedDistricts->count() > 0)
                            <div class="flex flex-wrap gap-1 items-center">
                                @foreach($user->managedDistricts->take(2) as $md)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                    Kec. {{ $md->name }}
                                </span>
                                @endforeach
                                @if($user->managedDistricts->count() > 2)
                                <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/50 px-1.5 py-0.5 rounded-md border border-primary-200/50 dark:border-primary-800/50">+{{ $user->managedDistricts->count() - 2 }}</span>
                                @endif
                            </div>
                            @elseif($user->district || $user->kecamatan)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                    Kec. {{ $user->kecamatan ?? optional($user->district)->name }}
                                </span>
                            @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Belum ada kecamatan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 hidden xl:table-cell whitespace-nowrap">{{ optional($user->created_at)->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="viewUser({{ $user->id }})" title="Lihat Detail"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button wire:click="editUser({{ $user->id }})" title="Edit Admin"
                                    class="p-1.5 rounded-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})" title="Hapus Admin"
                                    class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3 text-gray-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak ada data admin</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba ubah kata kunci pencarian atau tambah admin baru</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            {{ $users->links('vendor.pagination.superadmin') }}
        </div>
    </div>

    {{-- ===== View Admin Modal ===== --}}
    @if($showViewModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4 animate-in fade-in duration-200" role="dialog" wire:click.self="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col border border-gray-100 dark:border-gray-700">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750/30">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg shadow-xs">
                        {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $selectedUser->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedUser->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        Admin Wilayah
                    </span>
                    <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            
            {{-- Body --}}
            <div class="px-6 py-6 overflow-y-auto flex-1 space-y-5">
                {{-- Quick Stats Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">No. WhatsApp / HP</p>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $selectedUser->phone ?? '—' }}
                        </p>
                    </div>
                    <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Jenis Kelamin</p>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ $selectedUser->gender ?: '—' }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status Akun</p>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $selectedUser->status === 'blocked' ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">
                                {{ ucfirst($selectedUser->status ?? 'active') }} &bull; {{ $selectedUser->verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tanggal Bergabung</p>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1">
                            {{ $selectedUser->created_at ? $selectedUser->created_at->format('d M Y, H:i') : '—' }}
                        </p>
                    </div>
                </div>

                {{-- Alamat Lengkap --}}
                <div class="p-4 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-1">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Alamat Domisili</span>
                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200 leading-relaxed">
                        {{ $selectedUser->full_address ?: ($selectedUser->address ?: 'Belum ada data alamat yang diisi.') }}
                    </p>
                </div>

                {{-- Wilayah Kecamatan yang Dikelola --}}
                <div class="p-4 bg-gray-50/80 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Wilayah Kecamatan yang Dikelola</p>
                        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                            {{ $selectedUser->managedDistricts ? $selectedUser->managedDistricts->count() : ($selectedUser->district_id ? 1 : 0) }} Kecamatan
                        </span>
                    </div>
                    @if($selectedUser->managedDistricts && $selectedUser->managedDistricts->count() > 0)
                        <div class="flex flex-wrap gap-2 pt-1">
                            @foreach($selectedUser->managedDistricts as $md)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200/80 dark:border-gray-600 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                Kec. {{ $md->name }}
                                @if($md->city)
                                    <span class="text-[10px] text-gray-400 font-normal">({{ $md->city->name }})</span>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    @elseif($selectedUser->district || $selectedUser->district_id || $selectedUser->kecamatan)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200/80 dark:border-gray-600 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            Kec. {{ $selectedUser->kecamatan ?? optional($selectedUser->district)->name }}
                            @if($selectedUser->city_name || optional($selectedUser->district?->city)->name)
                                <span class="text-[10px] text-gray-400 font-normal">({{ $selectedUser->city_name ?? optional($selectedUser->district?->city)->name }})</span>
                            @endif
                        </span>
                    @else
                        <p class="text-xs text-gray-400 italic">Belum ada kecamatan yang ditugaskan ke admin ini.</p>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-750/30 flex justify-between items-center">
                <button type="button" wire:click.prevent="closeModal" class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Tutup
                </button>
                <button type="button" wire:click.prevent="editUser({{ $selectedUser->id }})" class="px-5 py-2 text-xs sm:text-sm font-bold bg-primary-600 hover:bg-primary-700 text-white rounded-xl shadow-xs transition">
                    Edit Admin
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Create / Edit Admin Modal with Searchable District Picker ===== --}}
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
        role="dialog" 
        wire:click.self="closeModal"
        x-data="{
            scrollToAdminPassword() {
                this.$nextTick(() => {
                    const section = document.getElementById('admin-password-section');
                    const input = document.getElementById('adminPasswordInput');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (input) {
                        setTimeout(() => input.focus(), 300);
                    }
                });
            }
        }"
        x-on:focus-superadmin-password.window="scrollToAdminPassword()">
        <div class="w-full max-w-4xl bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-200">
            
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $showEditModal ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'bg-primary-100 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400' }}">
                        @if($showEditModal)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $showEditModal ? 'Edit Data Admin' : 'Tambah Admin Wilayah Baru' }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $showEditModal ? 'bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300' : 'bg-primary-100 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300' }}">
                                {{ $showEditModal ? 'Mode Edit' : 'Admin Baru' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $showEditModal ? 'Perbarui informasi akun, kredensial, dan penugasan wilayah kecamatan' : 'Admin baru akan langsung aktif dan terverifikasi untuk mengelola kecamatan yang dipilih' }}</p>
                    </div>
                </div>
                <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-6 overflow-y-auto flex-1 space-y-6">
                <form wire:submit.prevent="saveUser" id="adminForm" class="space-y-6">
                    
                    {{-- Section 1: Informasi Profil & Kredensial --}}
                    <div class="bg-gray-50/60 dark:bg-gray-750/30 p-5 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-700 space-y-5 shadow-2xs">
                        <div class="flex items-center gap-2.5 pb-3.5 border-b border-gray-200/70 dark:border-gray-700/70">
                            <div class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-900/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-xs shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider">1. Informasi Akun & Kredensial</h4>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Kredensial login dan status operasional admin</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 block">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <input type="text" wire:model="name" placeholder="Nama Lengkap"
                                        class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                </div>
                                @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email Login --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 block">Email Login <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                    </div>
                                    <input type="email" wire:model="email" placeholder="admin@sayabantu.com"
                                        class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                </div>
                                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- No. WhatsApp --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 block">No. WhatsApp / HP</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <input type="text" wire:model="phone" placeholder="08xxxxxxxxxx"
                                        class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                </div>
                                @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Password Akun Admin --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 block">
                                    Password Akun Admin @if($showEditModal)<span class="text-gray-400 dark:text-gray-500 font-normal">(kosongkan jika tidak diubah)</span>@else<span class="text-rose-500">*</span>@endif
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <input type="password" wire:model="password"
                                        placeholder="{{ $showEditModal ? 'Isi hanya jika ingin mengganti password admin ini' : 'Minimal 8 karakter' }}"
                                        class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                </div>
                                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Status Akun --}}
                            <div class="md:col-span-2">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 block">Status Akun</label>
                                <select wire:model="status" class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                    <option value="active">🟢 Aktif (Dapat Login & Mengelola)</option>
                                    <option value="inactive">🔴 Nonaktif (Diblokir)</option>
                                </select>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Akun Admin yang dibuat oleh Superadmin otomatis berstatus terverifikasi dan dapat langsung login.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Penugasan Kecamatan dengan Fitur Filter Kota & Pencarian --}}
                    <div class="bg-gray-50/60 dark:bg-gray-750/30 p-5 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-700 space-y-4 shadow-2xs">
                        {{-- Header Section 2 --}}
                        <div class="flex items-center justify-between flex-wrap gap-3 pb-3.5 border-b border-gray-200/70 dark:border-gray-700/70">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider block">
                                        2. Penugasan Wilayah Kecamatan yang Dikelola <span class="text-rose-500">*</span>
                                    </label>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Admin hanya berwenang memoderasi data bantuan & mitra di kecamatan yang dipilih</p>
                                </div>
                            </div>

                            {{-- Selected Counter Badges --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                @if(count($managed_district_ids) > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        {{ count($managed_district_ids) }} Kecamatan Terpilih
                                    </span>
                                    @if(isset($selectedDistrictsList) && $selectedDistrictsList->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 shadow-2xs">
                                            {{ $selectedDistrictsList->pluck('city_id')->unique()->count() }} Kota/Kab
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        Belum ada kecamatan dipilih
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Chips / Tags Area for Currently Selected Districts --}}
                        @if(!empty($managed_district_ids) && isset($selectedDistrictsList) && $selectedDistrictsList->isNotEmpty())
                        <div class="p-3.5 bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/50 rounded-2xl space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5 uppercase tracking-wide">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Kecamatan Terpilih ({{ $selectedDistrictsList->count() }}):
                                </span>
                                <button type="button" wire:click="clearAllDistricts" class="text-[11px] font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline flex items-center gap-1 transition cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Kosongkan Semua
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto dropdown-scrollbar pr-1">
                                @foreach($selectedDistrictsList as $sd)
                                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-emerald-300/90 dark:border-emerald-700 shadow-2xs hover:border-emerald-400 transition group">
                                    <span>📍</span>
                                    <span>Kec. {{ $sd->name }}</span>
                                    @if($sd->city)
                                        <span class="text-[10px] text-gray-400 font-normal">({{ $sd->city->name }})</span>
                                    @endif
                                    <button type="button" wire:click="removeDistrict({{ $sd->id }})" class="p-0.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-full hover:bg-rose-50 dark:hover:bg-rose-950/50 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="px-3.5 py-2.5 bg-gray-100/70 dark:bg-gray-750/50 rounded-xl border border-gray-200/70 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Silakan klik pada kartu kecamatan di bawah atau pilih kota untuk menugaskan wilayah kerja admin ini.</span>
                        </div>
                        @endif

                        {{-- Filter Bar & Search --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-1">
                            {{-- Dropdown Filter Kota --}}
                            <div class="w-full sm:w-1/2">
                                <label class="text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1 block">Filter Berdasarkan Kota / Kabupaten:</label>
                                <select wire:model.live="cityFilter" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                    <option value="all">🗺️ Tampilkan Semua Kota / Kabupaten ({{ $cities->count() }} Kota)</option>
                                    @foreach($cities as $c)
                                        <option value="{{ $c->id }}">📍 {{ $c->name }} ({{ $c->districts->count() }} Kecamatan)</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Input Search Kecamatan --}}
                            <div class="w-full sm:w-1/2">
                                <label class="text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1 block">Cari Nama Kecamatan:</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="districtSearch"
                                        placeholder="Ketik nama kecamatan..."
                                        class="w-full pl-9 pr-8 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs">
                                    @if(!empty($districtSearch))
                                        <button type="button" wire:click="$set('districtSearch', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Batch Selector Buttons (Hanya muncul saat filter kota aktif atau sedang mencari kecamatan) --}}
                        <div class="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-700/60">
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                Menampilkan <span class="font-bold text-gray-800 dark:text-gray-200">{{ $districts->count() }}</span> kecamatan
                                @if($cityFilter !== 'all' && is_numeric($cityFilter))
                                    di <span class="font-bold text-primary-600 dark:text-primary-400">{{ optional($cities->firstWhere('id', $cityFilter))->name }}</span>
                                @endif
                            </div>
                            @if($cityFilter !== 'all' || !empty($districtSearch))
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="selectAllFilteredDistricts"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 rounded-lg transition cursor-pointer shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Pilih Semua di Filter
                                </button>
                                <button type="button" wire:click="deselectAllFilteredDistricts"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 rounded-lg transition cursor-pointer shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Batal Pilih di Filter
                                </button>
                            </div>
                            @endif
                        </div>

                        {{-- Daftar Kecamatan Berkotak / Interactive Grid Card --}}
                        <div class="max-h-72 overflow-y-auto dropdown-scrollbar rounded-2xl border border-gray-200 dark:border-gray-700 p-3 pr-2 bg-white/70 dark:bg-gray-800/60 shadow-inner">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @forelse($districts as $d)
                                @php
                                    $isSelected = in_array((int)$d->id, array_map('intval', (array)($managed_district_ids ?? [])), true);
                                @endphp
                                <div wire:click="toggleDistrict({{ $d->id }})" role="button" tabindex="0"
                                    wire:keydown.enter="toggleDistrict({{ $d->id }})"
                                    class="relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-150 select-none text-left
                                    {{ $isSelected ? 'bg-gradient-to-r from-emerald-50/95 to-teal-50/80 dark:from-emerald-950/50 dark:to-teal-950/40 border-emerald-500 dark:border-emerald-500 ring-1 ring-emerald-500/30 shadow-xs' : 'bg-white dark:bg-gray-750/70 border-gray-200/90 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/80 hover:border-emerald-300 dark:hover:border-emerald-600 shadow-2xs' }}">
                                    
                                    {{-- Status Checkbox Icon: Centang HANYA jika dipilih, kotak kosong jika belum --}}
                                    @if($isSelected)
                                        <div class="w-5 h-5 rounded-md bg-emerald-600 dark:bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded-md border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shrink-0"></div>
                                    @endif
                                    
                                    {{-- District Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-xs font-bold truncate {{ $isSelected ? 'text-emerald-950 dark:text-emerald-100' : 'text-gray-800 dark:text-gray-200' }}">
                                                Kec. {{ $d->name }}
                                            </p>
                                            @if($isSelected)
                                                <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/90 dark:bg-emerald-900/80 px-1.5 py-0.5 rounded shadow-2xs">Ditugaskan</span>
                                            @endif
                                        </div>
                                        @if($d->city)
                                            <p class="text-[10px] truncate mt-0.5 flex items-center gap-1 {{ $isSelected ? 'text-emerald-700/80 dark:text-emerald-300/80 font-medium' : 'text-gray-400 dark:text-gray-400' }}">
                                                <span>📍</span> {{ $d->city->name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="col-span-full text-center py-8 px-4 space-y-2">
                                    <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tidak ada data kecamatan yang cocok dengan filter.</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Coba ubah kata kunci pencarian atau pilih kota/kabupaten lain.</p>
                                    <button type="button" wire:click="$set('districtSearch', ''); $set('cityFilter', 'all');"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 rounded-xl border border-primary-200 dark:border-primary-800 transition cursor-pointer">
                                        Reset Filter & Pencarian
                                    </button>
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @error('managed_district_ids') <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Konfirmasi Kata Sandi Superadmin saat Edit Admin --}}
                    @if($showEditModal)
                    <div id="admin-password-section" 
                        class="p-5 sm:p-6 transition-all duration-300 rounded-2xl space-y-4 shadow-2xs {{ $errors->has('adminPassword') ? 'bg-rose-50/95 dark:bg-rose-950/40 border-2 border-rose-400 dark:border-rose-600 ring-4 ring-rose-500/20' : 'bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/90 dark:border-amber-800/70' }}">
                        
                        <div class="flex items-center gap-2.5 pb-3 border-b {{ $errors->has('adminPassword') ? 'border-rose-200 dark:border-rose-800' : 'border-amber-200/70 dark:border-amber-800/60' }}">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shadow-2xs flex-shrink-0 {{ $errors->has('adminPassword') ? 'bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider {{ $errors->has('adminPassword') ? 'text-rose-900 dark:text-rose-200' : 'text-amber-900 dark:text-amber-200' }}">Otorisasi Keamanan Superadmin</h4>
                                <p class="text-[11px] {{ $errors->has('adminPassword') ? 'text-rose-700 dark:text-rose-300 font-medium' : 'text-amber-700/80 dark:text-amber-400/80' }}">Konfirmasi hak akses sebelum menyimpan perubahan</p>
                            </div>
                        </div>

                        <p class="text-xs leading-relaxed {{ $errors->has('adminPassword') ? 'text-rose-800 dark:text-rose-200 font-medium' : 'text-amber-800/90 dark:text-amber-300/90' }}">
                            Masukkan kata sandi akun Superadmin Anda untuk memvalidasi dan mengonfirmasi perubahan data atau penugasan wilayah kerja admin ini.
                        </p>

                        <div>
                            <label for="adminPasswordInput" class="text-xs font-semibold mb-1.5 block {{ $errors->has('adminPassword') ? 'text-rose-900 dark:text-rose-200' : 'text-amber-900 dark:text-amber-200' }}">
                                Kata Sandi Akun Superadmin <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none {{ $errors->has('adminPassword') ? 'text-rose-500' : 'text-amber-500/70 dark:text-amber-400/70' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input type="password" 
                                    id="adminPasswordInput"
                                    wire:model="adminPassword"
                                    placeholder="Ketik kata sandi Superadmin Anda..."
                                    class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm border rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 transition shadow-2xs {{ $errors->has('adminPassword') ? 'border-rose-400 dark:border-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500 placeholder-rose-300 dark:placeholder-rose-600' : 'border-amber-300/90 dark:border-amber-700/80 placeholder-amber-400/60 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500' }}" />
                            </div>
                            @error('adminPassword') 
                                <div class="flex items-center gap-1.5 mt-2 text-xs text-rose-600 dark:text-rose-400 font-bold">
                                    <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>
                    @endif
                </form>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-750/30 flex items-center justify-between gap-3">
                <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="button" wire:click.prevent="closeModal"
                        class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" form="adminForm" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-xs sm:text-sm font-bold bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition flex items-center gap-2 shadow-sm disabled:opacity-60">
                        <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        {{ $showEditModal ? 'Simpan Perubahan Admin' : 'Buat Admin & Verifikasi' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Confirm Delete Modal ===== --}}
    @if($showConfirmDelete && $userToDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4" role="dialog" wire:click.self="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md p-6 border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950/70 flex items-center justify-center text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Hapus Admin</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini permanen dan tidak dapat dibatalkan</p>
                </div>
            </div>

            <!-- Target Admin Summary Card -->
            <div class="bg-gray-50 dark:bg-gray-750/50 rounded-2xl p-3.5 border border-gray-100 dark:border-gray-700 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nama Admin:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $userToDelete->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Email:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $userToDelete->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Kecamatan Dikelola:</span>
                    <span class="font-semibold text-primary-600 dark:text-primary-400">
                        {{ $userToDelete->managedDistricts->pluck('name')->map(fn($n) => 'Kec. ' . $n)->join(', ') ?: ($userToDelete->kecamatan ?: ($userToDelete->city_name ?: 'Tidak ada')) }}
                    </span>
                </div>
            </div>

            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                Seluruh hak akses admin ke kecamatan-kecamatan yang ditugaskan akan otomatis dicabut.
            </p>

            <!-- Superadmin Password Confirmation Input -->
            <div class="space-y-1.5 pt-1">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Masukkan Kata Sandi Superadmin Anda <span class="text-rose-500">*</span>
                </label>
                <input type="password" wire:model.defer="adminPassword" wire:keydown.enter="deleteUser"
                    placeholder="Kata sandi Superadmin Anda"
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition" />
                @error('adminPassword')
                    <p class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" wire:click.prevent="closeModal"
                    class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button wire:click="deleteUser" wire:loading.attr="disabled"
                    class="px-4 py-2.5 text-xs font-bold bg-rose-600 text-white rounded-xl hover:bg-rose-700 shadow-sm transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60">
                    <svg wire:loading class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    <span>Konfirmasi & Hapus Admin</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
