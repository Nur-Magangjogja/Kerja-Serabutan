<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Cairkan Penghasilan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Tarik saldo ke rekening bank / e-wallet</p>
                    </div>

                    <a href="{{ route('mitra.withdraw.history') }}" wire:navigate class="absolute right-0 top-1/2 -translate-y-1/2 z-20 p-2 hover:bg-white/20 rounded-xl transition text-xs font-bold flex items-center gap-1" title="Riwayat Pencairan">
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
                <div class="min-w-0 pr-2">
                    <span class="text-xs font-medium text-white/80">Saldo Penghasilan Tersedia</span>
                    <div class="{{ ($balance ?? 0) >= 10000000 ? 'text-lg sm:text-xl' : (($balance ?? 0) >= 1000000 ? 'text-xl sm:text-2xl' : 'text-2xl') }} font-black mt-1 tracking-tight whitespace-nowrap">Rp {{ number_format($balance, 0, ',', '.') }}</div>
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
                        @if($adminFee == 0 || $isPlatform)
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md flex items-center gap-1">
                                <span>Rp 0</span>
                                @if($isPlatform)
                                    <span class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-300"></span>
                                @else
                                    <span class="text-[10px] font-semibold">(Gratis)</span>
                                @endif
                            </span>
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
                    <div x-data="{
                        rawAmount: @entangle('amount').live,
                        formattedAmount: '',
                        formatNumber(val) {
                            if (val === null || val === undefined || val === '') return '';
                            let digits = String(val).replace(/[^0-9]/g, '').slice(0, 12);
                            if (!digits) return '';
                            let num = parseInt(digits, 10);
                            return isNaN(num) ? '' : num.toLocaleString('id-ID');
                        },
                        handleInput(e) {
                            let digits = e.target.value.replace(/[^0-9]/g, '').slice(0, 12);
                            let num = digits ? parseInt(digits, 10) : '';
                            this.rawAmount = num;
                            this.formattedAmount = digits ? parseInt(digits, 10).toLocaleString('id-ID') : '';
                        },
                        init() {
                            this.formattedAmount = this.formatNumber(this.rawAmount);
                            this.$watch('rawAmount', val => {
                                this.formattedAmount = this.formatNumber(val);
                            });
                        }
                    }" class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-sm">Rp</span>
                        <input type="text"
                            inputmode="numeric"
                            maxlength="16"
                            x-model="formattedAmount"
                            @input="handleInput($event)"
                            placeholder="Min. {{ number_format($minAmount, 0, ',', '.') }}"
                            class="w-full pl-12 pr-4 py-3 text-sm font-bold border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    @error('amount') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Batas pencairan min: Rp {{ number_format($minAmount, 0, ',', '.') }} (Kelipatan Rp 100)</p>
                </div>

                <div x-data="{
                    open: false,
                    search: '',
                    bankCode: @entangle('bankCode').live,
                    banks: @js($banks),
                    get currentBank() {
                        return this.banks.find(b => b.code.toUpperCase() === String(this.bankCode).toUpperCase()) || this.banks[0] || { code: 'BCA', name: 'Pilih Bank / E-Wallet', category: 'Bank', icon: '🏦', fee: 0, is_platform_account: true };
                    },
                    get filteredBanks() {
                        if (!this.search.trim()) return this.banks;
                        const q = this.search.toLowerCase();
                        return this.banks.filter(b => b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q) || (b.category && b.category.toLowerCase().includes(q)));
                    },
                    get categories() {
                        const list = this.filteredBanks;
                        const cats = [];
                        list.forEach(b => {
                            const cat = b.category || 'Lainnya';
                            if (!cats.includes(cat)) cats.push(cat);
                        });
                        return cats;
                    },
                    getBanksByCategory(cat) {
                        return this.filteredBanks.filter(b => (b.category || 'Lainnya') === cat);
                    },
                    selectBank(code) {
                        this.bankCode = code;
                        this.open = false;
                        this.search = '';
                    }
                }">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bank / E-Wallet Tujuan *</label>

                    <!-- Trigger Button (Compact, In-Flow, No-Overflow) -->
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl flex items-center justify-between text-left transition hover:border-emerald-400 dark:hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer shadow-2xs"
                        :class="open ? 'ring-2 ring-emerald-500 border-emerald-500 bg-white dark:bg-gray-750' : ''"
                    >
                        <div class="flex items-center gap-2.5 min-w-0 flex-1 pr-2">
                            <span class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-600/80 flex items-center justify-center text-sm shrink-0 shadow-2xs" x-text="currentBank.icon || '🏦'"></span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate flex items-center gap-1.5">
                                    <span class="truncate" x-text="currentBank.name"></span>
                                </div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5 truncate mt-0.5">
                                    <span x-text="currentBank.category || 'Bank'"></span>
                                    <span>•</span>
                                    <template x-if="currentBank.is_platform_account || Number(currentBank.fee || 0) === 0">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">✨ Bebas Biaya Admin</span>
                                    </template>
                                    <template x-if="!currentBank.is_platform_account && Number(currentBank.fee || 0) > 0">
                                        <span class="text-gray-500 dark:text-gray-400">Biaya: Rp <span x-text="Number(currentBank.fee).toLocaleString('id-ID')"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 text-gray-400 dark:text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    <!-- Inline In-Flow Expandable Panel (Memanjang ke bawah dalam alur form) -->
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="mt-2 bg-gray-50/90 dark:bg-gray-750/90 rounded-2xl border border-gray-200/90 dark:border-gray-600/90 shadow-2xs overflow-hidden"
                    >
                        <!-- Search Bar -->
                        <div class="p-2.5 border-b border-gray-200/80 dark:border-gray-600/80 bg-white/60 dark:bg-gray-800/60">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    x-model="search"
                                    x-ref="searchInput"
                                    x-init="$watch('open', val => { if(val) $nextTick(() => $refs.searchInput.focus()) })"
                                    placeholder="Cari bank atau e-wallet (BCA, DANA, GoPay)..."
                                    class="w-full pl-8 pr-7 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <button
                                    type="button"
                                    x-show="search.length > 0"
                                    @click="search = ''"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Categorized List -->
                        <div class="max-h-60 sm:max-h-72 overflow-y-auto dropdown-scrollbar p-1.5 pr-2 divide-y divide-gray-200/50 dark:divide-gray-700/50 space-y-1">
                            <template x-if="filteredBanks.length === 0">
                                <div class="py-6 text-center text-xs text-gray-400 dark:text-gray-500">
                                    <span class="text-lg block mb-1">🔍</span>
                                    Tidak ada bank / e-wallet yang cocok dengan "<span class="font-semibold text-gray-700 dark:text-gray-300" x-text="search"></span>"
                                </div>
                            </template>

                            <template x-for="cat in categories" :key="cat">
                                <div class="pt-1.5 first:pt-0">
                                    <div class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-400 flex items-center gap-1.5" x-text="cat"></div>
                                    <div class="space-y-0.5 mt-0.5">
                                        <template x-for="b in getBanksByCategory(cat)" :key="b.code">
                                            <button
                                                type="button"
                                                @click="selectBank(b.code)"
                                                class="w-full px-2.5 py-2 rounded-xl flex items-center justify-between text-left transition cursor-pointer group"
                                                :class="String(bankCode).toUpperCase() === b.code.toUpperCase() ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold ring-1 ring-emerald-300 dark:ring-emerald-700' : 'hover:bg-white dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200'"
                                            >
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1 pr-2">
                                                    <span class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-600/70 group-hover:bg-white dark:group-hover:bg-gray-600 flex items-center justify-center text-sm shrink-0" x-text="b.icon || '🏦'"></span>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-xs font-semibold truncate" x-text="b.name"></div>
                                                        <div class="text-[10px] text-gray-400 dark:text-gray-400 flex items-center gap-1 truncate">
                                                            <template x-if="b.is_platform_account || Number(b.fee || 0) === 0">
                                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">✨ Bebas Admin</span>
                                                            </template>
                                                            <template x-if="!b.is_platform_account && Number(b.fee || 0) > 0">
                                                                <span>Admin: Rp <span x-text="Number(b.fee).toLocaleString('id-ID')"></span></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="shrink-0 flex items-center">
                                                    <span x-show="String(bankCode).toUpperCase() === b.code.toUpperCase()" class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black">
                                                        ✓
                                                    </span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
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
