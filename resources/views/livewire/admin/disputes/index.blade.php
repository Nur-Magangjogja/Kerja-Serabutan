<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>⚖️ Arbitrase & Audit Pembatalan</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-semibold border border-rose-200 dark:border-rose-800">
                    Audit Wilayah
                </span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pusat arbitrase sengketa saldo dana tahan dan audit manual klaim pembatalan berdasar kesaksian, bukti, serta penjatuhan SP.
            </p>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session()->has('message'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Tabs Switcher --}}
    <div class="flex items-center gap-2 mb-4 border-b border-gray-200 dark:border-gray-800">
        <button wire:click="setActiveTab('disputes')" 
                class="pb-3 px-4 font-bold text-xs transition border-b-2 flex items-center gap-2 cursor-pointer {{ $activeTab === 'disputes' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>⚖️ Mediasi Sengketa Dana Tahan</span>
            @if(($activeFrozenDisputesCount ?? 0) > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white shadow-xs">
                    {{ $activeFrozenDisputesCount > 99 ? '99+' : $activeFrozenDisputesCount }}
                </span>
            @endif
        </button>
        <button wire:click="setActiveTab('cancellations')" 
                class="pb-3 px-4 font-bold text-xs transition border-b-2 flex items-center gap-2 cursor-pointer {{ $activeTab === 'cancellations' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>🛑 Audit Pembatalan Mitra & Customer</span>
            @if(($pendingCancelsCount ?? 0) > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs">
                    {{ $pendingCancelsCount > 99 ? '99+' : $pendingCancelsCount }}
                </span>
            @endif
        </button>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-black rounded-2xl p-4 shadow-sm border border-gray-200 dark:border-gray-800 mb-6">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                @if($activeTab === 'disputes')
                    <button wire:click="$set('status', 'frozen')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'frozen' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Dibekukan (Aktif)
                    </button>
                    <button wire:click="$set('status', 'resolved')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'resolved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Terselesaikan
                    </button>
                    <button wire:click="$set('status', 'all')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Semua
                    </button>
                @else
                    {{-- Status Filter for Cancellations --}}
                    <button wire:click="$set('status', 'pending')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Menunggu Audit
                    </button>
                    <button wire:click="$set('status', 'approved')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Disetujui
                    </button>
                    <button wire:click="$set('status', 'rejected')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Ditolak
                    </button>
                    <button wire:click="$set('status', 'all')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-black border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                        Semua Status
                    </button>

                    <div class="h-5 w-px bg-gray-200 dark:bg-gray-800 hidden sm:block"></div>

                    {{-- Requester Type Filter --}}
                    <select wire:model.live="requesterTypeFilter" class="px-3 py-2 text-xs bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl text-gray-800 dark:text-gray-200 font-medium focus:ring-2 focus:ring-primary-500">
                        <option value="all">Semua Pihak Pengaju</option>
                        <option value="partner">Diajukan oleh Mitra</option>
                        <option value="customer">Diajukan oleh Customer</option>
                    </select>
                @endif
            </div>

            <div class="w-full lg:w-72">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari order / pihak terkait..." 
                       class="w-full px-3.5 py-2 text-xs bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-400">
            </div>
        </div>
    </div>

    @if($activeTab === 'disputes')
        {{-- Dispute List Table --}}
        <div class="bg-white dark:bg-black rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="bg-white dark:bg-black text-gray-700 dark:text-gray-200 uppercase font-semibold text-[11px] border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-4">Bantuan / Order</th>
                            <th class="p-4">Customer & Mitra</th>
                            <th class="p-4">Nominal Bruto</th>
                            <th class="p-4">Alasan Komplain</th>
                            <th class="p-4">Status Dana Tahan</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($disputes as $help)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                <td class="p-4">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $help->title }}</span>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $help->district ? 'Kec. ' . $help->district->name : ($help->city->name ?? '-') }} • {{ $help->disputed_at ? $help->disputed_at->translatedFormat('d M Y, H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">Cust: {{ $help->user->name ?? 'Customer' }}</div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Mitra: {{ $help->mitra->name ?? 'Belum ada' }}</div>
                                </td>
                                <td class="p-4 font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($help->total_amount > 0 ? $help->total_amount : $help->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-4 max-w-xs">
                                    <p class="text-xs text-rose-600 dark:text-rose-400 italic line-clamp-2">"{{ $help->dispute_reason ?? 'Tidak ada deskripsi' }}"</p>
                                </td>
                                <td class="p-4">
                                    @if($help->escrow_status === \App\Models\Help::ESCROW_STATUS_DISPUTED_FREEZE)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                             DIBEKUKAN
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_RELEASED)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            RELEASED
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_REFUNDED)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            REFUNDED (100%)
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_PARTIAL_REFUND)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                            PARTIAL SPLIT
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-black dark:text-gray-300 border border-gray-200 dark:border-gray-800">
                                            {{ strtoupper($help->escrow_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if($help->escrow_status === \App\Models\Help::ESCROW_STATUS_DISPUTED_FREEZE)
                                        <button wire:click="openResolveModal({{ $help->id }})" 
                                                class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                            Mediasi Sengketa
                                        </button>
                                    @else
                                        <span class="text-[11px] text-gray-400">
                                            Selesai oleh {{ $help->disputeResolvedBy->name ?? 'Admin' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-xs">
                                    Tidak ada data sengketa atau komplain yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($disputes, 'links'))
                <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    {{ $disputes->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    @else
        {{-- Cancellation Requests List Table --}}
        <div class="bg-white dark:bg-black rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="bg-white dark:bg-black text-gray-700 dark:text-gray-200 uppercase font-semibold text-[11px] border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-4">ID & Bantuan</th>
                            <th class="p-4">Pengaju & Pihak</th>
                            <th class="p-4">Alasan & Catatan</th>
                            <th class="p-4">Bukti & Klarifikasi</th>
                            <th class="p-4">Status & Deadline</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($cancellations as $req)
                            @php
                                $helpLogs = $req->help?->cancelRequests ?? collect([$req]);
                                $cancelCount = $helpLogs->count();
                                $hasMultipleCancels = ($cancelCount > 1);
                                $isExpanded = in_array($req->help_id, $expandedHelpIds, true);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition {{ $hasMultipleCancels ? 'bg-amber-50/20 dark:bg-amber-950/10' : '' }}">
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $req->help->title ?? 'Bantuan' }}</span>
                                        @if($req->help?->order_id)
                                            <span class="text-[10px] text-gray-400 font-mono">({{ $req->help->order_id }})</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Dana Tahan: Rp {{ number_format($req->help->total_amount > 0 ? $req->help->total_amount : ($req->help->amount ?? 0), 0, ',', '.') }}
                                    </div>

                                    {{-- Log Pembatalan Per ID Tugas --}}
                                    @if($hasMultipleCancels)
                                        <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700 animate-pulse">
                                                🔁 {{ $cancelCount }}x Aktivitas Pembatalan
                                            </span>
                                            <button type="button" wire:click="toggleHelpLogs({{ $req->help_id }})" class="text-[10.5px] font-bold text-primary-600 dark:text-primary-400 hover:underline cursor-pointer flex items-center gap-0.5">
                                                <span>{{ $isExpanded ? '▲ Sembunyikan Log' : '▼ Lihat ' . $cancelCount . ' Log' }}</span>
                                            </button>
                                        </div>
                                    @else
                                        <div class="mt-1.5 flex items-center gap-1.5 text-[10.5px] text-gray-400">
                                            <span>Pengajuan ke-1</span>
                                            <button type="button" wire:click="toggleHelpLogs({{ $req->help_id }})" class="text-[10.5px] font-bold text-gray-500 dark:text-gray-400 hover:underline cursor-pointer">
                                                {{ $isExpanded ? '▲ Tutup Log' : '▼ Log' }}
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                        @if($req->requester_type === 'customer')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                Customer
                                            </span>
                                        @else
                                            @php
                                                $isReqKonsep2 = (($req->cancellation_stage ?? '') === 'in_progress' || ($req->previous_status ?? '') === 'in_progress' || ($req->help?->status ?? '') === 'partner_cancel_requested');
                                            @endphp
                                            @if($isReqKonsep2)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800" title="Pembatalan Tahap Pengerjaan">
                                                    Mitra (Konsep 2)
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Pembatalan Kendala Perjalanan">
                                                    Mitra (Konsep 1)
                                                </span>
                                            @endif
                                        @endif
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $req->requestedBy->name ?? 'User' }}</span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Cust: {{ $req->help->user->name ?? '-' }} • Mitra: {{ $req->help->mitra->name ?? ($req->partner->name ?? '-') }}
                                    </div>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $req->reason }}</p>
                                    @if($req->notes)
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic mt-0.5 line-clamp-1">"{{ $req->notes }}"</p>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="space-y-1">
                                        @if($req->evidence_photo)
                                            <a href="{{ asset('storage/' . $req->evidence_photo) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 dark:text-blue-400 hover:underline font-bold">
                                                 Bukti Pengaju ↗
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400">Tanpa Foto</span>
                                        @endif

                                        @if($req->partner_clarification)
                                            <div class="text-[10px] text-emerald-700 dark:text-emerald-300 bg-white dark:bg-black border border-emerald-200 dark:border-emerald-800 px-1.5 py-0.5 rounded">
                                                ✓ Ada Klarifikasi Mitra
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="space-y-1">
                                        @if($req->status === 'pending')
                                            @if($req->settlement_type === 'partner_unlinked_held')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                                    MITRA BEBAS (DITAHAN)
                                                </span>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                                    Menunggu respon Customer
                                                </div>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    PENDING AUDIT
                                                </span>
                                                @if($req->expires_at)
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                                        Batas: {{ $req->expires_at->diffForHumans() }}
                                                    </div>
                                                @endif
                                            @endif
                                        @elseif($req->status === 'approved')
                                            @if($req->settlement_type === 'partner_unlinked_held')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                                    MITRA BEBAS (DITAHAN)
                                                </span>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                                    Menunggu respon Customer
                                                </div>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    DISETUJUI ({{ $req->settlement_type }})
                                                </span>
                                                @if($req->sp_target !== 'none')
                                                    <div class="text-[10px] text-rose-600 dark:text-rose-400 font-bold">
                                                        ⚠️ Sanksi SP: {{ strtoupper($req->sp_target) }}
                                                    </div>
                                                @endif
                                            @endif
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                DITOLAK
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if($req->status === 'pending')
                                        <button wire:click="openCancelReviewModal({{ $req->id }})" 
                                                class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                            Audit Wilayah
                                        </button>
                                    @else
                                        <button wire:click="openCancelReviewModal({{ $req->id }})"
                                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold transition cursor-pointer">
                                            Detail Log
                                        </button>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            Oleh {{ $req->reviewedBy->name ?? 'Sistem' }}
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            {{-- Expanded Sub-row for Historical Logs on this Help Task --}}
                            @if($isExpanded)
                                <tr class="bg-gray-50/90 dark:bg-gray-950/90 border-b border-gray-200 dark:border-gray-800">
                                    <td colspan="6" class="p-4 sm:p-5">
                                        <div class="bg-white dark:bg-black p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-3">
                                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-base">📜</span>
                                                    <div>
                                                        <h4 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">
                                                            Log & Kronologi Pembatalan ID Pekerjaan #{{ $req->help_id }}
                                                            @if($req->help?->order_id)
                                                                <span class="text-xs font-mono text-gray-500 font-normal">({{ $req->help->order_id }})</span>
                                                            @endif
                                                        </h4>
                                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Total ditemukan {{ $cancelCount }} aktivitas pembatalan / pengalihan mitra pada tugas ini.</p>
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="toggleHelpLogs({{ $req->help_id }})" class="px-2.5 py-1 text-xs font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-lg transition cursor-pointer">
                                                    ✕ Tutup Log
                                                </button>
                                            </div>

                                            <div class="space-y-2.5">
                                                @foreach($helpLogs as $lIdx => $log)
                                                    @php
                                                        $isCurrentRow = ($log->id === $req->id);
                                                        $logNumber = $cancelCount - $lIdx;
                                                    @endphp
                                                    <div class="p-3 rounded-xl border text-xs transition {{ $isCurrentRow ? 'bg-primary-50/40 dark:bg-primary-950/30 border-primary-300 dark:border-primary-800 ring-1 ring-primary-500/20' : 'bg-gray-50/70 dark:bg-gray-900/70 border-gray-200 dark:border-gray-800' }}">
                                                        <div class="flex items-center justify-between flex-wrap gap-1.5 mb-1.5">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <span class="font-extrabold text-[11px] {{ $isCurrentRow ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                                                                    Log #{{ $logNumber }} • {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                                                </span>
                                                                @if($isCurrentRow)
                                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-primary-600 text-white">
                                                                        Baris Ini
                                                                    </span>
                                                                @endif
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->requester_type === 'customer' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                                                    {{ $log->requester_type === 'customer' ? 'Customer' : 'Mitra' }}: {{ $log->requestedBy?->name ?? 'User' }}
                                                                </span>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                                    Aksi: {{ strtoupper(str_replace('_', ' ', $log->action_type ?? 'cancel')) }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $log->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($log->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                                                                    {{ strtoupper($log->status) }}
                                                                </span>
                                                                @if($log->status === 'pending')
                                                                    <button type="button" wire:click="openCancelReviewModal({{ $log->id }})" class="text-[10px] font-bold text-amber-600 hover:underline">
                                                                        Audit Ini →
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px] pt-1.5 border-t border-gray-100 dark:border-gray-800">
                                                            <div>
                                                                <span class="text-gray-500 dark:text-gray-400">Mitra Saat Itu:</span>
                                                                <strong class="text-gray-800 dark:text-gray-200">{{ $log->partner?->name ?? '-' }}</strong>
                                                                <div class="mt-0.5">
                                                                    <span class="text-gray-500 dark:text-gray-400">Alasan:</span>
                                                                    <strong class="text-gray-800 dark:text-gray-200">"{{ $log->reason }}"</strong>
                                                                    @if($log->notes)
                                                                        <span class="italic text-gray-500 dark:text-gray-400">({{ $log->notes }})</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div>
                                                                @if($log->status !== 'pending')
                                                                    <div class="text-gray-500 dark:text-gray-400">
                                                                        Ditinjau oleh: <strong class="text-gray-700 dark:text-gray-300">{{ $log->reviewedBy?->name ?? 'Sistem / Admin' }}</strong>
                                                                        @if($log->admin_notes)
                                                                            <p class="italic text-gray-600 dark:text-gray-300 mt-0.5">Catatan: "{{ $log->admin_notes }}"</p>
                                                                        @endif
                                                                        @if($log->sp_target && $log->sp_target !== 'none')
                                                                            <span class="text-rose-600 dark:text-rose-400 font-bold block mt-0.5">⚠️ Sanksi SP: {{ strtoupper($log->sp_target) }} (Tingkat {{ $log->partner_sp_level ?? $log->customer_sp_level ?? 1 }})</span>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <div class="text-amber-700 dark:text-amber-400 font-medium">
                                                                        ⏳ Sedang dalam antrean audit wilayah
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-xs">
                                    Tidak ada data permintaan pembatalan yang menunggu audit wilayah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($cancellations, 'links'))
                <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    {{ $cancellations->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Resolusi Sengketa --}}
    @if($showResolveModal && $selectedHelp)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="closeResolveModal">
            <div class="bg-white dark:bg-black rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Arbitrase Sengketa Bantuan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedHelp->title }}</p>
                    </div>
                    <button wire:click="closeResolveModal" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Order Summary --}}
                <div class="bg-white dark:bg-black p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 mb-4 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Customer:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $selectedHelp->user->name ?? 'Customer' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Mitra:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $selectedHelp->mitra->name ?? 'Mitra' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Dana Terkunci:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($selectedHelp->total_amount > 0 ? $selectedHelp->total_amount : $selectedHelp->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-1">
                        <span class="text-gray-500 dark:text-gray-400">Alasan Sengketa:</span>
                        <span class="font-semibold text-rose-700 dark:text-rose-300 italic text-right">"{{ $selectedHelp->dispute_reason }}"</span>
                    </div>
                </div>

                {{-- Resolution Options --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Pilih Keputusan Arbitrase:</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white dark:bg-black {{ $resolutionType === 'full_release' ? 'border-emerald-500 text-emerald-700 dark:text-emerald-300' : 'border-gray-200 dark:border-gray-800' }} cursor-pointer text-xs">
                            <input type="radio" wire:model.live="resolutionType" value="full_release" class="text-emerald-600">
                            <div>
                                <strong class="text-emerald-700 dark:text-emerald-300">Pelepasan Penuh (Full Release ke Mitra)</strong>
                                <p class="text-gray-500 dark:text-gray-400 text-[11px]">Pekerjaan dinilai selesai sah. Saldo diteruskan ke mitra & komisi platform.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white dark:bg-black {{ $resolutionType === 'full_refund' ? 'border-amber-500 text-amber-700 dark:text-amber-300' : 'border-gray-200 dark:border-gray-800' }} cursor-pointer text-xs">
                            <input type="radio" wire:model.live="resolutionType" value="full_refund" class="text-amber-600">
                            <div>
                                <strong class="text-amber-700 dark:text-amber-300">Pengembalian Penuh (100% Refund ke Customer)</strong>
                                <p class="text-gray-500 dark:text-gray-400 text-[11px]">Pekerjaan dibatalkan total. Seluruh dana bruto dikembalikan ke saldo customer.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white dark:bg-black {{ $resolutionType === 'partial_split' ? 'border-purple-500 text-purple-700 dark:text-purple-300' : 'border-gray-200 dark:border-gray-800' }} cursor-pointer text-xs">
                            <input type="radio" wire:model.live="resolutionType" value="partial_split" class="text-purple-600">
                            <div>
                                <strong class="text-purple-700 dark:text-purple-300">Pembagian Parsial (Partial Split / Proporsional)</strong>
                                <p class="text-gray-500 dark:text-gray-400 text-[11px]">Pekerjaan sebagian selesai. Dana dibagi antara mitra, customer, dan biaya platform.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Partial Split Inputs --}}
                @if($resolutionType === 'partial_split')
                    <div class="mb-4 p-3 bg-white dark:bg-black border border-purple-200 dark:border-purple-800 rounded-xl grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Mitra (Rp)</label>
                            <input type="number" wire:model.live="partnerAmount" class="w-full px-2.5 py-1.5 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Biaya Platf (Rp)</label>
                            <input type="number" wire:model.live="platformFee" class="w-full px-2.5 py-1.5 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Customer (Rp)</label>
                            <input type="number" wire:model.live="customerRefund" class="w-full px-2.5 py-1.5 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-xs">
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2 mt-6">
                    <button wire:click="closeResolveModal" type="button" class="flex-1 py-2.5 px-4 bg-white dark:bg-black border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition">Batal</button>
                    <button wire:click="executeResolution" wire:loading.attr="disabled" type="button" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                        <span wire:loading.remove wire:target="executeResolution">Eksekusi Keputusan</span>
                        <span wire:loading wire:target="executeResolution">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Audit Pembatalan Manual & Evaluasi SP --}}
    @if($showCancelReviewModal && $selectedCancelRequest)
        @php
            $help = $selectedCancelRequest->help;
            $isPartner = ($selectedCancelRequest->requester_type === 'partner');
            $customer = $help?->user ?? $selectedCancelRequest->customer;
            $partner = $help?->mitra ?? $selectedCancelRequest->partner;
            $gross = (float) ($help?->total_amount > 0 ? $help?->total_amount : ($help?->amount ?: 0));

            $formatWa = function(?string $phone, string $text = '') {
                if (!$phone) return null;
                $clean = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($clean, '0')) {
                    $clean = '62' . substr($clean, 1);
                } elseif (str_starts_with($clean, '8')) {
                    $clean = '62' . $clean;
                }
                return 'https://wa.me/' . $clean . ($text ? '?text=' . urlencode($text) : '');
            };

            $isKonsep2 = ($isPartner && (($selectedCancelRequest->cancellation_stage ?? '') === 'in_progress' || ($selectedCancelRequest->previous_status ?? '') === 'in_progress' || ($help?->status ?? '') === 'partner_cancel_requested'));

            if ($isKonsep2) {
                $customerWaText = "Halo Kak " . ($customer?->name ?? 'Customer') . ", kami dari Tim Admin SayaBantu menindaklanjuti kendala pengerjaan yang diajukan oleh mitra pada tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") dengan alasan: \"" . $selectedCancelRequest->reason . "\". Kami ingin mengonfirmasi kondisi di lokasi untuk menyepakati pengembalian dana (refund) yang adil. Terima kasih.";
                $mitraWaText = "Halo Rekan " . ($partner?->name ?? 'Mitra') . ", kami dari Tim Admin SayaBantu menindaklanjuti pengajuan kendala lapangan tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") dengan alasan: \"" . $selectedCancelRequest->reason . "\". Kami sedang memverifikasi dengan customer untuk penyelesaian saldo pengerjaan. Terima kasih.";
            } elseif ($isPartner) {
                $customerWaText = "Halo Kak " . ($customer?->name ?? 'Customer') . ", kami dari Tim Admin SayaBantu menginformasikan bahwa mitra sebelumnya mengajukan pembatalan tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") karena kendala: \"" . $selectedCancelRequest->reason . "\". Saat ini sistem telah mengalihkan pesanan ke pool pencarian mitra baru. Mohon info jika ada catatan khusus. Terima kasih.";
                $mitraWaText = "Halo Rekan " . ($partner?->name ?? 'Mitra') . ", kami dari Tim Admin SayaBantu menindaklanjuti pengajuan pembatalan tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") dengan alasan: \"" . $selectedCancelRequest->reason . "\". Mohon klarifikasi atau konfirmasi tambahan terkait kendala tersebut. Terima kasih.";
            } else {
                $customerWaText = "Halo Kak " . ($customer?->name ?? 'Customer') . ", kami dari Tim Admin SayaBantu menindaklanjuti permohonan pembatalan tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") dengan alasan: \"" . $selectedCancelRequest->reason . "\". Mohon konfirmasi apakah Anda ingin kami carikan mitra baru (lempar ke pool) atau batalkan total & refund 100% saldo? Terima kasih.";
                $mitraWaText = "Halo Rekan " . ($partner?->name ?? 'Mitra') . ", kami dari Tim Admin SayaBantu menindaklanjuti laporan/pengajuan pembatalan dari customer pada tugas #" . ($help?->id ?? '') . " (" . ($help?->title ?? 'Bantuan') . ") dengan alasan: \"" . $selectedCancelRequest->reason . "\". Mohon klarifikasi segera mengenai kondisi tugas di lapangan. Terima kasih.";
            }

            $customerWaUrl = $formatWa($customer?->phone, $customerWaText);
            $partnerWaUrl = $formatWa($partner?->phone, $mitraWaText);
        @endphp

        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 z-50 animate-fade-in overflow-hidden"
             wire:click.self="closeCancelReviewModal">
            <div class="bg-white dark:bg-black rounded-t-3xl sm:rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[92vh] sm:max-h-[90vh] flex flex-col overflow-hidden animate-slide-up sm:animate-none">
                
                {{-- Sticky Modal Header --}}
                <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between shrink-0 bg-white dark:bg-black z-10">
                    <div class="min-w-0 flex-1 pr-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-base sm:text-lg text-gray-900 dark:text-white leading-tight">
                                Audit Pembatalan & Sanksi SP
                            </h3>
                            @if($isKonsep2)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shrink-0">
                                    🛵 Mitra (Konsep 2 - In Progress)
                                </span>
                            @elseif($isPartner)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-black text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shrink-0">
                                    🛵 Mitra (Konsep 1 - Transit)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-black text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0">
                                    👤 Pengaju: Customer
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $help?->title ?? 'Tugas Bantuan' }} • Nilai: Rp {{ number_format($gross, 0, ',', '.') }}</p>
                    </div>
                    <button wire:click="closeCancelReviewModal" class="p-1.5 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition shrink-0 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Scrollable Modal Body --}}
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4 text-xs overscroll-contain bg-white dark:bg-black">
                    {{-- Telemetri Lapangan & Bukti Sensor (GPS, Waktu, Chat, Respons) --}}
                    @php
                        $actionLabels = [
                            'partner_incident'  => ['label' => 'Kendala Mitra', 'badge' => 'bg-white text-rose-700 border-rose-200 dark:bg-black dark:text-rose-300 dark:border-rose-800'],
                            'switch_partner'    => ['label' => 'Ganti Mitra', 'badge' => 'bg-white text-blue-700 border-blue-200 dark:bg-black dark:text-blue-300 dark:border-blue-800'],
                            'customer_withdraw' => ['label' => 'Tarik Pekerjaan', 'badge' => 'bg-white text-amber-800 border-amber-200 dark:bg-black dark:text-amber-300 dark:border-amber-800'],
                        ];
                        $currAction = $selectedCancelRequest->action_type ?? 'cancellation';
                        $actionInfo = $actionLabels[$currAction] ?? ['label' => strtoupper(str_replace('_', ' ', $currAction)), 'badge' => 'bg-white text-indigo-700 border-indigo-200 dark:bg-black dark:text-indigo-300 dark:border-indigo-800'];
                    @endphp

                    <div class="bg-white dark:bg-black text-gray-900 dark:text-white p-3.5 sm:p-4 rounded-2xl border border-gray-200 dark:border-gray-800 text-xs space-y-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-2.5 flex-wrap gap-1.5">
                            <span class="font-bold text-gray-900 dark:text-gray-100 text-xs flex items-center gap-1.5">
                                <span>📡 Telemetri Lapangan & Riwayat Bukti Sensor</span>
                            </span>
                            <span class="text-[10px] sm:text-[11px] px-2.5 py-0.5 rounded-lg font-bold border {{ $actionInfo['badge'] }}">
                                Aksi: {{ $actionInfo['label'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5">
                            {{-- 1. Pergerakan GPS Mitra --}}
                            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl p-2.5 text-center flex flex-col justify-center min-w-0">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Pergerakan GPS</span>
                                <span class="text-xs sm:text-sm font-black block mt-0.5 truncate {{ ($selectedCancelRequest->partner_moved_km ?? 0) > 0.1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($selectedCancelRequest->partner_moved_km ?? 0, 1) }} KM
                                </span>
                                <span class="text-[9px] sm:text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5 truncate">
                                    {{ ($selectedCancelRequest->partner_moved_km ?? 0) > 0.1 ? '✓ Bergerak' : '⚠️ Tidak bergerak' }}
                                </span>
                            </div>

                            {{-- 2. Waktu Berlalu --}}
                            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl p-2.5 text-center flex flex-col justify-center min-w-0">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Waktu Sejak Diambil</span>
                                <span class="text-xs sm:text-sm font-black text-amber-600 dark:text-amber-400 block mt-0.5 truncate">
                                    {{ $selectedCancelRequest->time_elapsed_minutes ?? 0 }} Menit
                                </span>
                                <span class="text-[9px] sm:text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5 truncate">Durasi tugas</span>
                            </div>

                            {{-- 3. Jarak ke Sasaran --}}
                            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl p-2.5 text-center flex flex-col justify-center min-w-0">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Jarak ke Sasaran</span>
                                <span class="text-xs sm:text-sm font-black text-sky-600 dark:text-sky-400 block mt-0.5 truncate">
                                    {{ number_format($selectedCancelRequest->distance_to_target_km ?? 0, 1) }} KM
                                </span>
                                <span class="text-[9px] sm:text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5 truncate">Sisa jarak</span>
                            </div>

                            {{-- 4. Aktivitas Chat --}}
                            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-xl p-2.5 text-center flex flex-col justify-center min-w-0">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Aktivitas Chat</span>
                                <span class="text-xs sm:text-sm font-black text-purple-600 dark:text-purple-400 block mt-0.5 truncate">
                                    {{ $selectedCancelRequest->chat_messages_count ?? 0 }} Pesan
                                </span>
                                <span class="text-[9px] sm:text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5 truncate" title="{{ $selectedCancelRequest->partner_last_chat_at ? $selectedCancelRequest->partner_last_chat_at->diffForHumans() : 'Belum balas' }}">
                                    {{ $selectedCancelRequest->partner_last_chat_at ? 'Mitra aktif' : 'Mitra pasif' }}
                                </span>
                            </div>
                        </div>

                        {{-- Status Respons Konfirmasi Mitra --}}
                        @if($selectedCancelRequest->partner_response_type)
                            <div class="p-2.5 rounded-xl border text-xs bg-white dark:bg-black {{ $selectedCancelRequest->partner_response_type === 'rejected' ? 'border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200' : ($selectedCancelRequest->partner_response_type === 'confirmed' ? 'border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200' : 'border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-300') }}">
                                <div class="flex items-center justify-between flex-wrap gap-1">
                                    <span class="font-bold flex items-center gap-1 text-[11px] sm:text-xs">
                                        @if($selectedCancelRequest->partner_response_type === 'rejected')
                                            <span>✕ Mitra Menolak Penarikan (Mengajukan Pembelaan)</span>
                                        @elseif($selectedCancelRequest->partner_response_type === 'confirmed')
                                            <span>✓ Mitra Menyetujui Penarikan</span>
                                        @else
                                            <span>⏳ Menunggu Konfirmasi Mitra</span>
                                        @endif
                                    </span>
                                    @if($selectedCancelRequest->partner_responded_at)
                                        <span class="text-[10px] opacity-80">{{ $selectedCancelRequest->partner_responded_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                @if($selectedCancelRequest->partner_response_notes)
                                    <p class="mt-1 italic text-[11px] opacity-95">"{{ $selectedCancelRequest->partner_response_notes }}"</p>
                                @endif
                                @if($selectedCancelRequest->partner_response_photo)
                                    <div class="mt-1.5">
                                        <a href="{{ asset('storage/' . $selectedCancelRequest->partner_response_photo) }}" target="_blank" class="text-[11px] underline font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            Lihat Foto Bukti Pembelaan Mitra ↗
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Detail Alasan & Bukti --}}
                    <div class="bg-white dark:bg-black p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 text-xs space-y-2.5">
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">Alasan Pembatalan:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100 sm:text-right">{{ $selectedCancelRequest->reason }}</span>
                        </div>

                        @if($selectedCancelRequest->notes)
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-1 pt-1 border-t border-gray-200 dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Keterangan Pengaju:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 italic sm:text-right">"{{ $selectedCancelRequest->notes }}"</span>
                            </div>
                        @endif

                        @if($selectedCancelRequest->evidence_photo)
                            <div class="pt-1.5 border-t border-gray-200 dark:border-gray-800 flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">Bukti Foto Pengaju:</span>
                                <a href="{{ asset('storage/' . $selectedCancelRequest->evidence_photo) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline font-bold flex items-center gap-1">
                                    <span>Lihat Foto Bukti ↗</span>
                                </a>
                            </div>
                        @endif

                        @if($selectedCancelRequest->partner_clarification)
                            <div class="p-2.5 rounded-xl bg-white dark:bg-black border border-emerald-300 dark:border-emerald-800 mt-2 space-y-1">
                                <div class="font-bold text-emerald-800 dark:text-emerald-300 text-[11px] flex items-center gap-1">
                                    <span>💬 Tanggapan / Klarifikasi Mitra:</span>
                                </div>
                                <p class="text-emerald-900 dark:text-emerald-200 italic text-xs">"{{ $selectedCancelRequest->partner_clarification }}"</p>
                                @if($selectedCancelRequest->partner_clarification_photo)
                                    <a href="{{ asset('storage/' . $selectedCancelRequest->partner_clarification_photo) }}" target="_blank" class="inline-block text-[11px] text-blue-600 dark:text-blue-400 hover:underline font-bold">
                                        Lihat Bukti Pendukung Mitra ↗
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Riwayat & Log Pembatalan Tugas Ini --}}
                    @php
                        $totalLogs = count($cancelLogs);
                    @endphp
                    <div class="bg-white dark:bg-black p-3.5 sm:p-4 rounded-2xl border {{ $totalLogs > 1 ? 'border-amber-300 dark:border-amber-700/80 ring-1 ring-amber-500/20' : 'border-gray-200 dark:border-gray-800' }} text-xs space-y-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2.5 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm">📜</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100 text-xs">
                                    Log Aktivitas Pembatalan Tugas (ID: #{{ $selectedCancelRequest->help_id }})
                                </span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $totalLogs > 1 ? 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700 animate-pulse' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                Total: {{ $totalLogs }}x Aktivitas
                            </span>
                        </div>

                        @if($totalLogs > 1)
                            <div class="p-2.5 bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-[11px] text-amber-900 dark:text-amber-200 leading-relaxed flex items-start gap-2">
                                <span class="text-xs shrink-0 mt-0.5">⚠️</span>
                                <span><strong>Aktivitas Pembatalan Berulang Terdeteksi:</strong> Tugas ini telah mengalami {{ $totalLogs }} kali pengajuan pembatalan/ganti mitra. Tinjau kronologi di bawah untuk memahami seluruh riwayat sebelum mengambil keputusan audit.</span>
                            </div>
                        @endif

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @foreach($cancelLogs as $idx => $cLog)
                                @php
                                    $isCurrent = ($cLog->id === $selectedCancelRequest->id);
                                    $logNum = $totalLogs - $idx;
                                @endphp
                                <div class="p-2.5 rounded-xl border text-[11px] transition {{ $isCurrent ? 'bg-primary-50/40 dark:bg-primary-950/30 border-primary-400 dark:border-primary-700 ring-1 ring-primary-500/20' : 'bg-gray-50/60 dark:bg-gray-900/60 border-gray-200 dark:border-gray-800' }}">
                                    <div class="flex items-center justify-between flex-wrap gap-1 mb-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold {{ $isCurrent ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                                                Log #{{ $logNum }} • {{ $cLog->created_at ? $cLog->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                            </span>
                                            @if($isCurrent)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-primary-600 text-white">
                                                    Sedang Ditinjau
                                                </span>
                                            @endif
                                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold {{ $cLog->requester_type === 'customer' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                                {{ $cLog->requester_type === 'customer' ? 'Customer' : 'Mitra' }}: {{ $cLog->requestedBy?->name ?? 'User' }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                Aksi: {{ strtoupper(str_replace('_', ' ', $cLog->action_type ?? 'cancel')) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold {{ $cLog->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($cLog->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                                                {{ strtoupper($cLog->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-[10.5px] text-gray-600 dark:text-gray-400 space-y-0.5 pt-1 border-t border-gray-100 dark:border-gray-800/60">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-500">Mitra Terkait:</span>
                                            <strong class="text-gray-800 dark:text-gray-200">{{ $cLog->partner?->name ?? '-' }}</strong>
                                            • <span class="text-gray-500">Alasan:</span>
                                            <strong class="text-gray-800 dark:text-gray-200">"{{ $cLog->reason }}"</strong>
                                            @if($cLog->notes)
                                                <span class="italic text-gray-500">({{ $cLog->notes }})</span>
                                            @endif
                                        </div>
                                        @if($cLog->status !== 'pending')
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                                Ditinjau oleh: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $cLog->reviewedBy?->name ?? 'Sistem / Admin' }}</span>
                                                @if($cLog->admin_notes)
                                                    • Catatan: <em>"{{ $cLog->admin_notes }}"</em>
                                                @endif
                                                @if($cLog->sp_target && $cLog->sp_target !== 'none')
                                                    • <span class="text-rose-600 dark:text-rose-400 font-bold">SP: {{ strtoupper($cLog->sp_target) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Fitur Chat / Klarifikasi Dua Arah (Customer & Mitra) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-gray-800 dark:text-gray-200 text-xs flex items-center gap-1.5">
                                <span>💬 Hubungi & Minta Penjelasan (Investigasi Langsung):</span>
                            </label>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">Cross-check 2 arah</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            {{-- Kartu Customer --}}
                            <div class="p-3 bg-white dark:bg-black border border-blue-200 dark:border-blue-800 rounded-xl space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-blue-900 dark:text-blue-300 text-[11px] flex items-center gap-1">
                                        <span>👤 Customer (Pemesan)</span>
                                    </span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $customer?->phone ?? 'No Phone' }}</span>
                                </div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ $customer?->name ?? 'Customer' }}
                                </div>
                                <div class="flex items-center gap-1.5 pt-1">
                                    @if($customerWaUrl)
                                        <a href="{{ $customerWaUrl }}" target="_blank" class="flex-1 py-1.5 px-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] inline-flex items-center justify-center gap-1 transition shadow-2xs">
                                            <span>Chat WA</span>
                                        </a>
                                    @else
                                        <button type="button" disabled class="flex-1 py-1.5 px-2 bg-gray-100 dark:bg-gray-900 text-gray-400 rounded-lg text-[11px] font-bold">No WA</button>
                                    @endif
                                    @if($customer?->phone)
                                        <a href="tel:{{ $customer->phone }}" class="py-1.5 px-2.5 bg-white dark:bg-black hover:bg-gray-100 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200 rounded-lg font-bold text-[11px] inline-flex items-center justify-center transition">
                                            <span>Telp</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Kartu Mitra --}}
                            <div class="p-3 bg-white dark:bg-black border border-amber-200 dark:border-amber-800 rounded-xl space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-amber-900 dark:text-amber-300 text-[11px] flex items-center gap-1">
                                        <span>🛵 Mitra (Rekan Jasa)</span>
                                    </span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $partner?->phone ?? 'No Phone' }}</span>
                                </div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ $partner?->name ?? 'Mitra' }}
                                </div>
                                <div class="flex items-center gap-1.5 pt-1">
                                    @if($partnerWaUrl)
                                        <a href="{{ $partnerWaUrl }}" target="_blank" class="flex-1 py-1.5 px-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] inline-flex items-center justify-center gap-1 transition shadow-2xs">
                                            <span>Chat WA</span>
                                        </a>
                                    @else
                                        <button type="button" disabled class="flex-1 py-1.5 px-2 bg-gray-100 dark:bg-gray-900 text-gray-400 rounded-lg text-[11px] font-bold">No WA</button>
                                    @endif
                                    @if($partner?->phone)
                                        <a href="tel:{{ $partner->phone }}" class="py-1.5 px-2.5 bg-white dark:bg-black hover:bg-gray-100 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200 rounded-lg font-bold text-[11px] inline-flex items-center justify-center transition">
                                            <span>Telp</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pilihan Keputusan & Penyelesaian Berdasarkan Pengaju & Status Otomatis --}}
                    <div class="space-y-3 pt-1">
                        @if($isKonsep2)
                            {{-- KONDISI KONSEP 2: PEMBATALAN TAHAP PENGERJAAN OLEH MITRA (IN-PROGRESS) --}}
                            <div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 rounded-2xl text-xs space-y-2">
                                <div class="font-bold text-rose-900 dark:text-rose-200 flex items-center gap-1.5 text-xs sm:text-sm">
                                    <span class="text-base">⚠️</span>
                                    <span>Konsep 2: Pengajuan Pembatalan Saat Pengerjaan Telah Dimulai</span>
                                </div>
                                <p class="text-rose-800 dark:text-rose-300/90 leading-relaxed text-[11px]">
                                    Mitra mengajukan pembatalan tugas setelah menekan 'Mulai Pekerjaan'. Sesuai SOP, Admin <strong>wajib menghubungi Customer terlebih dahulu</strong> via WhatsApp/Telepon untuk klarifikasi kondisi riil di lapangan dan menentukan pengembalian dana (refund) berdasarkan kesepakatan.
                                </p>
                                <div class="p-2.5 bg-white dark:bg-black rounded-xl border border-rose-200 dark:border-rose-800/80 text-[11px] text-gray-700 dark:text-gray-300 flex items-center justify-between flex-wrap gap-2">
                                    <span>Dana Tahan Escrow: <strong>Rp {{ number_format($gross, 0, ',', '.') }}</strong></span>
                                    <span class="text-rose-600 dark:text-rose-400 font-bold">Status: Terkunci (Partner Cancel Requested)</span>
                                </div>
                            </div>

                            {{-- Bantuan Cepat Admin: Pisahkan & Bebaskan Mitra --}}
                            @if($selectedCancelRequest->status === 'pending' && ($selectedCancelRequest->help?->mitra_id || $selectedCancelRequest->partner_id))
                                <div class="p-3.5 bg-sky-50/80 dark:bg-sky-950/40 border-2 border-sky-300 dark:border-sky-700/80 rounded-2xl text-xs space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">🔓</span>
                                            <div>
                                                <h5 class="font-bold text-sky-950 dark:text-sky-100 text-xs sm:text-sm">Bantuan Admin: Pisahkan & Bebaskan Mitra</h5>
                                                <p class="text-[11px] text-sky-800 dark:text-sky-300">Customer lambat / belum merespons pembatalan?</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-200 border border-sky-300 dark:border-sky-700">
                                            Bebaskan Status Sibuk
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-sky-900/90 dark:text-sky-200/90 leading-relaxed">
                                        Jika Customer tidak kunjung merespons sehingga Mitra tertahan dalam status sibuk, Admin dapat <strong>membebaskan mitra</strong> agar dapat kembali mengambil tugas lain. Tugas akan <strong>ditahan (tidak tampil di pool)</strong> sampai Customer membuka aplikasi dan memilih mencari pengganti atau refund.
                                    </p>
                                    <div class="pt-1">
                                        <button type="button" 
                                                wire:click="unlinkPartnerAndHoldTask" 
                                                wire:loading.attr="disabled"
                                                wire:confirm="Yakin ingin memisahkan dan membebaskan mitra dari tugas ini? Status sibuk mitra akan langsung dilepas dan tugas ditahan (tidak tampil di pool) sampai customer mengonfirmasi."
                                                class="w-full py-2.5 px-3 bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                            <span wire:loading.remove wire:target="unlinkPartnerAndHoldTask">⚡ Pisahkan Mitra & Tahan Tugas (Bebaskan Akun Mitra)</span>
                                            <span wire:loading wire:target="unlinkPartnerAndHoldTask">Memproses Pelepasan Mitra...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Opsi Keputusan Penyelesaian Saldo & Refund (Konsep 2) --}}
                            <div class="space-y-2">
                                <label class="block font-bold text-gray-800 dark:text-gray-200 text-xs mb-1">
                                    Pilihan Penyelesaian Saldo / Refund (Sesuai Kesepakatan Customer & Admin):
                                </label>
                                
                                <div class="space-y-2 text-xs">
                                    {{-- Opsi A: Full Refund 100% ke Customer --}}
                                    <label wire:click="$set('settlementType', 'full_refund')"
                                           class="flex items-start gap-2.5 p-2.5 rounded-xl border bg-white dark:bg-black cursor-pointer transition {{ $settlementType === 'full_refund' ? 'border-primary-500 ring-2 ring-primary-500/20 font-semibold text-primary-900 dark:text-primary-300' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                        <input type="radio" name="konsep2_settlement_radio" {{ $settlementType === 'full_refund' ? 'checked' : '' }} class="mt-0.5 text-primary-600">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <strong class="text-gray-900 dark:text-white">💰 100% Full Refund ke Customer</strong>
                                                <span class="text-emerald-600 font-bold font-mono">Rp {{ number_format($gross, 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">Seluruh saldo dikembalikan ke customer (Rp 0 ke Mitra). Pilih jika pekerjaan sama sekali belum terlaksana atau kendala di pihak mitra.</p>
                                        </div>
                                    </label>

                                    {{-- Opsi B: Bagi Rata / 50:50 Split --}}
                                    <label wire:click="$set('settlementType', 'partial_settlement'); $set('cancelRefundAmount', {{ round($gross * 0.5) }}); $set('cancelPartnerAmount', {{ round($gross * 0.5) }})"
                                           class="flex items-start gap-2.5 p-2.5 rounded-xl border bg-white dark:bg-black cursor-pointer transition {{ ($settlementType === 'partial_settlement' && $cancelRefundAmount == round($gross * 0.5)) ? 'border-purple-500 ring-2 ring-purple-500/20 font-semibold text-purple-900 dark:text-purple-300' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                        <input type="radio" name="konsep2_settlement_radio" {{ ($settlementType === 'partial_settlement' && $cancelRefundAmount == round($gross * 0.5)) ? 'checked' : '' }} class="mt-0.5 text-purple-600">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <strong class="text-purple-800 dark:text-purple-300">⚖️ Bagi Rata (50:50 Split)</strong>
                                                <span class="text-purple-600 font-bold font-mono">Cust: Rp {{ number_format(round($gross*0.5), 0, ',', '.') }} • Mitra: Rp {{ number_format(round($gross*0.5), 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">Saldo dibagi 50% untuk Customer dan 50% untuk Mitra atas pekerjaan yang telah separuh jalan.</p>
                                        </div>
                                    </label>

                                    {{-- Opsi C: Penyesuaian Kustom (Sesuai Negosiasi) --}}
                                    <label wire:click="$set('settlementType', 'partial_settlement')"
                                           class="flex items-start gap-2.5 p-2.5 rounded-xl border bg-white dark:bg-black cursor-pointer transition {{ ($settlementType === 'partial_settlement' && $cancelRefundAmount != round($gross * 0.5)) ? 'border-indigo-500 ring-2 ring-indigo-500/20 font-semibold text-indigo-900 dark:text-indigo-300' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                        <input type="radio" name="konsep2_settlement_radio" {{ ($settlementType === 'partial_settlement' && $cancelRefundAmount != round($gross * 0.5)) ? 'checked' : '' }} class="mt-0.5 text-indigo-600">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <strong class="text-indigo-800 dark:text-indigo-300">📝 Nominal Kustom (Hasil Kesepakatan WA/Telp)</strong>
                                                <span class="text-indigo-600 font-bold text-[11px]">Input Manual</span>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">Masukkan pembagian nominal khusus sesuai kesepakatan langsung antara Admin dan Customer.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Form Input Nominal Kustom if partial_settlement --}}
                            @if($settlementType === 'partial_settlement')
                                <div class="p-3 bg-white dark:bg-black border border-indigo-200 dark:border-indigo-800 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Pengembalian ke Customer (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" wire:model.live="cancelRefundAmount" class="w-full p-2 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Pembayaran ke Mitra (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" wire:model.live="cancelPartnerAmount" class="w-full p-2 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    </div>
                                    <div class="sm:col-span-2 text-[10px] text-gray-500 dark:text-gray-400">
                                        Total bruto: Rp {{ number_format($gross, 0, ',', '.') }}. Perubahan pada salah satu nominal akan otomatis menghitung sisa nominal lainnya.
                                    </div>
                                </div>
                            @endif

                        @elseif($isPartner || ($selectedCancelRequest->action_type ?? '') === 'partner_incident')
                            {{-- KONDISI 1: PEMBATALAN KENDALA MITRA (OTOMATIS SELESAI) --}}
                            <div class="p-3.5 bg-white dark:bg-black border border-emerald-300 dark:border-emerald-800 rounded-2xl text-xs space-y-1.5">
                                <div class="font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    <span class="text-sm">⚡</span>
                                    <span>Status Operasional: Relist Otomatis ke Pool Mitra Selesai (Konsep 1)</span>
                                </div>
                                <p class="text-emerald-800 dark:text-emerald-300/90 leading-relaxed text-[11px]">
                                    Sistem telah <strong>otomatis melepaskan tugas</strong> dari mitra ini dan <strong>mengembalikan pesanan ke pool terbuka</strong> agar customer segera mendapatkan mitra pengganti. Saldo Dana Tahan <strong>(Rp {{ number_format($gross, 0, ',', '.') }})</strong> tetap aman di sistem.
                                </p>
                                <div class="pt-1.5 border-t border-emerald-200 dark:border-emerald-800 text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">
                                    💡 <em>Tugas Admin: Tinjau telemetri GPS dan foto bukti di atas untuk evaluasi sanksi SP (tanpa SP jika kendala darurat sah).</em>
                                </div>
                            </div>

                        @elseif(($selectedCancelRequest->action_type ?? '') === 'switch_partner')
                            {{-- KONDISI 2A: CUSTOMER GANTI MITRA (OTOMATIS SELESAI) --}}
                            <div class="p-3.5 bg-white dark:bg-black border border-emerald-300 dark:border-emerald-800 rounded-2xl text-xs space-y-1.5">
                                <div class="font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    <span class="text-sm">⚡</span>
                                    <span>Status Operasional: Pengalihan Mitra (Ganti Mitra) Selesai</span>
                                </div>
                                <p class="text-emerald-800 dark:text-emerald-300/90 leading-relaxed text-[11px]">
                                    Customer memilih <strong>Ganti Mitra</strong> karena mitra lama tidak bergerak / lambat. Sistem telah otomatis melepas dan mengeksklusi mitra lama, serta mengembalikan pesanan ke pool terbuka.
                                </p>
                                <div class="pt-1.5 border-t border-emerald-200 dark:border-emerald-800 text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">
                                    💡 <em>Tugas Admin: Periksa durasi dan aktivitas chat di atas untuk memberikan sanksi SP1 kepada mitra yang pasif/lambat.</em>
                                </div>
                            </div>

                        @elseif(($selectedCancelRequest->action_type ?? '') === 'customer_withdraw' && ($selectedCancelRequest->partner_response_type ?? '') === 'confirmed')
                            {{-- KONDISI 2B-1: CUSTOMER TARIK PEKERJAAN & MITRA SETUJU (OTOMATIS FULL REFUND) --}}
                            <div class="p-3.5 bg-white dark:bg-black border border-emerald-300 dark:border-emerald-800 rounded-2xl text-xs space-y-1.5">
                                <div class="font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    <span class="text-sm">⚡</span>
                                    <span>Status Operasional: Batal Total & Full Refund Otomatis Selesai</span>
                                </div>
                                <p class="text-emerald-800 dark:text-emerald-300/90 leading-relaxed text-[11px]">
                                    Mitra telah menyetujui konfirmasi penarikan pekerjaan. Pesanan telah ditutup dan <strong>100% saldo dana tahan (Rp {{ number_format($gross, 0, ',', '.') }})</strong> telah dikembalikan otomatis ke dompet customer.
                                </p>
                            </div>

                        @elseif(($selectedCancelRequest->action_type ?? '') === 'customer_withdraw' && ($selectedCancelRequest->partner_response_type ?? '') === 'rejected')
                            {{-- KONDISI 2B-2: SENGKETA PENARIKAN (MITRA MENOLAK & AJUKAN PEMBELAAN) --}}
                            <div class="p-3.5 bg-white dark:bg-black border border-amber-300 dark:border-amber-800 rounded-2xl text-xs space-y-1.5">
                                <div class="font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                                    <span class="text-sm">⚖️</span>
                                    <span>Sengketa Penarikan: Mitra Mengajukan Keberatan / Pembelaan</span>
                                </div>
                                <p class="text-amber-800 dark:text-amber-300/90 leading-relaxed text-[11px]">
                                    Mitra menolak pembatalan sepihak karena mengklaim telah menempuh perjalanan menuju lokasi. Berdasarkan data telemetri GPS dan pembelaan di atas, tentukan keputusan penyelesaian saldo dana tahan (Rp {{ number_format($gross, 0, ',', '.') }}):
                                </p>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-800 dark:text-gray-200 text-xs mb-1.5">
                                    Keputusan Penyelesaian Saldo Sengketa:
                                </label>
                                <div class="space-y-2 text-xs">
                                    {{-- Opsi A: Full Refund ke Customer --}}
                                    <label wire:click="$set('settlementType', 'full_refund')"
                                           class="flex items-start gap-2.5 p-2.5 rounded-xl border bg-white dark:bg-black cursor-pointer transition {{ $settlementType === 'full_refund' ? 'border-primary-500 font-semibold text-primary-900 dark:text-primary-300' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                        <input type="radio" name="dispute_settlement_radio" {{ $settlementType === 'full_refund' ? 'checked' : '' }} class="mt-0.5 text-primary-600">
                                        <div class="min-w-0 flex-1">
                                            <strong class="text-gray-900 dark:text-white">💰 Full Refund 100% ke Customer</strong>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">Pilih jika klaim mitra tidak terbukti valid / pergerakan GPS mitra minim (< 0.2 KM).</p>
                                        </div>
                                    </label>

                                    {{-- Opsi B: Kompensasi Parsial ke Mitra --}}
                                    <label wire:click="$set('settlementType', 'partial_settlement')"
                                           class="flex items-start gap-2.5 p-2.5 rounded-xl border bg-white dark:bg-black cursor-pointer transition {{ in_array($settlementType, ['partial_settlement', 'item_settled']) ? 'border-purple-500 font-semibold text-purple-900 dark:text-purple-300' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                        <input type="radio" name="dispute_settlement_radio" {{ in_array($settlementType, ['partial_settlement', 'item_settled']) ? 'checked' : '' }} class="mt-0.5 text-purple-600">
                                        <div class="min-w-0 flex-1">
                                            <strong class="text-purple-800 dark:text-purple-300">⚖️ Kompensasi Uang Jalan Mitra & Refund Sisa ke Customer</strong>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">Mitra mendapatkan kompensasi transport yang adil atas jarak tempuh yang telah dilalui.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Partial Split Form Inputs if active --}}
                            @if(in_array($settlementType, ['partial_settlement', 'item_settled']))
                                <div class="p-3 bg-white dark:bg-black border border-purple-200 dark:border-purple-800 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Refund Customer (Rp)</label>
                                        <input type="number" wire:model.live="cancelRefundAmount" class="w-full p-2 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-bold text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Kompensasi Mitra (Rp)</label>
                                        <input type="number" wire:model.live="cancelPartnerAmount" class="w-full p-2 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-bold text-gray-900 dark:text-white">
                                    </div>
                                </div>
                            @endif

                        @else
                            {{-- KASUS LAIN / DEFAULT --}}
                            <div class="p-3 bg-white dark:bg-black border border-blue-200 dark:border-blue-800 rounded-2xl text-xs space-y-1">
                                <div class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1">
                                    <span>ℹ️ Pengajuan Pembatalan:</span>
                                </div>
                                <p class="text-blue-800 dark:text-blue-300/90 leading-relaxed text-[11px]">
                                    Permohonan pembatalan tugas bantuan #{{ $help?->id }}. Tinjau informasi telemetri dan konfirmasi evaluasi kedisiplinan.
                                </p>
                            </div>
                        @endif

                        {{-- Admin SP Penalty Controls --}}
                        <div class="p-3.5 bg-white dark:bg-black border border-amber-300 dark:border-amber-800 rounded-2xl space-y-3">
                            <label class="block font-bold text-amber-900 dark:text-amber-300 text-xs flex items-center justify-between">
                                <span>⚖️ Evaluasi Sanksi Surat Peringatan (SP):</span>
                                <span class="text-[11px] font-normal text-amber-700 dark:text-amber-400">Pilih pihak bersalah</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-black border cursor-pointer transition {{ $spTarget === 'none' ? 'border-emerald-500 font-bold text-emerald-700 dark:text-emerald-300' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300' }}">
                                    <input type="radio" wire:model.live="spTarget" value="none" class="text-emerald-600">
                                    <span>Tanpa SP (Kendala Sah)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-black border cursor-pointer transition {{ $spTarget === 'partner' ? 'border-rose-500 font-bold text-rose-700 dark:text-rose-300' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300' }}">
                                    <input type="radio" wire:model.live="spTarget" value="partner" class="text-rose-600">
                                    <span>Beri SP ke Mitra</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-black border cursor-pointer transition {{ $spTarget === 'customer' ? 'border-rose-500 font-bold text-rose-700 dark:text-rose-300' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300' }}">
                                    <input type="radio" wire:model.live="spTarget" value="customer" class="text-rose-600">
                                    <span>Beri SP ke Customer</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-black border cursor-pointer transition {{ $spTarget === 'both' ? 'border-rose-500 font-bold text-rose-700 dark:text-rose-300' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300' }}">
                                    <input type="radio" wire:model.live="spTarget" value="both" class="text-rose-600">
                                    <span>Beri SP KEDUA Pihak</span>
                                </label>
                            </div>

                            @if(in_array($spTarget, ['partner', 'both']))
                                <div class="p-3 bg-white dark:bg-black border border-rose-200 dark:border-rose-800 rounded-xl space-y-2">
                                    <div class="font-bold text-rose-800 dark:text-rose-300 text-[11px] flex items-center gap-1.5">
                                        <span>🛵 Sanksi Surat Peringatan untuk Mitra:</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-5">
                                            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Tingkat Sanksi SP</label>
                                            <select wire:model.live="partnerSpLevel" class="w-full h-9 px-3 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                                                <option value="1">SP 1 (Peringatan Ringan)</option>
                                                <option value="2">SP 2 (Peringatan Sedang)</option>
                                                <option value="3">SP 3 (Peringatan Keras)</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-7">
                                            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Alasan Pemberian SP</label>
                                            <input type="text" wire:model.defer="partnerSpReason" placeholder="Alasan sanksi untuk mitra..." class="w-full h-9 px-3 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-rose-500">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(in_array($spTarget, ['customer', 'both']))
                                <div class="p-3 bg-white dark:bg-black border border-rose-200 dark:border-rose-800 rounded-xl space-y-2">
                                    <div class="font-bold text-rose-800 dark:text-rose-300 text-[11px] flex items-center gap-1.5">
                                        <span>👤 Sanksi Surat Peringatan untuk Customer:</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-5">
                                            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Tingkat Sanksi SP</label>
                                            <select wire:model.live="customerSpLevel" class="w-full h-9 px-3 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                                                <option value="1">SP 1 (Peringatan Ringan)</option>
                                                <option value="2">SP 2 (Peringatan Sedang)</option>
                                                <option value="3">SP 3 (Peringatan Keras)</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-7">
                                            <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Alasan Pemberian SP</label>
                                            <input type="text" wire:model.defer="customerSpReason" placeholder="Alasan sanksi untuk customer..." class="w-full h-9 px-3 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-rose-500">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Admin Audit Notes (Optional) --}}
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1 text-xs">
                                Catatan Hasil Audit Admin <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <textarea wire:model.defer="cancelAdminNotes" rows="2" class="w-full p-2.5 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500" placeholder="Penjelasan hasil verifikasi atau catatan penyelesaian... (opsional)"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Sticky Modal Footer Actions --}}
                <div class="px-4 py-3 sm:px-6 sm:py-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-black shrink-0 flex items-center gap-2.5 z-10" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
                    <button wire:click="closeCancelReviewModal" type="button" class="flex-1 py-2.5 sm:py-3 bg-white dark:bg-black hover:bg-gray-100 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold rounded-xl border border-gray-200 dark:border-gray-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="executeCancelReview" wire:loading.attr="disabled" type="button" class="flex-1 py-2.5 sm:py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span wire:loading.remove wire:target="executeCancelReview">Simpan Hasil Audit & Sanksi SP</span>
                        <span wire:loading wire:target="executeCancelReview">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
