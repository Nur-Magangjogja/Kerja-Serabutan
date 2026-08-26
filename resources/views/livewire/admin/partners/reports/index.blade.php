<div class="space-y-5">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
    @endphp

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Aduan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola aduan masalah, investigasi sengketa, dan persetujuan refund secara real-time</p>
        </div>
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($reports->total()) }} Laporan
        </span>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-amber-400 transition" wire:click="$set('status', 'pending')">
            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
            <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $totalPending }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-blue-400 transition" wire:click="$set('status', 'in_progress')">
            <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
            <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $totalInProgress }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-emerald-400 transition" wire:click="$set('status', 'resolved')">
            <p class="text-xs text-gray-500 dark:text-gray-400">Resolved</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalResolved }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-rose-400 transition" wire:click="$set('refundStatus', 'requested')">
            <p class="text-xs text-rose-600 dark:text-rose-400 font-bold">🛡️ Pengajuan Refund</p>
            <p class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $totalRefundRequested }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-primary-400 transition" wire:click="$set('category', 'dari_customer')">
            <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
            <p class="text-xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $totalFromCustomer }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 cursor-pointer hover:border-violet-400 transition" wire:click="$set('category', 'dari_mitra')">
            <p class="text-xs text-gray-500 dark:text-gray-400">Mitra</p>
            <p class="text-xl font-bold text-violet-600 dark:text-violet-400 mt-1">{{ $totalFromMitra }}</p>
        </div>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Laporan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama reporter, judul, kata kunci..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status Aduan</label>
                <select wire:model.live="status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status Refund</label>
                <select wire:model.live="refundStatus"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Refund</option>
                    <option value="requested">🛡️ Butuh Refund</option>
                    <option value="approved">✅ Refund Disetujui</option>
                    <option value="rejected">❌ Refund Ditolak</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Kategori Pelapor</label>
                <select wire:model.live="category"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Pelapor</option>
                    <option value="dari_customer">Dari Customer</option>
                    <option value="dari_mitra">Dari Mitra</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($reports->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    📋
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Laporan Aduan</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Semua laporan aduan telah selesai ditangani atau tidak ada laporan yang sesuai dengan filter pencarian.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">ID / Pelapor</th>
                            <th class="px-4 py-3">Terlapor / Tugas</th>
                            <th class="px-4 py-3">Judul & Masalah</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Status Refund</th>
                            <th class="px-4 py-3">Chat</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach ($reports as $report)
                            @php
                                $reporter = $report->reporter ?? $report->user;
                                $reported = $report->reportedUser;
                                $help = $report->reportedHelp;
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900 dark:text-white text-xs">#{{ $report->id }}</span>
                                    <div class="mt-1">
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $reporter?->name ?? 'User Tidak Diketahui' }}</p>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $reporter?->role === 'mitra' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $reporter?->role === 'mitra' ? 'Mitra' : 'Customer' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($reported)
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $reported->name }}</p>
                                        <span class="text-[10px] text-gray-400">{{ $reported->role === 'mitra' ? 'Mitra' : 'Customer' }}</span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                    @if ($help)
                                        <p class="text-[10px] text-gray-400 mt-0.5 line-clamp-1 max-w-[140px]" title="{{ $help->title }}">
                                            Ref: {{ $help->title }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 max-w-xs">
                                    <p class="font-bold text-gray-900 dark:text-white line-clamp-1">{{ $report->title }}</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-[11px] line-clamp-2 mt-0.5">{{ $report->message }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($report->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300">
                                            Pending
                                        </span>
                                    @elseif ($report->status === 'in_progress')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300">
                                            In Progress
                                        </span>
                                    @elseif ($report->status === 'resolved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($report->refund_status === 'requested')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 animate-pulse">
                                            🛡️ Butuh Refund
                                        </span>
                                    @elseif ($report->refund_status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            ✅ Refund Selesai
                                        </span>
                                    @elseif ($report->refund_status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            ❌ Ditolak
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <a href="{{ route($routePrefix . 'partners.reports.chat', $report->id) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition">
                                        <span>💬</span>
                                        <span>{{ $report->messages_count }}</span>
                                    </a>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-400 text-[11px]">
                                    {{ $report->created_at->format('d M Y • H:i') }}
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route($routePrefix . 'partners.reports.show', $report->id) }}"
                                        class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                        Investigasi & Detail →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
