<div class="min-h-screen bg-white">
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        .card-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        
        .header-pattern {
            position: relative;
            overflow: hidden;
        }
        
        .header-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header - BRImo Style -->
        <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
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
                        <h1 class="text-lg font-bold">Riwayat Transaksi</h1>
                        <p class="text-xs text-white/90 mt-0.5">Mutasi saldo Anda</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('components.notification-icon', ['route' => route('customer.notifications.index')])
                    </div>
                </div>
            </div>

            <!-- Curved separator -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-8">
            <div class="space-y-3">
                @if($transactions->count())
                    @foreach($transactions as $t)
                        <div wire:click="showTransaction({{ $t->id }})" class="bg-white rounded-lg border border-gray-200 p-4 hover:border-blue-300 transition cursor-pointer">
                            <div class="flex items-center justify-between gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ ($t->type ?? '') === 'topup' ? 'bg-green-50' : 'bg-red-50' }}">
                                        @if(($t->type ?? '') === 'topup')
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 text-sm mb-0.5">
                                        @if(($t->type ?? '') === 'topup')
                                            Top Up Saldo
                                        @else
                                            Pembayaran Bantuan
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        @if(($t->type ?? '') === 'topup')
                                            {{ $t->payment_type ? ucfirst($t->payment_type) : 'Top Up' }}
                                            @if(!empty($t->order_id))
                                                • {{ $t->order_id }}
                                            @endif
                                        @else
                                            @if(!empty($t->reference_id))
                                                Bantuan #{{ $t->reference_id }}
                                            @else
                                                {{ $t->description ?? 'Pembayaran' }}
                                            @endif
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $t->created_at->format('d M Y • H:i') }}</p>
                                </div>

                                <!-- Amount -->
                                <div class="flex-shrink-0 text-right">
                                    <div class="text-sm font-bold {{ ($t->type ?? '') === 'topup' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ($t->type ?? '') === 'topup' ? '+' : '-' }} Rp {{ number_format(abs($t->amount), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4 flex items-center justify-center">
                        @php
                            $current = $transactions->currentPage();
                            $last = $transactions->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        <nav class="inline-flex items-center space-x-2" role="navigation" aria-label="Pagination">
                            {{-- Previous --}}
                            <button
                                wire:click="gotoPage({{ max(1, $current - 1) }})"
                                @if($transactions->onFirstPage()) disabled @endif
                                class="px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                ‹
                            </button>

                            {{-- First + ellipsis --}}
                            @if($start > 1)
                                <button wire:click="gotoPage(1)" class="px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">1</button>
                                @if($start > 2)
                                    <span class="px-2 text-sm text-gray-400">…</span>
                                @endif
                            @endif

                            {{-- Page numbers --}}
                            @for($page = $start; $page <= $end; $page++)
                                <button
                                    wire:click="gotoPage({{ $page }})"
                                    class="px-3 py-1.5 rounded-md border text-sm font-medium transition {{ $page === $current ? 'bg-primary-600 border-primary-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </button>
                            @endfor

                            {{-- Last + ellipsis --}}
                            @if($end < $last)
                                @if($end < $last - 1)
                                    <span class="px-2 text-sm text-gray-400">…</span>
                                @endif
                                <button wire:click="gotoPage({{ $last }})" class="px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">{{ $last }}</button>
                            @endif

                            {{-- Next --}}
                            <button
                                wire:click="gotoPage({{ min($last, $current + 1) }})"
                                @if($current === $last) disabled @endif
                                class="px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                ›
                            </button>
                        </nav>
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-700">Belum ada transaksi</p>
                        <p class="text-xs text-gray-500 mt-1">Riwayat transaksi Anda akan muncul di sini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    @if($selectedTransaction)
        <div data-transaction-modal class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click="closeTransaction">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Detail Transaksi</h3>
                    <button wire:click="closeTransaction" class="p-2 hover:bg-gray-100 rounded-full transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Type Badge -->
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $selectedTransaction['type'] === 'topup' ? 'bg-green-50' : 'bg-red-50' }}">
                            @if($selectedTransaction['type'] === 'topup')
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @else
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="text-center mb-6">
                        <p class="text-xs text-gray-500 mb-1">Nominal</p>
                        <p class="text-2xl font-bold {{ $selectedTransaction['type'] === 'topup' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $selectedTransaction['type'] === 'topup' ? '+' : '-' }} Rp {{ number_format(abs($selectedTransaction['amount']), 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Details -->
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tipe</span>
                            <span class="font-medium text-gray-900">
                                @if($selectedTransaction['type'] === 'topup')
                                    Top Up Saldo
                                @else
                                    Pembayaran Bantuan
                                @endif
                            </span>
                        </div>

                        @if($selectedTransaction['payment_type'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Metode</span>
                                <span class="font-medium text-gray-900">{{ ucfirst($selectedTransaction['payment_type']) }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['order_id'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Order ID</span>
                                <span class="font-medium text-gray-900 font-mono text-xs">{{ $selectedTransaction['order_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['reference_id'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Referensi</span>
                                <span class="font-medium text-gray-900">Bantuan #{{ $selectedTransaction['reference_id'] }}</span>
                            </div>
                        @endif

                        @if($selectedTransaction['description'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Keterangan</span>
                                <span class="font-medium text-gray-900 text-right">{{ $selectedTransaction['description'] }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-sm pt-3 border-t border-gray-200">
                            <span class="text-gray-600">Waktu</span>
                            <span class="font-medium text-gray-900">{{ $selectedTransaction['created_at'] }}</span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600"></span>
                            <span class="text-xs text-gray-500">{{ $selectedTransaction['created_at_human'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button wire:click="closeTransaction" class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>