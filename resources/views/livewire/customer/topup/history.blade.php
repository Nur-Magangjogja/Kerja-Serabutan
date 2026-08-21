<div class="min-h-screen bg-white">
    <div class="max-w-md mx-auto">
        <!-- Header - BRImo Style -->
        <div class="px-5 pt-5 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-3">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1">
                        <h1 class="text-lg font-bold">Riwayat Top-Up</h1>
                        <p class="text-xs text-white/90 mt-0.5">Semua request top-up Anda</p>
                    </div>

                    <div class="w-9"></div>
                </div>
            </div>

            <!-- Curved separator -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-24">
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl text-sm text-green-700 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                    </div>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <!-- Filter Tabs -->
            <div class="mb-5 flex gap-2 overflow-x-auto pb-2">
                <button wire:click="filterByStatus('all')"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $filterStatus === 'all' ? 'text-white' : 'text-gray-600 bg-gray-100' }}"
                    style="{{ $filterStatus === 'all' ? 'background: linear-gradient(to bottom right, #0098e7, #0060b0);' : '' }}">
                    Semua
                </button>
                <button wire:click="filterByStatus('waiting_approval')"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $filterStatus === 'waiting_approval' ? 'bg-yellow-500 text-white' : 'text-gray-600 bg-gray-100' }}">
                    Menunggu
                </button>
                <button wire:click="filterByStatus('approved')"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $filterStatus === 'approved' ? 'bg-green-500 text-white' : 'text-gray-600 bg-gray-100' }}">
                    Disetujui
                </button>
                <button wire:click="filterByStatus('rejected')"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $filterStatus === 'rejected' ? 'bg-red-500 text-white' : 'text-gray-600 bg-gray-100' }}">
                    Ditolak
                </button>
            </div>

            <!-- Transaction List -->
            <div class="space-y-3">
                @forelse($transactions as $transaction)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-900 text-sm">{{ $transaction->request_code ?? '#'.$transaction->id }}</h3>
                                    @if($transaction->status === 'waiting_approval')
                                        <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                            Menunggu
                                        </span>
                                    @elseif($transaction->status === 'approved' || $transaction->status === 'completed')
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Disetujui
                                        </span>
                                    @elseif($transaction->status === 'rejected')
                                        <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                            Ditolak
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-bold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">+{{ number_format($transaction->admin_fee, 0, ',', '.') }} admin</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <div class="text-sm">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-bold text-gray-900 ml-1">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</span>
                            </div>
                            <button wire:click="viewDetail({{ $transaction->id }})"
                                class="text-sm font-semibold hover:underline"
                                style="color: #0098e7;">
                                Detail →
                            </button>
                        </div>

                        @if($transaction->status === 'rejected' && $transaction->rejection_reason)
                            <div class="mt-3 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                                <p class="text-xs font-semibold text-red-800 mb-1">Alasan Penolakan:</p>
                                <p class="text-xs text-red-700">{{ $transaction->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Riwayat</h3>
                        <p class="text-sm text-gray-500 mb-6">Mulai top-up saldo Anda sekarang</p>
                        <a href="{{ route('customer.topup.request') }}"
                            class="inline-block px-6 py-3 text-white rounded-xl font-semibold hover:shadow-lg transition"
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
        <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">Detail Request Top-Up</h2>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6 space-y-4">
                    <!-- Status -->
                    <div class="text-center py-4">
                        @if($selectedTransaction->status === 'waiting_approval')
                            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-4xl">⏳</span>
                            </div>
                            <p class="text-lg font-bold text-yellow-700">Menunggu Verifikasi</p>
                        @elseif($selectedTransaction->status === 'approved' || $selectedTransaction->status === 'completed')
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-4xl">✅</span>
                            </div>
                            <p class="text-lg font-bold text-green-700">Request Disetujui</p>
                        @elseif($selectedTransaction->status === 'rejected')
                            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-4xl">❌</span>
                            </div>
                            <p class="text-lg font-bold text-red-700">Request Ditolak</p>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Kode Request:</span>
                            <span class="font-semibold text-gray-900">{{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tanggal Request:</span>
                            <span class="font-medium text-gray-900">{{ $selectedTransaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Nominal Top-Up:</span>
                            <span class="font-semibold text-gray-900">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Biaya Admin:</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($selectedTransaction->admin_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between">
                            <span class="font-bold text-gray-900">Total Pembayaran:</span>
                            <span class="font-bold text-primary-600 text-lg">Rp {{ number_format($selectedTransaction->total_payment, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="text-sm font-semibold text-gray-700 mb-2 block">Metode Pembayaran:</label>
                        <p class="text-sm text-gray-900">{{ ucwords(str_replace(['_', 'bank'], [' ', 'Transfer Bank '], $selectedTransaction->payment_method ?? '-')) }}</p>
                    </div>

                    <!-- Bukti Transfer -->
                    @if($selectedTransaction->proof_of_payment)
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">Bukti Transfer:</label>
                            <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" 
                                class="w-full rounded-xl shadow-lg border border-gray-200"
                                alt="Bukti Transfer">
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($selectedTransaction->status === 'rejected' && $selectedTransaction->rejection_reason)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <label class="text-sm font-semibold text-red-800 mb-2 block">Alasan Penolakan:</label>
                            <p class="text-sm text-red-700">{{ $selectedTransaction->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Approval Info -->
                    @if($selectedTransaction->approved_by && $selectedTransaction->approvedBy)
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <label class="text-sm font-semibold text-green-800 mb-2 block">Disetujui Oleh:</label>
                            <p class="text-sm text-green-700">{{ $selectedTransaction->approvedBy->name }}</p>
                            <p class="text-xs text-green-600 mt-1">{{ $selectedTransaction->approved_at?->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4">
                    <button wire:click="closeModal"
                        class="w-full px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

