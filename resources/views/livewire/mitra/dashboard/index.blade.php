<div class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <style>
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
                        <h1 class="text-sm sm:text-base font-bold text-white leading-tight">{{ optional(auth()->user())->name ?? 'Mitra' }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @include('components.notification-icon', ['route' => route('mitra.notifications.index'), 'class' => 'bg-white/15 backdrop-blur-md p-2.5 rounded-xl hover:bg-white/25 transition shadow-xs cursor-pointer text-white'])
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
                <div
                    class="flex-1"
                    x-data="{
                        show: sessionStorage.getItem('mitra_balance_visible') === 'true',

                        toggleBalance() {
                            this.show = !this.show;

                            sessionStorage.setItem(
                                'mitra_balance_visible',
                                this.show ? 'true' : 'false'
                            );
                        }
                    }"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Saldo</p>

                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-show="show" x-cloak>
                            Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                        </h2>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-show="!show" x-cloak>Rp ••••••</h2>
                        <button type="button" @click="toggleBalance()" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition cursor-pointer text-gray-500 dark:text-gray-400" aria-label="Tampilkan atau sembunyikan saldo">
                            <svg x-show="!show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a
                        href="{{ route('mitra.transactions.index') }}"
                        class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer border border-gray-200/60 dark:border-gray-600/60"
                        title="Riwayat Mutasi Saldo"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Mutasi</span>
                    </a>
                    <a
                        href="{{ route('mitra.withdraw.form') }}"
                        class="text-white px-3.5 py-2 rounded-xl text-xs font-bold bg-primary-600 hover:bg-primary-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Tarik Saldo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Official Warning / Shadow Ban Alert Banner for Mitra --}}
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
                                Akun Anda sementara dibatasi dari mengambil tugas bantuan baru karena dalam peninjauan kepatuhan.
                            @else
                                Harap selalu menjaga kualitas pelayanan dan menyelesaikan tugas tepat waktu sesuai SOP platform.
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
            <!-- 1. Cari Pekerjaan -->
            <a href="{{ route('mitra.helps.all') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-[#0098e7] dark:text-[#38bdf8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Cari</span>
            </a>

            <!-- 2. Pekerjaan Aktif -->
            <a href="{{ route('mitra.helps.processing') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-[#0098e7] dark:text-[#38bdf8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Pekerjaan</span>
            </a>

            <!-- 3. Riwayat -->
            <a href="{{ route('mitra.withdraw.history') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-center group-hover:scale-105 group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Riwayat</span>
            </a>

            <!-- 4. Chat -->
            <a href="{{ route('mitra.chat') }}" class="flex flex-col items-center gap-1.5 p-1 transition group cursor-pointer text-center relative">
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
        @if(session()->has('error'))
            <div class="mb-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session()->has('message'))
            <div class="mb-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div class="flex-1 font-medium">{{ session('message') }}</div>
            </div>
        @endif

        <!-- Active Task Banner if Mitra already has an active task -->
        @if(!empty($activeTask))
            <div class="mb-5 bg-blue-50/70 dark:bg-gray-800 border border-blue-200/80 dark:border-gray-700 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base flex-shrink-0 font-bold shadow-xs">
                            {{ $activeTask->progress_icon }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-blue-900 dark:text-blue-200 truncate">Tugas Aktif Berjalan</h4>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-blue-600 text-white shadow-xs">
                                    {{ $activeTask->progress_percentage }}%
                                </span>
                            </div>
                            <p class="text-xs text-blue-800 dark:text-blue-300 mt-0.5 font-medium truncate">
                                "{{ $activeTask->title }}" • <span class="font-bold">{{ $activeTask->progress_summary }}</span>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('mitra.helps.detail', $activeTask->id) }}" class="flex-shrink-0 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm whitespace-nowrap">
                        Buka Tugas
                    </a>
                </div>
                <!-- Mini Progress Track -->
                <div class="w-full bg-blue-100/70 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-blue-600 dark:bg-blue-500 transition-all duration-500 {{ $activeTask->progress_percentage < 100 ? 'animate-pulse' : '' }}"
                         style="width: {{ $activeTask->progress_percentage }}%;"></div>
                </div>
            </div>
        @endif

        <!-- GPS Tracker Component - Tampil untuk bantuan aktif -->
        @foreach($helps as $help)
            @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']) && $help->mitra_id === auth()->id())
                <div class="mb-4">
                    <livewire:mitra.gps.tracker :helpId="$help->id" :key="'gps-'.$help->id" />
                </div>
            @endif
        @endforeach

        <!-- Banner Section (Spacious, Modern & Interactive) -->
        @php
            $mitraBanners = json_decode((string) \App\Models\AppSetting::get('banner_mitra', '[]'), true) ?: [];
        @endphp
        <div class="mt-2 mb-8" x-data="{
            active: 0,
            total: {{ !empty($mitraBanners) && count($mitraBanners) ? count($mitraBanners) : 3 }},
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
                @if(!empty($mitraBanners) && count($mitraBanners))
                    <div class="flex h-full transition-transform duration-700 ease-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
                        @foreach($mitraBanners as $b)
                            <div class="flex-shrink-0 w-full h-full relative">
                                <img src="{{ asset('storage/' . $b) }}" alt="Banner" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback High-Aesthetic Interactive Slides for Mitra -->
                    <div class="flex h-full transition-transform duration-700 ease-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
                        <!-- Slide 1 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 50%, #0f172a 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-blue-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>💼</span>
                                    <span>Peluang Kerja</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Raih Penghasilan Tambahan
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Ambil pekerjaan di sekitarmu dan atur jadwal kerja secara fleksibel.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('mitra.helps.all') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-blue-800 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Cari Pekerjaan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">🧰</span>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #7c3aed 0%, #6366f1 50%, #1e1b4b 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-indigo-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>⚡</span>
                                    <span>Penarikan Kilat</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Tarik Saldo Kapan Saja
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Cairkan pendapatanmu langsung ke rekening bank terdaftar tanpa repot.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('mitra.withdraw.form') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-indigo-800 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Tarik Saldo</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform -rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">💸</span>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="flex-shrink-0 w-full h-full relative p-5 sm:p-6 flex items-center justify-between text-white overflow-hidden"
                             style="background: linear-gradient(135deg, #d97706 0%, #ea580c 50%, #451a03 100%);">
                            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute right-16 -top-8 w-28 h-28 rounded-full bg-amber-400/20 blur-xl pointer-events-none"></div>

                            <div class="relative z-10 max-w-[65%] sm:max-w-[70%] space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-bold text-white uppercase tracking-wider border border-white/25">
                                    <span>⭐</span>
                                    <span>Mitra Unggulan</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                    Tingkatkan Rating & Order
                                </h3>
                                <p class="text-xs text-white/90 font-medium line-clamp-2 leading-relaxed">
                                    Berikan pelayanan terbaik untuk meraih bintang 5 dan order prioritas.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('mitra.withdraw.history') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-amber-800 hover:bg-white/90 text-xs font-bold rounded-xl shadow-sm transition-transform active:scale-95">
                                        <span>Riwayat Tugas</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="relative z-10 flex-shrink-0 mr-1 sm:mr-3">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/10 transform rotate-3 hover:rotate-0 transition-transform">
                                    <span class="text-3xl sm:text-4xl">🏆</span>
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
    </div>


    @if(empty($mitraBanners) || !count($mitraBanners))
        <script>
            (function () {
                const banners = [
                    { title: 'Promo Spesial', desc: 'Dapatkan bonus saldo dan insentif khusus.', bgCss: 'linear-gradient(135deg,#6366f1,#4f46e5)' },
                    { title: 'Insentif Mitra', desc: 'Selesaikan lebih banyak bantuan, dapatkan insentif.', bgCss: 'linear-gradient(135deg,#10b981,#059669)' },
                    { title: 'Badge Aktif', desc: 'Selesaikan 5 bantuan dan dapatkan badge Mitra Aktif.', bgCss: 'linear-gradient(135deg,#f59e0b,#f97316)' }
                ];

                const track = document.getElementById('promo-track');
                const dotsContainer = document.getElementById('promo-dots');
                const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('button')) : [];
                let idx = 0;
                let timer = null;

                // build slides (create DOM nodes and use inline background to avoid Tailwind purge issues)
                if (track) {
                    track.innerHTML = '';
                    const frag = document.createDocumentFragment();
                    banners.forEach(b => {
                        const slide = document.createElement('div');
                        slide.className = 'w-full flex-shrink-0 p-6 flex items-center justify-center text-white';
                        slide.style.background = b.bgCss;
                        const inner = document.createElement('div');
                        inner.className = 'text-center';
                        const title = document.createElement('div');
                        title.className = 'font-extrabold text-xl mb-1 tracking-tight';
                        title.textContent = b.title;
                        const desc = document.createElement('div');
                        desc.className = 'text-sm opacity-90 font-medium';
                        desc.textContent = b.desc;
                        inner.appendChild(title);
                        inner.appendChild(desc);
                        slide.appendChild(inner);
                        frag.appendChild(slide);
                    });
                    track.appendChild(frag);
                }

                function update() {
                    if (track) {
                        const percent = (idx * 100) / banners.length;
                        track.style.transform = `translateX(${-percent}%)`;
                    }
                    if (dots.length) {
                        dots.forEach((d, k) => {
                            d.classList.toggle('bg-primary-600', k === idx);
                            d.classList.toggle('bg-gray-300', k !== idx);
                        });
                    }
                }

                function go(i) {
                    idx = (i + banners.length) % banners.length;
                    update();
                }

                function resetTimer() {
                    if (timer) clearInterval(timer);
                    timer = setInterval(() => go(idx + 1), 4200);
                }

                // dot clicks
                if (dotsContainer) {
                    dotsContainer.addEventListener('click', function (e) {
                        const dot = e.target.closest('button[data-dot]');
                        if (!dot) return;
                        const i = parseInt(dot.dataset.dot);
                        go(i);
                        resetTimer();
                    });
                }

                // init
                if (track) {
                    // ensure track has width for transform to work correctly
                    track.style.width = `${banners.length * 100}%`;
                    Array.from(track.children).forEach(child => child.style.width = `${100 / banners.length}%`);
                    update();
                    resetTimer();
                }
            })();
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initBannerSlider(wrapperSelector) {
                const wrapper = document.querySelector(wrapperSelector);
                if (!wrapper) return;
                const container = wrapper.parentElement; // expected visible container
                const slides = Array.from(wrapper.children || []);
                if (!slides.length || slides.length <= 1) return;

                function setup() {
                    const cw = container.clientWidth || container.getBoundingClientRect().width;
                    wrapper.style.width = (cw * slides.length) + 'px';
                    wrapper.style.display = 'flex';
                    wrapper.style.transition = 'transform 700ms cubic-bezier(.2,.9,.2,1)';
                    slides.forEach(s => {
                        s.style.width = cw + 'px';
                        s.style.flex = '0 0 auto';
                    });
                }

                let idx = 0;
                let timer = null;

                function go(i) {
                    idx = (i + slides.length) % slides.length;
                    const shift = -(idx * (container.clientWidth || container.getBoundingClientRect().width));
                    wrapper.style.transform = 'translateX(' + shift + 'px)';
                }

                setup();
                window.addEventListener('resize', setup);

                timer = setInterval(function () { go(idx + 1); }, 3500);

                container.addEventListener('mouseenter', function () { if (timer) clearInterval(timer); });
                container.addEventListener('mouseleave', function () { if (timer) clearInterval(timer); timer = setInterval(function () { go(idx + 1); }, 3500); });
            }

            try { initBannerSlider('.mitra-banner-slides'); } catch (e) { console.warn('mitra slider init', e); }
            try { initBannerSlider('.customer-banner-slides'); } catch (e) { /* ignore */ }
        });
    </script>

    <!-- Modal Preview Bantuan (Bottom Sheet Style) -->
    <div id="helpPreviewModal" class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 hidden pb-16">
        <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[75vh] overflow-y-auto" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Preview Bantuan</h3>
                    <button type="button" onclick="closePreviewModal()" class="p-2 hover:bg-gray-100 rounded-full transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-5 pb-6">
                <div class="mb-4">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Judul Bantuan</p>
                    <p id="previewTitle" class="text-base font-bold text-gray-900">-</p>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Nominal untuk Mitra</p>
                    <div id="previewAmount" class="inline-block bg-green-100 text-green-700 px-3 py-1.5 rounded-lg font-bold text-sm">
                        💰 Rp 0
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Jadwal Permintaan</p>
                    <div id="previewScheduled" class="text-sm text-gray-700">-</div>
                </div>

                <!-- Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-xs font-semibold text-blue-800 mb-1">🔒 Informasi Terbatas</p>
                    <p class="text-xs text-blue-700">
                        Deskripsi, alamat lengkap, lokasi di peta, foto, dan kontak customer akan ditampilkan setelah Anda mengambil bantuan ini.
                    </p>
                </div>
            </div>

            <!-- Sticky footer -->
            <div class="sticky bottom-0 bg-white border-t pt-4 px-5 pb-5">
                <div class="flex gap-3">
                    <button type="button" onclick="closePreviewModal()" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">
                        Batal
                    </button>
                    @if(!empty($activeTask))
                        <a href="{{ route('mitra.helps.detail', $activeTask->id) }}" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl font-bold transition flex items-center justify-center text-center text-xs shadow-sm">
                            Selesaikan Tugas Aktif
                        </a>
                    @else
                        <button type="button" id="previewTakeBtn" onclick="takeHelpFromModal()" class="flex-1 bg-primary-500 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-primary-600 transition">
                            Ambil Bantuan
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentHelpId = null;

        function showHelpPreview(helpId, title, amount, scheduled) {
            currentHelpId = helpId;
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewAmount').textContent = '💰 Rp ' + amount.toLocaleString('id-ID');
            const schedEl = document.getElementById('previewScheduled');
            if (schedEl) {
                if (scheduled && scheduled.length) {
                    schedEl.textContent = scheduled;
                } else {
                    // fallback: fetch latest help data
                    fetch('/helps/' + helpId + '/json', { credentials: 'same-origin' })
                        .then(r => r.ok ? r.json() : Promise.reject(r))
                        .then(data => {
                            schedEl.textContent = data.scheduled_at ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(data.scheduled_at)) : '-';
                        }).catch(() => { schedEl.textContent = '-'; });
                }
            }
            document.getElementById('helpPreviewModal').classList.remove('hidden');
        }

        function closePreviewModal() {
            document.getElementById('helpPreviewModal').classList.add('hidden');
            currentHelpId = null;
        }

        function takeHelpFromModal() {
            if (!currentHelpId) return;

            // Fungsi untuk reset button
            const resetButton = (btn, originalText) => {
                btn.textContent = originalText;
                btn.disabled = false;
            };
            
            // Fungsi untuk ambil bantuan dengan/tanpa lokasi
            const takeBantuanWithLocation = (lat = null, lng = null) => {
                if (lat && lng) {
                    console.log('📍 Mengambil bantuan dengan lokasi:', { lat, lng });
                    @this.takeHelp(currentHelpId, lat, lng);
                } else {
                    console.log('📍 Mengambil bantuan tanpa lokasi GPS');
                    @this.takeHelp(currentHelpId);
                }
                closePreviewModal();
            };
            
            // Fungsi fallback: Coba gunakan IP-based location
            const tryIPBasedLocation = (btn, originalText) => {
                console.log('🌐 Mencoba IP-based location...');
                btn.textContent = 'Mendeteksi lokasi dari IP...';
                
                // Gunakan ipapi.co untuk mendapatkan koordinat dari IP
                fetch('https://ipapi.co/json/', { timeout: 3000 })
                    .then(response => response.json())
                    .then(data => {
                        if (data.latitude && data.longitude) {
                            console.log('✅ IP-based location berhasil:', data);
                            takeBantuanWithLocation(data.latitude, data.longitude);
                            resetButton(btn, originalText);
                        } else {
                            throw new Error('Invalid location data');
                        }
                    })
                    .catch(error => {
                        console.error('❌ IP-based location gagal:', error);
                        // Tetap tanyakan apakah mau ambil tanpa lokasi
                        if (confirm('📍 Lokasi tidak dapat dideteksi.\n\n✅ Ambil bantuan tanpa GPS tracking?\n\n(Lokasi dapat diupdate nanti saat Anda mulai bergerak)')) {
                            takeBantuanWithLocation();
                        }
                        resetButton(btn, originalText);
                    });
            };

            // Request GPS permission dan ambil lokasi
            if (navigator.geolocation) {
                // Show loading on button
                const btn = document.getElementById('previewTakeBtn');
                const originalText = btn.textContent;
                btn.textContent = 'Mengambil GPS...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        console.log('✅ GPS Location obtained:', { lat, lng, accuracy: accuracy + 'm' });
                        
                        // Call Livewire method dengan GPS coordinates
                        takeBantuanWithLocation(lat, lng);
                        
                        // Reset button
                        resetButton(btn, originalText);
                    },
                    (error) => {
                        console.error('❌ GPS Error:', error);
                        
                        // Error codes:
                        // 1 = PERMISSION_DENIED
                        // 2 = POSITION_UNAVAILABLE (no GPS hardware atau signal)
                        // 3 = TIMEOUT
                        
                        if (error.code === 2) {
                            // GPS tidak tersedia (laptop/desktop tanpa GPS)
                            console.log('💻 Device tidak memiliki GPS, mencoba IP-based location...');
                            tryIPBasedLocation(btn, originalText);
                        } else if (error.code === 1) {
                            // User menolak permission
                            if (confirm('📍 Akses lokasi ditolak.\n\n🌐 Coba deteksi lokasi dari IP address?\n\n(Akurasi lebih rendah tetapi cukup untuk tracking)')) {
                                tryIPBasedLocation(btn, originalText);
                            } else if (confirm('✅ Ambil bantuan tanpa GPS tracking?\n\n(Lokasi dapat diupdate nanti)')) {
                                takeBantuanWithLocation();
                                resetButton(btn, originalText);
                            } else {
                                resetButton(btn, originalText);
                            }
                        } else {
                            // Timeout atau error lain
                            if (confirm('⏱️ GPS timeout atau error.\n\n🌐 Coba deteksi lokasi dari IP address?')) {
                                tryIPBasedLocation(btn, originalText);
                            } else if (confirm('✅ Ambil bantuan tanpa GPS tracking?')) {
                                takeBantuanWithLocation();
                                resetButton(btn, originalText);
                            } else {
                                resetButton(btn, originalText);
                            }
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 8000, // 8 detik timeout (lebih lama untuk laptop)
                        maximumAge: 0
                    }
                );
            } else {
                // Browser tidak support GPS (browser lama)
                const btn = document.getElementById('previewTakeBtn');
                const originalText = btn.textContent;
                console.warn('⚠️ Browser tidak support Geolocation API');
                
                if (confirm('🌐 Browser tidak mendukung GPS.\n\nCoba deteksi lokasi dari IP address?')) {
                    btn.disabled = true;
                    tryIPBasedLocation(btn, originalText);
                } else if (confirm('✅ Ambil bantuan tanpa GPS tracking?')) {
                    takeBantuanWithLocation();
                } else {
                    // User cancel
                }
            }
        }

        // Close modal when clicking outside
        document.getElementById('helpPreviewModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });

        // Listen for help-taken event from Livewire
        window.addEventListener('help-taken', function(event) {
            // Reload page to show updated list
            window.location.reload();
        });
    </script>
</div>