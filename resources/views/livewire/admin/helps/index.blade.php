<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Moderasi Bantuan</h1>
                @if(isset($managedCities) && $managedCities->count() > 1)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Wilayah: {{ auth()->user()->admin_city_names }}
                    </span>
                @elseif(auth()->user() && (auth()->user()->city_name || auth()->user()->city_id || auth()->user()->city))
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Wilayah: {{ auth()->user()->city_name ?? (is_object(auth()->user()->city) ? auth()->user()->city->name : auth()->user()->city) }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau, pantau, dan kelola seluruh permintaan bantuan di wilayah wewenang Anda</p>
        </div>
    </div>

    {{-- ===== Stats Overview Cards (Interactive Status Filters) ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        {{-- Total Bantuan --}}
        <div wire:click="filterByStatus('all')"
            class="bg-white dark:bg-gray-800 rounded-2xl border {{ $statusFilter === '' ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-3.5 sm:p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 truncate">Total Bantuan</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-0.5 truncate">{{ number_format($totalHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
        </div>

        {{-- Menunggu / Pending --}}
        <div wire:click="filterByStatus('pending')"
            class="bg-white dark:bg-gray-800 rounded-2xl border {{ $statusFilter === 'pending' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-3.5 sm:p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 truncate">Menunggu</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5 truncate">{{ number_format($pendingHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        {{-- Sedang Berjalan / Aktif --}}
        <div wire:click="filterByStatus('active')"
            class="bg-white dark:bg-gray-800 rounded-2xl border {{ $statusFilter === 'active' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-3.5 sm:p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 truncate">Sedang Aktif</p>
                <p class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5 truncate">{{ number_format($activeHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>

        {{-- Selesai --}}
        <div wire:click="filterByStatus('completed')"
            class="bg-white dark:bg-gray-800 rounded-2xl border {{ $statusFilter === 'completed' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-3.5 sm:p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 truncate">Selesai</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 truncate">{{ number_format($completedHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
        </div>

        {{-- Dibatalkan --}}
        <div wire:click="filterByStatus('cancelled')"
            class="bg-white dark:bg-gray-800 rounded-2xl border {{ $statusFilter === 'cancelled' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-3.5 sm:p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition col-span-2 sm:col-span-1">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 truncate">Dibatalkan</p>
                <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-0.5 truncate">{{ number_format($cancelledHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
        </div>
    </div>

    {{-- Alert Flash --}}
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center gap-3 text-sm shadow-xs">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span class="font-bold">{{ session('message') }}</span>
        </div>
    @endif

    {{-- ===== Search & Filters Bar ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        {{-- Search & City Filter & Per Page --}}
        <div class="flex items-center gap-2.5 w-full justify-between flex-wrap">
            <div class="flex items-center gap-2.5 flex-1 min-w-[280px]">
                <div class="relative w-full max-w-md">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari judul, order ID, pemohon..."
                        class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                @if(isset($managedCities) && $managedCities->count() > 1)
                    <select wire:model.live="cityFilter"
                        class="py-2 pl-3 pr-8 text-xs font-bold border border-primary-200 dark:border-primary-800 rounded-xl bg-primary-50/50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer shadow-2xs">
                        <option value="all">Semua Wilayah Saya ({{ $managedCities->count() }} Kota)</option>
                        @foreach($managedCities as $mc)
                            <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline">Tampilkan:</span>
                <select wire:model.live="perPage"
                    class="py-2 pl-3 pr-8 text-xs font-semibold border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 hidden">#ID</th>
                        <th class="px-4 py-3">Permohonan Bantuan</th>
                        <th class="px-4 py-3">Customer / Pemohon</th>
                        <th class="px-4 py-3 hidden md:table-cell">Mitra Pelaksana</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Kota</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 hidden xl:table-cell">Waktu</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($helps as $help)
                        @php
                        $stClass = match($help->status) {
                            'completed', 'selesai'                                          => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
                            'pending', 'menunggu', 'menunggu_mitra'                         => 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
                            'active', 'disetujui', 'taken', 'in_progress', 'sedang_diproses', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60',
                            'rejected', 'ditolak', 'dibatalkan', 'cancelled'               => 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60',
                            default                                                         => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        $statusLabel = match($help->status) {
                            'completed', 'selesai'                                          => 'Selesai',
                            'pending', 'menunggu', 'menunggu_mitra'                         => 'Menunggu Mitra',
                            'active', 'disetujui'                                           => 'Aktif',
                            'taken', 'sedang_diproses', 'in_progress'                       => 'Sedang Dikerjakan',
                            'partner_on_the_way'                                            => 'Mitra Menuju Lokasi',
                            'partner_arrived'                                               => 'Mitra Tiba',
                            'waiting_customer_confirmation'                                 => 'Menunggu Konfirmasi',
                            'rejected', 'ditolak'                                           => 'Ditolak',
                            'dibatalkan', 'cancelled'                                       => 'Dibatalkan',
                            default                                                         => ucfirst(str_replace('_', ' ', $help->status))
                        };
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-gray-400 dark:text-gray-500 hidden">
                                #{{ $help->order_id ?? $help->id }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $help->title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs mt-0.5">{{ Str::limit($help->description, 55) }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $help->customer->name ?? $help->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ $help->customer->phone ?? $help->user->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                @if($help->mitra)
                                    <div class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $help->mitra->name }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ $help->mitra->phone ?? '-' }}</div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">Belum diambil mitra</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300 hidden lg:table-cell">
                                {{ $help->city->name ?? ($help->customer?->city_name ?? '-') }}
                            </td>
                            <td class="px-4 py-3.5 font-black text-gray-900 dark:text-white text-right whitespace-nowrap">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $stClass }}">
                                    @if(in_array($help->status, ['pending', 'menunggu', 'menunggu_mitra']))
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 hidden xl:table-cell whitespace-nowrap">
                                {{ $help->created_at?->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-1">
                                <button type="button" wire:click="viewHelp({{ $help->id }})" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-primary-50 dark:hover:bg-primary-950/50 hover:text-primary-600 dark:hover:text-primary-400 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition cursor-pointer border border-transparent hover:border-primary-200 dark:hover:border-primary-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700/60 flex items-center justify-center mb-3 text-gray-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Tidak ada data bantuan</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tidak ditemukan permintaan bantuan pada filter status atau pencarian ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            {{ $helps->links('vendor.pagination.superadmin') }}
        </div>
    </div>

    {{-- ===== Help Detail & Activity History Modal for Admin ===== --}}
    @if($showDetailModal && $selectedHelp)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" wire:click="closeDetailModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 max-h-[90vh] flex flex-col animate-scale-in"
                 @click.stop>
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-primary-600 to-blue-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-black text-sm">
                            #{{ $selectedHelp->order_id ?? $selectedHelp->id }}
                        </div>
                        <div>
                            <h3 class="font-bold text-base leading-tight">{{ $selectedHelp->title }}</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Detail Bantuan, Jejak Aktivitas & Bukti Pengerjaan</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-1.5 rounded-lg hover:bg-white/20 transition cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="p-6 overflow-y-auto space-y-6 text-sm">
                    {{-- Summary Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-400 block uppercase">Customer / Pemohon</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-0.5 block">{{ $selectedHelp->customer->name ?? $selectedHelp->user->name ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $selectedHelp->customer->phone ?? $selectedHelp->user->phone ?? '-' }}</span>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-400 block uppercase">Mitra Pelaksana</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $selectedHelp->mitra->name ?? 'Belum Diambil' }}</span>
                            <span class="text-xs text-gray-500">{{ $selectedHelp->mitra->phone ?? '-' }}</span>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-400 block uppercase">Nominal & Status</span>
                            <span class="font-black text-gray-900 dark:text-white mt-0.5 block">Rp {{ number_format($selectedHelp->amount ?? 0, 0, ',', '.') }}</span>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                                {{ ucfirst(str_replace('_', ' ', $selectedHelp->status)) }}
                            </span>
                        </div>
                    </div>

                    {{-- Description & Address --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Deskripsi Permohonan:</span>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $selectedHelp->description }}</p>
                        @if($selectedHelp->full_address || $selectedHelp->location)
                            <div class="mt-2.5 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 text-xs text-gray-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $selectedHelp->full_address ?? $selectedHelp->location }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Photos Comparison (Initial vs Proof) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Initial Photo --}}
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200 block mb-2">📷 Foto Awal dari Customer:</span>
                            @if($selectedHelp->photo)
                                <a href="{{ asset('storage/' . $selectedHelp->photo) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/' . $selectedHelp->photo) }}" alt="Foto Awal" class="w-full h-44 object-cover rounded-xl border border-gray-200 dark:border-gray-600 hover:opacity-95 transition">
                                </a>
                            @else
                                <div class="w-full h-44 rounded-xl bg-gray-100 dark:bg-gray-700 flex flex-col items-center justify-center text-gray-400 text-xs">
                                    <svg class="w-8 h-8 mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Tidak ada foto awal
                                </div>
                            @endif
                        </div>

                        {{-- Proof Photo from Mitra --}}
                        <div class="p-3.5 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/40">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block mb-2">📸 Foto Bukti Pengerjaan dari Mitra:</span>
                            @if($selectedHelp->proof_photo)
                                <a href="{{ asset('storage/' . $selectedHelp->proof_photo) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/' . $selectedHelp->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full h-44 object-cover rounded-xl border border-emerald-200 dark:border-emerald-700 hover:opacity-95 transition shadow-xs">
                                </a>
                                @if($selectedHelp->completion_notes)
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic">"{{ $selectedHelp->completion_notes }}"</p>
                                @endif
                            @else
                                <div class="w-full h-44 rounded-xl bg-emerald-100/40 dark:bg-emerald-900/20 flex flex-col items-center justify-center text-emerald-600/70 text-xs">
                                    <svg class="w-8 h-8 mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Menunggu pengerjaan / upload bukti dari mitra
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Activity Journey Timeline --}}
                    <div>
                        <h4 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Kronologi & Jejak Aktivitas (Activity Timeline)
                        </h4>

                        @if($helpActivities && $helpActivities->count() > 0)
                            <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
                                @foreach($helpActivities as $act)
                                    <div class="relative flex items-start gap-3">
                                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                        <div class="flex-1 bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                                    {{ $act->user->name ?? 'Sistem' }} ({{ ucfirst($act->user->role ?? 'user') }})
                                                </span>
                                                <span class="text-[10px] text-gray-400">{{ $act->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $act->description }}</p>
                                            @if($act->photo)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $act->photo) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary-600 hover:text-primary-700">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        Lihat Foto Bukti Terlampir
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl text-center text-xs text-gray-500">
                                Belum ada rekaman audit aktivitas khusus untuk bantuan ini.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="button" wire:click="closeDetailModal" class="px-4 py-2 text-xs font-bold bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
