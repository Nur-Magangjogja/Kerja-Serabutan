<div id="admin-withdraw-modal" class="fixed inset-0 z-50 flex items-center justify-center p-6">
    <div id="admin-withdraw-modal-overlay" class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="relative w-full max-w-4xl mx-auto bg-white rounded-lg shadow-2xl z-10 max-h-[90vh] overflow-auto">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold">Proses Withdraw #{{ $withdraw->id }}</h3>
                <p class="text-sm text-gray-500">Permintaan dari mitra berikut informasinya.</p>
            </div>
            <div>
                <button id="close-admin-withdraw-modal"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-gray-100 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="{{ $withdraw->status === 'pending' ? 'md:col-span-2' : 'md:col-span-3' }} space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm text-gray-500">Mitra</h4>
                            <div class="font-semibold">{{ $withdraw->user?->name ?? '-' }} <span
                                    class="text-xs text-gray-400">(ID: {{ $withdraw->user_id }})</span></div>
                        </div>
                        <div class="text-right">
                            @if($withdraw->status === 'pending')
                                <div class="inline-flex items-center px-3 py-1 rounded bg-yellow-100 text-yellow-800">
                                    Pending</div>
                            @elseif($withdraw->status === 'processing')
                                <div class="inline-flex items-center px-3 py-1 rounded bg-blue-100 text-blue-800">Processing
                                </div>
                            @elseif($withdraw->status === 'success')
                                <div class="inline-flex items-center px-3 py-1 rounded bg-green-100 text-green-800">Success
                                </div>
                            @else
                                <div class="inline-flex items-center px-3 py-1 rounded bg-red-100 text-red-800">Failed</div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/60 p-4 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500">Nominal Pengajuan</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Dana Cair ke Pengguna (Net)</div>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($withdraw->effective_net_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-400">Bank / Rekening:</span>
                                <div class="font-semibold">{{ $withdraw->bank_code }} • {{ $withdraw->account_number }}</div>
                            </div>
                            <div>
                                <span class="text-gray-400">Biaya Admin Transfer:</span>
                                <div class="font-semibold {{ $withdraw->effective_admin_fee === 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $withdraw->effective_admin_fee === 0 ? 'Gratis (Rp 0)' : 'Rp ' . number_format($withdraw->effective_admin_fee, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @if($withdraw->description)
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600 text-xs">
                            <span class="text-gray-400">Penerima / Keterangan:</span>
                            <div class="font-medium mt-0.5">{{ $withdraw->description }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="text-sm text-gray-500">Status: <strong>{{ ucfirst($withdraw->status) }}</strong></div>
                    <div class="mt-2">Diproses pada: {{ $withdraw->processed_at ? $withdraw->processed_at->format('Y-m-d H:i') : '-' }}</div>
                    <div class="mt-2">Referensi: {{ $withdraw->external_id ?? '-' }}</div>
                </div>

                @if($withdraw->status === 'pending')
                    <div class="md:col-span-1">
                        <div class="bg-white p-4 border rounded mb-4">
                            <div class="text-sm text-gray-500">Aksi</div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-700">Permintaan ini sedang menunggu persetujuan SuperAdmin.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Note: modal behavior is initialized by the parent page JS after insertion -->
</div>