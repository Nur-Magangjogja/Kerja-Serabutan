<div class="mt-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Riwayat Transaksi Terbaru</h3>

    @if($transactions->isEmpty())
        <div class="text-center py-8 bg-gray-50 rounded-2xl">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500 text-sm">Belum ada transaksi</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($transactions as $transaction)
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                    <!-- Left Side -->
                    <div class="flex items-center gap-3 flex-1">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            @if($transaction->type === 'topup')
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">
                                @if($transaction->type === 'topup')
                                    Tambah Saldo
                                @else
                                    Pengurangan Saldo
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">
                                @if($transaction->type === 'topup')
                                    {{-- Prefer explicit payment_type when available --}}
                                    {{ $transaction->payment_type ? 'Topup via ' . ucfirst($transaction->payment_type) : ($transaction->description ?? 'Topup Saldo') }}
                                    @if($transaction->order_id)
                                        <div class="text-xs text-gray-400">Order: {{ $transaction->order_id }}</div>
                                    @endif
                                @else
                                    {{-- deduction: show reference help id if present --}}
                                    @if($transaction->reference_id)
                                        Untuk Bantuan #{{ $transaction->reference_id }}
                                    @else
                                        {{ $transaction->description ?? 'Pengurangan' }}
                                    @endif
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $transaction->created_at->format('d M Y - H:i') }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Amount -->
                    <div class="text-right">
                        <div class="font-bold {{ $transaction->type === 'topup' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'topup' ? '+' : '-' }} Rp
                            {{ number_format($transaction->amount, 0, ',', '.') }}
                        </div>
                        <div
                            class="text-xs px-2 py-1 rounded-full {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-700' : ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }} mt-1">
                            {{ ucfirst($transaction->status) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- View All Link -->
        <a href="{{ route('balance.history') ?? '#' }}"
            class="block text-center text-primary-600 text-sm font-semibold mt-4 hover:text-primary-700">
            Lihat Semua Transaksi →
        </a>
    @endif
</div>