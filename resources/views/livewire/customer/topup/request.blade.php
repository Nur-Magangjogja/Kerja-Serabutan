<div class="min-h-screen bg-gray-50">
    <!-- Header (smaller) -->
    <div class="px-5 pt-4 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
        <div class="absolute top-0 right-0 w-28 h-28 bg-white/5 rounded-full -mr-12 -mt-12"></div>
        <div class="absolute bottom-0 left-0 w-20 h-20 bg-white/5 rounded-full -ml-8 -mb-8"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between text-white mb-4">
                <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-center flex-1 px-2">
                    <h1 class="text-base font-semibold">Top-Up Saldo</h1>
                    <p class="text-xs text-white/90 mt-0.5">Isi saldo Anda dengan cepat</p>
                </div>

                <a href="{{ route('customer.topup.history') }}" class="p-2 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Curved separator (reduced) -->
        <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 56" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0,24 C360,56 1080,0 1440,32 L1440,56 L0,56 Z" fill="#f9fafb"></path>
        </svg>
    </div>

    <!-- Content -->
    <div class="bg-gray-50 -mt-6 px-5 pt-6 pb-6 max-w-md mx-auto">
        @if (session()->has('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-100 rounded-lg text-red-700 text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-700 text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Progress Steps -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 1 ? 'bg-blue-600' : 'bg-gray-300' }} flex items-center justify-center text-white font-bold text-sm mb-1">
                    1
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 1 ? 'text-blue-600' : 'text-gray-500' }}">Data</span>
            </div>
            <div class="flex-1 h-1 {{ $currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} -mt-5"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} flex items-center justify-center text-white font-bold text-sm mb-1">
                    2
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 2 ? 'text-blue-600' : 'text-gray-500' }}">Detail</span>
            </div>
            <div class="flex-1 h-1 {{ $currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-300' }} -mt-5"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-10 h-10 rounded-full {{ $currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-300' }} flex items-center justify-center text-white font-bold text-sm mb-1">
                    3
                </div>
                <span class="text-xs font-medium {{ $currentStep >= 3 ? 'text-blue-600' : 'text-gray-500' }}">Bayar</span>
            </div>
        </div>

        <!-- Step 1: Form Pengisian Data -->
        @if ($currentStep === 1)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form wire:submit.prevent="nextStep" class="space-y-4">
                    <!-- Nominal -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Nominal Top-Up *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 font-medium">Rp</span>
                            <input type="number" wire:model.live="amount" wire:change="calculateFees"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                placeholder="50000" min="10000" max="10000000">
                        </div>
                        @error('amount') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1.5">Min: Rp 10.000 - Max: Rp 10.000.000</p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-5 gap-2">
                        <button type="button" wire:click="setQuickAmount(20000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition {{ $amount == 20000 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                            20K
                        </button>
                        <button type="button" wire:click="setQuickAmount(50000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition {{ $amount == 50000 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                            50K
                        </button>
                        <button type="button" wire:click="setQuickAmount(100000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition {{ $amount == 100000 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                            100K
                        </button>
                        <button type="button" wire:click="setQuickAmount(200000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition {{ $amount == 200000 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                            200K
                        </button>
                        <button type="button" wire:click="setQuickAmount(500000)"
                            class="px-2 py-2.5 text-sm font-bold rounded-lg border-2 transition {{ $amount == 500000 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                            500K
                        </button>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Lengkap *</label>
                        <input type="text" wire:model="customerName"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700"
                            readonly>
                        @error('customerName') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Nomor Telepon *</label>
                        <input type="tel" wire:model="customerPhone"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="081234567892">
                        @error('customerPhone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email (opsional)</label>
                        <input type="email" wire:model="customerEmail"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="budi@example.com">
                        @error('customerEmail') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                        <textarea wire:model="customerNotes" rows="3"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        @error('customerNotes') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('customer.dashboard') }}"
                            class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold text-center hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm">
                            Lanjutkan →
                        </button>
                    </div>
                    @if(session('topup_form_data'))
                        <button type="button" wire:click="resetFormData"
                            onclick="return confirm('Yakin ingin reset semua data form?')"
                            class="w-full px-4 py-2 text-xs text-red-600 hover:text-red-700 rounded-lg hover:bg-red-50 font-medium transition border border-red-200">
                            Reset Data
                        </button>
                    @endif
                </form>
            </div>
        @endif

        <!-- Step 2: Detail Pembayaran -->
        @if ($currentStep === 2)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Rincian Pembayaran -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-5 text-white">
                    <h3 class="text-sm font-semibold mb-4 opacity-90">RINCIAN PEMBAYARAN</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Nominal Top-Up</span>
                            <span class="font-bold text-lg">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Biaya Admin</span>
                            <span class="font-bold">Rp {{ number_format($adminFee, 0, ',', '.') }}</span>
                        </div>
                        @if($uniqueCode)
                            <div class="flex justify-between items-center mt-1 opacity-90">
                                <span class="text-xs">Biaya Pengembangan</span>
                                <span class="text-sm font-semibold">Rp {{ number_format(intval($uniqueCode), 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-t border-white/20 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Total Bayar </span>
                                <span class="font-bold text-2xl">Rp {{ number_format($uniqueTotal ?: $totalPayment, 0, ',', '.') }}</span>
                            </div>
                            @if($uniqueCode)
                                <p class="text-xs mt-2 opacity-90">
                                    {{-- Kode unik: <span class="font-mono font-bold">{{ $uniqueCode }}
                                        </span>  --}}
                                        Mohon transfer tepat sesuai nominal di atas (tidak boleh lebih dan tidak boleh kurang).</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Data Pengirim -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Data Pengirim
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama:</span>
                                <span class="font-semibold text-gray-900">{{ $customerName }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Telepon:</span>
                                <span class="font-semibold text-gray-900">{{ $customerPhone }}</span>
                            </div>
                            @if($customerEmail)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-semibold text-gray-900">{{ $customerEmail }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Penting -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                            </svg>
                            <div class="text-xs text-amber-900 space-y-1">
                                <p class="font-bold mb-1">Informasi Penting:</p>
                                <p>• Transfer sesuai nominal total (termasuk kode unik)</p>
                                <p>• Saldo masuk setelah verifikasi admin</p>
                                <p>• Maksimal 1x24 jam untuk approval</p>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="previousStep"
                            class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                            ← Kembali
                        </button>
                        <button type="button" wire:click="nextStep"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm">
                            Lanjutkan →
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Metode Pembayaran & Upload Bukti -->
        @if ($currentStep === 3)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <!-- Total yang harus dibayar -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-4 mb-5 text-center">
                    <p class="text-xs opacity-90 mb-1">Total Pembayaran</p>
                    <p class="text-3xl font-bold">Rp {{ number_format($uniqueTotal ?: $totalPayment, 0, ',', '.') }}</p>
                    @if($uniqueCode)
                        {{-- <p class="text-sm mt-2">Kode unik: <span class="font-mono font-bold">{{ $uniqueCode }}</span></p> --}}
                        <p class="text-xs mt-1">Pastikan transfer tepat Rp {{ number_format($uniqueTotal ?: $totalPayment, 0, ',', '.') }} — tidak boleh lebih dan tidak boleh kurang.</p>
                    @endif
                </div>

                <form wire:submit.prevent="submitRequest" class="space-y-5">
                    <!-- QRIS -->
                    @if($qrisEnabled)
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <span class="text-lg">📱</span> E-WALLET (QRIS)
                            </h3>
                            <div wire:click="selectPaymentMethod('qris')"
                                class="border-2 {{ $paymentMethod === 'qris' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }} rounded-lg p-4 cursor-pointer transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-2xl">💳</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">QRIS - All E-Wallet</p>
                                            <p class="text-xs text-gray-600">GoPay, OVO, Dana, ShopeePay, dll</p>
                                        </div>
                                    </div>
                                    @if($paymentMethod === 'qris')
                                        <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($paymentMethod === 'qris')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="text-center">
                                            <p class="text-sm font-semibold text-gray-900 mb-3">Scan QR Code</p>
                                            @if(file_exists(public_path('images/payment/qris.png')))
                                                <img src="{{ asset('images/payment/qris.png') }}" 
                                                    alt="QRIS" 
                                                    class="w-48 h-48 mx-auto border border-gray-200 rounded-lg">
                                            @else
                                                <div class="w-48 h-48 mx-auto border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                                    <div class="text-center">
                                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                        </svg>
                                                        <p class="text-xs text-gray-500">QR belum tersedia</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Transfer Bank -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="text-lg"></span> TRANSFER BANK
                        </h3>
                        <div class="space-y-2">
                            @foreach($availableBanks as $bank)
                                <div wire:click="selectPaymentMethod('bank_{{ strtolower($bank['code']) }}')"
                                    class="border-2 {{ $paymentMethod === 'bank_'.strtolower($bank['code']) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }} rounded-lg p-3 cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border-2 border-gray-200 flex-shrink-0">
                                            <span class="text-xs font-extrabold text-gray-700">{{ strtoupper($bank['code']) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-gray-900 text-sm">{{ $bank['name'] }}</p>
                                            <p class="text-sm text-gray-700 font-mono">{{ $bank['account_number'] }}</p>
                                            <p class="text-xs text-gray-500">a.n. {{ $bank['account_name'] }}</p>
                                        </div>
                                        @if($paymentMethod === 'bank_'.strtolower($bank['code']))
                                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    @if($paymentMethod === 'bank_'.strtolower($bank['code']))
                                        <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ $bank['account_number'] }}'); alert('Nomor rekening disalin!')"
                                            class="w-full mt-3 px-3 py-2 text-xs font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            Salin Nomor Rekening
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @error('paymentMethod') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror

                    <!-- Upload Bukti Transfer -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="text-lg"></span> Upload Bukti Transfer *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition relative">
                            <input type="file" wire:model="proofOfPayment" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" class="hidden" id="proofUpload">
                            @if ($proofOfPayment)
                                <label for="proofUpload" class="cursor-pointer block">
                                    <div class="w-full rounded-lg overflow-hidden border border-gray-200 bg-white">
                                        @php
                                            $canPreviewTopup = false;
                                            try {
                                                $canPreviewTopup = method_exists($proofOfPayment, 'temporaryUrl') && $proofOfPayment->isPreviewable();
                                            } catch (\Throwable $e) {
                                                $canPreviewTopup = false;
                                            }
                                        @endphp
                                        @if ($canPreviewTopup)
                                            <img src="{{ $proofOfPayment->temporaryUrl() }}" alt="Preview" class="w-full h-48 object-cover">
                                        @else
                                            <div class="w-full h-48 flex flex-col items-center justify-center bg-gray-100 text-gray-700 p-2 text-center">
                                                <span class="text-xs font-semibold">{{ $proofOfPayment->getClientOriginalName() }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Ketuk untuk mengganti file</p>
                                </label>
                            @else
                                <label for="proofUpload" class="cursor-pointer block">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-gray-700">Klik untuk upload</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, JPEG, atau PNG (Max 2MB)</p>
                                </label>
                            @endif
                        </div>
                        @error('proofOfPayment') <span class="text-xs text-red-600 mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="previousStep"
                            class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                            ← Kembali
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Kirim Request</span>
                            <span wire:loading>Mengirim...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
        </div>
    </div>
</div>

