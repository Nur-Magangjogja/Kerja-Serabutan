<!-- Nominal Imbalan Pekerjaan (Khusus Kerja di Lokasi / On-Site) -->
@if($service_type !== 'pickup_delivery')
    <div id="group-amount" class="space-y-2">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
            <span class="flex items-center justify-between">
                <span class="flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    </svg>
                    Nominal Imbalan Pekerjaan
                    <span class="text-red-500 ml-1">*</span>
                </span>
                <span class="text-[11px] font-normal text-gray-500 dark:text-gray-400">
                    Min. Rp {{ number_format($minHelpNominal ?? 10000, 0, ',', '.') }}
                </span>
            </span>
        </label>

        <!-- Custom Stepper Input (Formatted with Dot Thousand Separator) -->
        <div class="flex items-center rounded-xl border @error('amount') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 bg-white dark:bg-gray-800 @enderror p-1.5 shadow-sm transition">
            <!-- Decrement Button (-1000) -->
            <button type="button" wire:click="adjustAmount(-1000)" title="Kurangi Rp 1000" class="w-11 h-11 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:scale-95 text-gray-700 dark:text-gray-200 font-bold text-xl flex items-center justify-center transition flex-shrink-0 cursor-pointer">
                −
            </button>

            <!-- Input Nominal with Dot Masking -->
            <div x-data="{
                rawAmount: @entangle('amount').live,
                formattedAmount: '',
                formatNumber(val) {
                    if (val === null || val === undefined || val === '') return '';
                    let digits = String(val).replace(/[^0-9]/g, '');
                    if (!digits) return '';
                    let num = parseInt(digits, 10);
                    return isNaN(num) ? '' : num.toLocaleString('id-ID');
                },
                handleInput(e) {
                    let digits = e.target.value.replace(/[^0-9]/g, '');
                    let num = digits ? parseInt(digits, 10) : 0;
                    this.rawAmount = num;
                    this.formattedAmount = digits ? num.toLocaleString('id-ID') : '';
                },
                init() {
                    this.formattedAmount = this.formatNumber(this.rawAmount);
                    this.$watch('rawAmount', val => {
                        this.formattedAmount = this.formatNumber(val);
                    });
                }
            }" class="relative flex-1 px-3">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-base">Rp</span>
                <input type="text"
                    inputmode="numeric"
                    x-model="formattedAmount"
                    @input="handleInput($event)"
                    id="amount-input"
                    placeholder="{{ number_format($minHelpNominal ?? 10000, 0, ',', '.') }}"
                    class="w-full pl-8 pr-2 py-1.5 text-center font-bold text-lg text-gray-900 dark:text-white border-none focus:ring-0 focus:outline-none bg-transparent">
            </div>

            <!-- Increment Button (+1000) -->
            <button type="button" wire:click="adjustAmount(1000)" title="Tambah Rp 1000" class="w-11 h-11 rounded-lg bg-blue-50 dark:bg-blue-900/40 hover:bg-blue-100 dark:hover:bg-blue-800/60 active:scale-95 text-blue-600 dark:text-blue-400 font-bold text-xl flex items-center justify-center transition flex-shrink-0 cursor-pointer">
                +
            </button>
        </div>

        <!-- Quick Adjustments Section (Tambah & Kurangi) -->
        <div class="space-y-2 pt-1">
            <!-- Tambah Cepat Grid -->
            <div class="space-y-1">
                <div class="flex items-center justify-between px-0.5 text-[11px] font-semibold text-blue-600 dark:text-blue-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </span>
                </div>
                <div class="grid grid-cols-5 gap-1 sm:gap-1.5">
                    <button type="button" wire:click="adjustAmount(5000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 transition border border-blue-200/70 dark:border-blue-800/70 active:scale-95 cursor-pointer">
                        +5 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(10000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 transition border border-blue-200/70 dark:border-blue-800/70 active:scale-95 cursor-pointer">
                        +10 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(20000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 transition border border-blue-200/70 dark:border-blue-800/70 active:scale-95 cursor-pointer">
                        +20 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(50000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 transition border border-blue-200/70 dark:border-blue-800/70 active:scale-95 cursor-pointer">
                        +50 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(100000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 transition border border-blue-200/70 dark:border-blue-800/70 active:scale-95 cursor-pointer">
                        +100 rb
                    </button>
                </div>
            </div>

            <!-- Kurangi Cepat Grid -->
            <div class="space-y-1">
                <div class="flex items-center justify-between px-0.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        Kurangi 
                    </span>
                </div>
                <div class="grid grid-cols-5 gap-1 sm:gap-1.5">
                    <button type="button" wire:click="adjustAmount(-5000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-rose-50/80 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition border border-rose-200/70 dark:border-rose-900/60 active:scale-95 cursor-pointer">
                        −5 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(-10000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-rose-50/80 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition border border-rose-200/70 dark:border-rose-900/60 active:scale-95 cursor-pointer">
                        −10 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(-20000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-rose-50/80 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition border border-rose-200/70 dark:border-rose-900/60 active:scale-95 cursor-pointer">
                        −20 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(-50000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-rose-50/80 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition border border-rose-200/70 dark:border-rose-900/60 active:scale-95 cursor-pointer">
                        −50 rb
                    </button>
                    <button type="button" wire:click="adjustAmount(-100000)" class="w-full py-1.5 px-0.5 text-center text-[11px] sm:text-xs font-semibold rounded-lg bg-rose-50/80 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition border border-rose-200/70 dark:border-rose-900/60 active:scale-95 cursor-pointer">
                        −100 rb
                    </button>
                </div>
            </div>
        </div>

        @error('amount')
            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block flex items-center font-medium">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ $message }}
            </span>
        @enderror
    </div>
@endif
