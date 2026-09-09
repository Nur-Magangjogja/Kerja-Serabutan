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
                Pusat arbitrase sengketa saldo escrow dan audit manual klaim pembatalan berdasar kesaksian, bukti, serta penjatuhan SP.
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
    <div class="flex items-center gap-2 mb-4 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="$set('activeTab', 'disputes')" 
                class="pb-3 px-4 font-bold text-xs transition border-b-2 flex items-center gap-2 {{ $activeTab === 'disputes' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>⚖️ Mediasi Sengketa Escrow</span>
        </button>
        <button wire:click="$set('activeTab', 'cancellations')" 
                class="pb-3 px-4 font-bold text-xs transition border-b-2 flex items-center gap-2 {{ $activeTab === 'cancellations' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <span>🛑 Audit Pembatalan Mitra & Customer</span>
        </button>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                @if($activeTab === 'disputes')
                    <button wire:click="$set('status', 'frozen')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'frozen' ? 'bg-rose-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Dibekukan (Aktif)
                    </button>
                    <button wire:click="$set('status', 'resolved')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'resolved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Terselesaikan
                    </button>
                    <button wire:click="$set('status', 'all')" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Semua
                    </button>
                @else
                    {{-- Status Filter for Cancellations --}}
                    <button wire:click="$set('status', 'pending')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Menunggu Audit
                    </button>
                    <button wire:click="$set('status', 'approved')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Disetujui
                    </button>
                    <button wire:click="$set('status', 'rejected')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Ditolak
                    </button>
                    <button wire:click="$set('status', 'all')" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                        Semua Status
                    </button>

                    <div class="h-5 w-px bg-gray-200 dark:bg-gray-600 hidden sm:block"></div>

                    {{-- Requester Type Filter --}}
                    <select wire:model.live="requesterTypeFilter" class="px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-800 dark:text-gray-200 font-medium">
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
                       class="w-full px-3.5 py-2 text-xs bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-400">
            </div>
        </div>
    </div>

    @if($activeTab === 'disputes')
        {{-- Dispute List Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 uppercase font-semibold text-[11px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="p-4">Bantuan / Order</th>
                            <th class="p-4">Customer & Mitra</th>
                            <th class="p-4">Nominal Bruto</th>
                            <th class="p-4">Alasan Komplain</th>
                            <th class="p-4">Status Escrow</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($disputes as $help)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="p-4">
                                    <span class="font-bold text-gray-900 dark:text-white">#{{ $help->id }} - {{ $help->title }}</span>
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
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                             DIBEKUKAN
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_RELEASED)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                            RELEASED
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_REFUNDED)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                            REFUNDED (100%)
                                        </span>
                                    @elseif($help->escrow_status === \App\Models\Help::ESCROW_STATUS_PARTIAL_REFUND)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                            PARTIAL SPLIT
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
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
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    {{ $disputes->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    @else
        {{-- Cancellation Requests List Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 uppercase font-semibold text-[11px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="p-4">ID & Bantuan</th>
                            <th class="p-4">Pengaju & Pihak</th>
                            <th class="p-4">Alasan & Catatan</th>
                            <th class="p-4">Bukti & Klarifikasi</th>
                            <th class="p-4">Status & Deadline</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($cancellations as $req)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="p-4">
                                    <span class="font-bold text-gray-900 dark:text-white">#{{ $req->help_id }} - {{ $req->help->title ?? 'Bantuan' }}</span>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Escrow: Rp {{ number_format($req->help->amount ?? 0, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        @if($req->requester_type === 'customer')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                                Customer
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                Mitra
                                            </span>
                                        @endif
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $req->requestedBy->name ?? 'User' }}</span>
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        Cust: {{ $req->help->user->name ?? '-' }} • Mitra: {{ $req->help->mitra->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $req->reason }}</p>
                                    @if($req->notes)
                                        <p class="text-[11px] text-gray-500 italic mt-0.5 line-clamp-1">"{{ $req->notes }}"</p>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="space-y-1">
                                        @if($req->evidence_photo)
                                            <a href="{{ asset('storage/' . $req->evidence_photo) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:underline font-bold">
                                                📷 Bukti Pengaju ↗
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400">Tanpa Foto</span>
                                        @endif

                                        @if($req->partner_clarification)
                                            <div class="text-[10px] text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                                ✓ Ada Klarifikasi Mitra
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="space-y-1">
                                        @if($req->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                PENDING AUDIT
                                            </span>
                                            @if($req->expires_at)
                                                <div class="text-[10px] text-gray-500">
                                                    Batas: {{ $req->expires_at->diffForHumans() }}
                                                </div>
                                            @endif
                                        @elseif($req->status === 'approved')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                DISETUJUI ({{ $req->settlement_type }})
                                            </span>
                                            @if($req->sp_target !== 'none')
                                                <div class="text-[10px] text-rose-600 font-bold">
                                                    ⚠️ Sanksi SP: {{ strtoupper($req->sp_target) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
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
                                        <span class="text-[11px] text-gray-400">
                                            Ditutup oleh {{ $req->reviewedBy->name ?? 'Sistem' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-xs">
                                    Tidak ada permintaan pembatalan yang menunggu audit wilayah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($cancellations, 'links'))
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    {{ $cancellations->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Resolusi Sengketa --}}
    @if($showResolveModal && $selectedHelp)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="closeResolveModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Arbitrase Sengketa Bantuan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Order #{{ $selectedHelp->id }} - {{ $selectedHelp->title }}</p>
                    </div>
                    <button wire:click="closeResolveModal" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Order Summary --}}
                <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700 mb-4 text-xs space-y-1.5">
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
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-1">
                        <span class="text-gray-500 dark:text-gray-400">Alasan Sengketa:</span>
                        <span class="font-semibold text-rose-700 dark:text-rose-300 italic text-right">"{{ $selectedHelp->dispute_reason }}"</span>
                    </div>
                </div>

                {{-- Resolution Options --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Pilih Keputusan Arbitrase:</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border {{ $resolutionType === 'full_release' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-gray-200 dark:border-gray-700' }} cursor-pointer text-xs">
                            <input type="radio" wire:model.live="resolutionType" value="full_release" class="text-emerald-600">
                            <div>
                                <strong class="text-emerald-700 dark:text-emerald-300">Pelepasan Penuh (Full Release ke Mitra)</strong>
                                <p class="text-gray-500 dark:text-gray-400 text-[11px]">Pekerjaan dinilai selesai sah. Saldo diteruskan ke mitra & komisi platform.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border {{ $resolutionType === 'full_refund' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30' : 'border-gray-200 dark:border-gray-700' }} cursor-pointer text-xs">
                            <input type="radio" wire:model.live="resolutionType" value="full_refund" class="text-amber-600">
                            <div>
                                <strong class="text-amber-700 dark:text-amber-300">Pengembalian Penuh (100% Refund ke Customer)</strong>
                                <p class="text-gray-500 dark:text-gray-400 text-[11px]">Pekerjaan dibatalkan total. Seluruh dana bruto dikembalikan ke saldo customer.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border {{ $resolutionType === 'partial_split' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30' : 'border-gray-200 dark:border-gray-700' }} cursor-pointer text-xs">
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
                    <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 rounded-xl grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Mitra (Rp)</label>
                            <input type="number" wire:model.live="partnerAmount" class="w-full px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Biaya Platf (Rp)</label>
                            <input type="number" wire:model.live="platformFee" class="w-full px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Customer (Rp)</label>
                            <input type="number" wire:model.live="customerRefund" class="w-full px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2 mt-6">
                    <button wire:click="closeResolveModal" type="button" class="flex-1 py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition">Batal</button>
                    <button wire:click="executeResolution" wire:loading.attr="disabled" type="button" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="executeResolution">Eksekusi Keputusan</span>
                        <span wire:loading wire:target="executeResolution">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Audit Pembatalan Manual & Evaluasi SP --}}
    @if($showCancelReviewModal && $selectedCancelRequest)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in overflow-y-auto"
             wire:click.self="closeCancelReviewModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 max-h-[90vh] overflow-y-auto my-auto">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Audit Pembatalan & Sanksi SP</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Order #{{ $selectedCancelRequest->help_id }} - {{ $selectedCancelRequest->help->title ?? '' }}</p>
                    </div>
                    <button wire:click="closeCancelReviewModal" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Summary Card --}}
                <div class="bg-gray-50 dark:bg-gray-750 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700 mb-4 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pengaju Pembatalan:</span>
                        <span class="font-bold {{ $selectedCancelRequest->requester_type === 'customer' ? 'text-blue-600' : 'text-amber-600' }}">
                            {{ $selectedCancelRequest->requester_type === 'customer' ? 'Customer (' . ($selectedCancelRequest->help->user->name ?? '-') . ')' : 'Mitra (' . ($selectedCancelRequest->help->mitra->name ?? '-') . ')' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Alasan Pembatalan:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-right">{{ $selectedCancelRequest->reason }}</span>
                    </div>
                    @if($selectedCancelRequest->notes)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Catatan Tambahan:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 italic text-right">"{{ $selectedCancelRequest->notes }}"</span>
                        </div>
                    @endif

                    @if($selectedCancelRequest->evidence_photo)
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600 flex justify-between items-center">
                            <span class="text-gray-500">Foto Bukti Pengaju:</span>
                            <a href="{{ asset('storage/' . $selectedCancelRequest->evidence_photo) }}" target="_blank" class="text-blue-600 hover:underline font-bold flex items-center gap-1">
                                <span>Lihat Foto Bukti ↗</span>
                            </a>
                        </div>
                    @endif

                    @if($selectedCancelRequest->partner_clarification)
                        <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 mt-2">
                            <div class="font-bold text-emerald-800 dark:text-emerald-300 text-[11px] mb-0.5">Pengakuan / Klarifikasi Mitra:</div>
                            <p class="text-emerald-900 dark:text-emerald-200 italic text-xs">"{{ $selectedCancelRequest->partner_clarification }}"</p>
                            @if($selectedCancelRequest->partner_clarification_photo)
                                <a href="{{ asset('storage/' . $selectedCancelRequest->partner_clarification_photo) }}" target="_blank" class="mt-1 inline-block text-[11px] text-blue-600 hover:underline font-bold">
                                    📷 Foto Bukti Pembelaan Mitra ↗
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Decision Selector --}}
                <div class="mb-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Keputusan Pembatalan:</label>
                        <div class="flex gap-2">
                            <label class="flex-1 p-2 rounded-xl border text-center cursor-pointer font-bold {{ $cancelDecision === 'approved' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="cancelDecision" value="approved" class="hidden">
                                Disetujui (Batalkan Order)
                            </label>
                            <label class="flex-1 p-2 rounded-xl border text-center cursor-pointer font-bold {{ $cancelDecision === 'rejected' ? 'bg-rose-50 border-rose-500 text-rose-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="cancelDecision" value="rejected" class="hidden">
                                Ditolak (Lanjutkan Order)
                            </label>
                        </div>
                    </div>

                    @if($cancelDecision === 'approved')
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Penyelesaian Finansial:</label>
                            <select wire:model.live="settlementType" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs">
                                <option value="full_refund">Full Refund (100% Saldo Escrow ke Customer)</option>
                                <option value="item_settled">Item Settled (Barang dibayar ke Mitra, sisa ke Customer)</option>
                                <option value="partial_settlement">Settlement Parsial / Proporsional</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Refund Customer (Rp)</label>
                                <input type="number" wire:model.defer="cancelRefundAmount" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 rounded-lg text-xs font-bold">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Kompensasi Mitra (Rp)</label>
                                <input type="number" wire:model.defer="cancelPartnerAmount" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 rounded-lg text-xs font-bold">
                            </div>
                        </div>
                    @endif

                    {{-- Admin SP Penalty Controls --}}
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl space-y-2.5">
                        <label class="block font-bold text-amber-900 dark:text-amber-200 text-xs flex items-center gap-1.5">
                            <span>⚖️ Evaluasi Sanksi Surat Peringatan (SP):</span>
                        </label>
                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white dark:bg-gray-700 border cursor-pointer {{ $spTarget === 'none' ? 'border-emerald-500 font-bold text-emerald-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="spTarget" value="none" class="text-emerald-600">
                                <span>Tanpa SP (Kendala Sah)</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white dark:bg-gray-700 border cursor-pointer {{ $spTarget === 'partner' ? 'border-rose-500 font-bold text-rose-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="spTarget" value="partner" class="text-rose-600">
                                <span>Beri SP ke Mitra</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white dark:bg-gray-700 border cursor-pointer {{ $spTarget === 'customer' ? 'border-rose-500 font-bold text-rose-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="spTarget" value="customer" class="text-rose-600">
                                <span>Beri SP ke Customer</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white dark:bg-gray-700 border cursor-pointer {{ $spTarget === 'both' ? 'border-rose-500 font-bold text-rose-700' : 'border-gray-200 text-gray-600' }}">
                                <input type="radio" wire:model.live="spTarget" value="both" class="text-rose-600">
                                <span>Beri SP KEDUA Pihak</span>
                            </label>
                        </div>

                        @if(in_array($spTarget, ['partner', 'both']))
                            <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-lg space-y-1.5">
                                <div class="font-bold text-rose-800 dark:text-rose-300 text-[11px]">Sanksi untuk Mitra:</div>
                                <div class="flex gap-2">
                                    <select wire:model.live="partnerSpLevel" class="p-1.5 bg-white dark:bg-gray-700 border border-gray-300 rounded text-xs font-bold">
                                        <option value="1">SP 1 (Peringatan Ringan)</option>
                                        <option value="2">SP 2 (Peringatan Sedang)</option>
                                        <option value="3">SP 3 (Peringatan Keras / Shadow Ban)</option>
                                    </select>
                                    <input type="text" wire:model.defer="partnerSpReason" placeholder="Alasan SP ke Mitra..." class="flex-1 p-1.5 bg-white dark:bg-gray-700 border border-gray-300 rounded text-xs">
                                </div>
                            </div>
                        @endif

                        @if(in_array($spTarget, ['customer', 'both']))
                            <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-lg space-y-1.5">
                                <div class="font-bold text-rose-800 dark:text-rose-300 text-[11px]">Sanksi untuk Customer:</div>
                                <div class="flex gap-2">
                                    <select wire:model.live="customerSpLevel" class="p-1.5 bg-white dark:bg-gray-700 border border-gray-300 rounded text-xs font-bold">
                                        <option value="1">SP 1 (Peringatan Ringan)</option>
                                        <option value="2">SP 2 (Peringatan Sedang)</option>
                                        <option value="3">SP 3 (Peringatan Keras)</option>
                                    </select>
                                    <input type="text" wire:model.defer="customerSpReason" placeholder="Alasan SP ke Customer..." class="flex-1 p-1.5 bg-white dark:bg-gray-700 border border-gray-300 rounded text-xs">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Hasil Audit Admin</label>
                        <textarea wire:model.defer="cancelAdminNotes" rows="2" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 rounded-lg text-xs" placeholder="Penjelasan hasil verifikasi..."></textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 mt-4">
                    <button wire:click="closeCancelReviewModal" type="button" class="flex-1 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl">
                        Batal
                    </button>
                    <button wire:click="executeCancelReview" wire:loading.attr="disabled" type="button" class="flex-1 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-xl shadow-sm flex items-center justify-center">
                        <span wire:loading.remove wire:target="executeCancelReview">Simpan Keputusan Audit</span>
                        <span wire:loading wire:target="executeCancelReview">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
