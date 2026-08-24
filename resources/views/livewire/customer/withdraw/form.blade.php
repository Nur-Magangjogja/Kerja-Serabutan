@extends('layouts.app')

@section('content')
<div class="min-h-screen text-gray-900 dark:text-gray-100 pb-20">
    <style>
        [x-cloak] { display: none !important; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Header Bar -->
    <div class="px-4 py-3.5 bg-gradient-to-r from-[#0098e7] via-[#0077cc] to-[#0060b0] text-white shadow-xs rounded-b-2xl">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.dashboard') }}" class="p-2 -ml-1 hover:bg-white/15 rounded-xl transition cursor-pointer" aria-label="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="text-center">
                <h1 class="text-sm font-bold tracking-tight">Tarik Saldo Customer</h1>
                <p class="text-[11px] text-white/80">Tarik sisa dana ke rekening / e-wallet Anda</p>
            </div>

            <a href="{{ route('customer.withdraw.history') }}" class="p-2 -mr-1 hover:bg-white/15 rounded-xl transition cursor-pointer" title="Riwayat Penarikan" aria-label="Riwayat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-md mx-auto px-4 pt-4 space-y-4">
        @php
            $balance = (int) round((float) ($user->balance ?? 0));
            $canWithdraw = $balance >= 10000;
        @endphp

        <!-- Saldo Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider block">Saldo Tersedia</span>
                    <div class="text-xl sm:text-2xl font-black text-primary-600 dark:text-sky-400 truncate mt-0.5">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/40 text-primary-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0">
                    💳
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 dark:text-gray-400">Min. penarikan <strong class="text-gray-700 dark:text-gray-300">Rp 10.000</strong></span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/40 text-[10px]">
                    ✓ Bebas Biaya Admin
                </span>
            </div>
        </div>

        <!-- Status / Flash Message -->
        @if(session('status'))
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-xl shadow-xs flex items-start gap-2.5 text-xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div class="leading-snug">{{ session('status') }}</div>
            </div>
        @endif

        @if($user->hasPendingOrProcessingWithdraws())
            <!-- Pending Card -->
            <div class="bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-5 text-center space-y-2.5 shadow-xs">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center mx-auto text-xl shadow-xs">
                    ⏳
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pengajuan Sedang Diproses</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        Permintaan penarikan dana Anda sedang dalam antrean transfer admin. Mohon tunggu hingga proses selesai.
                    </p>
                </div>
                <div class="pt-1.5">
                    <a href="{{ route('customer.withdraw.history') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        <span>Lihat Riwayat & Status</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @else
            @if(isset($errors) && $errors->any())
                <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-bold text-rose-800 dark:text-rose-300 mb-0.5">Periksa kembali data Anda:</p>
                            <ul class="list-disc pl-4 text-rose-700 dark:text-rose-400 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(!$canWithdraw)
                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-2 text-xs text-amber-800 dark:text-amber-300">
                    <span class="text-sm mt-0.5">⚠️</span>
                    <div>
                        <p class="font-bold">Saldo Belum Mencukupi</p>
                        <p class="mt-0.5">Dibutuhkan saldo minimal <strong>Rp 10.000</strong> untuk mengajukan penarikan dana.</p>
                    </div>
                </div>
            @endif

            <!-- Form Wrapper with Alpine.js -->
            <div x-data="{
                amount: '{{ old('amount', '') }}',
                maxBalance: {{ $balance }},
                minAmount: {{ $minAmount ?? 10000 }},
                bankCode: '{{ old('bank_code', 'BCA') }}',
                bankName: 'Bank Central Asia (BCA)',
                bankCategory: 'Bank',
                bankIcon: '🏦',
                selectedBankFee: 0,
                isPlatformAccount: true,
                customBankInput: '',
                openBankModal: false,
                searchBank: '',
                banks: @js($banks ?? \App\Models\AppSetting::getWithdrawBanks()),
                init() {
                    const match = this.banks.find(b => b.code === this.bankCode);
                    if (match) {
                        this.bankName = match.name;
                        this.bankCategory = match.category;
                        this.bankIcon = match.icon;
                        this.selectedBankFee = Number(match.fee || 0);
                        this.isPlatformAccount = Boolean(match.is_platform_account);
                    }
                    this.$watch('openBankModal', value => {
                        if (value) {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = '';
                        }
                    });
                },
                get filteredBanks() {
                    let list = this.banks.filter(b => b.is_active !== false);
                    if (!this.searchBank.trim()) return list;
                    const q = this.searchBank.toLowerCase();
                    return list.filter(b => b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q));
                },
                selectBank(b) {
                    this.bankCode = b.code;
                    this.bankName = b.name;
                    this.bankCategory = b.category;
                    this.bankIcon = b.icon;
                    this.selectedBankFee = Number(b.fee || 0);
                    this.isPlatformAccount = Boolean(b.is_platform_account);
                    this.openBankModal = false;
                    this.searchBank = '';
                },
                setAmount(val) {
                    if (val > this.maxBalance) val = this.maxBalance;
                    this.amount = val;
                },
                setAllBalance() {
                    this.amount = this.maxBalance;
                }
            }" class="space-y-4">
                <form action="{{ route('customer.withdraw.request') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- 1. Card Input Nominal -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                            <span>Nominal Penarikan</span>
                            <span class="text-[11px] font-normal text-gray-400">Min. Rp {{ number_format($minAmount ?? 10000, 0, ',', '.') }}</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400 dark:text-gray-500">
                                Rp
                            </span>
                            <input
                                type="number"
                                name="amount"
                                x-model="amount"
                                :min="minAmount"
                                max="{{ $balance }}"
                                step="100"
                                placeholder="0"
                                required
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-base font-black text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                            />
                        </div>

                        <!-- Preset Buttons -->
                        <div class="grid grid-cols-4 gap-1.5 pt-1">
                            <button type="button" @click="setAmount(25000)" :disabled="maxBalance < 25000" class="py-1.5 px-2 bg-gray-50 dark:bg-gray-700/50 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-200 border border-gray-200 dark:border-gray-600 rounded-lg text-[11px] font-bold text-gray-700 dark:text-gray-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                25rb
                            </button>
                            <button type="button" @click="setAmount(50000)" :disabled="maxBalance < 50000" class="py-1.5 px-2 bg-gray-50 dark:bg-gray-700/50 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-200 border border-gray-200 dark:border-gray-600 rounded-lg text-[11px] font-bold text-gray-700 dark:text-gray-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                50rb
                            </button>
                            <button type="button" @click="setAmount(100000)" :disabled="maxBalance < 100000" class="py-1.5 px-2 bg-gray-50 dark:bg-gray-700/50 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-200 border border-gray-200 dark:border-gray-600 rounded-lg text-[11px] font-bold text-gray-700 dark:text-gray-300 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                100rb
                            </button>
                            <button type="button" @click="setAllBalance()" :disabled="maxBalance < minAmount" class="py-1.5 px-2 bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800/60 text-primary-600 dark:text-sky-400 hover:bg-primary-100 rounded-lg text-[11px] font-bold transition disabled:opacity-40 disabled:cursor-not-allowed">
                                Semua
                            </button>
                        </div>
                    </div>

                    <!-- 2. Card Rekening / E-Wallet Tujuan -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3.5">
                        <div>
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                                Bank atau E-Wallet Tujuan
                            </label>

                            <!-- Hidden Bank Code input -->
                            <input type="hidden" name="bank_code" :value="bankCode === 'OTHER' ? (customBankInput || 'OTHER') : bankCode" />

                            <!-- Bank Select Trigger -->
                            <button
                                type="button"
                                @click="openBankModal = true"
                                class="w-full px-3.5 py-3 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl flex items-center justify-between text-left transition hover:border-primary-400 dark:hover:border-primary-500 cursor-pointer"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="text-lg" x-text="bankIcon"></span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-gray-900 dark:text-white truncate flex items-center gap-1.5">
                                            <span x-text="bankName"></span>
                                            <template x-if="isPlatformAccount || selectedBankFee === 0">
                                                <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                                                    ✨ Bebas Admin
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500" x-text="selectedBankFee === 0 ? bankCategory + ' • Bebas Biaya (Bank Platform)' : bankCategory + ' • Biaya Transfer: Rp ' + Number(selectedBankFee).toLocaleString('id-ID')"></div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Custom Bank Name input when OTHER is selected -->
                            <template x-if="bankCode === 'OTHER'">
                                <div class="mt-2.5">
                                    <input
                                        type="text"
                                        x-model="customBankInput"
                                        placeholder="Tulis nama bank/e-wallet Anda (Contoh: Bank Nagari / AstraPay)"
                                        required
                                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
                                    />
                                </div>
                            </template>
                        </div>

                        <!-- Nomor Rekening / HP E-Wallet -->
                        <div>
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                                Nomor Rekening / Nomor E-Wallet
                            </label>
                            <input
                                type="text"
                                name="account_number"
                                value="{{ old('account_number') }}"
                                placeholder="Masukkan nomor rekening / no. handphone"
                                required
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                            />
                        </div>

                        <!-- Nama Pemilik Rekening -->
                        <div>
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                                Nama Pemilik Rekening / Akun E-Wallet
                            </label>
                            <input
                                type="text"
                                name="account_name"
                                value="{{ old('account_name', $user->name) }}"
                                placeholder="Nama sesuai buku tabungan / KTP"
                                required
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                            />
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                Pastikan nama pemilik rekening sesuai agar transfer tidak tertolak oleh pihak bank.
                            </p>
                        </div>

                        <!-- Rincian Biaya Transfer & Estimasi Dana Masuk -->
                        <div class="bg-gray-50 dark:bg-gray-750/70 rounded-xl p-3 border border-gray-200/80 dark:border-gray-700/80 space-y-2 text-xs">
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Nominal Penarikan:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200" x-text="'Rp ' + Number(amount || 0).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                <span>Biaya Transfer Bank (Admin):</span>
                                <span class="font-bold" :class="selectedBankFee === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="selectedBankFee === 0 ? '✨ Gratis (Rp 0)' : 'Rp ' + Number(selectedBankFee).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs">
                                <span class="font-bold text-gray-900 dark:text-white">Estimasi Dana Cair:</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm" x-text="'Rp ' + Number(Math.max(0, (amount || 0) - selectedBankFee)).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="!{{ $canWithdraw ? 'true' : 'false' }} || !amount || amount < minAmount || amount > maxBalance"
                            class="w-full py-3.5 px-4 bg-gradient-to-r from-[#0098e7] via-[#0077cc] to-[#0060b0] hover:opacity-95 text-white text-xs font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99]"
                        >
                            <span>Ajukan Penarikan Saldo</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Bank Selection Modal -->
                <div
                    x-show="openBankModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
                    @keydown.escape.window="openBankModal = false"
                >
                    <div
                        @click.away="openBankModal = false"
                        class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-sm max-h-[85vh] flex flex-col shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- Modal Header -->
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between flex-shrink-0">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pilih Bank / E-Wallet</h3>
                                <p class="text-[11px] text-gray-400">Gunakan bank platform untuk bebas biaya admin</p>
                            </div>
                            <button type="button" @click="openBankModal = false" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Search Bar -->
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700/80 flex-shrink-0">
                            <input
                                type="text"
                                x-model="searchBank"
                                placeholder="Cari nama bank atau e-wallet..."
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
                            />
                        </div>

                        <!-- Bank List -->
                        <div class="p-2 overflow-y-auto space-y-1 divide-y divide-gray-50 dark:divide-gray-700/40">
                            <template x-for="b in filteredBanks" :key="b.code">
                                <button
                                    type="button"
                                    @click="selectBank(b)"
                                    class="w-full px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/60 flex items-center justify-between transition text-left cursor-pointer"
                                    :class="bankCode === b.code ? 'bg-primary-50 dark:bg-primary-950/50' : ''"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="text-lg" x-text="b.icon"></span>
                                        <div class="min-w-0">
                                            <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate flex items-center gap-1.5">
                                                <span x-text="b.name"></span>
                                            </div>
                                            <div class="text-[10px] text-gray-400 flex items-center gap-1.5 mt-0.5">
                                                <span x-text="b.category"></span>
                                                <span>•</span>
                                                <template x-if="b.fee === 0 || b.is_platform_account">
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">✨ Bebas Biaya</span>
                                                </template>
                                                <template x-if="b.fee > 0 && !b.is_platform_account">
                                                    <span class="text-gray-500">Admin: Rp <span x-text="Number(b.fee).toLocaleString('id-ID')"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <span x-show="bankCode === b.code" class="text-xs text-primary-600 font-bold">✓</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
