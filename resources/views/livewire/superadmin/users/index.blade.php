@php
    $title = 'Manajemen Mitra & Customer';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Mitra & Customer</h1> 
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau dan kelola akun mitra & customer dalam sistem</p>
        </div>
        <div class="flex items-center gap-2">
            <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                Memproses...
            </div>
        </div>
    </div>

    @if (session('message'))
        <div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-semibold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-semibold text-rose-800 dark:text-rose-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                    id="users_search"
                    name="search"
                    autocomplete="off"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Cari nama, email, atau HP..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            {{-- Role Filter --}}
            <select
                id="users_role_filter"
                name="role_filter"
                aria-label="Filter berdasarkan role"
                wire:model.live="roleFilter"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Role (Mitra & Customer)</option>
                <option value="customer">Customer</option>
                <option value="mitra">Mitra</option>
            </select>

            {{-- Per Page --}}
            <select
                id="users_per_page"
                name="per_page"
                aria-label="Jumlah pengguna per halaman"
                wire:model.live="perPage"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>

            @if(!empty($search) || !empty($roleFilter))
                <button type="button" wire:click="$set('search', ''); $set('roleFilter', '');"
                    class="px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-lg border border-rose-200 dark:border-rose-800 flex items-center gap-1.5 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </button>
            @endif

            <div class="ml-auto">
                {{-- loading indicator --}}
                <div wire:loading class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    Memuat...
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">No. HP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Status & Aktivitas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Kota</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Terdaftar</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($users as $user)
                    @php
                    $roleConfig = [
                        'super_admin' => ['label' => 'Super Admin', 'class' => 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400'],
                        'admin'       => ['label' => 'Admin',       'class' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400'],
                        'mitra'       => ['label' => 'Mitra',       'class' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400'],
                        'customer'    => ['label' => 'Customer',    'class' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400'],
                    ];
                    $rc = $roleConfig[$user->role] ?? ['label' => ucfirst($user->role), 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'];
                    $isActive = isset($user->status) && $user->status === 'active';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">{{ $user->phone ?? '—' }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $rc['class'] }}">{{ $rc['label'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 hidden sm:table-cell">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $isActive ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ isset($user->status) ? ucfirst($user->status) : '—' }}
                                </span>
                                <div class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400" title="{{ $user->last_activity_at ? 'Aktivitas terbaru: ' . $user->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum ada riwayat aktivitas bantuan' }}">
                                    <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="truncate">{{ $user->last_activity_for_humans }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            @if($user->role === 'admin' && $user->managedDistricts && $user->managedDistricts->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->managedDistricts->take(2) as $md)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Kec. {{ $md->name }}</span>
                                    @endforeach
                                    @if($user->managedDistricts->count() > 2)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $user->managedDistricts->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">{{ $user->district ? 'Kec. ' . $user->district->name : ($user->city_name ?? '—') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 hidden lg:table-cell whitespace-nowrap">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="viewUser({{ $user->id }})" title="Lihat Detail"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button wire:click="editUser({{ $user->id }})" title="Edit"
                                    class="p-1.5 rounded-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="openMigrationModal({{ $user->id }})" title="Migrasi Wilayah"
                                    class="p-1.5 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})" title="Hapus"
                                    class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada data pengguna</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba ubah filter pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            {{ $users->links('vendor.pagination.superadmin') }}
        </div>
    </div>

    {{-- ===== View User Modal ===== --}}
    @if($showViewModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4 animate-in fade-in duration-200">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col border border-gray-100 dark:border-gray-700">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/60">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="relative shrink-0">
                        @if($selectedUser->avatar_url)
                            <img src="{{ $selectedUser->avatar_url }}" alt="{{ $selectedUser->name }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-primary-500/30 shadow-xs">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg shadow-xs">
                                {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                            </div>
                        @endif
                        @if($selectedUser->verified)
                            <span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full p-0.5 ring-2 ring-white dark:ring-gray-800" title="KTP Terverifikasi">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $selectedUser->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $selectedUser->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Badges --}}
                    @php
                        $roleColors = [
                            'super_admin' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                            'admin'       => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                            'mitra'       => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                            'customer'    => 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                        ];
                        $statusColors = [
                            'active'   => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            'inactive' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                            'blocked'  => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                        ];
                    @endphp
                    <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $roleColors[$selectedUser->role] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ['super_admin'=>'Super Admin','admin'=>'Admin','mitra'=>'Mitra','customer'=>'Customer'][$selectedUser->role] ?? ucfirst($selectedUser->role) }}
                    </span>

                    <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Modal Tab Navigation --}}
            <div class="flex items-center gap-2 px-5 sm:px-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-800/40">
                <button type="button" wire:click="setModalTab('profile')"
                    class="py-3 px-3 sm:px-4 text-xs font-semibold border-b-2 transition-colors flex items-center gap-1.5 sm:gap-2 {{ $activeModalTab === 'profile' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Data Profil & Identitas
                </button>
                <button type="button" wire:click="setModalTab('audit')"
                    class="py-3 px-3 sm:px-4 text-xs font-semibold border-b-2 transition-colors flex items-center gap-1.5 sm:gap-2 {{ $activeModalTab === 'audit' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Riwayat & Audit Akun
                    @if(isset($auditTimeline) && $auditTimeline->total() > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ $auditTimeline->total() }}
                        </span>
                    @endif
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-5">
                @if($activeModalTab === 'profile')
                    @php
                        $isStaff = in_array($selectedUser->role, ['admin', 'super_admin']);
                    @endphp

                @if($isStaff)
                    {{-- Layout Khusus Admin & Super Admin (Tanpa Dokumen KTP/Selfie) --}}
                    <div class="space-y-4">
                        {{-- Data Pribadi & Kontak --}}
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-3.5">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Data Akun & Kontak {{ $selectedUser->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                            </h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div class="bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <span class="text-gray-400 block text-[10px]">Peran Sistem</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100 text-xs sm:text-sm block mt-0.5">{{ $selectedUser->role === 'super_admin' ? 'Super Admin' : 'Admin Wilayah' }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600 flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <span class="text-gray-400 block text-[10px]">No. HP / WhatsApp</span>
                                        @if($selectedUser->whatsapp_url)
                                            <a href="{{ $selectedUser->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shrink-0 shadow-2xs"
                                                title="Buka Chat WhatsApp">
                                                <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24">
                                                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.45c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.74-.66-1.24-1.48-1.39-1.73-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                                                </svg>
                                                <span>WhatsApp</span>
                                            </a>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-1">{{ $selectedUser->phone ?: '—' }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <span class="text-gray-400 block text-[10px]">Jenis Kelamin</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-0.5">{{ $selectedUser->gender ?: '—' }}</span>
                                </div>
                                <div class="bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <span class="text-gray-400 block text-[10px]">Status Akun</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border mt-1 {{ $statusColors[$selectedUser->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $selectedUser->status === 'blocked' ? 'Diblokir' : ($selectedUser->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-700 p-3.5 rounded-xl border border-gray-100 dark:border-gray-600 space-y-1">
                                <span class="text-gray-400 block text-[10px]">Alamat Domisili</span>
                                <p class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 leading-relaxed">{{ $selectedUser->full_address ?: ($selectedUser->address ?: 'Belum ada data alamat yang diisi.') }}</p>
                            </div>
                        </div>

                        {{-- Metadata Aktivitas --}}
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-2.5">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Informasi Aktivitas & Waktu
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <span class="text-gray-400 block text-[10px]">Waktu Terdaftar</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-0.5">{{ optional($selectedUser->created_at)?->translatedFormat('d M Y, H:i') ?? '—' }} WIB</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <span class="text-gray-400 block text-[10px]">Aktivitas Terakhir</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-0.5 truncate">{{ $selectedUser->last_activity_at ? $selectedUser->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum ada aktivitas' }}</span>
                                </div>
                            </div>
                        </div>

                        @if($selectedUser->role === 'admin')
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Wilayah Kecamatan yang Dikelola
                                </h4>
                                <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                                    {{ $selectedUser->managedDistricts ? $selectedUser->managedDistricts->count() : ($selectedUser->district ? 1 : 0) }} Kecamatan
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
                            @elseif($selectedUser->district || $selectedUser->district_id)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200/80 dark:border-gray-600 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    Kec. {{ is_object($selectedUser->district) ? $selectedUser->district->name : $selectedUser->kecamatan }}
                                </span>
                            @else
                                <p class="text-xs text-gray-400 italic">Belum ada kecamatan yang ditugaskan ke admin ini.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                @else
                    {{-- Layout Pengguna Customer & Mitra (Dengan Dokumen KTP & Selfie) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                        {{-- Kolom Kiri: Informasi Akun & Identitas (7 cols) --}}
                        <div class="lg:col-span-7 space-y-4">
                            {{-- Identitas Utama --}}
                            <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-3.5">
                                <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Data Pribadi & Kontak
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600">
                                        <span class="text-gray-400 block text-[10px]">Nomor NIK KTP</span>
                                        <span class="font-bold font-mono text-gray-900 dark:text-gray-100 text-sm block mt-0.5">{{ $selectedUser->nik ?: 'Belum diisi' }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 flex flex-col justify-between">
                                        <div class="flex items-center justify-between gap-1.5">
                                            <span class="text-gray-400 block text-[10px]">No. HP / WhatsApp</span>
                                            @if($selectedUser->whatsapp_url)
                                                <a href="{{ $selectedUser->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shrink-0 shadow-2xs"
                                                    title="Buka Chat WhatsApp">
                                                    <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24">
                                                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.45c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.74-.66-1.24-1.48-1.39-1.73-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                                                    </svg>
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                        </div>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-1">{{ $selectedUser->phone ?: '—' }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600">
                                        <span class="text-gray-400 block text-[10px]">Jenis Kelamin</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 block mt-0.5">{{ $selectedUser->gender ?: '—' }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600">
                                        <span class="text-gray-400 block text-[10px]">Status Akun</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border mt-0.5 {{ $statusColors[$selectedUser->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $selectedUser->status === 'blocked' ? 'Diblokir' : ($selectedUser->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-gray-700 p-3 rounded-xl border border-gray-100 dark:border-gray-600 space-y-1">
                                    <span class="text-gray-400 block text-[10px]">Alamat Lengkap</span>
                                    <p class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 leading-relaxed">{{ $selectedUser->full_address }}</p>
                                </div>
                            </div>

                            {{-- Metadata Akun --}}
                            <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-2.5">
                                <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Informasi Aktivitas
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div class="p-2 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                                        <span class="text-gray-400 block text-[10px]">Waktu Terdaftar</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($selectedUser->created_at)?->translatedFormat('d M Y, H:i') ?? '—' }} WIB</span>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                                        <span class="text-gray-400 block text-[10px]">Aktivitas Terakhir</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate block">{{ $selectedUser->last_activity_at ? $selectedUser->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum ada aktivitas' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Dokumen KTP & Verifikasi (5 cols) --}}
                        <div class="lg:col-span-5 space-y-4">
                            {{-- Kartu Foto KTP --}}
                            <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span></span> Foto e-KTP
                                    </h4>
                                    @if($selectedUser->verified)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Terverifikasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                            Belum Verifikasi
                                        </span>
                                    @endif
                                </div>

                                @if($selectedUser->ktp_url)
                                    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-900/10 dark:bg-gray-900/40 shadow-xs">
                                        <img src="{{ $selectedUser->ktp_url }}" alt="Dokumen KTP {{ $selectedUser->name }}" class="w-full h-44 object-cover object-center">
                                    </div>
                                    <div class="flex items-center justify-between text-xs pt-1">
                                        <a href="{{ $selectedUser->ktp_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            Buka Foto Asli
                                        </a>
                                        <a href="{{ $selectedUser->ktp_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Unduh
                                        </a>
                                    </div>
                                @else
                                    <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-6 text-center space-y-2">
                                        <div class="w-10 h-10 mx-auto rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Belum Ada Dokumen KTP</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Pengguna ini belum mengunggah foto identitas e-KTP.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Kartu Foto Selfie jika ada --}}
                            @if($selectedUser->selfie_url)
                            <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 space-y-2.5">
                                <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <span></span> Foto Selfie Verifikasi
                                </h4>
                                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-900/10 shadow-xs aspect-4/3">
                                    <img src="{{ $selectedUser->selfie_url }}" alt="Selfie {{ $selectedUser->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex items-center justify-between text-xs pt-1">
                                    <a href="{{ $selectedUser->selfie_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Buka Foto Asli
                                    </a>
                                    <a href="{{ $selectedUser->selfie_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Unduh
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
                @else
                    {{-- TAB 2: RIWAYAT & AUDIT AKUN (CONSOLIDATED AUDIT TIMELINE) --}}
                    <div class="space-y-4">
                        {{-- Home Administrative Territory & Status Card --}}
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold block">Wilayah Administratif Akun Saat Ini (Home Territory)</span>
                                <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $selectedUser->district ? 'Kec. ' . $selectedUser->district->name . ', ' : '' }}{{ $selectedUser->cityRelation?->name ?? ($selectedUser->city ?? 'Wilayah belum diatur') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[11px] px-2.5 py-1 rounded-lg font-medium bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                    Status Moderasi: <strong class="{{ $selectedUser->warning_level > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $selectedUser->warning_level_label }}</strong>
                                </span>
                                @if($selectedUser->is_shadow_banned)
                                    <span class="text-[11px] px-2 py-0.5 rounded-lg font-bold bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        Shadow Ban Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Filter Kategori Aktivitas --}}
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                            <button type="button" wire:click="setAuditFilter('all')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'all' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Semua
                            </button>
                            <button type="button" wire:click="setAuditFilter('help')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'help' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Pesanan Jasa
                            </button>
                            <button type="button" wire:click="setAuditFilter('cancel_dispute')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'cancel_dispute' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Pembatalan & Sengketa
                            </button>
                            <button type="button" wire:click="setAuditFilter('report')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'report' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Laporan Aduan
                            </button>
                            <button type="button" wire:click="setAuditFilter('discipline')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'discipline' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Disiplin & Sanksi
                            </button>
                            <button type="button" wire:click="setAuditFilter('financial')"
                                class="px-3 py-1.5 rounded-xl font-medium whitespace-nowrap transition-colors {{ $auditFilter === 'financial' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Keuangan Dompet
                            </button>
                        </div>

                        {{-- Daftar Timeline Audit --}}
                        <div class="space-y-3 pt-1">
                            @if(isset($auditTimeline) && $auditTimeline->count() > 0)
                                @foreach($auditTimeline as $event)
                                    <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 border border-gray-100 dark:border-gray-600 shadow-2xs space-y-2.5 transition-all">
                                        {{-- Top Bar: Timestamp & Type Badges --}}
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 text-[11px] font-mono text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-2.5 py-0.5 rounded-md border border-gray-200/60 dark:border-gray-600">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $event['occurred_at'] ? \Carbon\Carbon::parse($event['occurred_at'])->translatedFormat('d M Y, H:i') . ' WIB' : '—' }}
                                                </span>

                                                @php
                                                    $typeBg = match($event['event_type']) {
                                                        'help_order'     => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                                        'cancellation'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                                        'dispute'        => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                        'report'         => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                                        'support'        => 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                                                        'discipline'     => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                        'financial'      => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                                        'admin_action'   => 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                                                        default          => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $typeBg }}">
                                                    {{ $event['event_type_label'] }}
                                                </span>

                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/60 dark:border-gray-600">
                                                    Peran: {{ $event['user_role'] }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200/60 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                                    {{ $event['status_label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Territory Context --}}
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Wilayah Kasus: <strong>{{ $event['incident_territory'] }}</strong>
                                            </span>
                                            @if($event['is_cross_territory'])
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                    Lintas Wilayah
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Title & Summary Content --}}
                                        <div class="space-y-1.5">
                                            @if(!empty($event['title']))
                                                <h5 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ $event['title'] }}
                                                </h5>
                                            @endif
                                            <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed font-medium">
                                                {{ $event['summary'] }}
                                            </p>
                                            @if(!empty($event['official_message']) && $event['official_message'] !== $event['summary'])
                                                <div class="mt-2 p-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-750/70 border border-gray-200/60 dark:border-gray-600/60 text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-400 block">Pesan / Catatan Resmi:</span>
                                                    <p class="leading-relaxed">{{ $event['official_message'] }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Bottom Row: Badges, Values, & Strict Authority Access Boundary --}}
                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-600/70 text-xs">
                                            <div class="flex flex-wrap items-center gap-2.5">
                                                @if(isset($event['monetary_amount']) && $event['monetary_amount'] > 0)
                                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                        Nilai: Rp {{ number_format($event['monetary_amount'], 0, ',', '.') }}
                                                    </span>
                                                @endif

                                                @if(!empty($event['sp_level']))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                        Sanksi SP {{ $event['sp_level'] }}
                                                    </span>
                                                @endif

                                                @if(!empty($event['has_shadow_ban']))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                        Shadow Ban Aktif
                                                    </span>
                                                @endif

                                                @if(!empty($event['actor_name']))
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-400">
                                                        Aktor: {{ $event['actor_name'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div>
                                                @if($event['can_access_case'] && !empty($event['case_route']))
                                                    <a href="{{ $event['case_route'] }}" target="_blank"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-colors">
                                                        Buka Kasus
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    </a>
                                                @else
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 italic">
                                                        Wewenang Kasus di Luar Wilayah (Pengawasan Akun Saja)
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Pagination Controls for Audit --}}
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700 text-xs">
                                    <button type="button" wire:click="previousAuditPage"
                                        @if($auditTimeline->currentPage() <= 1) disabled @endif
                                        class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-600 font-medium text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Sebelumnya
                                    </button>
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px]">
                                        Halaman {{ $auditTimeline->currentPage() }} dari {{ max(1, $auditTimeline->lastPage()) }} (Total {{ $auditTimeline->total() }} entri)
                                    </span>
                                    <button type="button" wire:click="nextAuditPage"
                                        @if($auditTimeline->currentPage() >= $auditTimeline->lastPage()) disabled @endif
                                        class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-600 font-medium text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Selanjutnya
                                    </button>
                                </div>
                            @else
                                <div class="py-12 px-4 text-center bg-gray-50/50 dark:bg-gray-750/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 space-y-2">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">Belum Ada Riwayat Aktivitas</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Tidak ada catatan aktivitas untuk filter kategori yang dipilih.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Actions --}}
            <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <button type="button" wire:click.prevent="closeModal" class="px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click.prevent="openMigrationModal({{ $selectedUser->id }})" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-xs hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Migrasi Wilayah
                    </button>
                    <button type="button" wire:click.prevent="editUser({{ $selectedUser->id }})" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white rounded-xl shadow-xs hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Pengguna
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Create / Edit Modal ===== --}}
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $showEditModal ? 'Edit User' : 'Tambah User Baru' }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $showEditModal ? 'Perbarui informasi pengguna' : 'Lengkapi formulir untuk menambah pengguna baru' }}</p>
                </div>
                <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 overflow-y-auto flex-1">
                <form wire:submit.prevent="saveUser" id="userForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="user_name" class="text-xs font-medium text-gray-600 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="user_name" name="name" autocomplete="name" wire:model.defer="name" placeholder="Nama lengkap"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_email" class="text-xs font-medium text-gray-600 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="user_email" name="email" autocomplete="email" wire:model.defer="email" placeholder="email@contoh.com"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_phone" class="text-xs font-medium text-gray-600 dark:text-gray-300">No. HP</label>
                            <input type="text" id="user_phone" name="phone" autocomplete="tel" wire:model.defer="phone" placeholder="08xxxxxxxxxx"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_password" class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                Password
                                @if($showEditModal) <span class="text-gray-400 dark:text-gray-500">(kosongkan jika tidak diubah)</span>@else <span class="text-red-500">*</span>@endif
                            </label>
                            <input type="password" id="user_password" name="password" autocomplete="new-password" wire:model.defer="password"
                                placeholder="{{ $showEditModal ? 'Isi untuk mengubah' : 'Minimal 8 karakter' }}"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_role" class="text-xs font-medium text-gray-600 dark:text-gray-300">Role</label>
                            @if(auth()->user()?->role === 'admin')
                                <select id="user_role" name="role" wire:model.defer="role" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="customer">Customer</option>
                                    <option value="mitra">Mitra</option>
                                </select>
                            @else
                                <select id="user_role" name="role" wire:model.defer="role" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="customer">Customer</option>
                                    <option value="mitra">Mitra</option>
                                    <option value="admin">Admin</option>
                                    <option value="super_admin">Super Admin</option>
                                </select>
                            @endif
                            @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_status" class="text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
                            <select id="user_status" name="status" wire:model.defer="status" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div>
                            <label for="user_verified" class="text-xs font-medium text-gray-600 dark:text-gray-300">Verifikasi</label>
                            <select id="user_verified" name="verified" wire:model.defer="verified" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="1">Terverifikasi</option>
                                <option value="0">Belum</option>
                            </select>
                        </div>

                        <div>
                            <label for="user_city_id" class="text-xs font-medium text-gray-600 dark:text-gray-300">Kota</label>
                            <select id="user_city_id" name="city_id" wire:model.defer="city_id" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Pilih Kota --</option>
                                @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_nik" class="text-xs font-medium text-gray-600 dark:text-gray-300">NIK</label>
                            <input type="text" id="user_nik" name="nik" autocomplete="off" wire:model.defer="nik"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('nik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="user_gender" class="text-xs font-medium text-gray-600 dark:text-gray-300">Jenis Kelamin</label>
                            <select id="user_gender" name="gender" wire:model.defer="gender" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label for="user_province" class="text-xs font-medium text-gray-600 dark:text-gray-300">Provinsi</label>
                            <input type="text" id="user_province" name="province" autocomplete="address-level1" wire:model.defer="province" placeholder="Nama Provinsi"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('province') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Informasi Wilayah Wewenang Admin --}}
                    @if($role === 'admin')
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="p-3.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Penugasan Wilayah Admin Wilayah</span>
                            </p>
                            <p class="text-blue-700 dark:text-blue-300">
                                Wewenang wilayah Admin Wilayah ditugaskan secara spesifik per kecamatan melalui menu 
                                <a href="{{ route('superadmin.admin.users') }}" class="underline font-semibold hover:text-blue-900 dark:hover:text-blue-100">Manajemen Admin Wilayah</a>.
                            </p>
                        </div>
                    </div>
                    @endif

                </form>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-end gap-3">
                <button type="button" wire:click.prevent="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button type="submit" form="userForm" wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2 disabled:opacity-60">
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    {{ $showEditModal ? 'Perbarui User' : 'Simpan User' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Confirm Delete Modal ===== --}}
    @if($showConfirmDelete && $userToDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0 text-rose-600 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Hapus User</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                </div>
            </div>

            <!-- Target User Summary Card -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3.5 border border-gray-100 dark:border-gray-600 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nama Pengguna:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $userToDelete->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Email:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $userToDelete->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Role:</span>
                    <span class="font-bold uppercase text-primary-600">{{ $userToDelete->role }}</span>
                </div>
                @php
                    $targetBal = (float) $userToDelete->balance;
                @endphp
                <div class="flex justify-between pt-1 border-t border-gray-200/60 dark:border-gray-600">
                    <span class="text-gray-500 dark:text-gray-400">Sisa Saldo Akun:</span>
                    <span class="font-bold {{ $targetBal > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                        Rp {{ number_format($targetBal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if($targetBal > 0)
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><strong>Peringatan:</strong> Pengguna ini masih memiliki sisa saldo Rp {{ number_format($targetBal, 0, ',', '.') }}. Pastikan dana telah diselesaikan.</span>
                </div>
            @endif

            <!-- Password Confirmation Input -->
            <div class="space-y-1.5 pt-1">
                <label for="delete_admin_password" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Masukkan Kata Sandi Akun Anda <span class="text-red-500">*</span>
                </label>
                <input type="password" id="delete_admin_password" name="admin_password" autocomplete="current-password" wire:model.defer="adminPassword" wire:keydown.enter="deleteUser"
                    placeholder="Kata sandi akun Anda"
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition" />
                @error('adminPassword')
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" wire:click.prevent="closeModal"
                    class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button wire:click="deleteUser" wire:loading.attr="disabled"
                    class="px-4 py-2.5 text-xs font-bold bg-rose-600 text-white rounded-xl hover:bg-rose-700 shadow-sm transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60">
                    <svg wire:loading class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    <span>Konfirmasi & Hapus User</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Migration Territory Modal ===== --}}
    @if($showMigrationModal && $migrationUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Migrasi Wilayah Profil</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pindahkan wilayah administratif profil pengguna secara resmi</p>
                    </div>
                </div>
                <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 overflow-y-auto flex-1 space-y-4 text-xs">
                {{-- Info Pengguna --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700 space-y-1">
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Pengguna Target</span>
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $migrationUser->name }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase">
                            {{ $migrationUser->role }}
                        </span>
                    </div>
                </div>

                {{-- Wilayah Saat Ini (Read-only) --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700 space-y-2">
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Wilayah Saat Ini</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="bg-white dark:bg-gray-700 p-2.5 rounded-lg border border-gray-200/80 dark:border-gray-600">
                            <span class="text-[10px] text-gray-400 block">Provinsi</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate mt-0.5">
                                {{ $migrationUser->province ?: ($migrationUser->cityRelation?->province ?? '—') }}
                            </span>
                        </div>
                        <div class="bg-white dark:bg-gray-700 p-2.5 rounded-lg border border-gray-200/80 dark:border-gray-600">
                            <span class="text-[10px] text-gray-400 block">Kota / Kabupaten</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate mt-0.5">
                                {{ $migrationUser->cityRelation?->name ?: ($migrationUser->city_name ?: ($migrationUser->city ?: '—')) }}
                            </span>
                        </div>
                        <div class="bg-white dark:bg-gray-700 p-2.5 rounded-lg border border-gray-200/80 dark:border-gray-600">
                            <span class="text-[10px] text-gray-400 block">Kecamatan</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate mt-0.5">
                                {{ $migrationUser->district?->name ?: ($migrationUser->kecamatan ?: '—') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Wilayah Tujuan --}}
                @php
                    $selectedProvince = !empty($migrationProvinceId) ? collect($migrationAvailableProvinces)->firstWhere('id', (int)$migrationProvinceId) : null;
                    $selectedCity = !empty($migrationCityId) ? collect($migrationAvailableCities)->firstWhere('id', (int)$migrationCityId) : null;
                    $selectedDistrict = !empty($migrationDistrictId) ? collect($migrationAvailableDistricts)->firstWhere('id', (int)$migrationDistrictId) : null;
                @endphp

                <div class="pt-2 border-t border-gray-100 dark:border-gray-700 space-y-3.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Wilayah Tujuan</h3>
                    </div>

                    {{-- 1. Provinsi Tujuan --}}
                    <div class="space-y-1.5" x-data="{ searchProv: '' }">
                        <div class="flex items-center justify-between">
                            <span class="block text-xs font-bold text-gray-700 dark:text-gray-200">
                                1. Provinsi Tujuan <span class="text-rose-500">*</span>
                            </span>
                            @if(!empty($migrationProvinceId))
                                <button type="button" wire:click="$set('migrationProvinceId', null)" class="text-[11px] text-primary-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-semibold cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Ganti Provinsi</span>
                                </button>
                            @endif
                        </div>

                        @if(!empty($migrationProvinceId) && $selectedProvince)
                            {{-- Card Provinsi Terpilih --}}
                            <div class="p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <div class="text-[10px] uppercase font-bold text-emerald-800 dark:text-emerald-300">Provinsi Terpilih:</div>
                                        <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">{{ $selectedProvince['name'] }}</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/80 px-2 py-0.5 rounded-md border border-emerald-300 dark:border-emerald-700 shrink-0">
                                    Terpilih
                                </span>
                            </div>
                        @else
                            {{-- Daftar Pilihan Provinsi Anti-Overflow --}}
                            <div class="space-y-1.5">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text"
                                        id="search_migration_province"
                                        name="search_migration_province"
                                        autocomplete="off"
                                        aria-label="Cari nama Provinsi tujuan"
                                        x-model="searchProv"
                                        placeholder="Cari nama Provinsi tujuan..."
                                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-2xs">
                                </div>

                                <div class="max-h-36 overflow-y-auto dropdown-scrollbar divide-y divide-gray-100 dark:divide-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xs">
                                    @forelse($migrationAvailableProvinces as $prov)
                                        <button type="button"
                                            x-show="!searchProv || '{{ strtolower(addslashes($prov['name'])) }}'.includes(searchProv.toLowerCase())"
                                            wire:click="$set('migrationProvinceId', {{ $prov['id'] }})"
                                            class="w-full text-left px-3.5 py-2 flex items-center justify-between hover:bg-primary-50/80 dark:hover:bg-gray-700/80 transition cursor-pointer group">
                                            <div class="flex items-center gap-2 min-w-0 pr-2">
                                                <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-950 dark:group-hover:text-sky-400 flex items-center justify-center shrink-0 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                    </svg>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-sky-400 truncate">
                                                    {{ $prov['name'] }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-bold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-md border border-primary-200 dark:border-primary-800 group-hover:bg-primary-600 group-hover:text-white transition shrink-0">
                                                Pilih
                                            </span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-xs text-gray-400">Tidak ada data provinsi</div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                        <input type="hidden" id="migration_province_id" name="migration_province_id" autocomplete="off" wire:model="migrationProvinceId">
                        @error('migrationProvinceId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- 2. Kota / Kabupaten Tujuan --}}
                    <div class="space-y-1.5" x-data="{ searchCity: '' }">
                        <div class="flex items-center justify-between">
                            <span class="block text-xs font-bold text-gray-700 dark:text-gray-200">
                                2. Kota / Kabupaten Tujuan <span class="text-rose-500">*</span>
                            </span>
                            @if(!empty($migrationCityId))
                                <button type="button" wire:click="$set('migrationCityId', null)" class="text-[11px] text-primary-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-semibold cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Ganti Kota</span>
                                </button>
                            @endif
                        </div>

                        @if(empty($migrationProvinceId))
                            <div class="p-3 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/30 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pilih Provinsi di atas terlebih dahulu untuk menampilkan daftar Kota / Kabupaten.</span>
                            </div>
                        @elseif(!empty($migrationCityId) && $selectedCity)
                            {{-- Card Kota Terpilih --}}
                            <div class="p-3 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <div class="text-[10px] uppercase font-bold text-indigo-800 dark:text-indigo-300">Kota / Kab. Terpilih:</div>
                                        <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">{{ $selectedCity['name'] }}</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/80 px-2 py-0.5 rounded-md border border-indigo-300 dark:border-indigo-700 shrink-0">
                                    Terpilih
                                </span>
                            </div>
                        @else
                            {{-- Daftar Pilihan Kota Anti-Overflow --}}
                            <div class="space-y-1.5">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text"
                                        id="search_migration_city"
                                        name="search_migration_city"
                                        autocomplete="off"
                                        aria-label="Cari Kota atau Kabupaten tujuan"
                                        x-model="searchCity"
                                        placeholder="Cari Kota / Kabupaten di {{ $selectedProvince['name'] ?? '' }}..."
                                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-2xs">
                                </div>

                                <div class="max-h-36 overflow-y-auto dropdown-scrollbar divide-y divide-gray-100 dark:divide-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xs">
                                    @forelse($migrationAvailableCities as $c)
                                        <button type="button"
                                            x-show="!searchCity || '{{ strtolower(addslashes($c['name'])) }}'.includes(searchCity.toLowerCase())"
                                            wire:click="$set('migrationCityId', {{ $c['id'] }})"
                                            class="w-full text-left px-3.5 py-2 flex items-center justify-between hover:bg-primary-50/80 dark:hover:bg-gray-700/80 transition cursor-pointer group">
                                            <div class="flex items-center gap-2 min-w-0 pr-2">
                                                <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-950 dark:group-hover:text-sky-400 flex items-center justify-center shrink-0 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-sky-400 truncate">
                                                    {{ $c['name'] }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-bold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-md border border-primary-200 dark:border-primary-800 group-hover:bg-primary-600 group-hover:text-white transition shrink-0">
                                                Pilih
                                            </span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-xs text-gray-400">Tidak ada kota tersedia pada provinsi ini</div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                        <input type="hidden" id="migration_city_id" name="migration_city_id" autocomplete="off" wire:model="migrationCityId">
                        @error('migrationCityId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- 3. Kecamatan Tujuan (Jika Tersedia) --}}
                    @if(!empty($migrationCityId) && !empty($migrationAvailableDistricts))
                        <div class="space-y-1.5 pt-1 animate-fadeIn transition-all" x-data="{ searchDist: '' }">
                            <div class="flex items-center justify-between">
                                <span class="block text-xs font-bold text-gray-700 dark:text-gray-200">
                                    3. Kecamatan Tujuan <span class="text-rose-500">*</span>
                                </span>
                                @if(!empty($migrationDistrictId))
                                    <button type="button" wire:click="$set('migrationDistrictId', null)" class="text-[11px] text-primary-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-semibold cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Ganti Kecamatan</span>
                                    </button>
                                @else
                                    <span class="text-[10px] font-semibold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-full border border-primary-200 dark:border-primary-800">
                                        Wilayah Operasional
                                    </span>
                                @endif
                            </div>

                            @if(!empty($migrationDistrictId) && $selectedDistrict)
                                {{-- Card Kecamatan Terpilih --}}
                                <div class="p-3 rounded-xl bg-primary-50/80 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 flex items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/60 text-primary-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <div class="truncate">
                                            <div class="text-[10px] uppercase font-bold text-primary-800 dark:text-sky-300">Kecamatan Terpilih:</div>
                                            <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">Kec. {{ $selectedDistrict['name'] }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-2 py-0.5 rounded-md border border-emerald-300 dark:border-emerald-700 shrink-0">
                                        Terpilih
                                    </span>
                                </div>
                            @else
                                {{-- Daftar Pilihan Kecamatan Anti-Overflow --}}
                                <div class="space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text"
                                            id="search_migration_district"
                                            name="search_migration_district"
                                            autocomplete="off"
                                            aria-label="Cari Kecamatan tujuan"
                                            x-model="searchDist"
                                            placeholder="Cari kecamatan di {{ $selectedCity['name'] ?? '' }}..."
                                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-2xs">
                                    </div>

                                    <div class="max-h-36 overflow-y-auto dropdown-scrollbar divide-y divide-gray-100 dark:divide-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xs">
                                        @forelse($migrationAvailableDistricts as $d)
                                            <button type="button"
                                                x-show="!searchDist || '{{ strtolower(addslashes($d['name'])) }}'.includes(searchDist.toLowerCase())"
                                                wire:click="$set('migrationDistrictId', {{ $d['id'] }})"
                                                class="w-full text-left px-3.5 py-2 flex items-center justify-between hover:bg-primary-50/80 dark:hover:bg-gray-700/80 transition cursor-pointer group">
                                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-950 dark:group-hover:text-sky-400 flex items-center justify-center shrink-0 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-sky-400 truncate">
                                                        Kec. {{ $d['name'] }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] font-bold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-md border border-primary-200 dark:border-primary-800 group-hover:bg-primary-600 group-hover:text-white transition shrink-0">
                                                    Pilih
                                                </span>
                                            </button>
                                        @empty
                                            <div class="p-3 text-center text-xs text-gray-400">Tidak ada kecamatan tersedia</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                            <input type="hidden" id="migration_district_id" name="migration_district_id" autocomplete="off" wire:model="migrationDistrictId">
                            @error('migrationDistrictId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- 4. Alasan Migrasi Wilayah --}}
                    <div class="space-y-1.5">
                        <label for="migration_reason" class="block text-xs font-bold text-gray-700 dark:text-gray-200">
                            4. Alasan Migrasi Wilayah <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="migration_reason" name="migration_reason" wire:model="migrationReason" rows="3" placeholder="Tuliskan alasan resmi pemindahan wilayah administratif pengguna..."
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 transition shadow-2xs"></textarea>
                        @error('migrationReason') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Official Notice / Confirmation --}}
                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed font-medium">Perubahan ini hanya memindahkan wilayah administratif profil. Riwayat dan wilayah pekerjaan tidak berubah.</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                <button type="button" wire:click.prevent="closeModal"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button type="button" wire:click="submitMigration" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-xs hover:shadow transition-all disabled:opacity-50">
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    <span>Konfirmasi Migrasi Wilayah</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>