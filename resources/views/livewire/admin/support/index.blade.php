<div class="p-4 sm:p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>Chat Admin Wilayah</span>
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Layanan konsultasi langsung dan bantuan umum pelanggan serta mitra di wilayah kewenangan Anda.
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Menunggu Respon</div>
            <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $totalPending }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Sedang Diproses</div>
            <div class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalInProgress }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Selesai</div>
            <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalResolved }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Dari Customer / Mitra</div>
            <div class="mt-1 text-base font-bold text-gray-800 dark:text-gray-200">
                <span class="text-indigo-600 dark:text-indigo-400">{{ $totalFromCustomer }}</span> / <span class="text-violet-600 dark:text-violet-400">{{ $totalFromMitra }}</span>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
            {{-- Search Bar --}}
            <div class="relative flex-1 max-w-md">
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama pengirim atau isi pesan..."
                    class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Category Filter --}}
            <div class="flex items-center gap-2">
                <select wire:model.live="category"
                    class="py-2 px-3 text-sm bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Pengirim</option>
                    <option value="dari_customer">Customer</option>
                    <option value="dari_mitra">Mitra</option>
                </select>

                <select wire:model.live="status"
                    class="py-2 px-3 text-sm bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu Respon</option>
                    <option value="in_progress">Sedang Diproses</option>
                    <option value="resolved">Selesai</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Support Conversation Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-750 text-xs uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Pengirim</th>
                        <th class="py-3 px-4">Wilayah</th>
                        <th class="py-3 px-4">Topik & Pesan Terakhir</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-750/50 transition">
                            <td class="py-3.5 px-4 text-center text-xs text-gray-500 dark:text-gray-400">
                                {{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $rep->reporter?->name ?? 'Pengguna' }}
                                </div>
                                <div class="mt-0.5">
                                    @if($rep->category === 'dari_customer')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            Customer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
                                            Mitra
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="text-gray-900 dark:text-white font-medium">
                                    {{ $rep->reporter?->district?->name ?? 'Kecamatan Tidak Terdata' }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ $rep->reporter?->city?->name ?? $rep->reporter?->city ?? '' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-semibold text-xs text-gray-900 dark:text-white truncate">
                                    {{ $rep->title ?: 'Pusat Bantuan / Konsultasi' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                    {{ $rep->message ?: 'Tidak ada pesan' }}
                                </div>
                                @if($rep->messages_count > 0)
                                    <span class="text-[10px] text-primary-600 dark:text-primary-400 font-medium">
                                        {{ $rep->messages_count }} pesan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ $rep->updated_at->format('d M Y') }}</div>
                                <div class="text-[10px]">{{ $rep->updated_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rep->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        Menunggu Respon
                                    </span>
                                @elseif(in_array($rep->status, ['in_progress', 'investigating', 'under_review']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        Sedang Diproses
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route($routePrefix . 'support.chat', $rep->id) }}" wire:navigate
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>Buka Chat</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400 text-sm">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Tidak ada percakapan dukungan umum di wilayah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
