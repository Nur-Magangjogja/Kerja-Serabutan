<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
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
                    <h1 class="text-base font-bold truncate">Top-Up Saldo</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Isi saldo via QRIS (Bebas Biaya Admin)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="px-5 pt-5 pb-8 max-w-md mx-auto">
        @if (session()->has('error'))
            <div class="mb-4 p-3.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 rounded-xl text-red-700 dark:text-red-300 text-sm flex items-center gap-2.5 shadow-xs">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session()->has('success'))
            <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl text-blue-700 dark:text-blue-300 text-sm flex items-center gap-2.5 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Progress Steps -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 1 ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm mb-1 transition-colors">
                    1
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 1 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">Nominal</span>
            </div>
            <div class="flex-1 h-1 {{ $currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }} -mt-5 transition-colors"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 2 ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm mb-1 transition-colors">
                    2
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 2 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">Konfirmasi</span>
            </div>
            <div class="flex-1 h-1 {{ $currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }} -mt-5 transition-colors"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 3 ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm mb-1 transition-colors">
                    3
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 3 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">Scan QRIS</span>
            </div>
        </div>

        <!-- Step 1: Form Pengisian Data -->
        @if ($currentStep === 1)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <form wire:submit.prevent="nextStep" class="space-y-4">
                    <!-- Nominal -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Nominal Top-Up *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                            <input type="number" wire:model.live="amount" wire:change="calculateFees"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 dark:placeholder-gray-500 font-semibold"
                                placeholder="50000" min="100" max="10000000" step="100">
                        </div>
                        @error('amount') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block font-medium">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Minimal Rp. 10.000 - 10.000.000</p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-5 gap-2">
                        <button type="button" wire:click="setQuickAmount(20000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition cursor-pointer {{ $amount == 20000 ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                            20K
                        </button>
                        <button type="button" wire:click="setQuickAmount(50000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition cursor-pointer {{ $amount == 50000 ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                            50K
                        </button>
                        <button type="button" wire:click="setQuickAmount(100000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition cursor-pointer {{ $amount == 100000 ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                            100K
                        </button>
                        <button type="button" wire:click="setQuickAmount(200000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition cursor-pointer {{ $amount == 200000 ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                            200K
                        </button>
                        <button type="button" wire:click="setQuickAmount(500000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition cursor-pointer {{ $amount == 500000 ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                            500K
                        </button>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Nama Lengkap *</label>
                        <input type="text" wire:model="customerName"
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 font-medium"
                            readonly>
                        @error('customerName') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Nomor Telepon *</label>
                        <input type="tel" wire:model="customerPhone"
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="081234567892">
                        @error('customerPhone') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" wire:model="customerEmail"
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="budi@example.com">
                        @error('customerEmail') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('customer.dashboard') }}"
                            class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-600 transition cursor-pointer">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition shadow-md shadow-blue-500/20 cursor-pointer">
                            Lanjutkan →
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Step 2: Detail & Konfirmasi -->
        @if ($currentStep === 2)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Rincian Pembayaran -->
                <div class="bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] p-5 text-white">
                    <h3 class="text-xs font-bold uppercase tracking-wider mb-4 opacity-90">RINCIAN PEMBAYARAN</h3>
                    <div class="space-y-3">
                        <div class="border-t border-white/20 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-sm">Total Transfer QRIS</span>
                                <span class="font-extrabold text-2xl">Rp {{ number_format($totalPayment, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Data Pengirim -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Data Akun Pemohon
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Nama:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $customerName }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Telepon:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $customerPhone }}</span>
                            </div>
                            @if($customerEmail)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $customerEmail }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Metode Bayar:</span>
                                <span class="font-bold text-primary-600 dark:text-primary-400">QRIS (All Bank & E-Wallet)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Penting Alert -->
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl p-4 shadow-xs">
                        <div class="flex gap-2.5">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-xs text-blue-900 dark:text-blue-200 space-y-1">
                                <p class="font-bold mb-1 text-blue-950 dark:text-blue-100">Informasi Pembayaran:</p>
                                <p>• Pada langkah selanjutnya, scan kode QRIS dan transfer sebesar <strong>Rp {{ number_format($totalPayment, 0, ',', '.') }}</strong>.</p>
                                <p>• Unggah bukti transfer (screenshot struk berhasil).</p>
                                <p>• Saldo akan ditambahkan setelah diverifikasi oleh admin.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="previousStep"
                            class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition cursor-pointer">
                            ← Kembali
                        </button>
                        <button type="button" wire:click="nextStep"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition shadow-md shadow-blue-500/20 cursor-pointer">
                            Lanjut ke QRIS →
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Scan QRIS & Upload Bukti Pembayaran -->
        @if ($currentStep === 3)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-5">
                <!-- Total Amount Banner -->
                <div class="bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] text-white rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-xs opacity-90 mb-1">Total yang Harus Ditransfer</p>
                    <p class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalPayment, 0, ',', '.') }}</p>
                    <span class="inline-block mt-1 px-2 py-0.5 text-[11px] font-bold bg-white/20 rounded-full">
                        Bebas Biaya Admin (0% Pajak)
                    </span>
                </div>

                @if(empty($qrisImage) || !$qrisEnabled)
                    <!-- Alert QRIS Belum Dikonfigurasi -->
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-2xl flex items-start gap-3.5 text-amber-900 dark:text-amber-200">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="text-xs space-y-1">
                            <p class="font-bold text-sm text-amber-950 dark:text-amber-100">Metode QRIS Belum Tersedia</p>
                            <p>Saat ini Super Admin belum mengunggah barcode QRIS untuk pengisian saldo. Pengisian saldo belum dapat diproses secara mandiri. Silakan hubungi admin atau customer service untuk bantuan pengisian saldo manual.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="previousStep"
                            class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition cursor-pointer">
                            ← Kembali
                        </button>
                        <button type="button" disabled
                            class="flex-1 px-6 py-3 bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl font-semibold cursor-not-allowed">
                            QRIS Belum Tersedia
                        </button>
                    </div>
                @else
                    <!-- QRIS Card Display -->
                    <div class="border-2 border-blue-500/40 dark:border-blue-500/30 bg-blue-50/40 dark:bg-blue-950/20 rounded-2xl p-4 sm:p-5 text-center space-y-3">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-lg">📱</span>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                SCAN QRIS PEMBAYARAN
                            </h3>
                        </div>

                        @php
                            $displayQrisUrl = str_starts_with($qrisImage, 'images/') 
                                ? asset($qrisImage) 
                                : asset('storage/' . $qrisImage);
                        @endphp

                        <!-- QR Box Frame -->
                        <div class="bg-white p-3.5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 inline-block mx-auto max-w-[270px] w-full">
                            <img src="{{ $displayQrisUrl }}" 
                                alt="QRIS Barcode" 
                                class="w-56 h-56 object-contain rounded-xl mx-auto">
                            
                            <div class="mt-2.5 pt-2 border-t border-gray-100 text-center">
                                <p class="text-xs font-bold text-gray-900 truncate">
                                    {{ $qrisMerchantName ?: 'PT SayaBantu' }}
                                </p>
                                @if($qrisNmid)
                                    <p class="text-[10px] text-gray-500 font-mono mt-0.5">NMID: {{ $qrisNmid }}</p>
                                @endif
                            </div>

                            <!-- Tombol Unduh QRIS -->
                            <div class="mt-3 pt-2 border-t border-gray-100">
                                <a href="{{ $displayQrisUrl }}" 
                                   download="QRIS-SayaBantu.png" 
                                   target="_blank"
                                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-3 bg-blue-50 hover:bg-blue-100 active:bg-blue-200 text-blue-700 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer border border-blue-200">
                                    <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Unduh Gambar QRIS</span>
                                </a>
                            </div>
                        </div>

                        <!-- Petunjuk Pembayaran -->
                        <div class="text-xs text-gray-600 dark:text-gray-300 bg-white/80 dark:bg-gray-800/80 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700 text-left space-y-1.5">
                            <p class="font-bold text-gray-900 dark:text-white">Cara Pembayaran:</p>
                            <p>1. <strong>Unduh QRIS</strong> dengan tombol di atas (jika bertransaksi di HP yang sama), atau scan langsung barcode menggunakan HP lain.</p>
                            <p>2. Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, DANA, ShopeePay, LinkAja).</p>
                            <p>3. Pilih menu <strong>Scan QR / Bayar</strong>, lalu pilih gambar QRIS dari <strong>Galeri Foto</strong> HP Anda.</p>
                            <p>4. Masukkan nominal transfer persis <strong>Rp {{ number_format($totalPayment, 0, ',', '.') }}</strong> dan selesaikan pembayaran.</p>
                            <p>5. Unggah screenshot / struk bukti transfer pada form di bawah ini.</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="submitRequest" class="space-y-4">
                        <!-- Upload Bukti Transfer -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <span>📷</span> Upload Bukti Transfer QRIS <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/60 dark:bg-gray-700/30 rounded-xl p-5 text-center hover:border-blue-400 dark:hover:border-blue-500 transition relative">
                                <input type="file" wire:model="proofOfPayment" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" class="hidden" id="proofUpload">
                                
                                @if ($proofOfPayment)
                                    <label for="proofUpload" class="cursor-pointer block">
                                        <div class="w-full rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800">
                                            @php
                                                $canPreview = false;
                                                try {
                                                    $canPreview = method_exists($proofOfPayment, 'temporaryUrl') && $proofOfPayment->isPreviewable();
                                                } catch (\Throwable $e) {
                                                    $canPreview = false;
                                                }
                                            @endphp
                                            @if ($canPreview)
                                                <img src="{{ $proofOfPayment->temporaryUrl() }}" alt="Preview Bukti" class="w-full max-h-56 object-contain mx-auto p-1">
                                            @else
                                                <div class="w-full h-36 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 p-2 text-center">
                                                    <span class="text-xs font-semibold">{{ $proofOfPayment->getClientOriginalName() }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="text-xs text-primary-600 dark:text-primary-400 font-semibold mt-2">Ketuk untuk mengganti file bukti</p>
                                    </label>
                                @else
                                    <label for="proofUpload" class="cursor-pointer block">
                                        <svg class="w-10 h-10 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Bukti Pembayaran QRIS</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, JPEG, atau PNG (Maks 2MB)</p>
                                    </label>
                                @endif
                            </div>
                            @error('proofOfPayment') <span class="text-xs text-red-600 dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="proofOfPayment" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                Memproses unggahan bukti...
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="previousStep"
                                class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition cursor-pointer">
                                ← Kembali
                            </button>
                            <button type="submit"
                                class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl font-semibold transition shadow-md shadow-blue-500/20 cursor-pointer disabled:opacity-50"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Kirim Bukti Pembayaran</span>
                                <span wire:loading>Mengirim Request...</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
