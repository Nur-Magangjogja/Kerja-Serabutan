@extends('layouts.superadmin')

@section('content')
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Withdraw</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Permintaan tarik saldo dari mitra</p>
        </div>
    </div>

    @if(session('status'))
    <div class="flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('status') }}
    </div>
    @endif

    {{-- ===== Summary Cards ===== --}}
    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total Request</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">{{ $counts['all'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Pending</p>
                <p class="text-lg sm:text-xl font-bold text-amber-700 dark:text-amber-400 truncate">{{ $counts['pending'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Processing</p>
                <p class="text-lg sm:text-xl font-bold text-blue-700 dark:text-blue-400 truncate">{{ $counts['processing'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Success</p>
                <p class="text-lg sm:text-xl font-bold text-emerald-700 dark:text-emerald-400 truncate">{{ $counts['success'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        {{-- Filter bar --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('superadmin.withdraws.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status</label>
                    <select name="status"
                        class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">User</label>
                    <input type="text" name="user" value="{{ request('user') }}" placeholder="ID atau nama"
                        class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Bank</label>
                    <select name="bank_code"
                        class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Semua Bank</option>
                        @foreach(($banks ?? []) as $b)
                        <option value="{{ $b }}" {{ request('bank_code') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Dari</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('superadmin.withdraws.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mitra</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bank / Rekening</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($items as $item)
                    @php
                    $statusConfig = match($item->status) {
                        'pending'    => ['class' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',   'label' => 'Pending'],
                        'processing' => ['class' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',       'label' => 'Processing'],
                        'success'    => ['class' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400', 'label' => 'Success'],
                        default      => ['class' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',       'label' => 'Failed'],
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $item->id }}</td>
                        <td class="px-4 py-3.5">
                            @if($item->user)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $item->user->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">ID: {{ $item->user_id }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right font-bold text-gray-800 dark:text-gray-100">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">{{ $item->bank_code }} / {{ $item->account_number }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <button data-id="{{ $item->id }}"
                                class="open-withdraw-modal inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat / Proses
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal container --}}
<div id="superadmin-withdraw-modal-container"></div>

<script>
(function () {
    function openModal(id) {
        var container = document.getElementById('superadmin-withdraw-modal-container');
        fetch('/superadmin/withdraws/' + id + '/modal')
            .then(function (res) { return res.text(); })
            .then(function (html) {
                container.innerHTML = html;
                initSuperadminWithdrawModal(container);
                window.scrollTo(0, 0);
            })
            .catch(function (err) { console.error('Failed to load modal:', err); });
    }

    document.querySelectorAll('.open-withdraw-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            openModal(id);
        });
    });
})();

function initSuperadminWithdrawModal(container) {
    var modal = container.querySelector('#superadmin-withdraw-modal');
    if (!modal) return;
    var overlay = modal.querySelector('#superadmin-withdraw-modal-overlay');
    var closeBtn = modal.querySelector('#close-superadmin-withdraw-modal');
    function removeModal() { container.innerHTML = ''; document.body.style.overflow = ''; }
    try {
        if (window.Alpine && typeof Alpine.initTree === 'function') Alpine.initTree(modal);
    } catch (e) { console.warn('Alpine initTree failed', e); }

    try {
        var openRejectBtn = modal.querySelector('#open-reject-local');
        var fallback = modal.querySelector('#withdraw-reject-modal-fallback');
        var closeFallback = modal.querySelector('#close-reject-fallback');
        var cancelFallback = modal.querySelector('#cancel-reject-fallback');
        var overlayFallback = modal.querySelector('#withdraw-reject-fallback-overlay');
        function showFallback() { if (!fallback) return; fallback.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
        function hideFallback() { if (!fallback) return; fallback.classList.add('hidden'); document.body.style.overflow = ''; }
        if (openRejectBtn) openRejectBtn.addEventListener('click', function () { showFallback(); try { window.dispatchEvent(new CustomEvent('open-reject')); } catch (e) {} });
        if (closeFallback) closeFallback.addEventListener('click', hideFallback);
        if (cancelFallback) cancelFallback.addEventListener('click', hideFallback);
        if (overlayFallback) overlayFallback.addEventListener('click', hideFallback);
    } catch (e) { console.warn('reject handler failed', e); }

    if (closeBtn) closeBtn.addEventListener('click', removeModal);
    if (overlay) overlay.addEventListener('click', removeModal);
    document.body.style.overflow = 'hidden';
}
</script>
@endsection
