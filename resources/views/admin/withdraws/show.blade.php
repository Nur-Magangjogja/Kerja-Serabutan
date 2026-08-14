@extends('layouts.admin')

@section('content')
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Proses Withdraw</h1>
                    <p class="text-sm text-gray-600 mt-1">Detail permintaan tarik saldo dan aksi approve/reject oleh admin.
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.withdraws.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                        &larr; Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="p-8 space-y-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900">Withdraw #{{ $withdraw->id }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Permintaan dari mitra berikut informasinya.</p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Status</div>
                    @if($withdraw->status === 'pending')
                        <div class="inline-flex items-center px-3 py-1 rounded bg-yellow-100 text-yellow-800">Pending</div>
                    @elseif($withdraw->status === 'processing')
                        <div class="inline-flex items-center px-3 py-1 rounded bg-blue-100 text-blue-800">Processing</div>
                    @elseif($withdraw->status === 'success')
                        <div class="inline-flex items-center px-3 py-1 rounded bg-green-100 text-green-800">Success</div>
                    @else
                        <div class="inline-flex items-center px-3 py-1 rounded bg-red-100 text-red-800">Failed</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded">
                    <div class="text-xs text-gray-500">Mitra</div>
                    <div class="font-semibold">{{ $withdraw->user?->name ?? '-' }} <span class="text-xs text-gray-400">(ID:
                            {{ $withdraw->user_id }})</span></div>
                </div>

                <div class="bg-gray-50 p-4 rounded">
                    <div class="text-xs text-gray-500">Saldo Saat Ini</div>
                    <div class="font-semibold">Rp {{ number_format($withdraw->user?->balance ?? 0, 0, ',', '.') }}</div>
                </div>

                <div class="bg-gray-50 p-4 rounded">
                    <div class="text-xs text-gray-500">Jumlah Permintaan</div>
                    <div class="font-semibold">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded p-4 mb-4">
                <div class="text-sm text-gray-500">Bank / Rekening</div>
                <div class="font-medium">{{ $withdraw->bank_code }} / {{ $withdraw->account_number }}</div>
                <div class="text-sm text-gray-500 mt-3">Keterangan</div>
                <div>{{ $withdraw->description ?? '-' }}</div>
            </div>

            @if($withdraw->status === 'pending')
                    <div class="text-sm text-gray-500">Permintaan ini sedang menunggu persetujuan SuperAdmin.</div>
            @else
                <div class="text-sm text-gray-500">Status: <strong>{{ ucfirst($withdraw->status) }}</strong></div>
                <div class="mt-2">Diproses pada:
                    {{ $withdraw->processed_at ? $withdraw->processed_at->format('Y-m-d H:i') : '-' }}
                </div>
                <div class="mt-2">Referensi: {{ $withdraw->external_id ?? '-' }}</div>
            @endif
        </div>
    </div>

    @if(auth()->user() && auth()->user()->isSuperAdmin())
    <!-- Reject Confirmation Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div id="rejectModalOverlay" class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md mx-4 z-10">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Tolak Permintaan Withdraw</h3>
                <button id="rejectModalClose" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Masukkan catatan penolakan (opsional) dan konfirmasi penolakan.</p>
                <form action="{{ route('superadmin.withdraws.reject', $withdraw) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Catatan Penolakan</label>
                        <input type="text" name="note" class="mt-1 block w-full rounded border-gray-300"
                            placeholder="Contoh: Saldo tidak mencukupi" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="rejectModalCancel"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Konfirmasi Tolak</button>
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

            function showModal() {
                modal.classList.remove('hidden');
            }
            function hideModal() {
                modal.classList.add('hidden');
            }

            if (openBtn) openBtn.addEventListener('click', showModal);
            if (overlay) overlay.addEventListener('click', hideModal);
            if (closeBtn) closeBtn.addEventListener('click', hideModal);
            if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
        })();
    </script>
    @endif
@endsection