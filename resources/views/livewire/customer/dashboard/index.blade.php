<div class="min-h-screen bg-gray-100">
    <style>
        [x-cloak] {
        display: none !important;
        }
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }
        .card-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-shadow-hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .focus-ring:focus{ outline: none; box-shadow: 0 0 0 3px rgba(14,165,164,0.2); }
        
        /* BRImo-style decorative pattern */
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
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
    </style>

    <!-- Header Section -->
    <div class="px-5 pt-5 pb-16 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-[2rem] shadow-sm text-white">
        <!-- Decorative ambient circles -->
        <div class="absolute top-0 right-0 w-44 h-44 bg-white/10 rounded-full blur-2xl -mr-16 -mt-16 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-36 h-36 bg-white/5 rounded-full blur-xl -ml-12 -mb-12 pointer-events-none"></div>
        
        <div class="relative z-10 space-y-4">
            <!-- Top Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php
                        $__avatar = optional(auth()->user())->selfie_photo ?? optional(auth()->user())->photo ?? optional(auth()->user())->profile_photo_path ?? null;
                    @endphp
                    <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden ring-2 ring-white/40 shadow-xs flex-shrink-0">
                        <img src="{{ $__avatar ? asset('storage/' . $__avatar) : asset('images/avatar-placeholder.svg') }}" 
                            alt="Avatar" 
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';">
                    </div>
                    <div>
                        <p class="text-xs text-white/80 font-medium">Selamat datang,</p>
                        <h1 class="text-sm sm:text-base font-bold text-white leading-tight">{{ optional(auth()->user())->name ?? 'Pengguna' }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @include('components.notification-icon', ['route' => route('customer.notifications.index'), 'class' => 'bg-white/15 backdrop-blur-md p-2.5 rounded-xl hover:bg-white/25 transition shadow-xs cursor-pointer text-white'])
                </div>
            </div>

            <!-- Account Meta Badge -->
            <div class="pt-0.5">
                <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md text-white text-xs px-3.5 py-1.5 rounded-full font-medium shadow-xs border border-white/20">
                    <svg class="w-3.5 h-3.5 text-white/90" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6c0 4.5 6 10 6 10s6-5.5 6-10a6 6 0 00-6-6z"/></svg>
                    <span>{{ optional(optional(auth()->user())->city)->name ?? (auth()->user()->city ?? '-') }}</span>
                    <span class="opacity-60">•</span>
                    <span>Member sejak {{ optional(auth()->user())->created_at ? optional(auth()->user())->created_at->format('M Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Card (Smooth Controlled Overlap) -->
    <div class="px-5 -mt-8 relative z-20">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between gap-3">
                <!-- Balance Information -->
                <div
                    class="min-w-0 flex-1"
                    x-data="{
                        show: sessionStorage.getItem('balance_visible') === 'true',

                        toggleBalance() {
                            this.show = !this.show;

                            sessionStorage.setItem(
                                'balance_visible',
                                this.show ? 'true' : 'false'
                            );
                        }
                    }"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                        Total Saldo
                    </p>

                    <div class="flex items-center gap-2 min-w-0">
                        <!-- SALDO TERLIHAT -->
                        <h2
                            class="{{ ($balance ?? 0) >= 10000000 ? 'text-sm sm:text-base md:text-lg' : (($balance ?? 0) >= 1000000 ? 'text-base sm:text-lg md:text-xl' : 'text-lg sm:text-xl') }} font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap tracking-tight"
                            x-show="show"
                            x-cloak
                        >
                            Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                        </h2>

                        <!-- SALDO TERSEMBUNYI -->
                        <h2
                            class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 tracking-tight whitespace-nowrap"
                            x-show="!show"
                            x-cloak
                        >
                            Rp ••••••
                        </h2>

                        <!-- TOGGLE VISIBILITY -->
                        <button
                            type="button"
                            @click="toggleBalance()"
                            class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition cursor-pointer text-gray-500 dark:text-gray-400 flex-shrink-0"
                            aria-label="Tampilkan atau sembunyikan saldo"
                        >
                            <!-- ICON MATA TERTUTUP -->
                            <svg
                                x-show="!show"
                                x-cloak
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>

                            <!-- ICON MATA TERBUKA -->
                            <svg
                                x-show="show"
                                x-cloak
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- ACTIONS: TARIK DANA & TOP UP -->
                <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                    <!-- TARIK DANA -->
                    <a
                        href="{{ route('customer.withdraw.form') }}"
                        class="px-2.5 py-2 sm:px-3 sm:py-2.5 rounded-xl text-xs font-bold transition border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 flex items-center gap-1 sm:gap-1.5 cursor-pointer flex-shrink-0 shadow-xs whitespace-nowrap"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Tarik Dana</span>
                    </a>

                    <!-- TOP UP -->
                    <a
                        href="{{ route('customer.topup.request') }}"
                        class="text-white px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-xl text-xs font-bold transition shadow-md hover:shadow-lg flex items-center gap-1 sm:gap-1.5 cursor-pointer flex-shrink-0 whitespace-nowrap"
                        style="background: linear-gradient(to bottom right, #0098e7, #0060b0);"
                    >
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Top Up</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Quick Shortcut -->
    <div class="px-5 mt-2.5 sm:mt-3 flex items-center justify-end relative z-10">
        <a
            href="{{ route('customer.chat') }}"
            class="relative w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 shadow-xs hover:shadow-md hover:scale-105 active:scale-95 hover:border-sky-300 dark:hover:border-sky-600 transition flex items-center justify-center group cursor-pointer"
            title="Buka Chat"
            aria-label="Buka Chat"
        >
            <svg class="w-6 h-6 text-[#0098e7] dark:text-[#38bdf8] group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-5 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z" />
            </svg>
            @if(!empty($unreadChatCount) && $unreadChatCount > 0)
                <span class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center text-[10px] font-bold bg-rose-500 text-white rounded-full min-w-[18px] h-[18px] px-1 shadow-xs ring-2 ring-white dark:ring-gray-800 animate-pulse">
                    {{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}
                </span>
            @endif
        </a>
    </div>

    {{-- Official Warning / Shadow Ban Alert Banner for Customer --}}
    @if(auth()->check() && (auth()->user()->warning_level > 0 || auth()->user()->is_shadow_banned))
        <div class="px-5 mt-4 relative z-10">
            <div class="p-4 rounded-2xl border shadow-xs {{ auth()->user()->is_shadow_banned ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200' : 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' }}">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-base {{ auth()->user()->is_shadow_banned ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white' }}">
                        {{ auth()->user()->is_shadow_banned ? '🚫' : '⚠️' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-xs font-bold">
                                @if(auth()->user()->is_shadow_banned)
                                    Akun Dalam Pembatasan Fitur (Shadow Ban)
                                @else
                                    Surat Peringatan Resmi (SP {{ auth()->user()->warning_level }})
                                @endif
                            </h4>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ auth()->user()->is_shadow_banned ? 'bg-rose-200 text-rose-900' : 'bg-amber-200 text-amber-900' }}">
                                Moderasi Admin
                            </span>
                        </div>
                        <p class="text-xs mt-1 leading-relaxed opacity-90">
                            @if(auth()->user()->latest_warning_message)
                                "{{ auth()->user()->latest_warning_message }}"
                            @elseif(auth()->user()->is_shadow_banned)
                                Akun Anda sementara dibatasi dari membuat pekerjaan bantuan baru karena dalam proses peninjauan kepatuhan.
                            @else
                                Harap mematuhi syarat & ketentuan platform SayaBantu agar terhindar dari sanksi penangguhan akun.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="px-5 pt-6 sm:pt-8 pb-6">
        <!-- Banner Section (Spacious, Modern & Interactive) -->
        @php
            $customerBanners = json_decode((string) \App\Models\AppSetting::get('banner_customer', '[]'), true) ?: [];
        @endphp
        <div class="mt-2 mb-8" wire:ignore x-data="{
            active: 0,
            total: {{ !empty($customerBanners) && count($customerBanners) ? count($customerBanners) : 3 }},
            timer: null,
            intervalMs: 3500,
            startAuto() {
                this.stopAuto();
                if (this.total > 1) {
                    this.timer = setInterval(() => {
                        this.active = (this.active + 1) % this.total;
                    }, this.intervalMs);
                }
            },
            stopAuto() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            goTo(index) {
                this.active = index;
                this.startAuto();
            },
            init() {
                this.startAuto();
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.stopAuto();
                    } else {
                        this.startAuto();
                    }
                });
            }
        }" @mouseenter="stopAuto()" @mouseleave="startAuto()" @touchstart="stopAuto()" @touchend="startAuto()">
            <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg shadow-sky-500/5 border border-gray-100/80 dark:border-gray-700/60 h-44 sm:h-48 bg-gray-900">
                @if(!empty($customerBanners) && count($customerBanners))
                    <div class="flex h-full transition-transform duration-700 ease-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
                        @foreach($customerBanners as $b)
                            <div class="flex-shrink-0 w-full h-full relative">
                                <img src="{{ asset('storage/' . $b) }}" alt="Banner" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback High-Aesthetic Interactive Slides -->
                    <div class="flex h-full transition-transform duration-700 ease-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
                        <!-- Slide 1 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #0284c7 0%, #0060b0 50%, #0f172a 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-sky-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>⚡</span>
                                    <span>Solusi Cepat</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Butuh Bantuan Cepat?
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Posting tugas Anda & temukan mitra terdekat siap membantu dalam hitungan menit.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('customer.helps.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-sky-700 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Buat Permintaan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">🛠️</span>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #064e3b 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-emerald-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>💳</span>
                                    <span>Isi Saldo</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Top Up Saldo Praktis
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Pembayaran via QRIS & Transfer Bank dengan konfirmasi instan dan aman.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('customer.topup.request') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-emerald-800 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Top Up Sekarang</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform -rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">💰</span>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 50%, #431407 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-amber-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>⭐</span>
                                    <span>Mitra Terpercaya</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Layanan Bergaransi
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Mitra terverifikasi siap menyelesaikan pekerjaan dengan hasil terbaik.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('customer.helps.history') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-orange-800 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Lihat Riwayat</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">🌟</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Interactive Indicator Dots (Pill Style) -->
            <div class="flex justify-center items-center mt-3 gap-1.5">
                <template x-for="i in total" :key="i">
                    <button @click="goTo(i - 1)"
                            class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                            :class="active === (i - 1) ? 'w-6 bg-[#0098e7]' : 'w-1.5 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400'"></button>
                </template>
            </div>
        </div>

        <!-- Bantuan Saya Section -->
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Bantuan Saya</h2>
                <a href="{{ route('customer.helps.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline">Lihat Semua →</a>
            </div>

            <div class="space-y-3">
                @if($activeTab !== 'history')
                    @php
                        // Only show helps that are waiting for a mitra (include legacy status names)
                        $waitingHelps = collect($availableHelps)->filter(function($h) {
                            return in_array($h->status, ['mencari_mitra', 'menunggu_mitra', 'memperoleh_mitra', 'taken']);
                        });
                    @endphp
                    @forelse($waitingHelps as $help)
                        <a href="{{ route('customer.helps.detail', $help->id) }}"
                            class="block w-full text-left bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-xs hover:shadow-md border border-gray-100 dark:border-gray-700/70 transition-all group">
                            <div class="flex items-start gap-3.5">
                                <div class="w-13 h-13 rounded-2xl overflow-hidden bg-gradient-to-br from-sky-100 to-blue-50 dark:from-sky-950/60 dark:to-blue-900/40 border border-sky-200/60 dark:border-sky-800/60 flex-shrink-0 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform duration-300">
                                    @if($help->photo)
                                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ $help->title }}</h3>
                                        <span class="text-xs font-black text-sky-600 dark:text-sky-400 whitespace-nowrap">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mb-2">{{ $help->description }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>{{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-3 text-[11px] text-gray-400 dark:text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $help->city->name ?? '-' }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $help->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/70 p-6">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 flex items-center justify-center text-sky-600 dark:text-sky-400 shadow-2xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada permintaan yang menunggu mitra</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Buat permintaan bantuan baru untuk segera dibantu mitra terdekat</p>
                        </div>
                    @endforelse
                @endif

                @if(method_exists($availableHelps, 'links'))
                    <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                        {{ $availableHelps->links('vendor.pagination.superadmin') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

        <!-- Transaction History Component -->
        @if($activeTab === 'history')
            @livewire('customer.balance.transaction-history')
        @endif

        <!-- Help Detail Modal (bottom-sheet style like helps index) -->
        @if($selectedHelpData)
            <div class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(0,0,0,0.5);" wire:click="closeHelp">
                <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar" @click.stop>
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl z-10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Detail Permintaan</h3>
                            <button type="button" wire:click="closeHelp" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition cursor-pointer text-gray-500 dark:text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-5 pb-6">
                        @if(data_get($selectedHelpData, 'photo'))
                            <div class="mb-4 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 max-h-52 flex items-center justify-center">
                                <img src="{{ asset('storage/' . data_get($selectedHelpData, 'photo')) }}" 
                                    alt="Foto bantuan" class="w-full h-auto max-h-52 object-contain">
                            </div>
                        @endif

                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ data_get($selectedHelpData, 'title') }}</h2>
                        
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Nominal Bantuan</p>
                                <p class="text-xl font-black text-sky-600 dark:text-sky-400">
                                    Rp {{ number_format(data_get($selectedHelpData, 'amount', 0), 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Status</p>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                    {{ ucfirst(data_get($selectedHelpData, 'status')) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Deskripsi</p>
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50/70 dark:bg-gray-750/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60">{{ data_get($selectedHelpData, 'description') }}</p>
                            </div>

                            @if(data_get($selectedHelpData, 'location'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Lokasi</p>
                                    <div class="flex items-start gap-2 text-xs text-gray-800 dark:text-gray-200">
                                        <svg class="w-4 h-4 text-sky-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <p>{{ data_get($selectedHelpData, 'location') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'full_address'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Alamat Lengkap</p>
                                    <p class="text-xs text-gray-800 dark:text-gray-200">{{ data_get($selectedHelpData, 'full_address') }}</p>
                                </div>
                                <div class="mt-3">
                                    <div id="detail-map" class="w-full h-48 rounded-xl overflow-hidden mb-4 border border-gray-200 dark:border-gray-700"></div>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'equipment_provided'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Peralatan Disediakan</p>
                                    <p class="text-xs text-gray-800 dark:text-gray-200">{{ data_get($selectedHelpData, 'equipment_provided') }}</p>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'city_name'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Kota</p>
                                    <p class="text-xs text-gray-800 dark:text-gray-200">{{ data_get($selectedHelpData, 'city_name') }}</p>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'scheduled_at'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Jadwal Permintaan</p>
                                    <p class="text-xs text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse(data_get($selectedHelpData, 'scheduled_at'))->translatedFormat('d M Y, H:i') }} WIB</p>
                                </div>
                            @endif

                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Dibuat</p>
                                <p class="text-xs text-gray-800 dark:text-gray-200">{{ data_get($selectedHelpData, 'created_at_human') }}</p>
                            </div>
                        </div>

                    </div>

                    <!-- Sticky footer (close button) -->
                    <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 pt-3.5 px-5 pb-5">
                        <button type="button" wire:click="closeHelp" 
                            class="w-full px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('livewire:load', function () {
        try { new Swiper('.swiper-slides'); } catch (e) { /* ignore */ }
    });
</script>
