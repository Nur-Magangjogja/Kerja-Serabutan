<div>
    <div wire:poll.15s.visible class="space-y-5">
        {{-- ===== Page Header ===== --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Approval Top-Up Saldo</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Monitoring request top-up dari customer (Read Only)</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Auto-refresh 15s</span>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            <p class="text-xs text-blue-800 dark:text-blue-200">Admin hanya memiliki akses melihat data bukti transfer. Proses approval / penolakan dilakukan oleh <strong>Super Admin</strong>.</p>
        </div>

        @if (session()->has('success'))
            <div class="flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ===== Summary Stat Cards ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Pending</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $pendingRequests->total() }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Nominal Pending</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($pendingRequests->sum('amount'), 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Transaksi</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $pendingRequests->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ===== Table Card ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            @if($pendingRequests->isEmpty())
                <div class="px-4 py-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Tidak Ada Request Pending</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Semua request top-up sudah diproses oleh Super Admin</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Kode</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Bayar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Metode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($pendingRequests as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                <td class="px-4 py-3.5 font-mono text-xs font-semibold text-gray-500 dark:text-gray-400">#{{ $transaction->id }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($transaction->user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $transaction->user->name }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $transaction->customer_email ?? $transaction->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                    {{ $transaction->request_code ?? '#'.$transaction->id }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                    Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden sm:table-cell">
                                    {{ $transaction->payment_method ?? 'QRIS' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button type="button" 
                                        wire:click="viewDetail({{ $transaction->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-lg transition-colors shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span wire:loading.remove wire:target="viewDetail({{ $transaction->id }})">Detail</span>
                                        <span wire:loading wire:target="viewDetail({{ $transaction->id }})">...</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($pendingRequests->hasPages())
                    <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                        {{ $pendingRequests->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- ===== Detail Modal ===== --}}
        @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700" wire:click.stop>
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Bukti Transfer #{{ $selectedTransaction->id }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Request: {{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</p>
                    </div>
                    <button wire:click="closeModal" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-3 text-xs">
                        <div><p class="text-gray-400 dark:text-gray-500">Customer</p><p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ $selectedTransaction->user->name }}</p></div>
                        <div><p class="text-gray-400 dark:text-gray-500">Kode Request</p><p class="font-mono font-bold text-gray-900 dark:text-white mt-0.5">{{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</p></div>
                        <div><p class="text-gray-400 dark:text-gray-500">Nominal</p><p class="font-bold text-gray-900 dark:text-white mt-0.5">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</p></div>
                        <div><p class="text-gray-400 dark:text-gray-500">Total Bayar</p><p class="font-bold text-primary-600 dark:text-primary-400 mt-0.5">Rp {{ number_format($selectedTransaction->total_payment, 0, ',', '.') }}</p></div>
                    </div>

                    @if($selectedTransaction->proof_of_payment)
                        <div>
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Foto Bukti Transfer</p>
                            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 p-2">
                                <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" 
                                    class="w-full max-h-96 object-contain mx-auto rounded-lg"
                                    alt="Bukti Transfer"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2214%22 fill=%22%239ca3af%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-gray-400">Bukti transfer belum diunggah</div>
                    @endif
                </div>

                <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
