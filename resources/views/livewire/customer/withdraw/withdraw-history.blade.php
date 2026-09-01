<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <a href="{{ route('customer.withdraw.form') }}" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Riwayat Penarikan Dana</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Daftar penarikan saldo ke rekening</p>
                    </div>

                    <a href="{{ route('customer.withdraw.form') }}" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-xl text-xs font-bold transition flex-shrink-0">
                        + Tarik
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-5 pb-24 space-y-4">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Filter Status Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button wire:click="filterByStatus('all')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterStatus === 'all' ? 'bg-[#0077cc] text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Semua
                </button>
                <button wire:click="filterByStatus('pending')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterStatus === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Menunggu
                </button>
                <button wire:click="filterByStatus('completed')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterStatus === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Berhasil
                </button>
                <button wire:click="filterByStatus('rejected')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Ditolak
                </button>
            </div>

            <!-- List Withdraw Cards -->
            @if($withdraws->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-8 text-center shadow-xs">
                    <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                        💳
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Transaksi</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">
                        Belum ada riwayat penarikan dana untuk status yang dipilih.
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($withdraws as $wd)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 shadow-xs space-y-3">
                            {{-- Card Header --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-xs text-gray-900 dark:text-white">#WD-{{ $wd->id }}</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $wd->created_at->format('d M Y • H:i') }}</p>
                                </div>

                                {{-- Status Badge --}}
                                <div>
                                    @if($wd->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                    @elseif($wd->status === 'completed' || $wd->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Berhasil Ditransfer
                                        </span>
                                    @elseif($wd->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Bank / E-Wallet Destination --}}
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-600/50 text-xs flex items-start gap-3">
                                <div class="w-9 h-9 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center text-base shadow-xs flex-shrink-0">
                                    🏦
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 dark:text-white uppercase">{{ $wd->bank_code }}</span>
                                        <span class="font-mono text-gray-600 dark:text-gray-300 text-[11px] font-semibold">{{ $wd->account_number }}</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">a.n. {{ $wd->account_name }}</p>
                                </div>
                            </div>

                            {{-- Financial Summary --}}
                            <div class="grid grid-cols-3 gap-2 py-1 text-center border-y border-gray-100 dark:border-gray-700/60 text-xs">
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Penarikan</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-[11px]">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Biaya Admin</span>
                                    <span class="font-semibold text-gray-600 dark:text-gray-300 text-[11px]">
                                        {{ ($wd->admin_fee ?? 0) > 0 ? 'Rp ' . number_format($wd->admin_fee, 0, ',', '.') : 'Rp 0' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Diterima Bersih</span>
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-[11px]">Rp {{ number_format($wd->net_amount ?: $wd->amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- Bukti Transfer Admin (Jika Tersedia) --}}
                            @if($wd->proof_of_transfer)
                                <div class="pt-1">
                                    <button type="button" wire:click="viewProof({{ $wd->id }}, '{{ asset('storage/' . $wd->proof_of_transfer) }}')"
                                        class="w-full py-2 px-3 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Lihat Bukti Transfer Admin</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Alasan Penolakan (Jika Status Rejected) --}}
                            @if($wd->status === 'rejected')
                                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl text-xs space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold text-rose-800 dark:text-rose-300">
                                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Alasan Penolakan dari Admin:</span>
                                    </div>
                                    <p class="text-rose-700 dark:text-rose-400 pl-5 text-[11px] leading-relaxed">
                                        {{ $wd->description ?? 'Permintaan penarikan tidak memenuhi syarat validasi rekening.' }}
                                    </p>
                                    <p class="text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold pl-5 pt-0.5">
                                        ✓ Saldo Rp {{ number_format($wd->amount, 0, ',', '.') }} telah otomatis dikembalikan utuh ke dompet Anda.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    {{ $withdraws->links('vendor.pagination.superadmin') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Preview Bukti Transfer --}}
    @if($showProofModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" wire:click.self="closeProofModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white">Bukti Transfer #WD-{{ $selectedWithdrawId }}</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Diunggah oleh Super Admin / Admin</p>
                    </div>
                    <button wire:click="closeProofModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>
                <div class="p-4 bg-gray-900/5 dark:bg-gray-950/40 text-center">
                    <img src="{{ $selectedProofUrl }}" alt="Bukti Transfer" class="max-h-80 w-auto mx-auto rounded-xl object-contain shadow-sm">
                </div>
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex gap-2">
                    <a href="{{ $selectedProofUrl }}" target="_blank" download class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold text-center transition shadow-xs">
                        Buka Gambar Penuh / Unduh
                    </a>
                    <button wire:click="closeProofModal" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition hover:bg-gray-200 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
