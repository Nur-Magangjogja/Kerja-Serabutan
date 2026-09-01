<div wire:poll.20s>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Verifikasi KTP Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau dan verifikasi dokumen identitas pendaftar (Customer & Mitra)</p>
        </div>
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 rounded-lg">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            Memproses...
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm shadow-xs">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama, email, NIK, kota..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Role Filter --}}
            <select wire:model.live="roleFilter"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Peran</option>
                <option value="customer">Customer</option>
                <option value="mitra">Mitra</option>
            </select>

            {{-- City Filter (Super Admin only) / City Badge (City Admin) --}}
            @if($authUser && in_array($authUser->role, ['super_admin', 'superadmin']))
                <select wire:model.live="cityFilter"
                    class="py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 max-w-[180px]">
                    <option value="all">Semua Kota</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                    <option value="unassigned">Kota Belum Terdaftar</option>
                </select>
            @elseif($authUser && $authUser->city_id)
                <div class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-600">
                    <svg class="w-3.5 h-3.5 text-primary-600 dark:text-sky-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Wilayah: {{ $authUser->city_name ?? 'Kota Terdaftar' }}</span>
                </div>
            @endif

            {{-- Status Filter --}}
            <select wire:model.live="statusFilter"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu (Pending)</option>
                <option value="approved">Terverifikasi</option>
                <option value="rejected">Ditolak</option>
            </select>

            {{-- Per Page --}}
            <select wire:model.live="perPage"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50/50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / hal</option>
                <option value="25">25 / hal</option>
                <option value="50">50 / hal</option>
                <option value="100">100 / hal</option>
            </select>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Peran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Kota / Wilayah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Dokumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Waktu Daftar</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($verifications as $v)
                    @php
                    $isApproved = ($v->status === 'approved');
                    $isRejected = ($v->status === 'rejected');
                    $isPending = !$isApproved && !$isRejected;
                    
                    $roleLabel = ($v->role === 'mitra') ? 'Mitra' : 'Customer';
                    $roleBadge = ($v->role === 'mitra') 
                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60' 
                        : 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60';
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $v->id }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-xs">
                                    {{ strtoupper(substr($v->full_name ?? ($v->name ?? 'U'), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate text-xs sm:text-sm">{{ $v->full_name ?? ($v->name ?? '—') }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $v->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold {{ $roleBadge }}">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 font-mono text-xs hidden md:table-cell">{{ $v->nik ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 text-xs hidden lg:table-cell">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ $v->city ?? 'Belum terdata' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 hidden sm:table-cell">
                            <div class="flex items-center gap-1.5">
                                @if(!empty($v->selfie_url))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">Selfie</span>
                                @endif
                                @if(!empty($v->ktp_url))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">KTP</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($isApproved)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">Terverifikasi</span>
                            @elseif($isRejected)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80">Ditolak</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 hidden xl:table-cell whitespace-nowrap">
                            {{ optional($v->created_at)->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <button type="button" wire:click="viewKtp({{ $v->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 border border-primary-200/60 dark:border-primary-800/60 rounded-xl transition-all shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-750 flex items-center justify-center mb-3 text-gray-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak ada data verifikasi</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba sesuaikan filter pencarian atau pilih opsi "Semua Kota"</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750/30">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>

    {{-- ===== Detail Modal ===== --}}
    @if($showModal && $selected)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true" wire:click="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-4xl shadow-2xl my-auto max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 dark:border-gray-700" wire:click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800">
                <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
                    <div class="w-11 h-11 rounded-2xl bg-primary-600 flex items-center justify-center text-white font-bold text-base shadow-sm flex-shrink-0">
                        {{ strtoupper(substr($selected->full_name ?? ($selected->name ?? 'U'), 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white truncate max-w-xs">{{ $selected->full_name ?? ($selected->name ?? '—') }}</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold {{ ($selected->role === 'mitra') ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300' }}">
                                {{ ucfirst($selected->role ?? 'customer') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $selected->email ?? '—' }} • ID Registrasi: #{{ $selected->id }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if(($selected->status ?? '') === 'approved')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Terverifikasi</span>
                    @elseif(($selected->status ?? '') === 'rejected')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Ditolak</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Menunggu</span>
                    @endif
                    <button type="button" wire:click="closeModal" class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-5">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    {{-- Info Pribadi Section --}}
                    <div class="lg:col-span-2 space-y-4 min-w-0">
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Informasi Identitas KTP</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div class="min-w-0"><p class="text-[11px] text-gray-400 dark:text-gray-500">NIK (Nomor Induk Kependudukan)</p><p class="text-sm font-mono font-bold text-gray-900 dark:text-gray-100 mt-0.5 break-all">{{ $selected->nik ?? '—' }}</p></div>
                                <div class="min-w-0"><p class="text-[11px] text-gray-400 dark:text-gray-500">Nama Lengkap</p><p class="text-sm font-bold text-gray-900 dark:text-gray-100 mt-0.5 break-words">{{ $selected->full_name ?? '—' }}</p></div>
                                <div class="min-w-0"><p class="text-[11px] text-gray-400 dark:text-gray-500">No. HP / WhatsApp</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-0.5 break-words">{{ $selected->phone ?? '—' }}</p></div>
                                <div class="min-w-0"><p class="text-[11px] text-gray-400 dark:text-gray-500">Jenis Kelamin</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-0.5">{{ $selected->gender ?? '—' }}</p></div>
                            </div>
                        </div>

                        <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Alamat Lengkap KTP</h4>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-3 break-words">{{ $selected->full_address }}</p>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                                <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 min-w-0"><span class="text-gray-400 block text-[10px]">RT / RW</span> <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">RT {{ $selected->rt ? sprintf('%02d', (int)$selected->rt) : '-' }}/RW {{ $selected->rw ? sprintf('%02d', (int)$selected->rw) : '-' }}</span></div>
                                <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 min-w-0"><span class="text-gray-400 block text-[10px]">Kelurahan</span> <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $selected->kelurahan ?? '-' }}</span></div>
                                <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 min-w-0"><span class="text-gray-400 block text-[10px]">Kecamatan</span> <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $selected->kecamatan ?? '-' }}</span></div>
                                <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 min-w-0"><span class="text-gray-400 block text-[10px]">Kota / Kab</span> <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $selected->city ?? '-' }}</span></div>
                                <div class="bg-white dark:bg-gray-700 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600 min-w-0"><span class="text-gray-400 block text-[10px]">Provinsi</span> <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $selected->province ?? '-' }}</span></div>
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen Section --}}
                    <div class="space-y-4 min-w-0">
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Foto Selfie Verifikasi</h4>
                            @if(!empty($selected->selfie_url))
                                <a href="{{ $selected->selfie_url }}" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 group shadow-xs aspect-4/3 bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ $selected->selfie_url }}" alt="Selfie" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                </a>
                                <div class="mt-2.5 flex items-center justify-between text-xs">
                                    <a href="{{ $selected->selfie_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Buka foto asli</a>
                                    <a href="{{ $selected->selfie_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline">Unduh berkas</a>
                                </div>
                            @else
                                <div class="w-full aspect-4/3 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center text-xs text-gray-400">Tidak ada foto selfie</div>
                            @endif
                        </div>

                        <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Foto KTP Fisik</h4>
                            @if(!empty($selected->ktp_url))
                                <a href="{{ $selected->ktp_url }}" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 group bg-white dark:bg-gray-900 shadow-xs aspect-video">
                                    <img src="{{ $selected->ktp_url }}" alt="KTP" class="w-full h-full object-contain group-hover:scale-105 transition duration-300 p-1" />
                                </a>
                                <div class="mt-2.5 flex items-center justify-between text-xs">
                                    <a href="{{ $selected->ktp_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Buka foto asli</a>
                                    <a href="{{ $selected->ktp_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline">Unduh berkas</a>
                                </div>
                            @else
                                <div class="w-full aspect-video bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center text-xs text-gray-400">Tidak ada foto KTP</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Reject form inline inside modal if triggered --}}
                @if($showRejectModal)
                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 space-y-3">
                    <h4 class="text-sm font-bold text-rose-800 dark:text-rose-300">Alasan Penolakan Registrasi</h4>
                    <textarea wire:model="rejectReason" maxlength="500" rows="3" placeholder="Contoh: Foto KTP buram / NIK tidak sesuai / Foto selfie tidak jelas..."
                        class="w-full px-3 py-2 text-xs sm:text-sm border border-rose-200 dark:border-rose-700 rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    @error('rejectReason') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancelReject" class="px-3.5 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">Batal</button>
                        <button type="button" wire:click="confirmReject" class="px-3.5 py-1.5 text-xs font-bold bg-rose-600 text-white rounded-xl hover:bg-rose-700 shadow-xs cursor-pointer">Konfirmasi Tolak</button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-750/30 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
                <span class="text-xs text-gray-400 dark:text-gray-500 text-center sm:text-left">Pastikan data sesuai dengan dokumen KTP sebelum menyetujui</span>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        Tutup
                    </button>
                    @if(($selected->status ?? '') !== 'approved')
                        <button type="button" wire:click="openRejectModal({{ $selected->id }})" class="px-4 py-2 text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            Tolak
                        </button>
                        <button type="button" wire:click="approveKtp({{ $selected->id }})" class="px-4 py-2 text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            Setujui & Verifikasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
