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
                        <img src="{{ $__avatar ? asset('storage/' . $__avatar) : asset('images/avatar-placeholder.svg') }}" alt="Avatar" class="w-full h-full object-cover">
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 shadow-md border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <!-- Balance Information -->
                <div
                    class="flex-1"
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

                    <div class="flex items-center gap-2">
                        <!-- SALDO TERLIHAT -->
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            x-show="show"
                            x-cloak
                        >
                            Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                        </h2>

                        <!-- SALDO TERSEMBUNYI -->
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            x-show="!show"
                            x-cloak
                        >
                            Rp ••••••
                        </h2>

                        <!-- TOGGLE VISIBILITY -->
                        <button
                            type="button"
                            @click="toggleBalance()"
                            class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition cursor-pointer text-gray-500 dark:text-gray-400"
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
                        class="px-3 py-2.5 rounded-xl text-xs font-bold transition border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 flex items-center gap-1.5 cursor-pointer flex-shrink-0 shadow-xs"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Tarik Dana</span>
                    </a>

                    <!-- TOP UP -->
                    <a
                        href="{{ route('customer.topup.request') }}"
                        class="text-white px-3.5 py-2.5 rounded-xl text-xs font-bold transition shadow-md hover:shadow-lg flex items-center gap-1.5 cursor-pointer flex-shrink-0"
                        style="background: linear-gradient(to bottom right, #0098e7, #0060b0);"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Top Up</span>
                    </a>
                </div>
            </div>
        </div>
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

    <!-- Quick Action Sub-Nav Grid (Clean Button + Label without outer card) -->
    <div class="px-5 mt-4 sm:mt-5 relative z-10">
        <div class="grid grid-cols-4 gap-2">
            <!-- 1. Buat Bantuan -->
            <a href="{{ route('customer.helps.create') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-[#0098e7] dark:text-[#38bdf8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Buat</span>
            </a>

            <!-- 2. Bantuan Saya -->
            <a href="{{ route('customer.helps.index') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-[#0098e7] dark:text-[#38bdf8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Bantuan</span>
            </a>

            <!-- 3. Riwayat -->
            <a href="{{ route('customer.helps.history') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Riwayat</span>
            </a>

            <!-- 4. Chat -->
            <a href="{{ route('customer.chat') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center relative">
                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                        <svg class="w-5 h-5 text-[#0098e7] dark:text-[#38bdf8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-5 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z" />
                        </svg>
                    </div>
                    @if(!empty($unreadChatCount) && $unreadChatCount > 0)
                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center text-[10px] font-bold bg-rose-500 text-white rounded-full min-w-[18px] h-[18px] px-1 shadow-xs ring-2 ring-white dark:ring-gray-800 animate-pulse">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                    @endif
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Chat</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-5 pt-9 sm:pt-11 pb-6">
        <!-- Banner Section (Spacious, Modern & Interactive) -->
        @php
            $customerBanners = json_decode((string) \App\Models\AppSetting::get('banner_customer', '[]'), true) ?: [];
        @endphp
        <div class="mt-2 mb-8" x-data="{
            active: 0,
            total: {{ !empty($customerBanners) && count($customerBanners) ? count($customerBanners) : 3 }},
            timer: null,
            startAuto() {
                this.timer = setInterval(() => {
                    this.active = (this.active + 1) % this.total;
                }, 4500);
            },
            stopAuto() {
                if (this.timer) clearInterval(this.timer);
            },
            goTo(index) {
                this.active = index;
                this.stopAuto();
                this.startAuto();
            }
        }" x-init="startAuto()" @mouseenter="stopAuto()" @mouseleave="startAuto()">
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
                <h2 class="text-base font-bold text-gray-900">Bantuan Saya</h2>
                <a href="{{ route('customer.helps.index') }}" class="text-xs font-semibold" style="color: #0098e7;">Lihat Semua →</a>
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
                            class="block w-full text-left bg-white rounded-xl p-3.5 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                    @if($help->photo)
                                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-lg">
                                            {{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-semibold text-sm text-gray-900 line-clamp-1">{{ $help->title }}</h3>
                                        <span class="text-xs font-bold whitespace-nowrap" style="color: #0098e7;">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 line-clamp-1 mb-1.5">{{ $help->description }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-1">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                        <span class="text-xs text-gray-400">{{ $help->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-10 bg-white rounded-xl shadow-sm">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">Tidak ada permintaan yang menunggu mitra</p>
                            <p class="text-xs text-gray-500 mt-1">Buat permintaan baru dengan klik tombol <span class="font-semibold">Buat</span></p>
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
                <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar" wire:click.stop>
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">Detail Permintaan</h3>
                            <button type="button" wire:click="closeHelp" class="p-2 hover:bg-gray-100 rounded-full transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-5 pb-6">
                        @if(data_get($selectedHelpData, 'photo'))
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . data_get($selectedHelpData, 'photo')) }}" 
                                    alt="Foto bantuan" class="w-full h-48 object-cover rounded-2xl">
                            </div>
                        @endif

                        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ data_get($selectedHelpData, 'title') }}</h2>
                        
                        <div class="flex items-center justify-between mb-4 pb-4 border-b">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Nominal Bantuan</p>
                                <p class="text-2xl font-bold" style="color: var(--brand-600)">
                                    Rp {{ number_format(data_get($selectedHelpData, 'amount', 0), 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-1">Status</p>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                    style="background: rgba(14,165,164,0.1); color: var(--brand-600)">
                                    {{ ucfirst(data_get($selectedHelpData, 'status')) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Deskripsi</p>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ data_get($selectedHelpData, 'description') }}</p>
                            </div>

                            @if(data_get($selectedHelpData, 'location'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Lokasi</p>
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'location') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'full_address'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Alamat Lengkap</p>
                                    <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'full_address') }}</p>
                                </div>
                                <div class="mt-3">
                                    <div id="detail-map" class="w-full h-48 rounded-xl overflow-hidden mb-4"></div>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'equipment_provided'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Peralatan Disediakan</p>
                                    <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'equipment_provided') }}</p>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'city_name'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Kota</p>
                                    <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'city_name') }}</p>
                                </div>
                            @endif

                            @if(data_get($selectedHelpData, 'scheduled_at'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Jadwal Permintaan</p>
                                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse(data_get($selectedHelpData, 'scheduled_at'))->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                            @endif

                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Dibuat</p>
                                <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'created_at_human') }}</p>
                            </div>
                        </div>

                    </div>

                    <!-- Sticky footer (close button) -->
                    <div class="sticky bottom-0 bg-white border-t pt-4 px-5 pb-5">
                        <button type="button" wire:click="closeHelp" 
                            class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
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
