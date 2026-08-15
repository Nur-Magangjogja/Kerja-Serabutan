@section('page-title', 'Approval Top-Up Saldo')
@section('page-description', 'Verifikasi dan approve request top-up dari customer')

<div>
<div wire:poll.15s.visible>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Approval Top-Up Saldo</h1>
                <p class="text-sm text-gray-500 mt-1">Monitoring request top-up dari customer (Read Only)</p>
                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs font-semibold text-blue-700">Admin hanya bisa melihat data. Approval dilakukan oleh Super Admin.</span>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500 bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-2.5 rounded-xl border border-green-200 shadow-sm">
                <svg class="w-4 h-4 animate-pulse text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold text-green-700">Auto-refresh setiap 10 detik</span>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-start gap-2">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Pending</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $pendingRequests->total() }}</h3>
                    <p class="text-gray-400 text-xs mt-1">Request menunggu</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Approved Hari Ini</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">0</h3>
                    <p class="text-gray-400 text-xs mt-1">Transaksi disetujui</p>
                </div>
                <div class="bg-green-50 p-4 rounded-xl">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Nominal</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($pendingRequests->sum('amount'), 0, ',', '.') }}</h3>
                    <p class="text-gray-400 text-xs mt-1">Menunggu approval</p>
                </div>
                <div class="bg-orange-50 p-4 rounded-xl">
                    <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    @if($pendingRequests->total() > 0)
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="bg-blue-400 p-2 rounded-lg">
                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-900">Informasi</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Terdapat <strong class="font-bold">{{ $pendingRequests->total() }}</strong> request top-up yang menunggu verifikasi. 
                        <strong>Hubungi Super Admin untuk melakukan approval.</strong>
                    </p>
                </div>
            </div>
        </div>
    @endif

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Kode Request
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Nominal
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Total Bayar
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Metode
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Waktu
                            </th>
                            <th class="px-6 py-5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pendingRequests as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $transaction->id }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 flex-shrink-0">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                                {{ substr($transaction->user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $transaction->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $transaction->customer_email ?? $transaction->user->email }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $transaction->user->city->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-mono font-semibold text-gray-900">
                                        {{ $transaction->request_code ?? '#'.$transaction->id }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $transaction->customer_phone ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        +Rp {{ number_format($transaction->admin_fee, 0, ',', '.') }} admin
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-bold text-primary-600">
                                        Rp {{ number_format($transaction->total_payment, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $transaction->payment_method ?? 'QRIS' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $transaction->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $transaction->created_at->format('H:i') }}
                                    </div>
                                    @if($transaction->expired_at)
                                        <div class="text-xs text-red-600 mt-1">
                                            Exp: {{ \Carbon\Carbon::parse($transaction->expired_at)->format('d M, H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                            wire:click="viewDetail({{ $transaction->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 transition-all duration-200 disabled:opacity-50 shadow-sm hover:shadow"
                                            title="Lihat Bukti">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span wire:loading.remove wire:target="viewDetail({{ $transaction->id }})">Lihat Detail</span>
                                            <span wire:loading wire:target="viewDetail({{ $transaction->id }})">...</span>
                                        </button>
                                        
                                        <!-- Disabled buttons with tooltip -->
                                        <div class="relative group">
                                            <button type="button" 
                                                disabled
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed opacity-50"
                                                title="Hanya Super Admin yang bisa approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Approve</span>
                                            </button>
                                            <div class="hidden group-hover:block absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap">
                                                Hanya Super Admin yang bisa approve
                                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                                    <div class="border-4 border-transparent border-t-gray-900"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak Ada Request Pending</h3>
                                        <p class="text-sm text-gray-500">Semua request top-up sudah diproses</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pendingRequests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pendingRequests->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">Bukti Transfer</h2>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <!-- Transaction Info -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600 mb-1">Customer:</p>
                                <p class="font-semibold text-gray-900">{{ $selectedTransaction->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-1">Kode Request:</p>
                                <p class="font-semibold text-gray-900">{{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-1">Nominal:</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-1">Total Bayar:</p>
                                <p class="font-semibold text-primary-600">Rp {{ number_format($selectedTransaction->total_payment, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Proof Image -->
                    @if($selectedTransaction->proof_of_payment)
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-2">Bukti Transfer:</p>
                            <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" 
                                class="w-full rounded-xl shadow-lg border border-gray-200"
                                alt="Bukti Transfer"
                                onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2218%22 fill=%22%239ca3af%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
                            <div class="mt-2 text-xs text-gray-400">
                                Path: {{ $selectedTransaction->proof_of_payment }}
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Bukti transfer tidak tersedia</p>
                    @endif

                    <!-- Info message for admin -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-900">Informasi</p>
                                <p class="text-sm text-blue-800 mt-1">
                                    Admin hanya dapat melihat detail request. Untuk approval atau penolakan, hubungi <strong>Super Admin</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal - Hidden for admin -->
    @if($showRejectModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
            <div class="bg-white rounded-3xl w-full max-w-md" wire:click.stop>
                <div class="p-6">
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Akses Terbatas</h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Admin tidak memiliki akses untuk approve atau reject request.<br>
                            Silakan hubungi <strong>Super Admin</strong>.
                        </p>
                        <button type="button" wire:click="closeModal"
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
