<div>
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-8 py-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Kelola admin dalam sistem</p>
                <button wire:click="openCreateModal"
                    class="px-6 py-3 bg-primary-600 text-white rounded-xl text-sm font-semibold hover:bg-primary-700 transition shadow-md hover:shadow-lg">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Admin</span>
                    </div>
                </button>
            </div>
            <div class="pb-2">
                <div wire:loading class="text-sm text-primary-700">Memproses... Mohon tunggu.</div>
            </div>
        </div>
    </div>

    <div class="py-2">
        <!-- Filter Cards -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <svg class="w-5 h-5 inline mr-2 text-primary-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Admin
                </label>
                <input type="text" wire:model.debounce.500ms="search" placeholder="Cari nama, email, atau HP..."
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
                <select wire:model.debounce.500ms="perPage"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="10">10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                </select>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <div class="text-sm font-semibold text-gray-700 mb-3">Total Admin</div>
                <div class="text-3xl font-bold text-primary-600">{{ $users->total() }}</div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Nama & Email</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No.
                                HP</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Verified</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Kota Dikelola</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Terdaftar</th>
                            <th class="px-6 py-5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $user->id }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 flex-shrink-0">
                                            <div
                                                class="h-12 w-12 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-lg">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->phone ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $user->verified ? 'Terverifikasi' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ (isset($user->status) && $user->status === 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ isset($user->status) ? ucfirst($user->status) : '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-700">
                                    @if($user->managedCities && $user->managedCities->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->managedCities as $managedCity)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $managedCity->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500">{{ $user->city_name ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button wire:click="viewUser({{ $user->id }})" wire:loading.attr="disabled"
                                            wire:target="viewUser,editUser,confirmDelete,deleteUser"
                                            class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition"
                                            title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button wire:click="editUser({{ $user->id }})" wire:loading.attr="disabled"
                                            wire:target="viewUser,editUser,confirmDelete,deleteUser"
                                            class="text-primary-600 hover:text-primary-900 p-2 hover:bg-primary-50 rounded-lg transition"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $user->id }})" wire:loading.attr="disabled"
                                            wire:target="viewUser,editUser,confirmDelete,deleteUser"
                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition"
                                            title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-gray-500 text-lg font-medium">Tidak ada data admin</p>
                                        <p class="text-gray-400 text-sm mt-1">Coba ubah filter atau tambah admin baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="bg-gray-50 px-6 py-5 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- View Admin Modal --}}
    @if($showViewModal && $selectedUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($selectedUser->name, 0, 1)) }}</div>
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $selectedUser->name }}</div>
                            <div class="text-sm text-gray-500">{{ $selectedUser->email }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click.prevent="closeModal"
                            class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <div class="text-xs text-gray-500">Nama Lengkap</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->name }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Email</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->email }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">No. HP</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->phone ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">NIK</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->nik ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Tempat, Tanggal Lahir</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->place_of_birth ?? '-' }},
                                    {{ optional($selectedUser->date_of_birth)->format('d M Y') ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Jenis Kelamin</div>
                                <div class="font-medium text-gray-900">
                                    {{ $selectedUser->gender ? ucfirst($selectedUser->gender) : '-' }}</div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="text-xs text-gray-500">Role</div>
                                <div class="font-medium text-gray-900">Admin</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Status</div>
                                <div class="font-medium text-gray-900">
                                    {{ $selectedUser->status ? ucfirst($selectedUser->status) : '—' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Verified</div>
                                <div class="font-medium text-gray-900">
                                    {{ $selectedUser->verified ? 'Terverifikasi' : 'Belum' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Kota</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->city_name ?? '-' }}</div>
                            </div>

                            @if($selectedUser->managedCities && $selectedUser->managedCities->count() > 0)
                            <div>
                                <div class="text-xs text-gray-500 mb-2">Kota yang Dikelola</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($selectedUser->managedCities as $managedCity)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-blue-100 text-blue-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $managedCity->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div>
                                <div class="text-xs text-gray-500">Pekerjaan</div>
                                <div class="font-medium text-gray-900">{{ $selectedUser->occupation ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Terdaftar</div>
                                <div class="font-medium text-gray-900">
                                    {{ optional($selectedUser->created_at)->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="text-xs text-gray-500">Alamat Lengkap</div>
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg text-gray-800">{{ $selectedUser->address ?? '-' }}</div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">RT / RW</div>
                            <div class="font-medium text-gray-900">
                                {{ ($selectedUser->rt ?? '-') . ' / ' . ($selectedUser->rw ?? '-') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Kelurahan</div>
                            <div class="font-medium text-gray-900">{{ $selectedUser->kelurahan ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Kecamatan</div>
                            <div class="font-medium text-gray-900">{{ $selectedUser->kecamatan ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Provinsi</div>
                            <div class="font-medium text-gray-900">{{ $selectedUser->province ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Agama</div>
                            <div class="font-medium text-gray-900">{{ $selectedUser->religion ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Status Perkawinan</div>
                            <div class="font-medium text-gray-900">{{ $selectedUser->marital_status ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Create / Edit Admin Modal (polished) --}}
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ $showEditModal ? 'Edit Admin' : 'Tambah Admin Baru' }}
                        </h3>
                        <p class="text-sm text-white/90">
                            {{ $showEditModal ? 'Perbarui informasi admin dengan hati-hati' : 'Lengkapi formulir untuk menambah admin baru' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click.prevent="closeModal"
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
                    <form wire:submit.prevent="saveUser" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Main form fields (2-column layout) -->
                            <div class="lg:col-span-2 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-gray-700">Nama Lengkap <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="name" placeholder="Nama lengkap"
                                            class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-700">Email <span
                                                class="text-red-500">*</span></label>
                                        <input type="email" wire:model.defer="email" placeholder="email@contoh.com"
                                            class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                        @error('email') <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-700">No. HP</label>
                                        <input type="text" wire:model.defer="phone" placeholder="08xxxxxxxxxx"
                                            class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                        @error('phone') <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-700">Password 
                                            @if($showEditModal)
                                                <span class="text-gray-500 text-xs">(kosongkan jika tidak ingin mengubah)</span>
                                            @else
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input type="password" wire:model.defer="password" 
                                            placeholder="{{ $showEditModal ? 'Isi untuk mengubah password' : 'Minimal 8 karakter' }}"
                                            class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                        @error('password') <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Status</label>
                                            <select wire:model.defer="status"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Nonaktif</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Verifikasi</label>
                                            <select wire:model.defer="verified"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                <option value="1">Terverifikasi</option>
                                                <option value="0">Belum</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 border-t pt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-medium text-gray-700">NIK</label>
                                            <input type="text" wire:model.defer="nik"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                            @error('nik') <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Jenis Kelamin</label>
                                            <select wire:model.defer="gender"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                <option value="">-- Pilih --</option>
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Tempat Lahir</label>
                                            <input type="text" wire:model.defer="place_of_birth"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Tanggal Lahir</label>
                                            <input type="date" wire:model.defer="date_of_birth"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                            @error('date_of_birth') <div class="text-sm text-red-600 mt-1">{{ $message }}
                                            </div> @enderror
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Pekerjaan</label>
                                            <input type="text" wire:model.defer="occupation"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-gray-700">Kota</label>
                                            <select wire:model.defer="city_id"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                <option value="">-- Pilih Kota --</option>
                                                @foreach($cities as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('city_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Multiple Cities for Admin -->
                                        <div class="md:col-span-2">
                                            <label class="text-xs font-medium text-gray-700 mb-2 block">
                                                <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Kota yang Dikelola Admin (Checklist)
                                            </label>
                                            <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-xl max-h-64 overflow-y-auto">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @forelse($availableCities as $c)
                                                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition-all cursor-pointer">
                                                            <input 
                                                                type="checkbox" 
                                                                wire:model.defer="managed_city_ids" 
                                                                value="{{ $c->id }}"
                                                                class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-2 focus:ring-primary-500 cursor-pointer"
                                                            />
                                                            <div class="flex-1">
                                                                <div class="text-sm font-semibold text-gray-900">{{ $c->name }}</div>
                                                                <div class="text-xs text-gray-500">{{ $c->province ?? 'Indonesia' }}</div>
                                                            </div>
                                                        </label>
                                                    @empty
                                                        <div class="col-span-2 text-center text-sm text-gray-500 py-4">
                                                            Belum ada kota tersedia atau semua kota sudah dikelola oleh admin lain
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">
                                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                Admin dapat mengelola lebih dari satu kota. Pilih kota yang ingin dikelola oleh admin ini.
                                            </p>
                                            @error('managed_city_ids') 
                                                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="text-xs font-medium text-gray-700">Alamat Lengkap</label>
                                            <textarea wire:model.defer="address" rows="3"
                                                class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions inside form so submit works with enter -->
                        <div class="pt-2 border-t flex items-center justify-end gap-3">
                            <button type="button" wire:click.prevent="closeModal"
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
                                <span>{{ $showEditModal ? 'Perbarui Admin' : 'Simpan Admin' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Delete Modal --}}
    @if($showConfirmDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-lg w-11/12 max-w-md p-6">
                <h3 class="text-lg font-semibold mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-600 mb-4">Anda yakin ingin menghapus admin ini? Aksi ini tidak dapat dibatalkan.
                </p>
                <div class="text-right">
                    <button type="button" wire:click.prevent="closeModal"
                        class="px-4 py-2 mr-2 bg-gray-100 rounded-lg">Batal</button>
                    <button wire:click="deleteUser" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
    @endif

</div>
