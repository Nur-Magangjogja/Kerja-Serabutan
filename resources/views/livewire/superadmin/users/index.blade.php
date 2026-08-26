@php
    $title = 'Manajemen User';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola semua pengguna dalam sistem</p>
        </div>
        <div class="flex items-center gap-2">
            <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                Memproses...
            </div>
        </div>
    </div>

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.debounce.400ms="search" placeholder="Cari nama, email, atau HP..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            {{-- Role Filter --}}
            <select wire:model="roleFilter"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Role</option>
                <option value="customer">Customer</option>
                <option value="mitra">Mitra</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>

            {{-- Per Page --}}
            <select wire:model="perPage"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>

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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
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
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $user->id }}</td>
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
                            @if($user->role === 'admin' && $user->managedCities && $user->managedCities->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->managedCities->take(2) as $mc)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">{{ $mc->name }}</span>
                                    @endforeach
                                    @if($user->managedCities->count() > 2)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $user->managedCities->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">{{ $user->city_name ?? '—' }}</span>
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
        @if($users->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- ===== View User Modal ===== --}}
    @if($showViewModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $selectedUser->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedUser->email }}</p>
                    </div>
                </div>
                <button type="button" wire:click.prevent="closeModal" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    @php
                    $fields = [
                        ['label' => 'Nama Lengkap',      'value' => $selectedUser->name],
                        ['label' => 'Email',              'value' => $selectedUser->email],
                        ['label' => 'No. HP',             'value' => $selectedUser->phone ?? '—'],
                        ['label' => 'NIK',                'value' => $selectedUser->nik ?? '—'],
                        ['label' => 'Tempat Lahir',       'value' => $selectedUser->place_of_birth ?? '—'],
                        ['label' => 'Tanggal Lahir',      'value' => optional($selectedUser->date_of_birth)?->format('d M Y') ?? '—'],
                        ['label' => 'Jenis Kelamin',      'value' => $selectedUser->gender ? ucfirst($selectedUser->gender) : '—'],
                        ['label' => 'Role',               'value' => ['super_admin'=>'Super Admin','admin'=>'Admin','mitra'=>'Mitra','customer'=>'Customer'][$selectedUser->role] ?? ucfirst($selectedUser->role)],
                        ['label' => 'Status',             'value' => $selectedUser->status ? ucfirst($selectedUser->status) : '—'],
                        ['label' => 'Verifikasi',         'value' => $selectedUser->verified ? 'Terverifikasi' : 'Belum Terverifikasi'],
                        ['label' => 'Kota',               'value' => $selectedUser->city_name ?? '—'],
                        ['label' => 'Pekerjaan',          'value' => $selectedUser->occupation ?? '—'],
                        ['label' => 'Agama',              'value' => $selectedUser->religion ?? '—'],
                        ['label' => 'Status Perkawinan',  'value' => $selectedUser->marital_status ?? '—'],
                        ['label' => 'Aktivitas Terakhir', 'value' => $selectedUser->last_activity_at ? $selectedUser->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB (' . $selectedUser->last_activity_for_humans . ')' : 'Belum ada aktivitas'],
                        ['label' => 'Terdaftar',          'value' => optional($selectedUser->created_at)?->format('d M Y H:i') ?? '—'],
                    ];
                    @endphp
                    @foreach($fields as $f)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">{{ $f['label'] }}</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $f['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                @if($selectedUser->address)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">Alamat Lengkap</p>
                    <p class="text-sm text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/50 rounded-lg px-4 py-3">{{ $selectedUser->address }}</p>
                </div>
                @endif

                @if($selectedUser->role === 'admin' && $selectedUser->managedCities && $selectedUser->managedCities->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">Kota yang Dikelola</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($selectedUser->managedCities as $mc)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">{{ $mc->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
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
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="name" placeholder="Nama lengkap"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model.defer="email" placeholder="email@contoh.com"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">No. HP</label>
                            <input type="text" wire:model.defer="phone" placeholder="08xxxxxxxxxx"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                Password
                                @if($showEditModal) <span class="text-gray-400 dark:text-gray-500">(kosongkan jika tidak diubah)</span>@else <span class="text-red-500">*</span>@endif
                            </label>
                            <input type="password" wire:model.defer="password"
                                placeholder="{{ $showEditModal ? 'Isi untuk mengubah' : 'Minimal 8 karakter' }}"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Role</label>
                            <select wire:model.defer="role" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="customer">Customer</option>
                                <option value="mitra">Mitra</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
                            <select wire:model.defer="status" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Verifikasi</label>
                            <select wire:model.defer="verified" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="1">Terverifikasi</option>
                                <option value="0">Belum</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Kota</label>
                            <select wire:model.defer="city_id" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Pilih Kota --</option>
                                @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">NIK</label>
                            <input type="text" wire:model.defer="nik"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('nik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Jenis Kelamin</label>
                            <select wire:model.defer="gender" class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Tempat Lahir</label>
                            <input type="text" wire:model.defer="place_of_birth"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Tanggal Lahir</label>
                            <input type="date" wire:model.defer="date_of_birth"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('date_of_birth') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Pekerjaan</label>
                            <input type="text" wire:model.defer="occupation"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Alamat Lengkap</label>
                            <textarea wire:model.defer="address" rows="2"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        </div>
                    </div>

                    {{-- Kota yang Dikelola Admin --}}
                    @if($role === 'admin')
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-2 block">Kota yang Dikelola (untuk Admin)</label>
                        <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/30">
                            <div class="grid grid-cols-2 gap-2">
                                @forelse($cities as $c)
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-primary-400 cursor-pointer transition-colors">
                                    <input type="checkbox" wire:model.defer="managed_city_ids" value="{{ $c->id }}"
                                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 cursor-pointer">
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $c->name }}</span>
                                </label>
                                @empty
                                <p class="col-span-2 text-sm text-gray-400 text-center py-3">Belum ada kota tersedia</p>
                                @endforelse
                            </div>
                        </div>
                        @error('managed_city_ids') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Masukkan Kata Sandi Akun Anda <span class="text-red-500">*</span>
                </label>
                <input type="password" wire:model.defer="adminPassword" wire:keydown.enter="deleteUser"
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
</div>