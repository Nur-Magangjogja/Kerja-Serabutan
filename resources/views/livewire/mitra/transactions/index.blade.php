<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors" wire:poll.5s>
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
                        <h1 class="text-base font-bold truncate">Riwayat Mutasi Saldo</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Pendapatan & penarikan saldo</p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-notification-icon :route="route('mitra.notifications.index')" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="px-5 pt-4">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
                <button wire:click="setFilter('all')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterType === 'all' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Semua
                </button>
                <button wire:click="setFilter('earning')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterType === 'earning' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Pendapatan
                </button>
                <button wire:click="setFilter('penalty')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterType === 'penalty' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Pembatalan
                </button>
                <button wire:click="setFilter('withdraw')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterType === 'withdraw' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Penarikan
                </button>
                <button wire:click="setFilter('topup')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition cursor-pointer {{ $filterType === 'topup' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                    Top Up
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-3 pb-24">
            <div class="space-y-3">
                @if($transactions->count())
                    @foreach($transactions as $t)
                        @php
                            $type = $t->type ?? 'earning';
                            $isPending = in_array($t->status, ['waiting_approval', 'pending']);
                            $isCancelled = ($t->status === 'cancelled');
                            $isCredit = in_array($type, ['earning', 'topup', 'refund'], true);

                            $typeLabel = match($type) {
                                'earning' => 'Pendapatan Bantuan',
                                'penalty' => 'Penyesuaian Pembatalan',
                                'withdraw' => 'Penarikan Saldo (Withdraw)',
                                'topup' => 'Top Up Saldo',
                                'deduction' => 'Potongan Saldo',
                                'refund' => 'Pengembalian Dana',
                                default => 'Transaksi Saldo',
                            };
                        @endphp
                        <div wire:click="showTransaction({{ $t->id }})" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 hover:border-blue-300 dark:hover:border-blue-500 transition cursor-pointer shadow-xs">
                            <div class="flex items-center justify-between gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    @if($type === 'earning')
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'penalty')
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                    @elseif($type === 'withdraw')
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'topup')
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">
                                            {{ $typeLabel }}
                                        </h3>
                                        @if($type === 'penalty')
                                            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold rounded-full border border-rose-200 dark:border-rose-800/50">
                                                Pembatalan
                                            </span>
                                        @elseif($type === 'earning')
                                            <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold rounded-full border border-emerald-200 dark:border-emerald-800/50">
                                                Saldo Masuk
                                            </span>
                                        @elseif($isPending)
                                            <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded-full border border-amber-200 dark:border-amber-800/50 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Diproses
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        @if($type === 'penalty')
                                            {{ $t->description ?? 'Penyesuaian Pembatalan Bantuan' }}
                                        @elseif($type === 'earning')
                                            {{ $t->description ?? ($t->help?->title ? 'Pendapatan: ' . $t->help->title : 'Pendapatan Bantuan #' . $t->reference_id) }}
                                        @elseif($type === 'withdraw')
                                            {{ $t->description ?? 'Penarikan Saldo Dompet Mitra' }}
                                        @else
                                            {{ $t->description ?? 'Mutasi Saldo Dompet' }}
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ $t->created_at ? $t->created_at->format('d M Y • H:i') : '-' }}</p>
                                </div>

                                <!-- Amount -->
                                <div class="flex-shrink-0 text-right">
                                    <div class="text-sm font-bold {{ $isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $isCredit ? '+' : '-' }} Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                    </div>
                                    @if($type === 'penalty')
                                        <p class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold">Pembatalan</p>
                                    @elseif($type === 'earning')
                                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Pendapatan Bersih</p>
                                    @elseif($isPending)
                                        <p class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">Menunggu Admin</p>
                                    @endif
                                </div>
                            </div>

                            @if($type === 'penalty')
                                <div class="mt-2.5 p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/60 rounded-xl flex items-center gap-2 text-xs text-rose-800 dark:text-rose-300">
                                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="truncate">Penyesuaian saldo akibat pembatalan tugas bantuan yang telah diambil.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-8 text-center">
                        <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat Transaksi</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Mutasi pendapatan dan penarikan akan dicatat di sini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    @if($selectedTransaction)
        @php
            $isModalPending = in_array($selectedTransaction['status'] ?? '', ['waiting_approval', 'pending']);
            $isModalCancelled = (($selectedTransaction['status'] ?? '') === 'cancelled');
            $isModalCredit = $selectedTransaction['is_credit'] ?? false;
        @endphp
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-4" wire:click="closeTransaction">
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-t-3xl sm:rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6" wire:click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold">Detail Transaksi Mitra</h2>
                    <button wire:click="closeTransaction" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="pt-4">
                    <!-- Icon Big -->
                    <div class="flex justify-center mb-3">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $selectedTransaction['type'] === 'penalty' ? 'bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400' : ($isModalCredit ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400') }}">
                            @if($selectedTransaction['type'] === 'penalty')
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @elseif($isModalCredit)
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @else
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="text-center mb-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nominal</p>
                        <p class="text-2xl font-bold {{ $isModalCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $isModalCredit ? '+' : '-' }} Rp {{ number_format(abs($selectedTransaction['amount']), 0, ',', '.') }}
                        </p>
                        @if($selectedTransaction['type'] === 'penalty')
                            <div class="mt-2.5 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl text-left text-xs text-rose-800 dark:text-rose-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Penyesuaian Pembatalan Tugas</p>
                                    <p class="mt-0.5 text-rose-700/90 dark:text-rose-300/90">Saldo Anda disesuaikan akibat pembatalan tugas bantuan yang telah Anda ambil.</p>
                                </div>
                            </div>
                        @elseif($selectedTransaction['type'] === 'earning')
                            <div class="mt-2.5 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-left text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Pendapatan Bersih Berhasil Masuk</p>
                                    <p class="mt-0.5 text-emerald-700/90 dark:text-emerald-400/90">Customer telah mengonfirmasi penyelesaian tugas dan dana telah masuk utuh ke saldo dompet Anda.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 space-y-3 border border-gray-100 dark:border-gray-700/60">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tipe Transaksi</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $selectedTransaction['type_label'] }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            @if($isModalPending)
                                <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Diproses
                                </span>
                            @elseif($selectedTransaction['type'] === 'penalty')
                                <span class="font-bold text-rose-600 dark:text-rose-400">Diterapkan</span>
                            @elseif(in_array($selectedTransaction['status'] ?? '', ['approved', 'completed']))
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Berhasil</span>
                            @elseif(($selectedTransaction['status'] ?? '') === 'rejected')
                                <span class="font-semibold text-rose-600 dark:text-rose-400">Ditolak</span>
                            @else
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($selectedTransaction['status'] ?? 'Selesai') }}</span>
                            @endif
                        </div>

                        @if($selectedTransaction['help_title'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Judul Bantuan</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-right">{{ $selectedTransaction['help_title'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['order_id'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Order ID</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $selectedTransaction['order_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['reference_id'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">ID Referensi</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">#{{ $selectedTransaction['reference_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['description'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Keterangan</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 text-right">{{ $selectedTransaction['description'] }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-sm pt-3 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">Waktu</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $selectedTransaction['created_at'] }}</span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400"></span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $selectedTransaction['created_at_human'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button wire:click="closeTransaction" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
