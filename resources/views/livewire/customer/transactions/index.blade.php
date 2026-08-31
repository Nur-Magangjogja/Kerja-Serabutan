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
                        <h1 class="text-base font-bold truncate">Riwayat Transaksi</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Mutasi saldo & pengembalian dana</p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        @include('components.notification-icon', ['route' => route('customer.notifications.index'), 'class' => 'bg-white/15 backdrop-blur-md p-2 rounded-xl hover:bg-white/25 transition cursor-pointer text-white'])
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="px-5 pt-4">
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="px-5 pt-4">
                <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Tabs (Modern Glassmorphic Segmented Control) -->
        <div class="px-5 pt-3.5">
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-1.5 rounded-2xl shadow-xs border border-gray-200/70 dark:border-gray-700/80 space-y-1.5">
                <div class="grid grid-cols-3 gap-1.5">
                    <button wire:click="setFilter('all')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'all' ? 'bg-gradient-to-r from-primary-600 to-sky-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'all' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">🌟</span>
                        <span>Semua</span>
                    </button>
                    <button wire:click="setFilter('topup')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'topup' ? 'bg-gradient-to-r from-primary-600 to-sky-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'topup' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">💳</span>
                        <span>Top Up</span>
                    </button>
                    <button wire:click="setFilter('withdraw')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'withdraw' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/25 ring-2 ring-blue-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'withdraw' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">💸</span>
                        <span>Tarik Saldo</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <button wire:click="setFilter('payment')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'payment' ? 'bg-gradient-to-r from-primary-600 to-sky-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'payment' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">🛍️</span>
                        <span>Pembayaran</span>
                    </button>
                    <button wire:click="setFilter('refund')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'refund' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/25 ring-2 ring-emerald-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'refund' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">↩️</span>
                        <span>Dana Kembali</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-3 pb-8">
            <div class="space-y-3 transition-opacity duration-200" wire:loading.class="opacity-60 pointer-events-none" wire:target="setFilter">
                @if($transactions->count())
                    @foreach($transactions as $t)
                        @php
                            $type = $t->type ?? 'deduction';
                            $isPending = in_array($t->status, ['waiting_approval', 'pending']);
                            $isCancelled = ($t->status === 'cancelled');
                            $isRejected = ($t->status === 'rejected');
                            $isCredit = in_array($type, ['topup', 'refund', 'earning'], true);

                            $typeLabel = match($type) {
                                'topup' => 'Top Up Saldo',
                                'withdraw' => 'Penarikan Saldo (Withdraw)',
                                'refund' => 'Pengembalian Dana (Refund)',
                                'escrow_lock' => 'Pembayaran Bantuan',
                                'deduction' => 'Potongan Saldo',
                                'cancellation', 'penalty' => 'Pembatalan Tugas',
                                default => 'Transaksi Saldo',
                            };
                        @endphp
                        {{-- Interactive Transaction Card --}}
                        <div wire:click="showTransaction({{ $t->id }})" 
                             class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-4 shadow-2xs hover:shadow-md hover:border-primary-400/70 dark:hover:border-primary-500/60 active:scale-[0.985] transition-all duration-200 cursor-pointer overflow-hidden">
                            
                            {{-- Top Highlight Glow Effect on Hover --}}
                            <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-primary-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                            <div class="flex items-center justify-between gap-3">
                                <!-- Icon Squircle -->
                                <div class="flex-shrink-0">
                                    @if($type === 'topup')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center border shadow-2xs group-hover:scale-105 transition-transform {{ $isPending ? 'bg-gradient-to-br from-amber-100 to-yellow-50 dark:from-amber-950/80 dark:to-yellow-950/60 text-amber-600 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/50' : ($isCancelled ? 'bg-gradient-to-br from-purple-100 to-indigo-50 dark:from-purple-950/80 dark:to-indigo-950/60 text-purple-600 dark:text-purple-400 border-purple-200/60 dark:border-purple-800/50' : ($isRejected ? 'bg-gradient-to-br from-rose-100 to-red-50 dark:from-rose-950/80 dark:to-red-950/60 text-rose-600 dark:text-rose-400 border-rose-200/60 dark:border-rose-800/50' : 'bg-gradient-to-br from-emerald-100 to-teal-50 dark:from-emerald-950/80 dark:to-teal-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/50')) }}">
                                            @if($isPending)
                                                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @elseif($isCancelled)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @elseif($isRejected)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                                </svg>
                                            @endif
                                        </div>
                                    @elseif($type === 'withdraw')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-rose-100 to-red-50 dark:from-rose-950/80 dark:to-red-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'refund')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-100 to-teal-50 dark:from-emerald-950/80 dark:to-teal-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'escrow_lock')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-50 dark:from-blue-950/80 dark:to-indigo-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm group-hover:text-primary-600 dark:group-hover:text-sky-400 transition-colors">
                                            {{ $typeLabel }}
                                        </h3>
                                        @if($isPending)
                                            <span class="px-2 py-0.2 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded-full border border-amber-200 dark:border-amber-800/50 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Pending
                                            </span>
                                        @elseif($isCancelled)
                                            <span class="px-2 py-0.2 bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-[10px] font-bold rounded-full border border-purple-200 dark:border-purple-800/50">
                                                Dibatalkan
                                            </span>
                                        @elseif($isRejected)
                                            <span class="px-2 py-0.2 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[10px] font-bold rounded-full border border-rose-200 dark:border-rose-800/50">
                                                Ditolak
                                            </span>
                                        @elseif($type === 'refund')
                                            <span class="px-2 py-0.2 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold rounded-full border border-emerald-200 dark:border-emerald-800/50">
                                                Dana Masuk
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate font-medium">
                                        @if($type === 'topup')
                                            {{ $t->payment_method ? strtoupper($t->payment_method) : ($t->payment_type ? ucfirst($t->payment_type) : 'Top Up Saldo') }}
                                            @if(!empty($t->request_code))
                                                • {{ $t->request_code }}
                                            @elseif(!empty($t->order_id))
                                                • {{ $t->order_id }}
                                            @endif
                                        @elseif($type === 'refund')
                                            {{ $t->description ?? ($t->help?->title ? 'Refund: ' . $t->help->title : 'Pengembalian Dana Bantuan #' . $t->reference_id) }}
                                        @elseif($type === 'escrow_lock')
                                            {{ $t->description ?? ($t->help?->title ? 'Bantuan: ' . $t->help->title : 'Pembayaran Bantuan #' . $t->reference_id) }}
                                        @else
                                            {{ $t->description ?? 'Mutasi Saldo' }}
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ $t->created_at ? $t->created_at->format('d M Y • H:i') : '-' }}</span>
                                    </p>
                                </div>

                                <!-- Amount & Action Arrow -->
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    <div class="text-right">
                                        <div class="text-sm sm:text-base font-black tracking-tight {{ $isCredit ? ($isPending ? 'text-amber-600 dark:text-amber-400' : ($isCancelled ? 'text-purple-600 dark:text-purple-400 line-through' : ($isRejected ? 'text-rose-600 dark:text-rose-400 line-through' : 'text-emerald-600 dark:text-emerald-400'))) : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $isCredit ? '+' : '-' }}Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                        </div>
                                        @if($type === 'topup')
                                            <p class="text-[10px] {{ $isPending ? 'text-amber-600 dark:text-amber-400 font-bold' : ($isCancelled ? 'text-purple-600 dark:text-purple-400' : ($isRejected ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400 font-bold')) }}">
                                                {{ $isPending ? 'Menunggu Admin' : ($isCancelled ? 'Dibatalkan' : ($isRejected ? 'Ditolak' : 'Berhasil')) }}
                                            </p>
                                        @elseif($type === 'refund')
                                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Refund 100%</p>
                                        @elseif($isPending)
                                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">Diproses</p>
                                        @else
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Berhasil</p>
                                        @endif
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-primary-500 group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 p-8 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-50 to-sky-50 dark:from-gray-700/60 dark:to-gray-700/30 flex items-center justify-center mx-auto mb-3 text-primary-500 dark:text-sky-400 shadow-2xs">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Belum Ada Riwayat Transaksi</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Mutasi saldo, top up, dan pembayaran pesanan Anda akan tercatat rapi di sini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal (Modern Digital Receipt Style) -->
    @if($selectedTransaction)
        @php
            $isModalPending = in_array($selectedTransaction['status'] ?? '', ['waiting_approval', 'pending']);
            $isModalCancelled = (($selectedTransaction['status'] ?? '') === 'cancelled');
            $isModalRejected = (($selectedTransaction['status'] ?? '') === 'rejected');
            $isModalCredit = $selectedTransaction['is_credit'] ?? false;
        @endphp
        <div class="fixed inset-0 z-[70] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 transition-all duration-300"
             wire:click.self="closeTransaction">
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 transform transition-all duration-200 scale-100 animate-in fade-in zoom-in-95">
                
                {{-- Top Badge Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-xs">
                            🧾
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">Rincian Transaksi</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-400">Bukti mutasi saldo Anda</p>
                        </div>
                    </div>
                    <button wire:click="closeTransaction" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl cursor-pointer transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="pt-4">
                    <!-- Icon Big Receipt -->
                    <div class="flex justify-center mb-3">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center shadow-inner {{ $isModalCredit ? ($isModalPending ? 'bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400' : ($isModalCancelled ? 'bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400' : ($isModalRejected ? 'bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400'))) : 'bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400' }}">
                            @if($isModalCredit)
                                @if($isModalPending)
                                    <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif($isModalCancelled)
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                @elseif($isModalRejected)
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                    </svg>
                                @endif
                            @else
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Amount Header -->
                    <div class="text-center mb-4">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-400 mb-1">Nominal Mutasi</p>
                        <p class="text-2xl sm:text-3xl font-black tracking-tight {{ $isModalCredit ? ($isModalPending ? 'text-amber-600 dark:text-amber-400' : ($isModalCancelled ? 'text-purple-600 dark:text-purple-400 line-through' : ($isModalRejected ? 'text-rose-600 dark:text-rose-400 line-through' : 'text-emerald-600 dark:text-emerald-400'))) : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $isModalCredit ? '+' : '-' }}Rp {{ number_format(abs($selectedTransaction['amount']), 0, ',', '.') }}
                        </p>
                        @if($isModalPending)
                            <div class="mt-2.5 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl text-left text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Status: Pending (Menunggu Verifikasi Admin)</p>
                                    <p class="mt-0.5 text-amber-700/90 dark:text-amber-400/90">Permintaan top-up ini masih berjalan dan belum dikonfirmasi oleh admin. Saldo akan bertambah otomatis setelah disetujui.</p>
                                </div>
                            </div>
                        @elseif($isModalCancelled)
                            <div class="mt-2.5 p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 rounded-2xl text-left text-xs text-purple-900 dark:text-purple-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Status: Dibatalkan (Admin / Fraud)</p>
                                    <p class="mt-0.5 text-purple-800/90 dark:text-purple-400/90">Persetujuan transaksi top-up ini telah dibatalkan oleh Admin karena bukti tidak valid atau fiktif. Saldo telah disesuaikan kembali.</p>
                                </div>
                            </div>
                        @elseif($isModalRejected)
                            <div class="mt-2.5 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-left text-xs text-rose-900 dark:text-rose-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <div>
                                    <p class="font-bold">Status: Ditolak oleh Admin</p>
                                    <p class="mt-0.5 text-rose-800/90 dark:text-rose-400/90">{{ $selectedTransaction['rejection_reason'] ?? 'Permintaan top-up ini ditolak oleh admin karena bukti transfer tidak valid atau belum masuk.' }}</p>
                                </div>
                            </div>
                        @elseif($selectedTransaction['type'] === 'refund')
                            <div class="mt-2.5 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-left text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Pengembalian Dana Berhasil</p>
                                    <p class="mt-0.5 text-emerald-700/90 dark:text-emerald-400/90">Dana 100% tanpa potongan telah dikembalikan ke saldo dompet Anda akibat pembatalan bantuan.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Details Table -->
                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-4 space-y-2.5 border border-gray-100 dark:border-gray-700/60">
                        <div class="flex justify-between items-center text-xs sm:text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tipe Transaksi</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">
                                {{ $selectedTransaction['type_label'] }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center text-xs sm:text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            @if($isModalPending)
                                <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pending (Menunggu Admin)
                                </span>
                            @elseif($isModalCancelled)
                                <span class="font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800/50">Dibatalkan / Koreksi</span>
                            @elseif($isModalRejected || ($selectedTransaction['status'] ?? '') === 'rejected')
                                <span class="font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800/50">Ditolak</span>
                            @elseif(in_array($selectedTransaction['status'] ?? '', ['approved', 'completed']))
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/50">Berhasil</span>
                            @else
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($selectedTransaction['status'] ?? 'Selesai') }}</span>
                            @endif
                        </div>

                        @if($selectedTransaction['help_title'])
                            <div class="flex justify-between items-start text-xs sm:text-sm pt-1 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Judul Bantuan</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100 text-right truncate max-w-[180px]">{{ $selectedTransaction['help_title'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['payment_type'])
                            <div class="flex justify-between items-center text-xs sm:text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Metode</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ ucfirst($selectedTransaction['payment_type']) }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['order_id'])
                            <div class="flex justify-between items-center text-xs sm:text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Order ID</span>
                                <span class="font-mono font-bold text-xs text-gray-800 dark:text-gray-200">{{ $selectedTransaction['order_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['reference_id'])
                            <div class="flex justify-between items-center text-xs sm:text-sm">
                                <span class="text-gray-500 dark:text-gray-400">ID Referensi</span>
                                <span class="font-mono font-bold text-xs text-gray-800 dark:text-gray-200">#{{ $selectedTransaction['reference_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['description'])
                            <div class="flex justify-between items-start text-xs sm:text-sm">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Keterangan</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 text-right truncate max-w-[180px]">{{ $selectedTransaction['description'] }}</span>
                            </div>
                        @endif

                        @if(!empty($selectedTransaction['rejection_reason']))
                            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-3 text-xs">
                                <span class="font-bold text-rose-800 dark:text-rose-300 block mb-1">Catatan Alasan Penolakan:</span>
                                <p class="text-rose-700 dark:text-rose-400">{{ $selectedTransaction['rejection_reason'] }}</p>
                            </div>
                        @endif

                        @if(!empty($selectedTransaction['proof_of_payment']))
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Bukti Transfer QRIS:</span>
                                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 max-h-48 bg-white p-1 flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $selectedTransaction['proof_of_payment']) }}" 
                                        alt="Bukti Transfer" class="max-w-full max-h-44 object-contain rounded-lg">
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-between items-center text-xs sm:text-sm pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">Waktu Mutasi</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedTransaction['created_at'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <button wire:click="closeTransaction" class="w-full py-3 bg-gradient-to-r from-primary-600 to-sky-600 hover:from-primary-700 hover:to-sky-700 text-white rounded-2xl font-bold transition-all shadow-md shadow-primary-500/20 active:scale-[0.98] cursor-pointer text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div></div>