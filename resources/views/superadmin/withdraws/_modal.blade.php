<div id="superadmin-withdraw-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
    <div id="superadmin-withdraw-modal-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-2xl z-10 max-h-[90vh] overflow-auto border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 rounded-t-xl z-20">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Withdraw Request #{{ $withdraw->id }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Detail permintaan penarikan saldo</p>
                </div>
                <button id="close-superadmin-withdraw-modal"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Status Badge -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        {{ substr($withdraw->user?->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $withdraw->user?->name ?? '-' }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">ID Mitra: {{ $withdraw->user_id }}</p>
                    </div>
                </div>
                <div>
                    @if($withdraw->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            Pending
                        </span>
                    @elseif($withdraw->status === 'processing')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">
                            <svg class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing
                        </span>
                    @elseif($withdraw->status === 'success')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Success
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Failed
                        </span>
                    @endif
                </div>
            </div>

            <!-- Amount Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700/60 dark:to-gray-800/60 p-6 rounded-xl border border-blue-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-1">Nominal Pengajuan</p>
                        <p class="text-3xl font-bold text-blue-900 dark:text-white">
                            Rp {{ number_format($withdraw->amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5">Dana Cair ke Pengguna (Net)</p>
                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($withdraw->effective_net_amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-700 grid grid-cols-3 gap-3 text-xs">
                    <div>
                        <p class="text-blue-700 dark:text-blue-400 mb-0.5 font-medium">Bank Tujuan</p>
                        <p class="font-bold text-blue-900 dark:text-gray-100">{{ $withdraw->bank_code }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 dark:text-blue-400 mb-0.5 font-medium">No. Rekening</p>
                        <p class="font-bold text-blue-900 dark:text-gray-100">{{ $withdraw->account_number }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 dark:text-blue-400 mb-0.5 font-medium">Biaya Admin Bank</p>
                        <p class="font-bold {{ $withdraw->effective_admin_fee === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $withdraw->effective_admin_fee === 0 ? 'Gratis (Rp 0)' : 'Rp ' . number_format($withdraw->effective_admin_fee, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @if($withdraw->description)
                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-700">
                    <p class="text-xs text-blue-700 dark:text-blue-400 mb-1">Keterangan / Penerima</p>
                    <p class="text-sm font-semibold text-blue-900 dark:text-gray-100">{{ $withdraw->description }}</p>
                </div>
                @endif
            </div>

            @if($withdraw->status === 'pending')
                <!-- Approval Form -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl text-xs text-blue-900 dark:text-blue-200 flex items-start gap-2.5 mb-5">
                        <span class="text-base mt-0.5">💳</span>
                        <div>
                            <span class="font-bold block">Nominal yang Wajib Ditransfer: Rp {{ number_format($withdraw->effective_net_amount, 0, ',', '.') }}</span>
                            <span class="text-[11px] block mt-0.5 text-blue-700 dark:text-blue-300">
                                Transfer dana sebesar Rp {{ number_format($withdraw->effective_net_amount, 0, ',', '.') }} ke rekening {{ $withdraw->bank_code }} • {{ $withdraw->account_number }} ({{ $withdraw->description }}). Biaya transfer bank sebesar Rp {{ number_format($withdraw->effective_admin_fee, 0, ',', '.') }} telah diperhitungkan secara otomatis.
                            </span>
                        </div>
                    </div>

                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Form Approval</h5>
                    <form action="{{ route('superadmin.withdraws.approve', $withdraw) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan <span class="text-gray-400 dark:text-gray-500 font-normal">(opsional)</span></label>
                                <input type="text" name="note" 
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    placeholder="Tambahkan catatan jika diperlukan" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Referensi Transfer <span class="text-gray-400 dark:text-gray-500 font-normal">(opsional)</span></label>
                                <input type="text" name="transfer_reference"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    placeholder="Masukkan referensi transfer" />
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <button type="button" id="open-reject-local"
                                class="px-5 py-2.5 bg-white dark:bg-gray-800 border-2 border-red-500 dark:border-red-600 text-red-600 dark:text-red-400 font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40">
                                <svg class="w-5 h-5 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Tolak
                            </button>
                            <button type="submit" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-sm">
                                <svg class="w-5 h-5 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve & Potong Saldo
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Processed Info -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informasi Pemrosesan</h5>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ ucfirst($withdraw->status) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Diproses pada</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $withdraw->processed_at ? $withdraw->processed_at->format('d M Y, H:i') : '-' }}
                            </span>
                        </div>
                        @if($withdraw->external_id)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Referensi</span>
                            <span class="text-sm font-mono font-semibold text-gray-900 dark:text-white">{{ $withdraw->external_id }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Fallback Reject Modal (used when modal is injected dynamically) -->
    <div id="withdraw-reject-modal-fallback" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div id="withdraw-reject-fallback-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tolak Permintaan Withdraw</h3>
                <button id="close-reject-fallback" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white text-xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Masukkan catatan penolakan (opsional) dan konfirmasi penolakan.</p>
                <form action="{{ route('superadmin.withdraws.reject', $withdraw) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Penolakan</label>
                        <input type="text" name="note" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Contoh: Saldo tidak mencukupi" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="cancel-reject-fallback"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium">Konfirmasi Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>