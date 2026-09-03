<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Cairkan Penghasilan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Tarik saldo ke rekening bank / e-wallet</p>
                    </div>

                    <a href="{{ route('mitra.withdraw.history') }}" class="p-2 hover:bg-white/20 rounded-xl transition text-xs font-bold flex-shrink-0 flex items-center gap-1" title="Riwayat Pencairan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-5 pb-24 space-y-4">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Kartu Info Saldo --}}
            <div class="bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#005da6] rounded-2xl p-5 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-white/80">Saldo Penghasilan Tersedia</span>
                    <div class="text-2xl font-black mt-1 tracking-tight">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                    <span class="text-[10px] text-white/70 block mt-0.5">Penghasilan dari jasa yang telah diselesaikan</span>
                </div>
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-inner flex-shrink-0">
                    🛵
                </div>
            </div>

            {{-- Form Pencairan --}}
            {{-- Ringkasan Realtime --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600 text-xs space-y-2">
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                        <span>Dana Masuk Rekening (Bersih):</span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Biaya Admin ({{ $selectedBankName }}):</span>
                        @if($adminFee == 0)
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">Rp 0 (Bebas Biaya)</span>
                        @else
                            <span class="font-semibold text-gray-700 dark:text-gray-300">+ Rp {{ number_format($adminFee, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-gray-900 dark:text-white text-sm pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span>Total Saldo yang Dipotong:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-black">Rp {{ number_format($totalDeduction, 0, ',', '.') }}</span>
                    </div>
                </div>
            <form wire:submit="submit" class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nominal yang Ingin Ditarik (Rp) *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-sm">Rp</span>
                        <input type="number" wire:model.live.debounce.200ms="amount" placeholder="Min. {{ number_format($minAmount, 0, ',', '.') }}" step="100" min="{{ $minAmount }}"
                            class="w-full pl-12 pr-4 py-3 text-sm font-bold border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    @error('amount') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Batas pencairan min: Rp {{ number_format($minAmount, 0, ',', '.') }} (Kelipatan Rp 100)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bank / E-Wallet Tujuan *</label>
                    <select wire:model.live="bankCode" class="w-full px-3.5 py-2.5 text-xs font-medium border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($banks->groupBy('category') as $category => $categoryBanks)
                            <optgroup label="{{ $category }}">
                                @foreach($categoryBanks as $b)
                                    <option value="{{ $b['code'] }}">
                                        {{ $b['icon'] ?? '🏦' }} {{ $b['name'] }} ({{ ($b['fee'] ?? 0) == 0 ? 'Bebas Biaya' : 'Biaya: Rp ' . number_format($b['fee'], 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('bankCode') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nomor Rekening / No. E-Wallet *</label>
                    <input type="text" wire:model="accountNumber" placeholder="Contoh: 1234567890 / 08123456789"
                        class="w-full px-3.5 py-2.5 text-xs font-medium border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('accountNumber') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nama Pemilik Rekening / Akun *</label>
                    <input type="text" wire:model="accountName" placeholder="Nama lengkap sesuai buku tabungan / e-wallet..."
                        class="w-full px-3.5 py-2.5 text-xs font-medium border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('accountName') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Cairkan Dana Sekarang</span>
                </button>
            </form>
        </div>
    </div>
</div>
