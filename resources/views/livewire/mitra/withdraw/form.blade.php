@extends('layouts.mitra')

@section('content')
<div class="min-h-screen text-gray-900 dark:text-gray-100">
    <style>
        [x-cloak] { display: none !important; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Header Bar -->
    <div class="px-4 py-3.5 bg-gradient-to-r from-[#0098e7] via-[#0077cc] to-[#0060b0] text-white shadow-xs rounded-b-2xl">
        <div class="flex items-center justify-between">
            <a href="{{ route('mitra.dashboard') }}" class="p-2 -ml-1 hover:bg-white/15 rounded-xl transition cursor-pointer" aria-label="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="text-center">
                <h1 class="text-sm font-bold tracking-tight">Tarik Saldo Mitra</h1>
                <p class="text-[11px] text-white/80">Cairkan pendapatan ke rekening / e-wallet</p>
            </div>

            <a href="{{ route('mitra.withdraw.history') }}" class="p-2 -mr-1 hover:bg-white/15 rounded-xl transition cursor-pointer" title="Riwayat Penarikan" aria-label="Riwayat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="px-4 pt-3.5 pb-36 space-y-3.5">
        @php
            $balance = (int) round((float) ($user->balance ?? 0));
            $canWithdraw = $balance >= 10000;
        @endphp

        <!-- Saldo Compact Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs">
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
                    ✓ Bebas Admin
                </span>
            </div>
        </div>

        <!-- Status / Errors -->
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
                        Permintaan penarikan Anda sedang dalam antrean transfer admin.
                    </p>
                </div>
                <div class="pt-1.5">
                    <a href="{{ route('mitra.withdraw.history') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        <span>Lihat Riwayat & Status</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @else
            @if($errors->any())
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

            <!-- Form Wrapper with Alpine state & Background scroll-lock -->
            <div x-data="{
                amount: '{{ old('amount', '') }}',
                maxBalance: {{ $balance }},
                bankCode: '{{ old('bank_code', 'BCA') }}',
                bankName: 'Bank Central Asia (BCA)',
                bankCategory: 'Bank',
                bankIcon: '🏦',
                customBankInput: '',
                openBankModal: false,
                searchBank: '',
                banks: [
                    { code: 'BCA', name: 'Bank Central Asia (BCA)', category: 'Bank', icon: '🏦' },
                    { code: 'BRI', name: 'Bank Rakyat Indonesia (BRI)', category: 'Bank', icon: '🏦' },
                    { code: 'BNI', name: 'Bank Negara Indonesia (BNI)', category: 'Bank', icon: '🏦' },
                    { code: 'MANDIRI', name: 'Bank Mandiri', category: 'Bank', icon: '🏦' },
                    { code: 'BSI', name: 'Bank Syariah Indonesia (BSI)', category: 'Bank', icon: '🏦' },
                    { code: 'CIMB', name: 'CIMB Niaga', category: 'Bank', icon: '🏦' },
                    { code: 'PERMATA', name: 'Bank Permata', category: 'Bank', icon: '🏦' },
                    { code: 'DANAMON', name: 'Bank Danamon', category: 'Bank', icon: '🏦' },
                    { code: 'SEABANK', name: 'SeaBank Indonesia', category: 'Bank', icon: '🏦' },
                    { code: 'JAGO', name: 'Bank Jago', category: 'Bank', icon: '🏦' },
                    { code: 'GOPAY', name: 'GoPay', category: 'E-Wallet', icon: '📱' },
                    { code: 'OVO', name: 'OVO', category: 'E-Wallet', icon: '📱' },
                    { code: 'DANA', name: 'DANA', category: 'E-Wallet', icon: '📱' },
                    { code: 'SHOPEEPAY', name: 'ShopeePay', category: 'E-Wallet', icon: '📱' },
                    { code: 'LINKAJA', name: 'LinkAja', category: 'E-Wallet', icon: '📱' },
                    { code: 'OTHER', name: 'Bank / E-Wallet Lainnya', category: 'Lainnya', icon: '💳' }
                ],
                get filteredBanks() {
                    if (!this.searchBank) return this.banks;
                    var q = this.searchBank.toLowerCase();
                    return this.banks.filter(function(b) {
                        return b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q) || b.category.toLowerCase().includes(q);
                    });
                },
                selectBank(item) {
                    this.bankCode = item.code;
                    this.bankName = item.name;
                    this.bankCategory = item.category;
                    this.bankIcon = item.icon;
                    this.openBankModal = false;
                },
                setAmount(val) {
                    if (val === 'all') {
                        this.amount = this.maxBalance;
                    } else {
                        this.amount = Math.min(val, this.maxBalance);
                    }
                }
            }"
            x-init="$watch('openBankModal', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            })"
            class="space-y-3.5">
                
                <form action="{{ route('mitra.withdraw.request') }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 shadow-xs space-y-3.5">
                    @csrf
                    
                    <!-- Nominal Penarikan -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nominal Penarikan *</label>
                            <span class="text-[10px] text-gray-400">Min. Rp 10.000</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-sm">Rp</span>
                            <input type="number" name="amount" x-model="amount" min="10000" max="{{ $balance }}" required
                                class="pl-9 w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white font-bold text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" 
                                placeholder="0" />
                        </div>

                        <!-- Quick Nominal Chips -->
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @if($balance >= 25000)
                                <button type="button" @click="setAmount(25000)" class="px-2 py-1 text-[10px] font-bold rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-500 bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                    Rp 25.000
                                </button>
                            @endif
                            @if($balance >= 50000)
                                <button type="button" @click="setAmount(50000)" class="px-2 py-1 text-[10px] font-bold rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-500 bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                    Rp 50.000
                                </button>
                            @endif
                            @if($balance >= 100000)
                                <button type="button" @click="setAmount(100000)" class="px-2 py-1 text-[10px] font-bold rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-500 bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                    Rp 100.000
                                </button>
                            @endif
                            @if($balance >= 10000)
                                <button type="button" @click="setAmount('all')" class="px-2 py-1 text-[10px] font-bold rounded-lg bg-blue-50 dark:bg-blue-900/40 text-primary-700 dark:text-primary-300 border border-blue-200 dark:border-blue-800/60 hover:bg-blue-100 transition cursor-pointer ml-auto">
                                    ⚡ Tarik Semua
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Bank Destination Custom Trigger (NO glitchy native select) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bank / E-Wallet Tujuan *</label>
                        
                        <!-- Hidden form field sent to server -->
                        <input type="hidden" name="bank_code" :value="bankCode === 'OTHER' && customBankInput ? customBankInput : bankCode">

                        <!-- Trigger Button -->
                        <button type="button" @click="openBankModal = true"
                            class="w-full flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 rounded-xl text-left transition cursor-pointer">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base" x-text="bankIcon"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="bankName"></div>
                                    <span class="text-[9px] text-gray-400 uppercase tracking-wider" x-text="bankCategory"></span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Custom Bank Input if 'OTHER' selected -->
                    <div x-show="bankCode === 'OTHER'" x-cloak>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Bank / Fintech Lainnya *</label>
                        <input type="text" x-model="customBankInput" placeholder="Contoh: Bank BJB, Bank Nagari, dll"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                    </div>

                    <!-- Nomor Rekening -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening / E-Wallet *</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition"
                            placeholder="Contoh: 1234567890 (atau 0812xxxxxx untuk e-wallet)" />
                    </div>

                    <!-- Nama Pemilik Rekening -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Pemilik Rekening *</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $user->name) }}" required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition"
                            placeholder="Nama sesuai rekening / KTP" />
                    </div>

                    <!-- Rincian Penarikan Box -->
                    <div class="bg-gray-50 dark:bg-gray-750 border border-gray-200/60 dark:border-gray-700/60 rounded-xl p-3 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>Nominal Penarikan</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="amount ? 'Rp ' + Number(amount).toLocaleString('id-ID') : 'Rp 0'"></span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>Biaya Admin</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Gratis (Rp 0)</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-1.5 font-bold text-xs text-gray-900 dark:text-white">
                            <span>Total Diterima</span>
                            <span class="text-primary-600 dark:text-sky-400" x-text="amount ? 'Rp ' + Number(amount).toLocaleString('id-ID') : 'Rp 0'"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-1">
                        <button type="submit" @if(!$canWithdraw) disabled @endif
                            class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer text-center">
                            Ajukan Penarikan Dana
                        </button>
                    </div>
                </form>

                <!-- Bank Selection Modal (Isolated Dialog with Scroll Containment) -->
                <div x-show="openBankModal" x-cloak 
                    class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overscroll-contain"
                    @click="openBankModal = false"
                    @wheel.prevent
                    @touchmove.stop>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-sm max-h-[75vh] flex flex-col shadow-2xl overflow-hidden text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-700 overscroll-contain"
                        @click.stop
                        @wheel.stop
                        @touchmove.stop>
                        
                        <!-- Modal Header with Search Filter -->
                        <div class="p-3.5 border-b border-gray-100 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Pilih Bank / E-Wallet</h3>
                                <button type="button" @click="openBankModal = false" class="p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" x-model="searchBank" placeholder="Cari bank atau e-wallet..."
                                    class="w-full pl-8 pr-3 py-1.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Scrollable Bank List (Isolated scroll) -->
                        <div class="p-3 overflow-y-auto max-h-[50vh] space-y-1 hide-scrollbar overscroll-contain">
                            <template x-for="item in filteredBanks" :key="item.code">
                                <button type="button" @click="selectBank(item)"
                                    class="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-750 transition cursor-pointer border border-transparent text-left"
                                    :class="bankCode === item.code ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800/60' : ''">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="text-base" x-text="item.icon"></span>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="item.name"></div>
                                            <span class="text-[9px] text-gray-400 uppercase tracking-wider" x-text="item.category"></span>
                                        </div>
                                    </div>
                                    <div x-show="bankCode === item.code" class="text-primary-600 dark:text-sky-400 font-bold text-xs">
                                        ✓
                                    </div>
                                </button>
                            </template>

                            <div x-show="filteredBanks.length === 0" class="text-center py-6 text-xs text-gray-400">
                                Bank tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Petunjuk Penarikan -->
        <div class="bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-2xl p-3.5 space-y-1.5">
            <div class="flex items-center gap-1.5 text-xs font-bold text-blue-900 dark:text-blue-200">
                <span>ℹ️</span>
                <span>Ketentuan Penarikan</span>
            </div>
            <ul class="text-[11px] text-blue-800 dark:text-blue-300 space-y-1 pl-4 list-disc leading-relaxed">
                <li>Permintaan transfer diproses admin pada jam operasional (1 s.d. 24 jam).</li>
                <li>Pastikan nomor rekening & nama pemilik rekening sesuai kartu identitas.</li>
            </ul>
        </div>
    </div>
</div>
@endsection