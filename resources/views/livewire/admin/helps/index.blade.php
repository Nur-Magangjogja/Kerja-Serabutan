<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Moderasi Bantuan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau dan kelola permintaan bantuan masuk di wilayah Anda</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.helps.approved') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Lihat Disetujui
            </a>
        </div>
    </div>

    {{-- ===== Stats Overview ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Bantuan</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($pendingHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Selesai</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($completedHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
        </div>
    </div>

    {{-- Alert Flash --}}
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center gap-3 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    {{-- ===== Filter & Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="w-full md:w-80">
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari judul atau deskripsi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="statusFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="active">Aktif / Disetujui</option>
                    <option value="completed">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>

                <select wire:model.live="perPage"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3">#ID</th>
                        <th class="px-4 py-3">Bantuan</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3 hidden md:table-cell">Kota</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($helps as $help)
                        @php
                        $stClass = match($help->status) {
                            'active', 'disetujui'   => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            'pending', 'menunggu'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            'completed', 'selesai'  => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            'rejected', 'ditolak'   => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                            default                 => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-400 dark:text-gray-500">#{{ $help->id }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $help->title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($help->description, 50) }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $help->customer->name ?? $help->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ $help->customer->phone ?? $help->user->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                {{ $help->city->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white text-right whitespace-nowrap">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">
                                    {{ ucfirst($help->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 hidden lg:table-cell whitespace-nowrap">
                                {{ $help->created_at?->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                                @if($help->status === 'pending' || $help->status === 'menunggu')
                                    <button wire:click="approveHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition">
                                        Setujui
                                    </button>
                                    <button wire:click="rejectHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                                        Tolak
                                    </button>
                                @else
                                    <button wire:click="viewHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada data bantuan yang sesuai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($helps->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                {{ $helps->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Help Detail & Activity History Modal for Admin ===== --}}
    @if($showDetailModal && $selectedHelp)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 max-h-[90vh] flex flex-col animate-scale-in"
                 @click.stop>
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-primary-600 to-blue-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-sm">
                            #{{ $selectedHelp->id }}
                        </div>
                        <div>
                            <h3 class="font-bold text-base leading-tight">{{ $selectedHelp->title }}</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Riwayat Aktivitas & Bukti Pengerjaan Lengkap</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" class="p-1.5 rounded-lg hover:bg-white/20 transition">
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
                            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-400 block uppercase">Customer</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100 mt-0.5 block">{{ $selectedHelp->customer->name ?? $selectedHelp->user->name ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $selectedHelp->customer->phone ?? $selectedHelp->user->phone ?? '-' }}</span>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-400 block uppercase">Mitra Pelaksana</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $selectedHelp->mitra->name ?? 'Belum Diambil' }}</span>
                            <span class="text-xs text-gray-500">{{ $selectedHelp->mitra->phone ?? '-' }}</span>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-400 block uppercase">Nominal & Status</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-0.5 block">Rp {{ number_format($selectedHelp->amount ?? 0, 0, ',', '.') }}</span>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                                {{ $selectedHelp->status }}
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

                    {{-- Activity Journey Timeline (Dari Awal sampai Akhir) --}}
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
                    <button wire:click="closeDetailModal" class="px-4 py-2 text-xs font-semibold bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
