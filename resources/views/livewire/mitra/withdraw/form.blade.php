@extends('layouts.mitra')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="max-w-md mx-auto">
            <!-- Header Section -->
            <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
                <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

                <div class="relative z-10 max-w-md mx-auto">
                    <div class="flex items-center justify-between text-white">
                        <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div class="text-center flex-1 min-w-0 px-2">
                            <h1 class="text-base font-bold truncate">Tarik Saldo</h1>
                            <p class="text-xs text-white/90 truncate mt-0.5">Ajukan penarikan dana dengan mudah</p>
                        </div>

                        <a href="{{ route('mitra.withdraw.history') }}" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0" aria-label="Riwayat Penarikan">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-5 pt-5 pb-8">
                <!-- Balance Card -->
                <div class="mb-5 bg-primary-600 dark:bg-gray-800 rounded-xl p-5 text-white shadow-sm border border-primary-700 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs opacity-90 mb-1">Saldo Tersedia</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($user->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-white/20">
                        <p class="text-xs opacity-75">Minimum penarikan: <span class="font-semibold">Rp 10.000</span></p>
                    </div>
                </div>

        <!-- Status Messages -->
        @if(session('status'))
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-950/40 border-l-4 border-blue-500 text-blue-800 dark:text-blue-200 rounded-lg shadow-sm flex items-start">
                <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        @endif

        @if($user->hasPendingOrProcessingWithdraws())
            <!-- Pending Withdrawal Notice -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pengajuan Sedang Diproses</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                    Pengajuan tarik saldo Anda sedang diproses oleh admin. Mohon tunggu 1-5 hari kerja. 
                    Anda akan menerima notifikasi ketika status berubah.
                </p>
                <a href="{{ route('mitra.withdraw.history') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Lihat Riwayat
                </a>
            </div>
        @else
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-red-800 dark:text-red-300 text-sm mb-1">Terdapat kesalahan:</p>
                            <ul class="list-disc pl-5 text-sm text-red-700 dark:text-red-400 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Withdrawal Form -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-5">
                <form action="{{ route('mitra.withdraw.request') }}" method="POST">
                    @csrf
                    
                    <!-- Amount Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Jumlah Penarikan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium text-sm">Rp</span>
                            <input type="number" name="amount" min="10000" value="{{ old('amount') }}" required
                                class="pl-10 w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" 
                                placeholder="0" />
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Minimum penarikan adalah Rp 10.000</p>
                    </div>

                    <!-- Bank Code Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Kode Bank</label>
                        <input type="text" name="bank_code" required 
                            class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Contoh: BCA, BNI, Mandiri" />
                    </div>

                    <!-- Account Number Input -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Nomor Rekening</label>
                        <input type="text" name="account_number" required
                            class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Masukkan nomor rekening tujuan" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <a href="{{ route('mitra.withdraw.history') }}" class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                            Riwayat
                        </a>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-all cursor-pointer">
                            Ajukan Penarikan
                        </button>
                    </div>
                </form>
            </div>
        @endif
            </div>
        </div>
    </div>
@endsection