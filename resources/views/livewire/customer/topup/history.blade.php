<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Riwayat Top-Up</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Semua request top-up Anda</p>
                    </div>

                    <div class="w-9 flex-shrink-0"></div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-5 pb-24">
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-950/40 border-l-4 border-green-500 rounded-xl text-sm text-green-700 dark:text-green-300 flex items-center gap-3 shadow-xs">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/60 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                    </div>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <!-- Filter Tabs -->
            <div class="mb-5 flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button wire:click="filterByStatus('all')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'all' ? 'text-white bg-gradient-to-r from-[#0098e7] to-[#0060b0] shadow-sm' : 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    Semua
                </button>
                <button wire:click="filterByStatus('waiting_approval')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'waiting_approval' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    Pending / Menunggu
                </button>
                <button wire:click="filterByStatus('approved')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    Disetujui
                </button>
                <button wire:click="filterByStatus('rejected')"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    Ditolak
                </button>
            </div>

            <!-- Transaction List -->
            <div class="space-y-3">
                @forelse($transactions as $transaction)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $transaction->request_code ?? '#'.$transaction->id }}</h3>
                                    @if($transaction->status === 'waiting_approval' || $transaction->status === 'pending')
                                        <span class="px-2.5 py-0.5 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-xs font-semibold rounded-full flex items-center gap-1.5 border border-amber-200 dark:border-amber-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                    @elseif($transaction->status === 'approved' || $transaction->status === 'completed')
                                        <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-semibold rounded-full flex items-center gap-1 border border-emerald-200 dark:border-emerald-800/50">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Disetujui
                                        </span>
                                    @elseif($transaction->status === 'cancelled')
                                        <span class="px-2.5 py-0.5 bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-semibold rounded-full border border-purple-200 dark:border-purple-800/50">
                                            Dibatalkan
                                        </span>
                                    @elseif($transaction->status === 'rejected')
                                        <span class="px-2.5 py-0.5 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-xs font-semibold rounded-full border border-rose-200 dark:border-rose-800/50">
                                            Ditolak
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">+{{ number_format($transaction->admin_fee, 0, ',', '.') }} admin</p>
                            </div>
                        </div>

                        <!-- Pending In-Progress Alert Banner -->
                        @if($transaction->status === 'waiting_approval' || $transaction->status === 'pending')
                            <div class="mb-3 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-xl flex items-start gap-2.5 text-xs text-amber-800 dark:text-amber-300">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-bold">Top-up Sedang Berjalan (Pending)</p>
                                    <p class="text-amber-700/90 dark:text-amber-400/90 mt-0.5">Permintaan top-up Anda telah dikirim dan saat ini belum dikonfirmasi oleh admin.</p>
                                </div>
                            </div>
                        @elseif($transaction->status === 'cancelled')
                            <div class="mb-3 p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60 rounded-xl flex items-start gap-2.5 text-xs text-purple-900 dark:text-purple-300">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <p class="font-bold">Top-Up Dibatalkan oleh Admin</p>
                                    <p class="text-purple-800/90 dark:text-purple-400/90 mt-0.5">
                                        {{ $transaction->rejection_reason ?? 'Persetujuan top-up ini telah dibatalkan karena bukti transfer tidak valid / fiktif. Saldo akun telah disesuaikan kembali.' }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($transaction->status === 'rejected' && $transaction->rejection_reason)
                            <div class="mb-3 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl">
                                <p class="text-xs font-semibold text-rose-800 dark:text-rose-300 mb-1">Alasan Penolakan:</p>
                                <p class="text-xs text-rose-700 dark:text-rose-400">{{ $transaction->rejection_reason }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Total:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100 ml-1">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</span>
                            </div>
                            <button wire:click="viewDetail({{ $transaction->id }})"
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline cursor-pointer">
                                Detail →
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Belum Ada Riwayat</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Mulai top-up saldo Anda sekarang</p>
                        <a href="{{ route('customer.topup.request') }}"
                            class="inline-block px-6 py-3 text-white rounded-xl font-semibold hover:shadow-lg transition cursor-pointer"
                            style="background: linear-gradient(to bottom right, #0098e7, #0060b0);">
                            Top-Up Saldo
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-t-3xl sm:rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4 rounded-t-3xl z-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Detail Request Top-Up</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6 space-y-4">
                    <!-- Status -->
                    <div class="text-center py-2">
                        @if($selectedTransaction->status === 'waiting_approval' || $selectedTransaction->status === 'pending')
                            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-3xl animate-pulse">⏳</span>
                            </div>
                            <p class="text-base font-bold text-amber-700 dark:text-amber-300">Menunggu Konfirmasi (Pending)</p>
                            <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-left text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Permintaan top-up saldo ini masih berjalan dan belum dikonfirmasi oleh admin. Saldo akan otomatis bertambah ke akun Anda setelah diverifikasi.</span>
                            </div>
                        @elseif($selectedTransaction->status === 'approved' || $selectedTransaction->status === 'completed')
                            <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-3xl">✅</span>
                            </div>
                            <p class="text-base font-bold text-emerald-700 dark:text-emerald-300">Request Disetujui</p>
                        @elseif($selectedTransaction->status === 'cancelled')
                            <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-3xl">⚠️</span>
                            </div>
                            <p class="text-base font-bold text-purple-700 dark:text-purple-300">Persetujuan Dibatalkan (Admin)</p>
                            <div class="mt-3 p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 rounded-xl text-left text-xs text-purple-900 dark:text-purple-300">
                                <p class="font-bold">Keterangan Pembatalan:</p>
                                <p class="mt-0.5 text-purple-800/90 dark:text-purple-400/90">{{ $selectedTransaction->rejection_reason ?? 'Persetujuan top-up dibatalkan oleh Admin karena bukti transfer tidak valid atau fiktif.' }}</p>
                            </div>
                        @elseif($selectedTransaction->status === 'rejected')
                            <div class="w-16 h-16 bg-rose-100 dark:bg-rose-900/40 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-3xl">❌</span>
                            </div>
                            <p class="text-base font-bold text-rose-700 dark:text-rose-300">Request Ditolak</p>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 space-y-3 border border-gray-100 dark:border-gray-700/60">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Kode Request:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tanggal Request:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $selectedTransaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Nominal Top-Up:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Biaya Admin:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($selectedTransaction->admin_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                            <span class="font-bold text-gray-900 dark:text-gray-100">Total Pembayaran:</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400 text-base">Rp {{ number_format($selectedTransaction->total_payment, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Metode Pembayaran:</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucwords(str_replace(['_', 'bank'], [' ', 'Transfer Bank '], $selectedTransaction->payment_method ?? '-')) }}</p>
                    </div>

                    <!-- Bukti Transfer -->
                    @if($selectedTransaction->proof_of_payment)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Bukti Transfer:</label>
                            <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" 
                                class="w-full rounded-xl shadow-xs border border-gray-200 dark:border-gray-700 max-h-64 object-contain bg-black/5"
                                alt="Bukti Transfer">
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($selectedTransaction->status === 'rejected' && $selectedTransaction->rejection_reason)
                        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl p-4">
                            <label class="text-xs font-semibold text-rose-800 dark:text-rose-300 mb-1 block">Alasan Penolakan:</label>
                            <p class="text-sm text-rose-700 dark:text-rose-400">{{ $selectedTransaction->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Approval Info -->
                    @if($selectedTransaction->approved_by && $selectedTransaction->approvedBy)
                        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                            <label class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-1 block">Disetujui Oleh:</label>
                            <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ $selectedTransaction->approvedBy->name }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-1">{{ $selectedTransaction->approved_at?->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 px-6 py-4">
                    <button wire:click="closeModal"
                        class="w-full px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

