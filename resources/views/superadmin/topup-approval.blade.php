@php
    $title = 'Approval Top-Up Saldo';
    $breadcrumb = 'Super Admin / Approval Top-Up';
@endphp

<div x-data="approvalModal()" @confirm-approve.window="openFromEvent($event)">
    <div wire:poll.15s.visible>
        {{-- ===== Page Header ===== --}}
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Approval Top-Up Saldo</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Verifikasi dan approve request top-up dari semua customer</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-800">
                <svg class="w-3.5 h-3.5 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>
                <span class="font-semibold">Auto-refresh 15 dtk</span>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
        @endif
        @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 rounded-xl text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- ===== Summary Cards ===== --}}
        {{-- ===== Summary Cards ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" /><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total Pending</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $pendingRequests->total() }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate hidden sm:block">Request menunggu</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Approved Hari Ini</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white truncate">0</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate hidden sm:block">Transaksi disetujui</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total Nominal</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($pendingRequests->sum('amount'), 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate hidden sm:block">Menunggu approval</p>
                </div>
            </div>
        </div>

        @if ($pendingRequests->total() > 0)
        <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
            <p class="text-sm text-amber-800 dark:text-amber-300">
                Terdapat <strong>{{ $pendingRequests->total() }}</strong> request top-up yang menunggu verifikasi. Pastikan untuk memverifikasi bukti transfer sebelum approve.
            </p>
        </div>
        @endif

        {{-- ===== Table Card ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Kode Request</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Total Bayar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Metode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Waktu</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($pendingRequests as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $transaction->id }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-9 w-9 flex-shrink-0">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm">
                                                {{ substr($transaction->user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $transaction->user->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $transaction->customer_email ?? $transaction->user->email }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $transaction->user->city->name ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">{{ $transaction->request_code ?? '#' . $transaction->id }}</code>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $transaction->customer_phone ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">+Rp {{ number_format($transaction->admin_fee, 0, ',', '.') }} admin</p>
                                </td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <p class="text-sm font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-gray-600 dark:text-gray-300">{{ $transaction->payment_method ?? 'QRIS' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell">
                                    <p class="text-xs text-gray-700 dark:text-gray-200">{{ $transaction->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $transaction->created_at->format('H:i') }}</p>
                                    @if ($transaction->expired_at)
                                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-0.5">Exp: {{ \Carbon\Carbon::parse($transaction->expired_at)->format('d M, H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" wire:click="viewDetail({{ $transaction->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors" title="Lihat Bukti">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>
                                        <button type="button" wire:click="openRejectModal({{ $transaction->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors" title="Tolak">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <button type="button" wire:loading.attr="disabled"
                                            data-id="{{ $transaction->id }}"
                                            data-name="{{ $transaction->user->name }}"
                                            data-amount="{{ 'Rp ' . number_format($transaction->amount, 0, ',', '.') }}"
                                            @click.prevent="openFromEl($event)"
                                            class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center mb-3">
                                            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak Ada Request Pending</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Semua request top-up sudah diproses</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pendingRequests->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                {{ $pendingRequests->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ===== Detail Modal ===== --}}
    @if ($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" wire:click.stop>
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Bukti Transfer</h2>
                        <button wire:click="closeModal" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-4 grid grid-cols-2 gap-4">
                        @foreach([
                            ['label' => 'Customer', 'value' => $selectedTransaction->user->name],
                            ['label' => 'Kode Request', 'value' => $selectedTransaction->request_code ?? '#' . $selectedTransaction->id],
                            ['label' => 'Nominal', 'value' => 'Rp ' . number_format($selectedTransaction->amount, 0, ',', '.')],
                            ['label' => 'Total Bayar', 'value' => 'Rp ' . number_format($selectedTransaction->total_payment, 0, ',', '.')],
                        ] as $f)
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">{{ $f['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $f['value'] }}</p>
                        </div>
                        @endforeach
                    </div>

                    <!-- Proof Image -->
                    @if ($selectedTransaction->proof_of_payment)
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-2">Bukti Transfer:</p>
                            <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}"
                                class="w-full rounded-xl shadow-lg border border-gray-200" alt="Bukti Transfer"
                                onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2218%22 fill=%22%239ca3af%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
                            <div class="mt-2 text-xs text-gray-400">
                                Path: {{ $selectedTransaction->proof_of_payment }}
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Bukti transfer tidak tersedia</p>
                    @endif

                    <div class="flex gap-3 mt-5">
                        <button type="button" wire:loading.attr="disabled"
                            data-id="{{ $selectedTransaction->id }}"
                            data-name="{{ $selectedTransaction->user->name }}"
                            data-amount="{{ 'Rp ' . number_format($selectedTransaction->amount, 0, ',', '.') }}"
                            @click.prevent="openFromEl($event)"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="approve({{ $selectedTransaction->id }})">Approve Request</span>
                            <span wire:loading wire:target="approve({{ $selectedTransaction->id }})">Memproses...</span>
                        </button>
                        <button type="button" wire:click="openRejectModal({{ $selectedTransaction->id }})" wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold bg-rose-600 text-white rounded-xl hover:bg-rose-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="openRejectModal({{ $selectedTransaction->id }})">Tolak Request</span>
                            <span wire:loading wire:target="openRejectModal({{ $selectedTransaction->id }})">Loading...</span>
                        </button>
                    </div>

                    {{-- Reject modal is rendered globally below to avoid stacking-context issues --}}
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Reject Modal ===== --}}
    @if ($showRejectModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-[120]" wire:click.stop>
                <div class="p-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Tolak Request Top-Up</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Anda akan menolak request dari <strong>{{ $selectedTransaction->user->name }}</strong>
                        dengan nominal <strong>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</strong>
                    </p>
                    <form wire:submit.prevent="reject" class="space-y-4">
                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea wire:model="rejectionReason" rows="4"
                                class="w-full mt-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                            @error('rejectionReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeModal"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                                Tolak Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Approval Confirmation Modal (Alpine) - teleported to body to avoid clipping -->
    <template x-teleport="body">
        <div x-cloak x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-black/40" @click="close()" aria-hidden="true"></div>

            <div x-transition class="relative w-full max-w-sm mx-4 px-4">
                <div class="bg-white text-gray-900 rounded-lg shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="p-5 relative">
                        <button @click="close()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <h3 class="text-sm font-semibold">Yakin ingin approve request ini?</h3>
                            <p class="text-xs text-gray-500 text-center">Lanjutkan untuk menambahkan saldo ke user berikut:</p>

                            <div class="mt-2 w-full bg-gray-50 rounded-md px-3 py-2 text-center">
                                <div class="text-sm font-medium" x-text="name"></div>
                                <div class="text-xs text-gray-400" x-text="amount"></div>
                            </div>

                            <div class="mt-4 flex w-full items-center justify-between gap-3">
                                <button @click="close()" class="flex-1 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-50">Cancel</button>
                                <button @click="$wire.approve(id); close()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
        function approvalModal() {
            return {
                show: false,
                id: null,
                name: '',
                amount: '',
                openFromEvent(e) {
                    const d = e.detail || {};
                    this.id = d.id ?? null;
                    this.name = d.name ?? '';
                    this.amount = d.amount ?? '';
                    this.show = true;
                },
                openFromEl(e) {
                    const el = e.currentTarget || e.target;
                    const id = el.dataset?.id ?? null;
                    const name = el.dataset?.name ?? '';
                    const amount = el.dataset?.amount ?? '';
                    this.id = id;
                    this.name = name;
                    this.amount = amount;
                    this.show = true;
                },
                close() {
                    this.show = false;
                    this.id = null;
                }
            }
        }
    </script>

</div>
</div>
