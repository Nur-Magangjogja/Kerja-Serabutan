@php
    $title = 'Manajemen Kota';
@endphp

<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Kota</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola kota dan provinsi layanan SayaBantu</p>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kota
        </button>
    </div>

    {{-- ===== Provinces Panel ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Provinsi Terdaftar</span>
                <span class="text-xs text-gray-400 dark:text-gray-500">(klik untuk filter kota)</span>
            </div>
            <button wire:click="openProvinceModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Provinsi
            </button>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="$set('filterProvinceId', null)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                {{ is_null($filterProvinceId) ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                Semua
            </button>
            @foreach($provinces as $prov)
            <div class="inline-flex items-center gap-1 group">
                <button wire:click="selectProvince({{ $prov->id }})"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                    {{ $filterProvinceId === $prov->id ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    {{ $prov->name }}
                </button>
                <div class="hidden group-hover:flex items-center gap-0.5">
                    <button wire:click.stop="openProvinceModal({{ $prov->id }})" title="Edit" class="p-1 text-primary-500 hover:text-primary-700 dark:hover:text-primary-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click.stop="confirmDeleteProvince({{ $prov->id }})" title="Hapus" class="p-1 text-rose-500 hover:text-rose-700 dark:hover:text-rose-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live="search" placeholder="Cari nama kota atau provinsi..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="perPage"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>
            <div wire:loading class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Memuat...
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kota</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Provinsi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Dibuat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($cities as $city)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $city->id }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $city->name }}</p>
                                    @if(!empty($loadDistricts) && $city->relationLoaded('districts'))
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $city->districts->count() }} kecamatan</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">{{ $city->province }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                {{ number_format($city->users_count) }} user
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <button wire:click="toggleStatus({{ $city->id }})"
                                class="inline-flex items-center gap-1.5 text-xs font-medium transition-colors
                                {{ $city->is_active ? 'text-emerald-700 dark:text-emerald-400 hover:text-emerald-800' : 'text-rose-600 dark:text-rose-400 hover:text-rose-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $city->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $city->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell whitespace-nowrap">{{ $city->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openDetailModal({{ $city->id }})" title="Detail"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button wire:click="editCity({{ $city->id }})" title="Edit"
                                    class="p-1.5 rounded-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $city->id }})" title="Hapus"
                                    class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- District sub-rows --}}
                    @if(!empty($loadDistricts) && $city->relationLoaded('districts') && $city->districts->isNotEmpty())
                        @foreach($city->districts as $district)
                        <tr class="bg-gray-50/80 dark:bg-gray-700/20 border-l-2 border-primary-200 dark:border-primary-800">
                            <td></td>
                            <td class="px-4 py-2 pl-14 text-sm text-gray-600 dark:text-gray-300">↳ {{ $district->name }}</td>
                            <td class="px-4 py-2 text-xs text-gray-400 dark:text-gray-500">Kecamatan</td>
                            <td></td>
                            <td class="px-4 py-2 text-xs {{ $district->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
                                {{ $district->is_active ? 'Aktif' : 'Nonaktif' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-400 dark:text-gray-500 hidden md:table-cell">{{ optional($district->created_at)->format('d M Y') }}</td>
                            <td></td>
                        </tr>
                        @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada data kota</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tambah kota baru untuk memulai</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cities->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $cities->links() }}
        </div>
        @endif
    </div>

    {{-- ===== Create/Edit City Modal ===== --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $editMode ? 'Edit Kota' : 'Tambah Kota Baru' }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $editMode ? 'Perbarui data kota' : 'Lengkapi formulir untuk menambah kota layanan' }}</p>
                </div>
                <button type="button" wire:click="$set('showModal', false)" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit.prevent="save" class="px-6 py-5 space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Nama Kota <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name" placeholder="Contoh: Bandung"
                        class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Provinsi <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="province" placeholder="Contoh: Jawa Barat"
                        class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('province') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Admin Kota (opsional)</label>
                    <select wire:model.defer="admin_id"
                        class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Tidak ada --</option>
                        @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                        @endforeach
                    </select>
                    @error('admin_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.defer="is_active" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-200">Aktifkan kota</span>
                </label>

                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2 disabled:opacity-60">
                        <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        {{ $editMode ? 'Perbarui Kota' : 'Simpan Kota' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ===== Province Modal ===== --}}
    @if($showProvinceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $provinceEditId ? 'Edit Provinsi' : 'Tambah Provinsi' }}</h3>
                <button type="button" wire:click="$set('showProvinceModal', false)" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit.prevent="saveProvince" class="px-6 py-5 space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Nama Provinsi</label>
                    <input type="text" wire:model.defer="provinceName"
                        class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('provinceName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="$set('showProvinceModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ===== Confirm Delete Province Modal ===== --}}
    @if($showProvinceDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Provinsi?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Semua relasi kota dengan provinsi ini akan dilepas.</p>
                </div>
            </div>
            @if($deletingProvinceName)
            <p class="text-sm bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 px-4 py-3 rounded-lg mb-4">{{ $deletingProvinceName }}</p>
            @endif
            <div class="flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('showProvinceDeleteModal', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button wire:click.prevent="deleteProvince" class="px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Confirm Delete City Modal ===== --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Hapus Kota?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Aksi ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            @if($deletingCityName)
            <p class="text-sm bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 px-4 py-3 rounded-lg mb-4">{{ $deletingCityName }}</p>
            @endif
            <div class="flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button wire:click.prevent="deleteCity" class="px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Detail City Modal (Chart) ===== --}}
    @if($showDetailModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Detail Kota: {{ $detailCityName }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Grafik pengguna aktif 30 hari terakhir</p>
                </div>
                <button type="button" wire:click.prevent="closeDetailModal" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <div wire:ignore>
                    <canvas id="cityUsersChart" height="160"></canvas>
                </div>
                <div id="cityChartData" class="hidden"
                    data-labels='@json($chartLabels)'
                    data-customers='@json($chartCustomerData)'
                    data-mitras='@json($chartMitraData)'>
                </div>
                <div id="cityChartDebug" class="hidden"></div>
                <script>
                    try {
                        if (typeof tryInitFromDom === 'function') { tryInitFromDom(); }
                    } catch (err) { console.error('[city-chart] inline error', err); }
                </script>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex justify-end">
                <button type="button" wire:click.prevent="closeDetailModal"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Chart.js scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        window.cityUsersChartInstance = null;

        function createChartInstance(el, labels, customerData, mitraData) {
            const ctx = el.getContext('2d');
            if (typeof Chart !== 'undefined' && Chart.getChart) {
                const existing = Chart.getChart(el) || Chart.getChart('cityUsersChart');
                if (existing) {
                    try { existing.destroy(); } catch (e) {}
                }
            }
            if (window.cityUsersChartInstance) {
                try { window.cityUsersChartInstance.destroy(); } catch (e) {}
                window.cityUsersChartInstance = null;
            }
            const isDark = document.documentElement.classList.contains('dark');
            window.cityUsersChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Customer (Aktif)', data: customerData, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.08)', tension: 0.3, fill: true },
                        { label: 'Mitra (Aktif)', data: mitraData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.3, fill: true }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: isDark ? '#9ca3af' : '#6b7280' }, grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' } },
                        y: { beginAtZero: true, ticks: { color: isDark ? '#9ca3af' : '#6b7280' }, grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        function tryInitFromDom() {
            const dataEl = document.getElementById('cityChartData');
            const el = document.getElementById('cityUsersChart');
            if (!dataEl || !el) return false;
            try {
                const labels = JSON.parse(dataEl.getAttribute('data-labels') || '[]');
                const customerData = JSON.parse(dataEl.getAttribute('data-customers') || '[]');
                const mitraData = JSON.parse(dataEl.getAttribute('data-mitras') || '[]');
                if (typeof Chart !== 'undefined') { createChartInstance(el, labels, customerData, mitraData); return true; }
            } catch (err) { console.error('[city-chart]', err); }
            return false;
        }
        window.tryInitFromDom = tryInitFromDom;

        const handler = () => tryInitFromDom();
        if (window.Livewire && typeof window.Livewire.hook === 'function') window.Livewire.hook('message.processed', handler);
        if (window.Livewire && typeof window.Livewire.on === 'function') {
            window.Livewire.on('cityDetailReady', (labels, customerData, mitraData) => {
                function tryInit() {
                    const el = document.getElementById('cityUsersChart');
                    if (!el) return false;
                    createChartInstance(el, labels || [], customerData || [], mitraData || []); return true;
                }
                if (!tryInit()) {
                    let i = 0;
                    const t = setInterval(() => { if (tryInit() || ++i >= 15) clearInterval(t); }, 120);
                }
            });
        }

        window.addEventListener('theme-changed', function(e) {
            const dark = e.detail?.isDark ?? document.documentElement.classList.contains('dark');
            if (window.cityUsersChartInstance && window.cityUsersChartInstance.options && window.cityUsersChartInstance.options.scales) {
                window.cityUsersChartInstance.options.scales.x.ticks.color = dark ? '#9ca3af' : '#6b7280';
                window.cityUsersChartInstance.options.scales.y.ticks.color = dark ? '#9ca3af' : '#6b7280';
                window.cityUsersChartInstance.options.scales.x.grid.color = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
                window.cityUsersChartInstance.options.scales.y.grid.color = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
                window.cityUsersChartInstance.update();
            }
        });
    })();
    </script>
</div>
