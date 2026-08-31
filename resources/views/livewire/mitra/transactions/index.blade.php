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

        <!-- Filter Tabs (Modern Glassmorphic Segmented Control) -->
        <div class="px-5 pt-3.5">
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-1.5 rounded-2xl shadow-xs border border-gray-200/70 dark:border-gray-700/80 space-y-1.5">
                <div class="grid grid-cols-3 gap-1.5">
                    <button wire:click="setFilter('all')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'all' ? 'bg-gradient-to-r from-primary-600 to-sky-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'all' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">🌟</span>
                        <span>Semua</span>
                    </button>
                    <button wire:click="setFilter('earning')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'earning' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/25 ring-2 ring-emerald-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'earning' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">💰</span>
                        <span>Pendapatan</span>
                    </button>
                    <button wire:click="setFilter('withdraw')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'withdraw' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/25 ring-2 ring-blue-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'withdraw' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">💸</span>
                        <span>Penarikan</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <button wire:click="setFilter('topup')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ $filterType === 'topup' ? 'bg-gradient-to-r from-primary-600 to-sky-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ $filterType === 'topup' ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">💳</span>
                        <span>Top Up</span>
                    </button>
                    <button wire:click="setFilter('cancellation')" class="group py-2 px-2 rounded-xl text-xs font-bold text-center transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 {{ ($filterType === 'cancellation' || $filterType === 'penalty') ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-md shadow-rose-500/25 ring-2 ring-rose-500/20' : 'bg-gray-50 dark:bg-gray-750/80 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="text-xs {{ ($filterType === 'cancellation' || $filterType === 'penalty') ? 'opacity-100 scale-110' : 'opacity-70 group-hover:scale-110' }} transition-transform">⚠️</span>
                        <span>Pembatalan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-3 pb-24">
            <div class="space-y-3 transition-opacity duration-200" wire:loading.class="opacity-60 pointer-events-none" wire:target="setFilter">
                @if($transactions->count())
                    @foreach($transactions as $t)
                        @php
                            $type = $t->type ?? 'earning';
                            $isPending = in_array($t->status, ['waiting_approval', 'pending']);
                            $isCancelled = ($t->status === 'cancelled');
                            $isCredit = in_array($type, ['earning', 'topup', 'refund'], true);

                            $typeLabel = match($type) {
                                'earning' => 'Pendapatan Bantuan',
                                'cancellation', 'penalty' => 'Pembatalan Tugas',
                                'withdraw' => 'Penarikan Saldo (Withdraw)',
                                'topup' => 'Top Up Saldo',
                                'deduction' => 'Potongan Saldo',
                                'refund' => 'Pengembalian Dana',
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
                                    @if($type === 'earning')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-100 to-teal-50 dark:from-emerald-950/80 dark:to-teal-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'cancellation' || $type === 'penalty')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-rose-100 to-red-50 dark:from-rose-950/80 dark:to-red-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                    @elseif($type === 'withdraw')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-50 dark:from-blue-950/80 dark:to-indigo-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                    @elseif($type === 'topup')
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-100 to-teal-50 dark:from-emerald-950/80 dark:to-teal-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m0 0l-4-4m4 4l4-4" />
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
                                        @if($type === 'cancellation' || $type === 'penalty')
                                            <span class="px-2 py-0.2 bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold rounded-full border border-rose-200 dark:border-rose-800/50">
                                                Pembatalan
                                            </span>
                                        @elseif($type === 'earning')
                                            <span class="px-2 py-0.2 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold rounded-full border border-emerald-200 dark:border-emerald-800/50">
                                                Saldo Masuk
                                            </span>
                                        @elseif($isPending)
                                            <span class="px-2 py-0.2 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded-full border border-amber-200 dark:border-amber-800/50 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Diproses
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate font-medium">
                                        @if($type === 'cancellation' || $type === 'penalty')
                                            {{ $t->description ?? 'Pembatalan Tugas Bantuan' }}
                                        @elseif($type === 'earning')
                                            {{ $t->description ?? ($t->help?->title ? 'Pendapatan: ' . $t->help->title : 'Pendapatan Bantuan #' . $t->reference_id) }}
                                        @elseif($type === 'withdraw')
                                            {{ $t->description ?? 'Penarikan Saldo Dompet Mitra' }}
                                        @else
                                            {{ $t->description ?? 'Mutasi Saldo Dompet' }}
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
                                        <div class="text-sm sm:text-base font-black tracking-tight {{ $isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $isCredit ? '+' : '-' }}Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                        </div>
                                        @if($type === 'cancellation' || $type === 'penalty')
                                            <p class="text-[10px] text-rose-600 dark:text-rose-400 font-bold">Pembatalan</p>
                                        @elseif($type === 'earning')
                                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Pendapatan Bersih</p>
                                        @elseif($isPending)
                                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">Menunggu Admin</p>
                                        @else
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Berhasil</p>
                                        @endif
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-primary-500 group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>

                            @if($type === 'cancellation' || $type === 'penalty')
                                <div class="mt-3 p-2.5 bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/60 rounded-xl flex items-center gap-2 text-xs text-rose-800 dark:text-rose-300">
                                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="truncate font-medium">Catatan riwayat pembatalan tugas bantuan.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 p-8 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-50 to-sky-50 dark:from-gray-700/60 dark:to-gray-700/30 flex items-center justify-center mx-auto mb-3 text-primary-500 dark:text-sky-400 shadow-2xs">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Belum Ada Riwayat Transaksi</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Mutasi pendapatan dan penarikan saldo Anda akan tercatat otomatis di sini.</p>
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
                            <p class="text-[11px] text-gray-400 dark:text-gray-400">Bukti mutasi dompet mitra</p>
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
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center shadow-inner {{ in_array($selectedTransaction['type'], ['cancellation', 'penalty']) ? 'bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400' : ($isModalCredit ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400' : 'bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400') }}">
                            @if(in_array($selectedTransaction['type'], ['cancellation', 'penalty']))
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @elseif($isModalCredit)
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @else
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Amount Header -->
                    <div class="text-center mb-4">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-400 mb-1">Nominal Mutasi</p>
                        <p class="text-2xl sm:text-3xl font-black tracking-tight {{ $isModalCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $isModalCredit ? '+' : '-' }}Rp {{ number_format(abs($selectedTransaction['amount']), 0, ',', '.') }}
                        </p>
                        @if(in_array($selectedTransaction['type'], ['cancellation', 'penalty']))
                            <div class="mt-2.5 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-left text-xs text-rose-800 dark:text-rose-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Pembatalan Tugas Bantuan</p>
                                    <p class="mt-0.5 text-rose-700/90 dark:text-rose-300/90">Catatan riwayat pembatalan tugas bantuan.</p>
                                </div>
                            </div>
                        @elseif($selectedTransaction['type'] === 'earning')
                            <div class="mt-2.5 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-left text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Pendapatan Bersih Masuk</p>
                                    <p class="mt-0.5 text-emerald-700/90 dark:text-emerald-400/90">Customer telah mengonfirmasi penyelesaian tugas dan upah bersih diteruskan penuh ke saldo dompet Anda.</p>
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
                                    Diproses
                                </span>
                            @elseif(in_array($selectedTransaction['type'], ['cancellation', 'penalty']))
                                <span class="font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800/50">Dibatalkan</span>
                            @elseif(in_array($selectedTransaction['status'] ?? '', ['approved', 'completed']))
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/50">Berhasil</span>
                            @elseif(($selectedTransaction['status'] ?? '') === 'rejected')
                                <span class="font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800/50">Ditolak</span>
                            @else
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($selectedTransaction['status'] ?? 'Selesai') }}</span>
                            @endif
                        </div>

                        @if($selectedTransaction['help_title'])
                            <div class="flex justify-between items-start text-xs sm:text-sm pt-1 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Judul Tugas</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100 text-right truncate max-w-[180px]">{{ $selectedTransaction['help_title'] }}</span>
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
</div>
