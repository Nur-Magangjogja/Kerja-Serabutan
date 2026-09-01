<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>⚖️ Mediasi & Penyelesaian Sengketa</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-semibold border border-rose-200 dark:border-rose-800">
                    Escrow Freeze
                </span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pusat resolusi komplain customer, pembekuan saldo, dan mediasi arbitrase Admin Wilayah.
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

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
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
            </div>

            <div class="w-full sm:w-72">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari order / customer / mitra..." 
                       class="w-full px-3.5 py-2 text-xs bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-400">
            </div>
        </div>
    </div>

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
                                    {{ $help->city->name ?? '-' }} • {{ $help->disputed_at ? $help->disputed_at->translatedFormat('d M Y, H:i') : '-' }}
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
                                        FROZEN
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

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            {{ $disputes->links('vendor.pagination.superadmin') }}
        </div>
    </div>

    {{-- Modal Resolusi Sengketa --}}
    @if($showResolveModal && $selectedHelp)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="closeResolveModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Arbitrase Sengketa Bantuan #{{ $selectedHelp->id }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedHelp->title }} ({{ $selectedHelp->city->name ?? '-' }})</p>
                    </div>
                    <button wire:click="closeResolveModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Detail Dispute Info --}}
                <div class="mb-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Total Nominal Bruto:</span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($selectedHelp->total_amount > 0 ? $selectedHelp->total_amount : $selectedHelp->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Alasan Komplain Customer:</span>
                        <span class="text-rose-600 dark:text-rose-400 italic text-right max-w-xs">"{{ $selectedHelp->dispute_reason }}"</span>
                    </div>
                </div>

                {{-- Proof Photo if available --}}
                @if($selectedHelp->proof_photo)
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Bukti Pengerjaan Mitra:</p>
                        <a href="{{ asset('storage/' . $selectedHelp->proof_photo) }}" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 max-h-36">
                            <img src="{{ asset('storage/' . $selectedHelp->proof_photo) }}" alt="Bukti Mitra" class="w-full h-36 object-cover hover:scale-105 transition">
                        </a>
                    </div>
                @endif

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

                {{-- Partial Split Custom Inputs --}}
                @if($resolutionType === 'partial_split')
                    <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 rounded-xl space-y-2 text-xs">
                        <div class="grid grid-cols-3 gap-2">
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
                        @error('customerRefund')
                            <p class="text-rose-500 font-semibold text-[11px]">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-2 mt-6">
                    <button wire:click="closeResolveModal"
                            type="button"
                            class="flex-1 py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button wire:click="executeResolution"
                            wire:loading.attr="disabled"
                            type="button"
                            class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="executeResolution">Eksekusi Keputusan</span>
                        <span wire:loading wire:target="executeResolution">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
