<div>
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-primary-50 to-primary-100 border-b border-primary-200">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-primary-600 mb-2">
                        <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="font-semibold">Verifikasi KTP</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Verifikasi KTP Mitra</h1>
                    <p class="text-sm text-gray-600 mt-1">Verifikasi identitas mitra untuk keamanan dan kepercayaan platform</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="text-xs text-gray-500">Total Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $verifications->where('status', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                </svg>
                <div>{{ session('message') }}</div>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 block">Cari Mitra</label>
                <div class="relative">
                    <input type="text" wire:model.debounce.500ms="search" placeholder="Nama, email, atau NIK..." class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" />
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 block">Filter Status</label>
                <select wire:model="statusFilter" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Terverifikasi</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 block">Per Halaman</label>
                <select wire:model="perPage" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="10">10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                </select>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Mitra</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Dokumen</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($verifications as $v)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 align-middle">#{{ $v->id }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr($v->full_name ?? ($v->name ?? '-'),0,1)) }}</div>
                                    <div>
                                        <div class="font-medium">{{ $v->full_name ?? ($v->name ?? '-') }}</div>
                                        <div class="text-xs text-gray-500">{{ $v->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">{{ $v->role ?? 'customer' }}</td>
                            <td class="px-4 py-4">{{ $v->nik ?? '-' }}</td>
                            <td class="px-4 py-4">
                                @if(!empty($v->selfie_url))
                                    <a href="#" wire:click.prevent="viewKtp({{ $v->id }})" class="text-indigo-600 hover:underline">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if(!empty($v->ktp_url))
                                    <a href="#" wire:click.prevent="viewKtp({{ $v->id }})" class="text-indigo-600 hover:underline">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if(($v->status ?? '') === 'approved')
                                    <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">Terverifikasi</span>
                                @elseif(($v->status ?? '') === 'rejected')
                                    <span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full">Ditolak</span>
                                @else
                                    <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">{{ optional($v->created_at)->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4 text-center">
                                <button type="button" wire:click="viewKtp({{ $v->id }})" class="px-3 py-1 bg-white border border-gray-200 rounded text-sm">Lihat</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-gray-500">Tidak ada data verifikasi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100">
                {{ $verifications->links() }}
            </div>
        </div>

        @if($showModal && $selected)
            <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" role="dialog" aria-modal="true">
                <div class="bg-white rounded-2xl w-full max-w-4xl p-6 shadow-2xl ring-1 ring-black ring-opacity-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold text-lg">{{ strtoupper(substr($selected->full_name ?? ($selected->name ?? '-'),0,1)) }}</div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $selected->full_name ?? ($selected->name ?? '-') }}</h3>
                                <div class="text-sm text-gray-500">ID: #{{ $selected->id }} • {{ ucfirst($selected->role ?? 'customer') }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div>
                                @if(($selected->status ?? '') === 'approved')
                                    <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">Terverifikasi</span>
                                @elseif(($selected->status ?? '') === 'rejected')
                                    <span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full">Ditolak</span>
                                @else
                                    <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                                @endif
                            </div>
                            <button type="button" wire:click="closeModal" class="text-gray-500 hover:text-gray-700" aria-label="Tutup">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-xs text-gray-500">Nama</div>
                                    <div class="text-sm text-gray-900">{{ $selected->full_name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Email</div>
                                    <div class="text-sm text-gray-900">{{ $selected->email ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">NIK</div>
                                    <div class="text-sm text-gray-900">{{ $selected->nik ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Upload</div>
                                    <div class="text-sm text-gray-900">{{ optional($selected->created_at)->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="approveKtp({{ $selected->id }})" class="px-4 py-2 bg-green-600 text-white rounded shadow-sm">Verifikasi</button>
                                    <button type="button" wire:click="rejectKtp({{ $selected->id }})" class="px-4 py-2 bg-red-600 text-white rounded shadow-sm">Tolak</button>
                                    @if(Route::has('admin.user.show'))
                                        <a href="{{ route('admin.user.show', $selected->user_id ?? $selected->id) }}" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-100">Lihat Profil</a>
                                    @else
                                        <span class="px-3 py-2 bg-gray-50 border border-gray-200 rounded text-sm text-gray-400">Lihat Profil</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-1 space-y-4">
                            <div>
                                <div class="text-sm text-gray-500 mb-2">Foto Selfie</div>
                                @if(!empty($selected->selfie_url))
                                    <a href="{{ $selected->selfie_url }}" target="_blank" rel="noopener noreferrer" class="block rounded overflow-hidden ring-1 ring-black ring-opacity-5 hover:shadow-lg">
                                        <img src="{{ $selected->selfie_url }}" alt="Selfie" class="w-full h-40 object-cover" />
                                    </a>
                                    <div class="mt-2 flex gap-2">
                                        <a href="{{ $selected->selfie_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Buka ukuran penuh</a>
                                        <a href="{{ $selected->selfie_url }}" download class="text-xs text-gray-600 hover:underline">Unduh</a>
                                    </div>
                                @else
                                    <div class="w-full h-40 bg-gray-50 rounded flex items-center justify-center text-gray-400">Tidak ada</div>
                                @endif
                            </div>

                            <div>
                                <div class="text-sm text-gray-500 mb-2">Foto KTP</div>
                                @if(!empty($selected->ktp_url))
                                    <a href="{{ $selected->ktp_url }}" target="_blank" rel="noopener noreferrer" class="block rounded overflow-hidden ring-1 ring-black ring-opacity-5 hover:shadow-lg">
                                        <img src="{{ $selected->ktp_url }}" alt="KTP" class="w-full h-40 object-contain bg-white" />
                                    </a>
                                    <div class="mt-2 flex gap-2">
                                        <a href="{{ $selected->ktp_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Buka ukuran penuh</a>
                                        <a href="{{ $selected->ktp_url }}" download class="text-xs text-gray-600 hover:underline">Unduh</a>
                                    </div>
                                @else
                                    <div class="w-full h-40 bg-gray-50 rounded flex items-center justify-center text-gray-400">Tidak ada</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
