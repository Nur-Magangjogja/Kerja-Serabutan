<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Moderasi Bantuan</h1>
                
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau, pantau, dan kelola seluruh permintaan bantuan di wilayah kecamatan wewenang Anda</p>
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
        {{-- Search & District Filter & Per Page --}}
        <div class="flex items-center gap-2.5 w-full justify-between flex-wrap">
            <div class="flex items-center gap-2.5 flex-1 min-w-[280px]">
                <div class="relative w-full max-w-md">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari judul, pesanan, pemohon..."
                        class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

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
                        <th class="px-4 py-3">Permohonan Bantuan</th>
                        <th class="px-4 py-3">Customer / Pemohon</th>
                        <th class="px-4 py-3 hidden md:table-cell">Mitra Pelaksana</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Kecamatan / Wilayah</th>
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
                            'selesai'                                                       => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
                            'menunggu_mitra'                                                => 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
                            'taken', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60',
                            'dibatalkan', 'partner_cancel_requested', 'customer_cancel_requested' => 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60',
                            default                                                         => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'
                        };
                        $statusLabel = match($help->status) {
                            'menunggu_mitra'                                                => 'Menunggu Mitra',
                            'taken'                                                         => 'Diambil Mitra',
                            'in_progress'                                                   => 'Sedang Dikerjakan',
                            'selesai'                                                       => 'Selesai',
                            'partner_on_the_way'                                            => 'Mitra Menuju Lokasi',
                            'partner_arrived'                                               => 'Mitra Tiba',
                            'waiting_customer_confirmation'                                 => 'Menunggu Konfirmasi',
                            'dibatalkan'                                                    => 'Dibatalkan',
                            'partner_cancel_requested'                                      => 'Pengajuan Batal (Mitra)',
                            'customer_cancel_requested'                                     => 'Pengajuan Batal (Customer)',
                            default                                                         => ucfirst(str_replace('_', ' ', $help->status ?? ''))
                        };
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors duration-150">
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
                                {{ $help->district ? 'Kec. ' . $help->district->name : ($help->customer?->district ? 'Kec. ' . $help->customer->district->name : ($help->city->name ?? ($help->customer?->city_name ?? '-'))) }}
                            </td>
                            <td class="px-4 py-3.5 font-black text-gray-900 dark:text-white text-right whitespace-nowrap">
                                Rp {{ number_format($help->total_amount > 0 ? $help->total_amount : $help->amount, 0, ',', '.') }}
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
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-2 text-gray-300 dark:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                <p class="text-xs font-bold">Tidak ada data permohonan bantuan</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau filter status Anda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($helps->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                {{ $helps->links('vendor.pagination.superadmin') }}
            </div>
        @endif
    </div>

    {{-- ===== Detail Modal (Comprehensive Overview & Activity Timeline) ===== --}}
    @if($showDetailModal && $selectedHelp)
        @php
            $isCancelled = in_array(strtolower($selectedHelp->status), ['batal', 'dibatalkan', 'cancelled']);
            $isCompleted = in_array(strtolower($selectedHelp->status), ['selesai', 'completed']);
            $isPending = in_array(strtolower($selectedHelp->status), ['menunggu_mitra', 'pending']);
            $isInProgress = in_array(strtolower($selectedHelp->status), ['dalam_pengerjaan', 'diambil', 'on_progress', 'otw']);
            
            $cancelReason = $selectedHelp->dispute_reason 
                ?? optional($selectedHelp->cancelRequest)->reason 
                ?? optional($selectedHelp->latestCancelRequest)->reason 
                ?? null;

            $totalAmount = $selectedHelp->total_amount > 0 ? $selectedHelp->total_amount : $selectedHelp->amount;
        @endphp

        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
            role="dialog" aria-modal="true">
            
            {{-- Full-Screen Backdrop Overlay (Attached directly to <body> covering 100% viewport) --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                 wire:click="closeDetailModal" 
                 aria-hidden="true"></div>

            {{-- Modal Dialog Panel --}}
            <div class="relative bg-white dark:bg-gray-850 rounded-3xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-200">
                
                {{-- Header (Solid Clean & Professional, No Gradient, No Red Background) --}}
                <div class="px-6 py-5 bg-white dark:bg-gray-800 border-b border-gray-200/80 dark:border-gray-700 flex items-start justify-between gap-4 shrink-0">
                    <div class="space-y-1.5 flex-1 min-w-0">
                        {{-- Top Row: Status Badges --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($isCancelled)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Dibatalkan &bull; Refund 100%
                                </span>
                            @elseif($isCompleted)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai Terverifikasi
                                </span>
                            @elseif($isInProgress)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Dalam Pengerjaan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Mitra
                                </span>
                            @endif

                            @if($selectedHelp->service_type)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                    {{ ucwords(str_replace('_', ' ', $selectedHelp->service_type)) }}
                                </span>
                            @endif
                        </div>

                        {{-- Main Title --}}
                        <h3 class="font-extrabold text-lg sm:text-xl text-gray-900 dark:text-white tracking-tight leading-snug break-words">
                            {{ $selectedHelp->title }}
                        </h3>

                        {{-- Subtitle --}}
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1.5 pt-0.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Detail Bantuan, Jejak Aktivitas &amp; Bukti Pengerjaan
                        </p>
                    </div>

                    {{-- Close Button --}}
                    <button type="button" wire:click="closeDetailModal" title="Tutup Modal"
                        class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition cursor-pointer shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="p-5 sm:p-6 overflow-y-auto dropdown-scrollbar space-y-5 text-sm">
                    
                    {{-- Alert Banner: Khusus Pembatalan & Refund (Audit Log) --}}
                    @if($isCancelled || !empty($cancelReason))
                    <div class="p-4 bg-rose-50/90 dark:bg-rose-950/30 border border-rose-200/90 dark:border-rose-800/60 rounded-2xl space-y-2.5 shadow-2xs">
                        <div class="flex items-center gap-2 text-rose-800 dark:text-rose-200 font-bold text-xs uppercase tracking-wide">
                            <div class="w-6 h-6 rounded-lg bg-rose-100 dark:bg-rose-900/60 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <span>Informasi Audit Pembatalan &amp; Pengembalian Dana</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                            <div class="bg-white/80 dark:bg-gray-800/80 p-3 rounded-xl border border-rose-100 dark:border-rose-900/40">
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase block">Alasan Pembatalan:</span>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-100 mt-1 leading-relaxed">
                                    {{ $cancelReason ?: 'Dibatalkan oleh customer sebelum pelaksanaan tugas.' }}
                                </p>
                            </div>

                            <div class="bg-white/80 dark:bg-gray-800/80 p-3 rounded-xl border border-rose-100 dark:border-rose-900/40 space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase block">Status Pengembalian Dana Tahan:</span>
                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    100% Refund (Rp {{ number_format($totalAmount, 0, ',', '.') }})
                                </p>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block">Dana dikembalikan otomatis ke saldo dompet customer.</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Parties Cards Grid (Customer & Mitra) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {{-- Customer Card --}}
                        <div class="p-4 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-2 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1">
                                    <span>👤</span> Customer / Pemohon
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                    Pemohon
                                </span>
                            </div>
                            <div class="flex items-center gap-3 pt-1">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($selectedHelp->customer->name ?? $selectedHelp->user->name ?? 'C', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                        {{ $selectedHelp->customer->name ?? $selectedHelp->user->name ?? 'Customer' }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $selectedHelp->customer->email ?? $selectedHelp->user->email ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">WhatsApp / HP:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $selectedHelp->customer->phone ?? $selectedHelp->user->phone ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Mitra Card --}}
                        <div class="p-4 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-2 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1">
                                    <span>🛵</span> Mitra Pelaksana
                                </span>
                                @if($selectedHelp->mitra)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                        Ditugaskan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        Belum Ada Mitra
                                    </span>
                                @endif
                            </div>

                            @if($selectedHelp->mitra)
                            <div class="flex items-center gap-3 pt-1">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($selectedHelp->mitra->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                        {{ $selectedHelp->mitra->name }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $selectedHelp->mitra->email }}
                                    </p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">WhatsApp / HP:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $selectedHelp->mitra->phone ?? '—' }}
                                </span>
                            </div>
                            @else
                            <div class="flex items-center gap-3 pt-1">
                                <div class="w-10 h-10 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                        Tanpa Mitra Pelaksana
                                    </h4>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                                        {{ $isCancelled ? 'Pesanan dibatalkan sebelum mitra mengambil pekerjaan ini.' : 'Menunggu mitra mengambil atau menerima tugas.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-gray-200/60 dark:border-gray-700/60 text-[11px] text-gray-400 italic">
                                Tidak ada data kontak mitra
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Financial Breakdown Card --}}
                    <div class="p-4 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-3 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200/70 dark:border-gray-700/70">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Rincian Finansial &amp; Pembayaran
                            </span>
                            <span class="text-xs font-extrabold text-primary-700 dark:text-primary-300">
                                Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                <span class="text-[10px] text-gray-400 block font-medium">Biaya Jasa</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($selectedHelp->service_fee ?: $selectedHelp->amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                <span class="text-[10px] text-gray-400 block font-medium">Titipan Belanja</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">
                                    Rp {{ number_format($selectedHelp->item_fund ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                <span class="text-[10px] text-gray-400 block font-medium">Biaya Layanan Platform</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($selectedHelp->platform_fee_amount ?? 2000, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                <span class="text-[10px] text-gray-400 block font-medium">Status Dana Tahan</span>
                                <span class="font-bold {{ $isCancelled ? 'text-rose-600 dark:text-rose-400' : 'text-primary-600 dark:text-primary-400' }}">
                                    {{ $isCancelled ? 'Refunded' : ucfirst($selectedHelp->escrow_status ?? 'Secured') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Description & Address --}}
                    <div class="p-4 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-2 shadow-2xs">
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block uppercase tracking-wider">Deskripsi Pekerjaan:</span>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                            {{ $selectedHelp->description ?: 'Tidak ada deskripsi rinci.' }}
                        </p>
                        @if($selectedHelp->full_address || $selectedHelp->location)
                            <div class="pt-2 text-xs text-gray-500 dark:text-gray-400 flex items-start gap-2">
                                <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div>
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Lokasi Penugasan: </span>
                                    <span>{{ $selectedHelp->full_address ?? $selectedHelp->location }}</span>
                                    @if($selectedHelp->district || $selectedHelp->city)
                                        <span class="text-[11px] text-gray-400 block mt-0.5 font-medium">
                                            (Kec. {{ optional($selectedHelp->district)->name ?? '-' }}, {{ optional($selectedHelp->city)->name ?? '-' }})
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Photos Comparison (Initial vs Proof) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Initial Photo --}}
                        <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-2 shadow-2xs">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block uppercase tracking-wider flex items-center gap-1.5">
                                <span>📷</span> Foto Awal dari Customer:
                            </span>
                            @if($selectedHelp->photo)
                                <a href="{{ asset('storage/' . $selectedHelp->photo) }}" target="_blank" rel="noopener" class="block group relative overflow-hidden rounded-xl">
                                    <img src="{{ asset('storage/' . $selectedHelp->photo) }}" alt="Foto Awal" class="w-full h-44 object-cover rounded-xl border border-gray-200 dark:border-gray-600 group-hover:scale-105 transition duration-200">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        Buka Foto Penuh
                                    </div>
                                </a>
                            @else
                                <div class="w-full h-44 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700 flex flex-col items-center justify-center text-gray-400 text-xs">
                                    <svg class="w-8 h-8 mb-1 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Tidak ada foto awal dilampirkan
                                </div>
                            @endif
                        </div>

                        {{-- Proof Photo from Mitra --}}
                        <div class="p-3.5 {{ $isCompleted ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40' : 'bg-gray-50/80 dark:bg-gray-750/40 border-gray-200/80 dark:border-gray-700/80' }} rounded-2xl border space-y-2 shadow-2xs">
                            <span class="text-xs font-bold {{ $isCompleted ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-800 dark:text-gray-200' }} block uppercase tracking-wider flex items-center gap-1.5">
                                <span>📸</span> Foto Bukti Pengerjaan Mitra:
                            </span>
                            @if($selectedHelp->proof_photo)
                                <a href="{{ asset('storage/' . $selectedHelp->proof_photo) }}" target="_blank" rel="noopener" class="block group relative overflow-hidden rounded-xl">
                                    <img src="{{ asset('storage/' . $selectedHelp->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full h-44 object-cover rounded-xl border border-emerald-200 dark:border-emerald-700 group-hover:scale-105 transition duration-200 shadow-xs">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        Buka Bukti Penuh
                                    </div>
                                </a>
                                @if($selectedHelp->completion_notes)
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700">"{{ $selectedHelp->completion_notes }}"</p>
                                @endif
                            @elseif($isCancelled)
                                <div class="w-full h-44 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700 flex flex-col items-center justify-center text-gray-400 text-xs text-center p-4">
                                    <svg class="w-8 h-8 mb-1.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="font-semibold text-gray-600 dark:text-gray-300">Tidak ada bukti pengerjaan</span>
                                    <span class="text-[11px] text-gray-400 mt-0.5">Bantuan dibatalkan sebelum pengerjaan oleh mitra.</span>
                                </div>
                            @else
                                <div class="w-full h-44 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700 flex flex-col items-center justify-center text-gray-400 text-xs text-center p-4">
                                    <svg class="w-8 h-8 mb-1 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Menunggu pengerjaan dan upload foto bukti oleh mitra
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Activity Journey Timeline --}}
                    <div class="p-4 bg-gray-50/80 dark:bg-gray-750/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-3 shadow-2xs">
                        <h4 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Kronologi &amp; Jejak Aktivitas (Activity Timeline)
                        </h4>

                        @if($helpActivities && $helpActivities->count() > 0)
                            <div class="relative pl-6 space-y-3.5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
                                @foreach($helpActivities as $act)
                                    <div class="relative flex items-start gap-3">
                                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-2xs">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                        <div class="flex-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                                    {{ $act->user->name ?? 'Sistem' }}
                                                    <span class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-1.5 py-0.2 rounded ml-1">
                                                        {{ ucfirst($act->user->role ?? 'Sistem') }}
                                                    </span>
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
                            {{-- Generated Lifecycle Chronology based on Help Record --}}
                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
                                {{-- Milestone 1: Created --}}
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-2xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <div class="flex-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-xs text-gray-800 dark:text-gray-200">Permintaan Bantuan Dibuat</span>
                                            <span class="text-[10px] text-gray-400">{{ optional($selectedHelp->created_at)->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300">Customer memposting permintaan ke sistem radar.</p>
                                    </div>
                                </div>

                                {{-- Milestone 2: Dana Tahan Secured --}}
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-2xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <div class="flex-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200/70 dark:border-gray-700 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                                Dana Diamankan di Sistem Dana Tahan
                                                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.2 rounded ml-1">Sistem</span>
                                            </span>
                                            <span class="text-[10px] text-gray-400">{{ optional($selectedHelp->created_at)->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300">
                                            Pembayaran sebesar Rp {{ number_format($totalAmount, 0, ',', '.') }} berhasil didebet dan ditampung aman di rekening penampung dana tahan SayaBantu.
                                        </p>
                                    </div>
                                </div>

                                {{-- Milestone 3: Terminal / Current State --}}
                                @if($isCancelled)
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-2xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <div class="flex-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-rose-200/80 dark:border-rose-800 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-xs text-rose-700 dark:text-rose-300">
                                                Permohonan Dibatalkan &amp; Dana Dikembalikan
                                                <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.2 rounded ml-1">Audit</span>
                                            </span>
                                            <span class="text-[10px] text-gray-400">{{ optional($selectedHelp->updated_at)->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                            {{ $cancelReason ?: 'Pesanan dibatalkan. Seluruh dana sebesar Rp ' . number_format($totalAmount, 0, ',', '.') . ' dikembalikan utuh ke saldo dompet customer.' }}
                                        </p>
                                    </div>
                                </div>
                                @elseif($isCompleted)
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-gray-800 shadow-2xs">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <div class="flex-1 bg-white dark:bg-gray-800 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-xs text-emerald-700 dark:text-emerald-300">
                                                Bantuan Selesai Dikerjakan &amp; Dana Diteruskan
                                                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.2 rounded ml-1">Selesai</span>
                                            </span>
                                            <span class="text-[10px] text-gray-400">{{ optional($selectedHelp->updated_at)->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300">
                                            Pekerjaan berhasil diselesaikan dan dana jasa diteruskan ke saldo mitra pelaksana.
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50/90 dark:bg-gray-750/70 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div class="text-[11px] text-gray-400 dark:text-gray-500 hidden sm:block">
                        Dibuat: {{ optional($selectedHelp->created_at)->format('d M Y, H:i') }} &bull; Diperbarui: {{ optional($selectedHelp->updated_at)->format('d M Y, H:i') }}
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-5 py-2.5 text-xs font-bold bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl transition cursor-pointer shadow-2xs">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>
