@php
    $title = 'Manajemen Kota';
    $breadcrumb = 'Super Admin / Manajemen Kota';
@endphp

<div>
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-8 py-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Kelola kota layanan sayabantu</p>
                <button wire:click="openCreateModal"
                    class="px-6 py-3 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 transition shadow-md hover:shadow-lg">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Kota</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="p-12">
        <!-- Provinces management card -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700">Provinsi</h4>
                    <div class="text-xs text-gray-500">Kelola daftar provinsi layanan</div>
                </div>
                <div class="flex items-center space-x-3">
                    <button wire:click="openProvinceModal()"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm">Tambah Provinsi</button>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-3">
                @foreach($provinces as $prov)
                    <div wire:click.prevent="selectProvince({{ $prov->id }})" class="p-3 rounded-lg flex items-center justify-between cursor-pointer {{ $filterProvinceId === $prov->id ? 'bg-primary-50 border border-primary-200' : 'bg-gray-50' }}">
                        <div>
                            <div class="text-sm font-medium">{{ $prov->name }}</div>
                            <div class="text-xs text-gray-500">{{ $prov->created_at->format('d M Y') }}</div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click.stop="openProvinceModal({{ $prov->id }})" class="text-primary-600 p-2 hover:bg-primary-50 rounded-lg">Edit</button>
                            <button wire:click.stop="confirmDeleteProvince({{ $prov->id }})" class="text-red-600 p-2 hover:bg-red-50 rounded-lg">Hapus</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Filter Cards -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <svg class="w-5 h-5 inline mr-2 text-primary-600" fill="none" stroke="currentColor"
                        class="flex items-center justify-between mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Kota
                </label>
                <input type="text" wire:model.live="search" placeholder="Cari nama kota atau provinsi..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <svg class="w-5 h-5 inline mr-2 text-primary-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Per Halaman
                </label>
                <select wire:model.live="perPage"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="10">10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                </select>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Nama Kota</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Provinsi</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Jumlah User</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Dibuat</th>
                            <th class="px-6 py-5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($cities as $city)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $city->id }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 flex-shrink-0 bg-primary-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $city->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">@if(!empty($loadDistricts) && $city->relationLoaded('districts')) {{ $city->districts->count() }} kecamatan @else - kecamatan @endif</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap"><div class="text-sm text-gray-900">{{ $city->province }}</div></td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ number_format($city->users_count) }} User</span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <button wire:click="toggleStatus({{ $city->id }})" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $city->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }} transition">{{ $city->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500">{{ $city->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button wire:click="openDetailModal({{ $city->id }})" class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button wire:click="editCity({{ $city->id }})" class="text-primary-600 hover:text-primary-900 p-2 hover:bg-primary-50 rounded-lg transition" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $city->id }})" class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- District rows grouped under the city --}}
                            @if(!empty($loadDistricts) && $city->relationLoaded('districts') && $city->districts->isNotEmpty())
                                @foreach($city->districts as $district)
                                    <tr class="bg-gray-50">
                                        <td></td>
                                        <td class="px-6 py-3 text-sm text-gray-700">&nbsp;&nbsp;&mdash; {{ $district->name }}</td>
                                        <td class="px-6 py-3 text-xs text-gray-500">Kecamatan</td>
                                        <td class="px-6 py-3 text-sm text-gray-500">&nbsp;</td>
                                        <td class="px-6 py-3 text-sm text-gray-500">@if($district->is_active)<span class="text-green-600">Aktif</span>@else<span class="text-red-600">Nonaktif</span>@endif</td>
                                        <td class="px-6 py-3 text-sm text-gray-400">{{ optional($district->created_at)->format('d M Y') }}</td>
                                        <td class="px-6 py-3 text-center text-sm text-gray-500">&nbsp;</td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        <p class="text-gray-500 text-lg font-medium">Tidak ada data kota</p>
                                        <p class="text-gray-400 text-sm mt-1">Tambah kota baru untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($cities->hasPages())
                <div class="bg-gray-50 px-6 py-5 border-t border-gray-200">
                    {{ $cities->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create / Edit Modal (polished like Users modal) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ $editMode ? 'Edit Kota' : 'Tambah Kota Baru' }}</h3>
                        <p class="text-sm text-white/90">
                            {{ $editMode ? 'Perbarui data kota dengan hati-hati' : 'Lengkapi formulir untuk menambah kota layanan' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="text-white/90 hover:text-white p-2 rounded-md" aria-label="Tutup modal">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal body (form) -->
                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-700">Nama Kota <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="name" placeholder="Contoh: Bandung"
                                    class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-700">Provinsi <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="province" placeholder="Contoh: Jawa Barat"
                                    class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                @error('province') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-700">Admin Kota (opsional)</label>
                                <select wire:model.defer="admin_id"
                                    class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">-- Tidak ada --</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                    @endforeach
                                </select>
                                @error('admin_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.defer="is_active" class="rounded" />
                                    <span class="text-sm">Aktifkan kota</span>
                                </label>
                            </div>
                        </div>

                        <!-- Footer Actions inside form so submit works with enter -->
                        <div class="pt-2 border-t flex items-center justify-end gap-3">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg flex items-center gap-2 text-sm"
                                wire:loading.attr="disabled">
                                <svg wire:loading class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                        fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>
                                <span>{{ $editMode ? 'Perbarui Kota' : 'Simpan Kota' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Province Modals --}}
    @if($showProvinceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ $provinceEditId ? 'Edit Provinsi' : 'Tambah Provinsi' }}</h3>
                        <p class="text-sm text-white/90">{{ $provinceEditId ? 'Perbarui nama provinsi' : 'Masukkan nama provinsi baru' }}</p>
                    </div>
                    <div>
                        <button type="button" wire:click="$set('showProvinceModal', false)" class="text-white/90 p-2">&times;</button>
                    </div>
                </div>

                <div class="p-6">
                    <form wire:submit.prevent="saveProvince">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Nama Provinsi</label>
                            <input type="text" wire:model.defer="provinceName" class="w-full mt-2 px-4 py-3 border rounded-lg" />
                            @error('provinceName') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" wire:click="$set('showProvinceModal', false)" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showProvinceDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Konfirmasi Hapus Provinsi</h3>
                        <p class="text-sm text-gray-600 mt-1">Anda yakin ingin menghapus provinsi ini? Semua relasi provinsi akan dilepas dari kota.</p>
                    </div>
                    <div>
                        <button type="button" wire:click="$set('showProvinceDeleteModal', false)" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-sm text-gray-700">Provinsi yang akan dihapus:</div>
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg text-gray-900 font-medium">{{ $deletingProvinceName ?? '-' }}</div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showProvinceDeleteModal', false)" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
                    <button type="button" wire:click.prevent="deleteProvince" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal (polished) -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-600 mt-1">Anda yakin ingin menghapus kota ini? Aksi ini tidak dapat
                            dibatalkan.</p>
                    </div>
                    <div>
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-sm text-gray-700">Kota yang akan dihapus:</div>
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg text-gray-900 font-medium">{{ $deletingCityName ?? '-' }}
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
                    <button type="button" wire:click.prevent="deleteCity"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal (per-city stats) --}}
    @if($showDetailModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div>
                        <h3 class="text-xl font-bold text-white">Detail Kota: {{ $detailCityName }}</h3>
                        <p class="text-sm text-white/90">Grafik jumlah pengguna aktif per hari (Customer & Mitra) — 30 hari
                            terakhir</p>
                    </div>
                    <div>
                        <button type="button" wire:click.prevent="closeDetailModal"
                            class="text-white/90 hover:text-white p-2 rounded-md">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div wire:ignore>
                        <canvas id="cityUsersChart" height="160"></canvas>
                    </div>
                    {{-- Hidden data payload so client can read chart arrays after Livewire updates DOM --}}
                    <div id="cityChartData" class="hidden" data-labels='@json($chartLabels)'
                        data-customers='@json($chartCustomerData)' data-mitras='@json($chartMitraData)'></div>
                    {{-- Visible debug panel for troubleshooting chart rendering --}}
                    <div id="cityChartDebug" class="mt-3 p-3 bg-gray-50 rounded text-xs text-gray-700"
                        style="white-space:pre-wrap;font-family:monospace;">[debug panel - awaiting data]</div>
                    <script>
                        try {
                            if (typeof tryInitFromDom === 'function') {
                                tryInitFromDom();
                                console.debug('[city-chart] tryInitFromDom invoked inline');
                                const db = document.getElementById('cityChartDebug'); if (db) db.innerText += '\n[inline] invoked tryInitFromDom()';
                            } else {
                                console.debug('[city-chart] tryInitFromDom not defined yet');
                                const db = document.getElementById('cityChartDebug'); if (db) db.innerText += '\n[inline] tryInitFromDom not defined yet';
                            }
                        } catch (err) {
                            console.error('[city-chart] inline init error', err);
                            const db = document.getElementById('cityChartDebug'); if (db) db.innerText += '\n[inline] error: ' + err;
                        }
                    </script>
                </div>

                <div class="px-6 py-4 border-t flex justify-end gap-3">
                    <button type="button" wire:click.prevent="closeDetailModal"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>

    @endif

    {{-- Chart initialization listener: always present on page so it runs when Livewire dispatches the event --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            window.cityUsersChartInstance = null;

            function createChartInstance(el, labels, customerData, mitraData) {
                const ctx = el.getContext('2d');
                if (window.cityUsersChartInstance) {
                    try { window.cityUsersChartInstance.destroy(); } catch (e) { }
                    window.cityUsersChartInstance = null;
                }
                window.cityUsersChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Customer (Aktif)',
                                data: customerData,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14,165,233,0.08)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Mitra (Aktif)',
                                data: mitraData,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.08)',
                                tension: 0.3,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { x: { display: true }, y: { display: true, beginAtZero: true } }
                    }
                });
            }

            // Try to initialize the chart by reading data attributes inside the modal.
            function tryInitFromDom() {
                const debugEl = document.getElementById('cityChartDebug');
                const dataEl = document.getElementById('cityChartData');
                if (debugEl) debugEl.innerText = '[city-chart] tryInitFromDom called...';
                if (!dataEl) {
                    if (debugEl) debugEl.innerText += '\nno data element found';
                    console.log('[city-chart] no dataEl');
                    return false;
                }
                const el = document.getElementById('cityUsersChart');
                if (debugEl) debugEl.innerText += '\ncanvas found=' + (!!el);
                if (!el) {
                    console.log('[city-chart] no canvas');
                    return false;
                }

                let labels = [];
                let customerData = [];
                let mitraData = [];
                try {
                    labels = JSON.parse(dataEl.getAttribute('data-labels') || '[]');
                    customerData = JSON.parse(dataEl.getAttribute('data-customers') || '[]');
                    mitraData = JSON.parse(dataEl.getAttribute('data-mitras') || '[]');
                } catch (err) {
                    console.error('[city-chart] Failed to parse chart data:', err);
                    if (debugEl) debugEl.innerText += '\nFailed to parse chart data: ' + err;
                }

                if (debugEl) debugEl.innerText += '\nparsed: labels=' + labels.length + ', customers=' + customerData.length + ', mitras=' + mitraData.length;

                if (typeof Chart === 'undefined') {
                    console.error('[city-chart] Chart.js not loaded');
                    if (debugEl) debugEl.innerText += '\nChart.js not loaded';
                    return false;
                }

                try {
                    createChartInstance(el, labels, customerData, mitraData);
                    if (debugEl) debugEl.innerText += '\nChart instance created';
                } catch (err) {
                    console.error('[city-chart] Error creating Chart instance:', err);
                    if (debugEl) debugEl.innerText += '\nError creating Chart instance: ' + err;
                }

                return true;
            }

            // Hook into Livewire DOM updates: try to initialize after each update (covers modal open)
            const tryInitHandler = function () {
                tryInitFromDom();
            };

            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('message.processed', tryInitHandler);
            } else if (window.livewire && typeof window.livewire.hook === 'function') {
                window.livewire.hook('message.processed', tryInitHandler);
            }

            // Also listen for Livewire emit (some Livewire versions use emit)
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('cityDetailReady', function (labels, customerData, mitraData) {
                    function tryInit() {
                        const el = document.getElementById('cityUsersChart');
                        if (!el) return false;
                        createChartInstance(el, labels || [], customerData || [], mitraData || []);
                        return true;
                    }
                    if (!tryInit()) {
                        let attempts = 0;
                        const maxAttempts = 15;
                        const timer = setInterval(function () {
                            attempts++;
                            if (tryInit() || attempts >= maxAttempts) clearInterval(timer);
                        }, 120);
                    }
                });
            } else if (window.livewire && typeof window.livewire.on === 'function') {
                window.livewire.on('cityDetailReady', function (labels, customerData, mitraData) {
                    function tryInit() {
                        const el = document.getElementById('cityUsersChart');
                        if (!el) return false;
                        createChartInstance(el, labels || [], customerData || [], mitraData || []);
                        return true;
                    }
                    if (!tryInit()) {
                        let attempts = 0;
                        const maxAttempts = 15;
                        const timer = setInterval(function () {
                            attempts++;
                            if (tryInit() || attempts >= maxAttempts) clearInterval(timer);
                        }, 120);
                    }
                });
            }
        })();
    </script>

</div>