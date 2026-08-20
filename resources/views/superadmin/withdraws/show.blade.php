@extends('layouts.superadmin')

@section('content')
    <div class="bg-white dark:bg-gray-800 shadow-xs border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Proses Withdraw</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Detail permintaan tarik saldo dan aksi approve/reject oleh SuperAdmin.</p>
                </div>
                <div>
                    <a href="{{ route('superadmin.withdraws.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium">
                        &larr; Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Withdraw #{{ $withdraw->id }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Permintaan dari mitra berikut informasinya.</p>
                </div>
                <div class="md:text-right">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                    @if($withdraw->status === 'pending')
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">Pending</div>
                    @elseif($withdraw->status === 'processing')
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">Processing</div>
                    @elseif($withdraw->status === 'success')
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">Success</div>
                    @else
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">Failed</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Mitra</div>
                    <div class="font-semibold text-gray-900 dark:text-white mt-1">{{ $withdraw->user?->name ?? '-' }} <span class="text-xs text-gray-400 dark:text-gray-500">(ID: {{ $withdraw->user_id }})</span></div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Saldo Saat Ini</div>
                    <div class="font-semibold text-gray-900 dark:text-white mt-1">Rp {{ number_format($withdraw->user?->balance ?? 0, 0, ',', '.') }}</div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Jumlah Permintaan</div>
                    <div class="font-semibold text-primary-600 dark:text-primary-400 mt-1">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Bank / Rekening</div>
                <div class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $withdraw->bank_code }} / {{ $withdraw->account_number }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-3">Keterangan</div>
                <div class="text-gray-800 dark:text-gray-200 mt-0.5">{{ $withdraw->description ?? '-' }}</div>
            </div>

            @if($withdraw->status === 'pending')
                <div class="flex flex-col md:flex-row gap-4">
                    <form action="{{ route('superadmin.withdraws.approve', $withdraw) }}" method="POST"
                        class="flex-1 bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 rounded-xl">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Referensi Transfer (opsional)</label>
                            <input type="text" name="transfer_reference" class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400" />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan (opsional)</label>
                            <input type="text" name="note" class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400" />
                        </div>
                        <div class="flex items-center justify-end">
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-xs">Approve &amp; Potong Saldo</button>
                        </div>
                    </form>

                    <div class="w-full md:w-56 bg-white dark:bg-gray-800 p-4 border border-rose-200 dark:border-rose-900/50 rounded-xl flex flex-col justify-between">
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">Jika ingin menolak permintaan ini, klik tombol di bawah.</p>
                        <button id="open-reject-modal" class="w-full px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg">Tolak</button>
                    </div>
                </div>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400">Status: <strong class="text-gray-900 dark:text-white">{{ ucfirst($withdraw->status) }}</strong></div>
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">Diproses pada:
                    {{ $withdraw->processed_at ? $withdraw->processed_at->format('Y-m-d H:i') : '-' }}
                </div>
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">Referensi: {{ $withdraw->external_id ?? '-' }}</div>
            @endif
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div id="rejectModalOverlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tolak Permintaan Withdraw</h3>
                <button id="rejectModalClose" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white text-xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Masukkan catatan penolakan (opsional) dan konfirmasi penolakan.</p>
                <form action="{{ route('superadmin.withdraws.reject', $withdraw) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Penolakan</label>
                        <input type="text" name="note" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400"
                            placeholder="Contoh: Saldo tidak mencukupi" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="rejectModalCancel"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg">Konfirmasi Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var openBtn = document.getElementById('open-reject-modal');
            var modal = document.getElementById('rejectModal');
            var overlay = document.getElementById('rejectModalOverlay');
            var closeBtn = document.getElementById('rejectModalClose');
            var cancelBtn = document.getElementById('rejectModalCancel');

            function showModal() { modal.classList.remove('hidden'); }
            function hideModal() { modal.classList.add('hidden'); }

            if (openBtn) openBtn.addEventListener('click', showModal);
            if (overlay) overlay.addEventListener('click', hideModal);
            if (closeBtn) closeBtn.addEventListener('click', hideModal);
            if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
        })();
    </script>
@endsection
