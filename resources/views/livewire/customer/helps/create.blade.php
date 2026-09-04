<div>
    <style>
        :root {
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        .card-shadow {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.2);
        }

        .header-pattern {
            position: relative;
            overflow: hidden;
        }

        .header-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        /* Leaflet Map Styles */
        #map {
            height: 280px !important;
            min-height: 280px;
            z-index: 1;
        }
        
        .leaflet-container {
            height: 100%;
            width: 100%;
            border-radius: 0.75rem;
        }

        /* Hilangkan panah spinner default pada input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <div id="main-content" class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="max-w-md mx-auto">
            <!-- Header Section -->
            <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
                <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

                <div class="relative z-10 text-white text-center">
                    <h1 class="text-base font-bold truncate">Buat Permintaan Baru</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Isi form di bawah untuk membuat permintaan</p>
                </div>
            </div>

            <!-- Content -->
            <div class="px-5 pt-5 pb-8">
                {{-- Floating Validation Error Banner --}}
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" x-init="scrollToFirstError(); setTimeout(() => show = false, 6000)"
                         class="mb-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 dark:border-red-500 p-3.5 rounded-r-xl shadow-sm flex items-start justify-between gap-3 animate-fade-in border border-red-100 dark:border-red-900/50">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-red-800 dark:text-red-300">Permintaan Belum Bisa Dikirim</p>
                                <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">Mohon lengkapi dan perbaiki kolom yang bertanda merah di bawah ini.</p>
                            </div>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 text-base font-bold leading-none cursor-pointer">&times;</button>
                    </div>
                @endif

                <form wire:submit.prevent="prepareConfirm" enctype="multipart/form-data" class="space-y-5">
                    <!-- Title -->
                    <div class="pt-1 pb-1" id="group-title">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                        clip-rule="evenodd" />
                                </svg>
                                Judul Bantuan
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input type="text" wire:model="title" id="title-input"
                            placeholder="Contoh: Butuh Bantuan Kerja Serabutan Kupas Bawang"
                            class="w-full px-4 py-3 text-sm rounded-lg border @error('title') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                        @error('title')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                        @php
                            $feeCalc = \App\Models\AppSetting::calculatePlatformFee((float) ($amount ?: 0));
                        @endphp
                        @if((float) ($amount ?: 0) > 0)
                            <div class="mt-3 p-3 bg-blue-50/70 dark:bg-blue-950/30 rounded-xl border border-blue-100 dark:border-blue-900/40 text-xs space-y-1.5">
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Imbalan Rekan Jasa :</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((float)$amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Biaya Layanan Platform (Tetap) :</span>
                                    <span class="font-semibold text-primary-600 dark:text-primary-400">+ Rp {{ number_format($feeCalc['fee_amount'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-900 dark:text-white pt-1.5 border-t border-blue-200/60 dark:border-blue-800/40 text-sm">
                                    <span>Total Saldo yang Dibutuhkan:</span>
                                    <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($feeCalc['total'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif
                    <!-- Amount (Nominal Uang) -->
                    <div id="group-amount">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                </svg>
                                Nominal Imbalan untuk Rekan Jasa
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>

                        <!-- Custom Stepper Input (Tanpa Panah Bawaan Browser) -->
                        <div class="flex items-center rounded-xl border @error('amount') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 bg-white dark:bg-gray-800 @enderror p-1.5 shadow-sm transition">
                            <!-- Decrement Button -->
                            <button type="button" wire:click="adjustAmount(-100)" title="Kurangi Rp 100" class="w-11 h-11 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:scale-95 text-gray-700 dark:text-gray-200 font-bold text-xl flex items-center justify-center transition flex-shrink-0 cursor-pointer">
                                −
                            </button>

                            <!-- Input Nominal -->
                            <div class="relative flex-1 px-3">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-base">Rp</span>
                                <input type="number" wire:model.live="amount" id="amount-input" placeholder="{{ $minHelpNominal ?? 10000 }}" min="{{ $minHelpNominal ?? 10000 }}" step="100"
                                    class="w-full pl-8 pr-2 py-1.5 text-center font-bold text-lg text-gray-900 dark:text-white border-none focus:ring-0 focus:outline-none bg-transparent">
                            </div>

                            <!-- Increment Button -->
                            <button type="button" wire:click="adjustAmount(100)" title="Tambah Rp 100" class="w-11 h-11 rounded-lg bg-blue-50 dark:bg-blue-900/40 hover:bg-blue-100 dark:hover:bg-blue-800/60 active:scale-95 text-blue-600 dark:text-blue-400 font-bold text-xl flex items-center justify-center transition flex-shrink-0 cursor-pointer">
                                +
                            </button>
                        </div>

                        <!-- Quick Increment Pills -->
                        <div class="flex items-center gap-1.5 mt-2.5 overflow-x-auto pb-1 scrollbar-hide">
                            <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 flex-shrink-0">Tambah:</span>
                            <button type="button" wire:click="adjustAmount(5000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +5 rb
                            </button>
                            <button type="button" wire:click="adjustAmount(10000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +10 rb
                            </button>
                            <button type="button" wire:click="adjustAmount(20000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +20 rb
                            </button>
                            <button type="button" wire:click="adjustAmount(50000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +50 rb
                            </button>
                            <button type="button" wire:click="adjustAmount(100000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +100 rb
                            </button>
                        </div>

                        <!-- Quick Preset Buttons -->
                        <div class="grid grid-cols-4 gap-2 mt-2">
                            <button type="button" wire:click="setPresetAmount(25000)" class="py-1.5 text-xs font-semibold rounded-lg border transition cursor-pointer {{ $amount == 25000 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                25 rb
                            </button>
                            <button type="button" wire:click="setPresetAmount(50000)" class="py-1.5 text-xs font-semibold rounded-lg border transition cursor-pointer {{ $amount == 50000 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                50 rb
                            </button>
                            <button type="button" wire:click="setPresetAmount(75000)" class="py-1.5 text-xs font-semibold rounded-lg border transition cursor-pointer {{ $amount == 75000 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                75 rb
                            </button>
                            <button type="button" wire:click="setPresetAmount(100000)" class="py-1.5 text-xs font-semibold rounded-lg border transition cursor-pointer {{ $amount == 100000 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                100 rb
                            </button>
                        </div>

                        <div class="mt-2 space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Minimal Rp {{ number_format($minHelpNominal ?? 10000, 0, ',', '.') }} 
                            </p>
                        </div>
                        @error('amount')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- City -->
                    <div id="group-city">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                Kota / Kabupaten
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                wire:model.live.debounce.300ms="cityQuery" 
                                placeholder="Ketik nama kota atau kabupaten..."
                                class="w-full px-4 py-3 text-sm rounded-lg border @error('city_id') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" 
                                autocomplete="off"
                                id="city-search-input">
                            
                            <input type="hidden" wire:model="city_id">

                            <!-- Search Icon -->
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Dropdown Results -->
                            @if (!empty($searchResults))
                                <ul class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto z-50">
                                    @foreach ($searchResults as $c)
                                        <li wire:click="setCityId({{ $c['id'] }}, '{{ addslashes($c['name']) }}', '{{ addslashes($c['province']) }}')"
                                            class="px-4 py-3 text-sm hover:bg-blue-50 dark:hover:bg-gray-700/80 cursor-pointer transition border-b border-gray-100 dark:border-gray-700/60 last:border-b-0 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="flex-1">
                                                @if(!empty($c['display']))
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $c['display'] }}</div>
                                                @else
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $c['name'] }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c['province'] }}</div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif (empty($searchResults) && !empty($cityQuery) && strlen($cityQuery) >= 2 && empty($city_id))
                                <div class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 z-50">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Kota tidak ditemukan
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Selected City Display -->
                        @if (!empty($city_id))
                            <div class="mt-2 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg p-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium text-blue-900 dark:text-blue-200 text-xs">{{ $cityQuery }}</span>
                                </div>
                                <button type="button" wire:click="clearCity" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-800 transition cursor-pointer">
                                    Ganti Kota
                                </button>
                            </div>
                        @endif

                        @error('city_id')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                        
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Ketik minimal 2 karakter untuk mencari kota Anda
                        </p>
                    </div>

                    <!-- Tandai Lokasi di Peta -->
                    <div id="group-map">
                        <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    Titik Lokasi Bantuan
                                    <span class="text-red-500 ml-1">*</span>
                                </span>
                            </label>
                            <button type="button" onclick="locateUserGPS()" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-800/60 transition shadow-sm active:scale-95 cursor-pointer">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.071-7.071l-1.414 1.414M8.343 15.657l-1.414 1.414m12.728 0l-1.414-1.414M8.343 8.343L6.929 6.929M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                                Gunakan Lokasi GPS
                            </button>
                        </div>

                        <!-- Map Container -->
                        <div class="relative rounded-xl overflow-hidden border @error('latitude') border-red-500 ring-2 ring-red-500/30 @else border-gray-300 dark:border-gray-700 @enderror shadow-inner bg-gray-100 dark:bg-gray-800 mb-2">
                            <div wire:ignore id="map" style="height: 280px; min-height: 280px;" class="w-full"></div>
                        </div>

                        <!-- Koordinat Display & Status Geocoding -->
                        <div id="coordinates-display"
                            class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg p-2.5 mb-2 hidden flex items-center justify-between flex-wrap gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>Titik GPS: <span id="lat-display" class="font-mono font-semibold">-</span>, <span id="lng-display" class="font-mono font-semibold">-</span></span>
                            </div>
                            <span id="gps-status-pill" class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold">Tersimpan</span>
                        </div>

                        <!-- Hidden inputs for Livewire coordinates -->
                        <input type="hidden" wire:model="latitude" id="latitude-input">
                        <input type="hidden" wire:model="longitude" id="longitude-input">

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Klik pada peta atau geser pin biru untuk menentukan titik lokasi bantuan.
                        </p>

                        @error('latitude')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block font-medium flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Detail Lokasi Bantuan (Otomatis dari Peta, Diposisikan Tepat di Bawah Peta) -->
                    <div id="group-location">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center justify-between">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    Alamat / Nama Lokasi Bantuan
                                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">(Otomatis Terisi)</span>
                                </span>
                                <span id="reverse-geocode-indicator" class="hidden text-[11px] text-blue-600 dark:text-blue-400 animate-pulse font-normal">
                                    📍 Mendeteksi alamat...
                                </span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="location" id="location-input"
                                placeholder="Alamat akan terisi otomatis saat Anda memilih titik peta..."
                                class="w-full px-4 py-3 text-sm rounded-lg border @error('location') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alamat terisi otomatis dari titik peta di atas.</p>
                        @error('location')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Patokan & Detail Khusus Tempat (Opsional) -->
                    <div id="group-full-address">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                                Detail Patokan Tempat / Ciri Rumah
                                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional)</span>
                            </span>
                        </label>
                        <textarea wire:model="full_address" rows="2"
                            placeholder="Contoh: Rumah pagar hitam samping warung Bu Siti, gang melati no. 4, lantai 2"
                            class="w-full px-4 py-2.5 text-sm rounded-lg border @error('full_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Tuliskan petunjuk arah agar Rekan Jasa tidak kesulitan mencari rumah/lokasi Anda.
                        </p>
                        @error('full_address')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Jadwal Permintaan (Tanggal & Jam) -->
                    <div id="group-schedule">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 000 2h8a1 1 0 100-2H6zM4 6a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                    Jadwalkan Waktu Bantuan
                                    <span class="text-gray-400 dark:text-gray-500 text-xs ml-1">(Tidak perlu di isi apabila tidak menjadwalkan)</span>
                                </span>
                            </label>
                            @if ($scheduled_date || $scheduled_time)
                                <button type="button" wire:click="clearSchedule" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-semibold px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 transition cursor-pointer">
                                    ✕ Hapus Jadwal
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <!-- Input Tanggal -->
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                                <input type="date" wire:model.live="scheduled_date" min="{{ date('Y-m-d') }}"
                                    class="w-full px-3 py-2.5 text-sm rounded-lg border @error('scheduled_date') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                @error('scheduled_date')
                                    <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Input Jam / Waktu -->
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1 flex items-center justify-between">
                                    <span>Jam / Pukul</span>
                                    <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">{{ $timezoneLabel }}</span>
                                </label>
                                <input type="time" wire:model.live="scheduled_time"
                                    class="w-full px-3 py-2.5 text-sm rounded-lg border @error('scheduled_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                @error('scheduled_time')
                                    <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Quick Schedule Presets -->
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-hide">
                            <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 flex-shrink-0">Pilihan:</span>
                            <button type="button" wire:click="setPresetSchedule('plus_2h')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                +2 Jam Lagi
                            </button>
                            <button type="button" wire:click="setPresetSchedule('tomorrow_morning')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                Besok Pagi (08:00)
                            </button>
                            <button type="button" wire:click="setPresetSchedule('tomorrow_afternoon')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                                Besok Siang (13:00)
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Kosongkan jika butuh bantuan sekarang (langsung tayang ke Mitra). Jika diisi, bantuan akan tayang saat waktu jadwal tiba.
                        </p>
                    </div>

                    <!-- Batas Waktu Batal Otomatis (Kadaluwarsa Pencarian Mitra) -->
                    <div id="group-expiry" class="p-3 bg-gray-50/80 dark:bg-gray-800/40 rounded-xl border border-gray-200/70 dark:border-gray-700/70">
                        <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Batas Waktu Pencarian Rekan Jasa
                                    <span class="text-red-500 ml-1">*</span>
                                </span>
                            </label>
                            <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-900/40">
                                Batal Otomatis & Refund 100%
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            @if ($scheduled_date)
                                Dihitung sejak tugas mulai tayang ke mitra pada jadwal (<strong>{{ $this->schedulePreview }}</strong>).
                            @else
                                Dihitung sejak tugas ditampilkan.
                            @endif
                        </p>

                        <!-- 4 Pilihan Cepat Minimalis (1 Baris Ringkas) -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" wire:click="setExpiryOption('1_hour')"
                                class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '1_hour' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                1 Jam
                            </button>

                            <button type="button" wire:click="setExpiryOption('6_hours')"
                                class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '6_hours' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                6 Jam
                            </button>

                            <button type="button" wire:click="setExpiryOption('24_hours')"
                                class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '24_hours' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                24 Jam
                            </button>

                            <button type="button" wire:click="setExpiryOption('custom')"
                                class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === 'custom' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Kustom
                            </button>
                        </div>

                        <!-- Form Input Kustom jika memilih 'custom' -->
                        @if ($expiry_option === 'custom')
                            <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-0.5">Tanggal Batas</label>
                                    <input type="date" wire:model.live="custom_expiry_date" min="{{ date('Y-m-d') }}"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border @error('custom_expiry_date') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                    @error('custom_expiry_date')
                                        <span class="text-red-500 text-[11px] mt-0.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-0.5 flex items-center justify-between">
                                        <span>Jam</span>
                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">{{ $timezoneLabel }}</span>
                                    </label>
                                    <input type="time" wire:model.live="custom_expiry_time"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border @error('custom_expiry_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                    @error('custom_expiry_time')
                                        <span class="text-red-500 text-[11px] mt-0.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Status Ringkasan Batas Waktu -->
                        <div class="mt-2 flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/90 p-2 rounded-lg border border-gray-200/70 dark:border-gray-700/60">
                            <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Batal otomatis jika belum ada mitra: <strong class="text-blue-600 dark:text-blue-400 font-semibold">{{ $this->expiryPreview }}</strong></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div id="group-description">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                Deskripsi Bantuan
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <textarea wire:model="description" id="description-input" rows="4"
                            placeholder="Jelaskan detail kebutuhan bantuan Anda secara lengkap..."
                            class="w-full px-4 py-3 text-sm rounded-lg border @error('description') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        @error('description')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Peralatan yang Sudah Disediakan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                Peralatan yang Sudah Disediakan
                                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1"></span>
                            </span>
                        </label>
                        <textarea wire:model="equipment_provided" rows="3"
                            placeholder="Contoh: Sudah ada alat alat kebersihan dan ember di rak"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Tuliskan alat atau peralatan yang sudah Anda sediakan (Bila ada).
                        </p>
                        @error('equipment_provided')
                            <span class="text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Foto Pendukung
                                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="file" wire:model="photo" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" id="photo-input"
                                class="hidden">
                            <label for="photo-input"
                                class="flex items-center justify-center w-full h-32 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 cursor-pointer transition bg-gray-50 dark:bg-gray-800/60 hover:bg-blue-50 dark:hover:bg-gray-700/50 overflow-hidden relative">
                                @if ($photo)
                                    @php
                                        $canPreview = false;
                                        try {
                                            $canPreview = method_exists($photo, 'temporaryUrl') && $photo->isPreviewable();
                                        } catch (\Throwable $e) {
                                            $canPreview = false;
                                        }
                                    @endphp
                                    @if ($canPreview)
                                        <img src="{{ $photo->temporaryUrl() }}" alt="preview" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex flex-col items-center justify-center w-full p-2 text-center">
                                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $photo->getClientOriginalName() }}</span>
                                        </div>
                                    @endif

                                    <button type="button" onclick="event.stopPropagation()"
                                        wire:click="$set('photo', null)"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center justify-center w-full">
                                        <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Pilih atau ambil foto</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">Klik untuk upload gambar</span>
                                    </div>
                                @endif
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Maksimal 2MB. Format: JPG, PNG, JPEG
                        </p>
                        @error('photo')
                            <span class="text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3 pt-6">
                        <a href="{{ route('dashboard') }}"
                            class="flex-1 inline-flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-5 py-3 text-sm rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </a>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-3 text-sm rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">Kirim Permintaan</span>
                        </button>
                    </div>
                </form>
                <script>
                    (function() {
                        // Map provinces to timezone group
                        const western = [
                            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Bengkulu',
                            'Lampung', 'Bangka Belitung',
                            'Banten', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur',
                            'Kalimantan Barat'
                        ];
                        const central = [
                            'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Tengah', 'Kalimantan Selatan',
                            'Kalimantan Timur', 'Sulawesi Selatan', 'Sulawesi Tengah', 'Sulawesi Tenggara', 'Gorontalo',
                            'Sulawesi Barat', 'Sulawesi Utara'
                        ];
                        const eastern = [
                            'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat'
                        ];

                        const zoneIana = {
                            'WIB': 'Asia/Jakarta',
                            'WITA': 'Asia/Makassar',
                            'WIT': 'Asia/Jayapura'
                        };

                        function provinceToZone(prov) {
                            if (!prov) return 'WIB';
                            prov = prov.trim();
                            if (western.indexOf(prov) !== -1) return 'WIB';
                            if (central.indexOf(prov) !== -1) return 'WITA';
                            if (eastern.indexOf(prov) !== -1) return 'WIT';
                            return 'WIB';
                        }

                        function formatTimeForZone(date, iana) {
                            try {
                                const fmt = new Intl.DateTimeFormat('id-ID', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false,
                                    timeZone: iana
                                });
                                return fmt.format(date);
                            } catch (e) {
                                // fallback to local time formatting
                                const hh = String(date.getHours()).padStart(2, '0');
                                const mm = String(date.getMinutes()).padStart(2, '0');
                                return `${hh}:${mm}`;
                            }
                        }

                        function setInputTimeToZone(iana) {
                            const hidden = document.getElementById('scheduled-time-hidden');
                            const manual = document.getElementById('scheduled-time-manual');
                            if (!hidden || !manual) return;
                            const now = new Date();
                            const timeStr = formatTimeForZone(now, iana); // returns HH:MM
                            manual.value = timeStr;
                            hidden.value = timeStr;
                            // Trigger input event so Livewire updates
                            hidden.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        }

                        function updateTimezoneDisplay() {
                            const citySelect = document.getElementById('city-select');
                            const tzDisplay = document.getElementById('timezone-display');
                            if (!citySelect || !tzDisplay) return;
                            const opt = citySelect.options[citySelect.selectedIndex];
                            const province = opt ? (opt.dataset.province || '') : '';
                            const zone = provinceToZone(province);
                            const iana = zoneIana[zone];
                            const now = new Date();
                            const timeText = formatTimeForZone(now, iana);
                            tzDisplay.textContent = timeText ? `Waktu lokal: ${zone} — ${timeText}` : `Waktu lokal: ${zone}`;
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            const citySelect = document.getElementById('city-select');
                            const hidden = document.getElementById('scheduled-time-hidden');
                            const manual = document.getElementById('scheduled-time-manual');
                            const tzBadge = document.getElementById('timezone-badge');

                            if (citySelect) {
                                citySelect.addEventListener('change', function() {
                                    const opt = citySelect.options[citySelect.selectedIndex];
                                    const province = opt ? (opt.dataset.province || '') : '';
                                    const zone = provinceToZone(province);
                                    const iana = zoneIana[zone];
                                    // Update badge and display
                                    if (tzBadge) tzBadge.textContent = zone;
                                    updateTimezoneDisplay();
                                    // Do not prefill the time input automatically; leave empty until user inputs
                                });
                            }

                            // Listen for Livewire-emitted timezone change when a city is selected
                            window.addEventListener('help:timezone-changed', function(e) {
                                try {
                                    const detail = e.detail || {};
                                    const zone = detail.zone || 'WIB';
                                    const iana = detail.iana || zoneIana[zone] || 'Asia/Jakarta';
                                    if (tzBadge) tzBadge.textContent = zone;
                                    // query display element fresh to avoid referencing undefined outer variable
                                    const tzDisplayEl = document.getElementById('timezone-display');
                                    const now = new Date();
                                    try {
                                        const fmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: iana });
                                        const timeText = fmt.format(now);
                                        if (tzDisplayEl) tzDisplayEl.textContent = `Waktu lokal: ${zone} — ${timeText}`;
                                    } catch (err) {
                                        if (tzDisplayEl) tzDisplayEl.textContent = `Waktu lokal: ${zone}`;
                                    }
                                    // Do not prefill the time input automatically; leave empty until user inputs
                                } catch (err) {
                                    console.error('help:timezone-changed handler error', err);
                                }
                            });

                            function normalizeManualAndSync() {
                                if (!manual || !hidden) return;
                                let v = manual.value || '';
                                // Remove AM/PM if present and convert
                                const ampmMatch = v.match(/(\d{1,2}):(\d{2})\s*([AP]M)?/i);
                                if (ampmMatch) {
                                    let hh = parseInt(ampmMatch[1], 10);
                                    const mm = parseInt(ampmMatch[2], 10);
                                    const ampm = (ampmMatch[3] || '').toUpperCase();
                                    if (ampm === 'PM' && hh < 12) hh += 12;
                                    if (ampm === 'AM' && hh === 12) hh = 0;
                                    v = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                                }
                                // If user typed like "3:5", normalize to 03:05
                                const parts = v.split(':');
                                if (parts.length === 2) {
                                    const hh = String(parseInt(parts[0], 10) || 0).padStart(2, '0');
                                    const mm = String(parseInt(parts[1], 10) || 0).padStart(2, '0');
                                    const normalized = `${hh}:${mm}`;
                                    if (normalized !== v) manual.value = normalized;
                                    if (hidden.value !== normalized) {
                                        hidden.value = normalized;
                                        hidden.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                    }
                                }
                            }

                            if (manual) {
                                manual.addEventListener('blur', normalizeManualAndSync);
                                manual.addEventListener('change', normalizeManualAndSync);

                                // Live sanitize: allow only digits, auto-insert colon after 2 digits,
                                // clamp hours to 0-23 and minutes to 0-59, and sync hidden when full.
                                // track previous raw value so we can handle delete/backspace gently
                                let _prevRaw = manual.value || '';
                                manual.addEventListener('input', function(e) {
                                    const raw = manual.value || '';
                                    const selStart = manual.selectionStart || 0;

                                    // count digits before caret in current raw value
                                    const before = raw.slice(0, selStart);
                                    const digitsBefore = (before.match(/\d/g) || []).length;

                                    // build digits-only (limit 4)
                                    let digits = raw.replace(/[^0-9]/g, '').slice(0, 4);

                                    // formatted candidate (minimal formatting)
                                    let candidate = digits.length <= 2 ? digits : digits.slice(0, 2) + ':' + digits.slice(2);

                                    // clamp if both parts present
                                    if (/^\d{1,2}:\d{1,2}$/.test(candidate)) {
                                        const p = candidate.split(':');
                                        let hh = parseInt(p[0], 10) || 0;
                                        let mm = parseInt(p[1], 10) || 0;
                                        hh = Math.max(0, Math.min(23, hh));
                                        mm = Math.max(0, Math.min(59, mm));
                                        candidate = `${String(hh).padStart(2, '0')}:${String(mm).padStart(2, '0')}`;
                                    }

                                    const inputType = (e && e.inputType) ? e.inputType : '';

                                    // If user performed a deletion, be less aggressive: avoid forcing normalization that
                                    // moves caret unexpectedly. Only remove/insert colon minimally.
                                    if (inputType && inputType.startsWith('delete')) {
                                        // If digits <=2, show as-is (no colon)
                                        if (digits.length <= 2) {
                                            if (manual.value !== digits) manual.value = digits;
                                            // set caret to end of digits
                                            try { manual.setSelectionRange(digits.length, digits.length); } catch (err) {}
                                        } else {
                                            // digits.length 3 or 4: show HH:MM or H:MM depending on digits
                                            const prevDigits = _prevRaw.replace(/[^0-9]/g, '').slice(0,4);
                                            // compute new caret based on digitsBefore (do not auto-pad)
                                            const newVal = digits.slice(0,2) + ':' + digits.slice(2);
                                            if (manual.value !== newVal) manual.value = newVal;
                                            let newPos = digitsBefore <= 2 ? digitsBefore : digitsBefore + 1;
                                            if (newPos > manual.value.length) newPos = manual.value.length;
                                            try { manual.setSelectionRange(newPos, newPos); } catch (err) {}
                                        }
                                    } else {
                                        // non-delete input: apply candidate and set caret mapping
                                        if (manual.value !== candidate) {
                                            manual.value = candidate;
                                            let newPos = digitsBefore <= 2 ? digitsBefore : digitsBefore + 1;
                                            if (newPos > manual.value.length) newPos = manual.value.length;
                                            try { manual.setSelectionRange(newPos, newPos); } catch (err) {}
                                        }
                                    }

                                    // sync hidden only when we have full HH:MM
                                    if (/^\d{2}:\d{2}$/.test(candidate)) {
                                        if (hidden.value !== candidate) {
                                            hidden.value = candidate;
                                            hidden.dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                    }

                                    _prevRaw = raw;
                                });

                                // initialize hidden from manual if present
                                normalizeManualAndSync();
                            }

                            // update tz display continuously and set badge
                            setInterval(updateTimezoneDisplay, 60 * 1000);
                            updateTimezoneDisplay();
                            if (tzBadge) {
                                // initial badge set based on selected city
                                const opt = citySelect ? citySelect.options[citySelect.selectedIndex] : null;
                                const province = opt ? (opt.dataset.province || '') : '';
                                const zone = provinceToZone(province);
                                tzBadge.textContent = zone;
                            }
                        });
                    })();
                </script>
            </div>
        </div>
    </div>

    <!-- Global submit overlay shown only while Livewire 'save' is processing (kept inside component root) -->
    <div wire:loading.class.remove="hidden" wire:target="save"
        class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center pointer-events-none">
        <div
            class="pointer-events-auto mb-6 md:mb-0 bg-white dark:bg-gray-800 bg-opacity-95 dark:bg-opacity-95 rounded-lg px-4 py-3 flex items-center gap-3 shadow-lg border border-gray-100 dark:border-gray-700">
            <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">Mengirim...</div>
        </div>
    </div>

    <!-- Confirmation Modal - Bottom Sheet Style -->
    @if ($showConfirmModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in"
            style="background: rgba(0,0,0,0.6);" wire:click="closeConfirmModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar animate-slide-up relative border-t border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                @click.stop style="padding-bottom: env(safe-area-inset-bottom,24px);">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Konfirmasi Permintaan</h3>
                        <button type="button" wire:click="closeConfirmModal"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-5 pb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Periksa ringkasan pesanan bantuan sebelum mempublikasikan.</p>

                    <!-- Breakdown Pembayaran -->
                    <div class="bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-4 space-y-2.5">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Imbalan Rekan Jasa</span>
                            <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($confirmAmount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Biaya Layanan Platform ({{ $confirmCommissionRate ?? 10 }}%)</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">+ Rp {{ number_format($confirmPlatformFee ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2.5 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 block">Total Pembayaran</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">Saldo ditahan aman (Escrow)</span>
                            </div>
                            <div class="text-xl font-extrabold text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($confirmTotal ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    @if ($confirmScheduled)
                        <div class="mb-4">
                            <div class="text-xs text-gray-600 dark:text-gray-400">Jadwal Permintaan</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $confirmScheduled }}</div>
                        </div>
                    @endif

                    <!-- Info Box Escrow (Alert) -->
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 rounded-xl p-3 mb-5">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div class="text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                                <p class="font-semibold mb-0.5">Jaminan Keamanan Dana :</p>
                                Dana sebesar <strong>Rp {{ number_format($confirmTotal ?? 0, 0, ',', '.') }}</strong> akan ditahan aman oleh sistem selama tugas berlangsung. Uang baru akan diteruskan ke Rekan Jasa setelah Anda mengonfirmasi pekerjaan selesai. Jika tugas dibatalkan, dana 100% dikembalikan utuh ke saldo Anda.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky footer with action buttons (always visible) -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 px-5 py-4 z-20 flex gap-3">
                    <button wire:click="closeConfirmModal" type="button"
                        class="flex-1 px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                        Kembali
                    </button>
                    <button wire:click="save" type="button" wire:loading.attr="disabled"
                        class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span wire:loading.remove wire:target="save">Konfirmasi</span>
                        <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- OpenStreetMap Script -->
    <script>
        // Modal overlay observer with full page blur
        (function() {
            const style = document.createElement('style');
            style.innerHTML = `
                /* Hide scrollbar */
                .hide-scrollbar::-webkit-scrollbar {
                    display: none;
                }
                .hide-scrollbar {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
                
                /* Animasi untuk modal */
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes slideUp {
                    from { 
                        opacity: 0;
                        transform: translateY(100%);
                    }
                    to { 
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .animate-fade-in {
                    animation: fadeIn 0.2s ease-out;
                }
                
                .animate-slide-up {
                    animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
                
                /* Blur effect untuk konten halaman dan maps */
                .blur-target {
                    filter: blur(8px);
                    transition: filter 0.3s ease;
                }
                
                body.modal-open {
                    overflow: hidden;
                }
                
                /* Pastikan modal tidak pernah blur */
                .modal-overlay {
                    filter: none !important;
                }
            `;
            document.head.appendChild(style);

            function updateModalState() {
                const hasOverlay = document.querySelector('.modal-overlay') !== null;
                const mainContent = document.getElementById('main-content');

                if (hasOverlay) {
                    document.body.classList.add('modal-open');
                    if (mainContent) {
                        mainContent.classList.add('blur-target');
                    }
                } else {
                    document.body.classList.remove('modal-open');
                    if (mainContent) {
                        mainContent.classList.remove('blur-target');
                    }
                }
            }

            // Observe modal changes
            const observer = new MutationObserver(updateModalState);

            document.addEventListener('DOMContentLoaded', function() {
                updateModalState();
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });

            // Update on Livewire navigation
            document.addEventListener('livewire:navigated', updateModalState);
        })();

        (function() {
            function waitForLeaflet(callback, maxAttempts = 60) {
                if (typeof L !== 'undefined') {
                    callback();
                    return;
                }
                if (!document.querySelector('script[src*="leaflet.js"]')) {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.crossOrigin = '';
                    document.head.appendChild(script);
                }
                if (!document.querySelector('link[href*="leaflet.css"]')) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    link.crossOrigin = '';
                    document.head.appendChild(link);
                }
                let attempts = 0;
                const timer = setInterval(() => {
                    attempts++;
                    if (typeof L !== 'undefined') {
                        clearInterval(timer);
                        callback();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(timer);
                        console.warn('Leaflet library failed to load in time.');
                    }
                }, 50);
            }

            document.addEventListener('DOMContentLoaded', function() {
                waitForLeaflet(() => initializeMap());
            });
            
            // Juga initialize saat Livewire navigated
            document.addEventListener('livewire:navigated', function() {
                waitForLeaflet(() => initializeMap());
            });
            
            let customerMap = null;
            let customerMarker = null;
            let mapResizeTimer = null;

            function safeInvalidateSize(mapObj, containerId = 'map') {
                if (!mapObj) return;
                try {
                    const el = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
                    if (el && document.body.contains(el) && mapObj._mapPane && mapObj._loaded && typeof mapObj.invalidateSize === 'function') {
                        mapObj.invalidateSize();
                    }
                } catch (e) {
                    // Suppress Leaflet detached element errors
                }
            }

            function locateUserGPS() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                    return;
                }

                const pill = document.getElementById('gps-status-pill');
                if (pill) {
                    pill.textContent = 'Mencari GPS...';
                    pill.className = 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold animate-pulse';
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        if (customerMap && typeof L !== 'undefined') {
                            customerMap.setView([lat, lng], 16);
                            
                            if (customerMarker) {
                                customerMarker.setLatLng([lat, lng]);
                            } else {
                                customerMarker = L.marker([lat, lng], { draggable: true }).addTo(customerMap);
                                customerMarker.on('dragend', function(e) {
                                    const pos = e.target.getLatLng();
                                    updateCoordinates(pos.lat, pos.lng, true);
                                });
                            }
                        }

                        updateCoordinates(lat, lng, true);
                    },
                    (error) => {
                        console.warn('GPS location error:', error.message);
                        if (pill) {
                            pill.textContent = 'Gagal Deteksi';
                            pill.className = 'px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-[11px] font-semibold';
                        }
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            }

            // Expose locateUserGPS to window so inline onclick can find it if needed
            window.locateUserGPS = locateUserGPS;

            function getLivewire() {
                try {
                    if (typeof @this !== 'undefined' && @this) return @this;
                } catch(e) {}
                try {
                    const root = document.getElementById('map')?.closest('[wire\\:id]');
                    if (root && window.Livewire) {
                        const id = root.getAttribute('wire:id');
                        if (id) return window.Livewire.find(id);
                    }
                } catch(e) {}
                return null;
            }

            function getLivewireProp(prop, fallback = null) {
                const lw = getLivewire();
                if (lw && typeof lw.get === 'function') {
                    try {
                        const val = lw.get(prop);
                        if (val !== undefined && val !== null) return val;
                    } catch(e){}
                }
                const input = document.querySelector(`input[wire\\:model="${prop}"], input[wire\\:model\\.live="${prop}"], textarea[wire\\:model="${prop}"], select[wire\\:model="${prop}"]`);
                if (input && input.value !== undefined && input.value !== '') return input.value;
                return fallback;
            }

            function setLivewireProp(prop, val) {
                const lw = getLivewire();
                if (lw && typeof lw.set === 'function') {
                    try { lw.set(prop, val); return; } catch(e){}
                }
                const input = document.querySelector(`input[wire\\:model="${prop}"], input[wire\\:model\\.live="${prop}"], textarea[wire\\:model="${prop}"], select[wire\\:model="${prop}"]`);
                if (input) {
                    input.value = val;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function updateCoordinates(lat, lng, isGPS = false) {
                const displayEl = document.getElementById('coordinates-display');
                if (displayEl) displayEl.classList.remove('hidden');

                const latEl = document.getElementById('lat-display');
                if (latEl) latEl.textContent = parseFloat(lat).toFixed(6);

                const lngEl = document.getElementById('lng-display');
                if (lngEl) lngEl.textContent = parseFloat(lng).toFixed(6);

                const pill = document.getElementById('gps-status-pill');
                if (pill) {
                    pill.textContent = isGPS ? 'GPS Realtime' : 'Titik Peta';
                    pill.className = isGPS 
                        ? 'px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold'
                        : 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold';
                }

                // Sync to Livewire safely
                setLivewireProp('latitude', lat);
                setLivewireProp('longitude', lng);

                // Automatic reverse geocode to fill 'location' field
                const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
                if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

                try {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, { headers: { 'Accept-Language': 'id' } })
                        .then(r => r.json())
                        .then(data => {
                            if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                            if (data && data.display_name) {
                                // Extract clean address (e.g. road / village / district)
                                let parts = data.display_name.split(',');
                                let cleanAddress = parts.slice(0, 4).join(',').trim();
                                setLivewireProp('location', cleanAddress);
                            }
                        }).catch(() => {
                            if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                        });
                } catch (e) {
                    if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                }
            }

            function initializeMap() {
                const mapContainer = document.getElementById('map');
                if (!mapContainer) return;

                if (typeof L === 'undefined') {
                    waitForLeaflet(() => initializeMap());
                    return;
                }

                if (mapResizeTimer) {
                    clearTimeout(mapResizeTimer);
                    mapResizeTimer = null;
                }

                // If a previous map instance exists globally on window or locally, remove it cleanly
                if (window._activeCustomerMap) {
                    try { window._activeCustomerMap.remove(); } catch(e){}
                    window._activeCustomerMap = null;
                }
                if (customerMap) {
                    try { customerMap.remove(); } catch(e){}
                    customerMap = null;
                }

                // Reset Leaflet ID attribute on the container DOM element
                if (mapContainer._leaflet_id) {
                    mapContainer._leaflet_id = null;
                }
                
                // Default center (Indonesia)
                const defaultLocation = [-7.7956, 110.3695]; // Yogyakarta
                const existingLat = getLivewireProp('latitude', {{ json_encode($latitude ?? null) }});
                const existingLng = getLivewireProp('longitude', {{ json_encode($longitude ?? null) }});

                try {
                    customerMap = L.map(mapContainer, {
                        center: (existingLat && existingLng) ? [existingLat, existingLng] : defaultLocation,
                        zoom: (existingLat && existingLng) ? 16 : 13,
                        scrollWheelZoom: true,
                        zoomControl: true
                    });
                    window._activeCustomerMap = customerMap;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19,
                    }).addTo(customerMap);
                    
                    mapResizeTimer = setTimeout(() => {
                        safeInvalidateSize(customerMap, mapContainer);
                    }, 250);

                    // Existing coordinates marker
                    if (existingLat && existingLng) {
                        customerMarker = L.marker([existingLat, existingLng], { draggable: true }).addTo(customerMap);
                        updateCoordinates(existingLat, existingLng, false);
                        customerMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateCoordinates(pos.lat, pos.lng, false);
                        });
                    }

                    // Click to pin
                    customerMap.on('click', function(e) {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;

                        if (customerMarker) {
                            customerMarker.setLatLng([lat, lng]);
                        } else {
                            customerMarker = L.marker([lat, lng], { draggable: true }).addTo(customerMap);
                            customerMarker.on('dragend', function(evt) {
                                const pos = evt.target.getLatLng();
                                updateCoordinates(pos.lat, pos.lng, false);
                            });
                        }

                        updateCoordinates(lat, lng, false);
                    });
                } catch (err) {
                    console.error('Error initializing customer map:', err);
                }
            }

            // Cleanup map before Livewire navigation
            document.addEventListener('livewire:navigating', () => {
                if (mapResizeTimer) {
                    clearTimeout(mapResizeTimer);
                    mapResizeTimer = null;
                }
                if (customerMap) {
                    try { customerMap.remove(); } catch(e){}
                    customerMap = null;
                }
                if (window._activeCustomerMap) {
                    try { window._activeCustomerMap.remove(); } catch(e){}
                    window._activeCustomerMap = null;
                }
                const mapEl = document.getElementById('map');
                if (mapEl && mapEl._leaflet_id) {
                    mapEl._leaflet_id = null;
                }
            });

            // Listen for city selection to center the map
            window.addEventListener('city-selected', (e) => {
                const map = customerMap || window._activeCustomerMap;
                if (e.detail && e.detail.cityName && map) {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(e.detail.cityName + ', Indonesia')}&limit=1`)
                        .then(r => r.json())
                        .then(results => {
                            if (results && results.length > 0) {
                                const cLat = parseFloat(results[0].lat);
                                const cLng = parseFloat(results[0].lon);
                                map.setView([cLat, cLng], 13);
                            }
                        }).catch(() => {});
                }
            });

            // Auto-scroll ke pesan peringatan / kolom error pertama HANYA saat form disubmit
            function scrollToFirstError() {
                setTimeout(() => {
                    const errorEl = document.querySelector('.field-error-message, #group-title .field-error-message, #title-input.border-red-500, input.border-red-500, textarea.border-red-500');
                    if (errorEl) {
                        const yOffset = -140;
                        const y = errorEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                        window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });

                        const parentGroup = errorEl.closest('div[id^="group-"]') || errorEl.closest('div');
                        if (parentGroup) {
                            const input = parentGroup.querySelector('input:not([type="hidden"]), textarea, select');
                            if (input) {
                                input.focus();
                                input.classList.add('ring-4', 'ring-red-400', 'transition-all');
                                setTimeout(() => input.classList.remove('ring-4', 'ring-red-400'), 3500);
                            }
                        }
                    }
                }, 80);
            }

            window.addEventListener('scroll-to-first-error', scrollToFirstError);

            // ─── Kunci posisi scroll saat mengunggah foto (mencegah mental ke atas) ───
            let uploadScrollPos = null;

            document.addEventListener('livewire:upload-start', () => {
                uploadScrollPos = window.pageYOffset || document.documentElement.scrollTop;
            });

            document.addEventListener('livewire:upload-finish', () => {
                if (uploadScrollPos !== null) {
                    const targetY = uploadScrollPos;
                    setTimeout(() => {
                        window.scrollTo({ top: targetY, behavior: 'instant' });
                        uploadScrollPos = null;
                    }, 40);
                }
                saveFormDraft();
            });

            document.addEventListener('livewire:upload-error', () => {
                if (uploadScrollPos !== null) {
                    window.scrollTo({ top: uploadScrollPos, behavior: 'instant' });
                    uploadScrollPos = null;
                }
            });

            // ─── Draf Form (Tersimpan saat REFRESH, Bersih/Kosong saat BERPINDAH HALAMAN) ───
            const DRAFT_KEY = 'sayabantu_create_help_draft';

            // Deteksi apakah pemuatan halaman ini adalah akibat REFRESH / RELOAD
            function isPageReload() {
                try {
                    const navEntries = performance.getEntriesByType('navigation');
                    if (navEntries && navEntries.length > 0) {
                        return navEntries[0].type === 'reload';
                    }
                    // Fallback untuk browser lawas
                    return performance.navigation && performance.navigation.type === 1;
                } catch (e) {
                    return false;
                }
            }

            function saveFormDraft() {
                try {
                    const data = {
                        title: getLivewireProp('title', ''),
                        amount: getLivewireProp('amount', {{ $minHelpNominal ?? 10000 }}),
                        city_id: getLivewireProp('city_id', ''),
                        cityQuery: getLivewireProp('cityQuery', ''),
                        location: getLivewireProp('location', ''),
                        full_address: getLivewireProp('full_address', ''),
                        scheduled_date: getLivewireProp('scheduled_date', ''),
                        scheduled_time: getLivewireProp('scheduled_time', ''),
                        description: getLivewireProp('description', ''),
                        equipment_provided: getLivewireProp('equipment_provided', ''),
                        latitude: getLivewireProp('latitude', null),
                        longitude: getLivewireProp('longitude', null),
                        timestamp: Date.now()
                    };
                    sessionStorage.setItem(DRAFT_KEY, JSON.stringify(data));
                } catch (e) {}
            }

            function restoreFormDraft() {
                try {
                    // JIKA BUKAN REFRESH (yaitu masuk dari halaman lain), hapus draf dan biarkan form kosong
                    if (!isPageReload()) {
                        sessionStorage.removeItem(DRAFT_KEY);
                        localStorage.removeItem(DRAFT_KEY);
                        return;
                    }

                    const raw = sessionStorage.getItem(DRAFT_KEY) || localStorage.getItem(DRAFT_KEY);
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (draft && (Date.now() - (draft.timestamp || 0) < 86400000)) {
                        const curTitle = getLivewireProp('title', '');
                        const curDesc = getLivewireProp('description', '');
                        if (!curTitle && !curDesc && (draft.title || draft.description || draft.city_id || draft.location || draft.full_address)) {
                            const lw = getLivewire();
                            if (lw && typeof lw.call === 'function') {
                                lw.call('restoreDraft', draft);
                            }
                            if (draft.latitude && draft.longitude && window.customerMap) {
                                setTimeout(() => {
                                    updateCoordinates(draft.latitude, draft.longitude, false);
                                }, 300);
                            }
                        }
                    }
                } catch (e) {}
            }

            document.addEventListener('livewire:initialized', () => {
                restoreFormDraft();
            });

            // Hapus draf saat bantuan berhasil dibuat atau form di-submit
            window.addEventListener('draft-cleared', () => {
                sessionStorage.removeItem(DRAFT_KEY);
                localStorage.removeItem(DRAFT_KEY);
            });

            // Bersihkan draf jika user menekan link navigasi / menu keluar dari halaman
            document.addEventListener('click', (e) => {
                const navLink = e.target.closest('a, button[onclick*="history"], [data-nav-leave]');
                if (navLink && !navLink.closest('form')) {
                    sessionStorage.removeItem(DRAFT_KEY);
                    localStorage.removeItem(DRAFT_KEY);
                }
            });

            // Simpan draf saat ada perubahan input pada form (hanya disimpan untuk antisipasi refresh)
            document.addEventListener('input', (e) => {
                if (e.target.closest('form')) {
                    setTimeout(saveFormDraft, 250);
                }
            });
            document.addEventListener('change', (e) => {
                if (e.target.closest('form')) {
                    setTimeout(saveFormDraft, 250);
                }
            });
        })();
    </script>
</div>