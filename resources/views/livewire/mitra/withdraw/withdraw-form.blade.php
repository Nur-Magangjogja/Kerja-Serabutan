<div class="max-w-xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-base font-extrabold text-gray-900 dark:text-white">Cairkan Penghasilan Mitra</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tarik penghasilan bantuan Anda ke rekening bank tujuan</p>
        </div>
        <a href="{{ route('mitra.withdraw.history') }}" class="text-xs font-bold text-emerald-600 hover:underline">
            Riwayat Pencairan →
        </a>
    </div>

    {{-- Kartu Info Saldo Mitra --}}
    <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-md flex items-center justify-between">
        <div>
            <span class="text-xs font-medium text-white/80">Saldo Penghasilan Tersedia</span>
            <div class="text-2xl font-black mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</div>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
            🛵
        </div>
    </div>

    {{-- Form Pencairan --}}
    <form wire:submit="submit" class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs space-y-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nominal yang Ingin Ditarik (Rp)</label>
            <input type="number" wire:model.live.debounce.200ms="amount" placeholder="Min. Rp {{ number_format($minAmount, 0, ',', '.') }}"
                class="w-full px-4 py-3 text-sm font-bold border border-gray-200 dark:border-gray-600 rounded-2xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
            @error('amount') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bank Tujuan</label>
                <select wire:model="bankCode" class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="BCA">Bank BCA</option>
                    <option value="BRI">Bank BRI</option>
                    <option value="BNI">Bank BNI</option>
                    <option value="MANDIRI">Bank Mandiri</option>
                    <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                    <option value="CIMB">CIMB Niaga</option>
                    <option value="JAGO">Bank Jago</option>
                    <option value="SEABANK">SeaBank</option>
                    <option value="DANA">DANA (E-Wallet)</option>
                    <option value="GOPAY">GoPay (E-Wallet)</option>
                    <option value="OVO">OVO (E-Wallet)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening / E-Wallet</label>
                <input type="text" wire:model="accountNumber" placeholder="Contoh: 1234567890"
                    class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                @error('accountNumber') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Pemilik Rekening</label>
            <input type="text" wire:model="accountName" placeholder="Nama sesuai rekening bank..."
                class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
            @error('accountName') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Ringkasan Realtime --}}
        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 text-xs space-y-2">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                <span>Biaya Layanan Admin Transfer:</span>
                <span>Rp {{ number_format($adminFee, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between font-extrabold text-gray-900 dark:text-white text-sm pt-2 border-t border-gray-200 dark:border-gray-600">
                <span>Jumlah Bersih yang Diterima:</span>
                <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold transition shadow-md cursor-pointer">
            Cairkan Dana Sekarang
        </button>
    </form>
</div>
