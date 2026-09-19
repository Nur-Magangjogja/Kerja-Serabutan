@php
    $appName       = \App\Models\AppSetting::get('app_name', 'SayaBantu');
    $siteLogo      = \App\Models\AppSetting::get('app_logo');
    $fav           = \App\Models\AppSetting::get('app_favicon') ?: $siteLogo;
    $appEmail      = \App\Models\AppSetting::get('app_email', 'admin@sayabantu.id');
    $appAddress    = \App\Models\AppSetting::get('app_address', 'Indonesia');
    $effectiveDate = '1 Oktober 2026';
    $version       = '1.0';
    $tocItems = [
        ['id' => 'komitmen',      'label' => '1. Komitmen Privasi',          'short' => 'Komitmen'],
        ['id' => 'data-dikumpul', 'label' => '2. Data yang Dikumpulkan',     'short' => 'Data'],
        ['id' => 'penggunaan',    'label' => '3. Tujuan Penggunaan Data',    'short' => 'Penggunaan'],
        ['id' => 'penyimpanan',   'label' => '4. Penyimpanan & Keamanan',    'short' => 'Keamanan'],
        ['id' => 'pihak-ketiga',  'label' => '5. Pengungkapan Pihak Ketiga', 'short' => 'Pihak Ketiga'],
        ['id' => 'hak-pengguna',  'label' => '6. Hak Anda (UU PDP)',         'short' => 'Hak Pengguna'],
        ['id' => 'cookies',       'label' => '7. Cookies & Teknologi',       'short' => 'Cookies'],
        ['id' => 'retensi',       'label' => '8. Retensi & Masa Simpan',     'short' => 'Retensi'],
        ['id' => 'perubahan-pp',  'label' => '9. Perubahan Kebijakan',       'short' => 'Perubahan'],
        ['id' => 'kontak-pp',     'label' => '10. Kontak DPO & Helpdesk',    'short' => 'Kontak DPO'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title>Kebijakan Privasi — {{ $appName }}</title>
    <meta name="description" content="Kebijakan Privasi resmi {{ $appName }}. Pelajari bagaimana kami mengumpulkan, mengelola, dan melindungi data pribadi Anda sesuai UU No. 27/2022 (UU PDP).">
    <meta name="robots" content="index, follow">

    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Anti-FOUC Theme Initializer --}}
    <script>
        (function(){
            var saved = localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (saved === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow-x: clip;
        }
        * {
            word-break: break-word;
        }
        /* Custom slim scrollbar for sticky ToC */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
            border-radius: 4px;
        }
        /* Reading Progress Bar */
        #readingProgress {
            transition: width 0.1s ease-out;
        }
        /* Desktop ToC Links */
        .toc-item {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .toc-item.active {
            background-color: rgba(99, 102, 241, 0.08);
            color: #6366f1;
            font-weight: 700;
            border-left-color: #6366f1;
        }
        .dark .toc-item.active {
            background-color: rgba(129, 140, 248, 0.12);
            color: #a5b4fc;
            font-weight: 700;
            border-left-color: #818cf8;
        }
        /* Sections scroll margin */
        section[id] {
            scroll-margin-top: 90px;
        }
        /* Mobile Drawer Transition */
        #mobileDrawer {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        #mobileDrawerPanel {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #mobileDrawer.drawer-closed {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        #mobileDrawer.drawer-closed #mobileDrawerPanel {
            transform: translateY(100%);
        }
        /* Back to top transition */
        #btt {
            transition: opacity 0.3s, transform 0.3s, visibility 0.3s;
        }
        #btt.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        #btt:not(.show) {
            opacity: 0;
            visibility: hidden;
            transform: translateY(16px);
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    {{-- ══════════════════════════════════════════
         TOP READING PROGRESS BAR
    ══════════════════════════════════════════ --}}
    <div class="fixed top-0 left-0 right-0 h-1 bg-transparent z-50 pointer-events-none">
        <div id="readingProgress" class="h-full w-0 bg-gradient-to-r from-indigo-500 via-purple-600 to-sky-500"></div>
    </div>

    {{-- ══════════════════════════════════════════
         STICKY HEADER (Glassmorphic)
    ══════════════════════════════════════════ --}}
    <header class="sticky top-0 z-40 bg-white/85 dark:bg-gray-900/85 backdrop-blur-xl border-b border-gray-200/80 dark:border-gray-800 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            
            {{-- Left: Back to Register Button --}}
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-indigo-950/40 text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200/70 dark:border-gray-700/80 transition-all font-semibold text-xs sm:text-sm group flex-shrink-0 shadow-xs active:scale-95"
                   title="Kembali ke Halaman Registrasi">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Register</span>
                    <span class="sm:hidden">Register</span>
                </a>

                {{-- Brand Logo & Name --}}
                <div class="h-5 w-px bg-gray-200 dark:bg-gray-700 hidden md:block"></div>
                <a href="{{ route('home') }}" class="hidden md:flex items-center gap-2.5 min-w-0 group">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $appName }}" class="w-7 h-7 rounded-lg object-contain bg-indigo-50 dark:bg-indigo-950/50 p-0.5 border border-indigo-100 dark:border-indigo-900">
                    @else
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                    @endif
                    <span class="font-bold text-gray-900 dark:text-white tracking-tight text-sm truncate">{{ $appName }}</span>
                </a>
            </div>

            {{-- Right: Theme Switcher & Mobile ToC Button --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                
                {{-- Theme Switcher Button --}}
                <button id="themeToggleBtn" type="button" aria-label="Ganti Mode Tampilan (Gelap/Terang)"
                        class="p-2 sm:px-3 sm:py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/70 dark:border-gray-700/80 transition-all flex items-center gap-2 text-xs font-medium shadow-xs">
                    <!-- Sun Icon for Dark Mode -->
                    <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon for Light Mode -->
                    <svg class="w-4 h-4 block dark:hidden text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span class="hidden sm:inline dark:text-gray-200 text-gray-700 text-xs">Mode</span>
                </button>

                {{-- Mobile Table of Contents Trigger Button --}}
                <button id="openDrawerBtn" type="button" aria-label="Buka Daftar Isi"
                        class="lg:hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                    </svg>
                    <span>Daftar Bab</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ══════════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-indigo-50/80 via-white to-slate-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-950 border-b border-gray-200/80 dark:border-gray-800/80 py-10 sm:py-14">
        {{-- Background Glow Highlights --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-48 bg-gradient-to-r from-indigo-400/15 via-purple-400/15 to-sky-400/15 blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
        
            {{-- Main Title --}}
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight mb-3">
                Kebijakan Privasi & Perlindungan Data
            </h1>

            {{-- Description --}}
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed mb-6">
                Komitmen kami dalam mengumpulkan, mengelola, menyimpan, dan melindungi keamanan data pribadi seluruh pengguna di platform <strong class="text-gray-900 dark:text-gray-200">{{ $appName }}</strong>.
            </p>

            {{-- Meta Badges / Info Pills --}}
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Berlaku: <strong class="text-gray-900 dark:text-white">{{ $effectiveDate }}</strong></span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Versi: <strong class="text-gray-900 dark:text-white">{{ $version }}</strong></span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Waktu Baca: <strong class="text-gray-900 dark:text-white">~5 Menit</strong></span>
                </div>

            </div>

        </div>
    </section>

    {{-- ══════════════════════════════════════════
         MAIN CONTENT: SIDEBAR (TOC) + ARTICLES
    ══════════════════════════════════════════ --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ── DESKTOP STICKY SIDEBAR (TOC) ── --}}
            <aside class="hidden lg:block lg:col-span-4 xl:col-span-3 sticky top-20 self-start space-y-4">
                
                {{-- Table of Contents Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 shadow-sm p-4 sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
                    <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Daftar Bab (10)</span>
                        </div>
                        <span id="readingPercentBadge" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">0%</span>
                    </div>

                    <nav class="space-y-1">
                        @foreach($tocItems as $item)
                            <a href="#{{ $item['id'] }}"
                               class="toc-item flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/60 border-l-2 border-transparent">
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    {{-- Quick Switcher to Terms --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('terms') }}"
                           class="flex items-center justify-between p-2.5 rounded-xl bg-sky-50/60 dark:bg-sky-950/30 hover:bg-sky-50 dark:hover:bg-sky-950/50 border border-sky-100 dark:border-sky-900/50 text-sky-700 dark:text-sky-300 text-xs font-semibold transition-all group">
                            <span class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Syarat & Ketentuan</span>
                            </span>
                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Fast DPO Helpdesk Card --}}
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-900 dark:to-gray-800/80 rounded-2xl border border-indigo-200/70 dark:border-gray-800 p-4 shadow-xs">
                    <p class="text-xs font-bold text-gray-900 dark:text-white mb-1">Permintaan Hak Data?</p>
                    <p class="text-[11px] text-gray-600 dark:text-gray-400 mb-3 leading-relaxed">Hubungi Data Protection Officer (DPO) untuk akses, ralat, atau penghapusan data.</p>
                    <a href="mailto:{{ $appEmail }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        <span>{{ $appEmail }}</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </aside>

            {{-- ── ARTICLE CONTENT ── --}}
            <article class="lg:col-span-8 xl:col-span-9 space-y-6 min-w-0">

                {{-- Important Notice Banner --}}
                <div class="bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/70 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-xs">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/15 dark:bg-indigo-400/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 leading-relaxed min-w-0">
                        <strong class="font-bold text-indigo-950 dark:text-white block mb-0.5">Kepatuhan Hukum Privasi:</strong>
                        Kebijakan Privasi ini disusun berdasarkan ketentuan <strong>Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)</strong> guna menjamin keamanan hak privasi Anda di seluruh ekosistem <strong class="font-semibold">{{ $appName }}</strong>.
                    </div>
                </div>

                {{-- ─── BAB 1: KOMITMEN PRIVASI ─── --}}
                <section id="komitmen" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">1</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Komitmen Privasi Kami</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        @php
                            $komitmenList = [
                                'Hanya mengumpulkan data yang esensial untuk operasional dan verifikasi layanan.',
                                'Menerapkan enkripsi data sensitif berstandar industri modern.',
                                'Transparan mengenai tujuan pemrosesan data tanpa syarat tersembunyi.',
                                'Tidak menjual data pribadi pengguna kepada pihak ketiga untuk periklanan luar.',
                                'Memfasilitasi hak subjek data untuk meminta pembaruan atau penghapusan.',
                                'Mematuhi seluruh regulasi dan audit kepatuhan perlindungan data di Indonesia.',
                            ];
                        @endphp

                        @foreach($komitmenList as $item)
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-200">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 2: DATA YANG DIKUMPULKAN ─── --}}
                <section id="data-dikumpul" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">2</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Data yang Kami Kumpulkan</h2>
                    </div>

                    <div class="space-y-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        @php
                            $dataKelompok = [
                                ['2.1 Data Identitas & Verifikasi (Wajib)', [
                                    'Nama lengkap sesuai identitas resmi KTP.',
                                    'Nomor Induk Kependudukan (NIK) untuk verifikasi keaslian akun.',
                                    'Foto KTP asli yang diunggah saat registrasi untuk pencegahan penipuan.',
                                    'Alamat email dan nomor telepon / WhatsApp yang aktif.',
                                    'Foto profil pengguna (opsional).',
                                ]],
                                ['2.2 Data Rekening & Keuangan (Khusus Mitra / Saldo)', [
                                    'Nama bank tujuan dan nama pemilik rekening resmi.',
                                    'Nomor rekening bank untuk pencairan kompensasi Mitra (Withdraw).',
                                    'Catatan riwayat saldo, top-up, dan transaksi bantuan.',
                                ]],
                                ['2.3 Data Teknis & Lokasi Operasional', [
                                    'Koordinat lokasi (GPS) saat mengajukan atau menerima penugasan bantuan.',
                                    'Alamat detail tempat penugasan bantuan yang disepakati.',
                                    'Log perangkat, tipe browser, IP address, dan waktu sesi akses.',
                                ]],
                            ];
                        @endphp

                        @foreach($dataKelompok as [$title, $items])
                            <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2 text-sm">{{ $title }}</h3>
                                <ul class="space-y-1.5">
                                    @foreach($items as $item)
                                        <li class="flex items-start gap-2">
                                            <span class="text-indigo-500 flex-shrink-0 mt-0.5 font-bold">›</span>
                                            <span class="min-w-0">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        {{-- Sensitive Data Security Note --}}
                        <div class="p-3.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 text-amber-900 dark:text-amber-200">
                            <strong>Perlindungan Data Spesifik NIK & KTP:</strong> Data NIK dan foto KTP diproses semata-mata untuk verifikasi KYC (Know Your Customer) demi menjaga integritas komunitas pengguna SayaBantu dari tindak pidana dan penipuan.
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 3: TUJUAN PENGGUNAAN DATA ─── --}}
                <section id="penggunaan" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">3</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Tujuan Penggunaan Data</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">Informasi yang dikumpulkan diproses untuk tujuan-tujuan berikut:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                        @php
                            $tujuanList = [
                                ['01', 'Verifikasi & Keamanan', 'Memvalidasi keaslian pengguna baru, mencegah duplikasi akun, dan melindungi platform dari penipuan.'],
                                ['02', 'Operasional Penugasan', 'Mempertemukan permohonan bantuan Customer dengan Mitra yang sesuai secara cepat dan tepat.'],
                                ['03', 'Pemrosesan Transaksi', 'Mengelola catatan saldo, verifikasi pembayaran top up, dan pencairan kompensasi Mitra.'],
                                ['04', 'Notifikasi & Komunikasi', 'Mengirimkan pembaruan status bantuan secara realtime dan informasi keamanan akun.'],
                                ['05', 'Peningkatan Kualitas', 'Menganalisis performa sistem secara agregat tanpa mengekspos identitas individu.'],
                                ['06', 'Kepatuhan Regulasi', 'Memenuhi kewajiban pelaporan hukum jika diperintahkan oleh otoritas peradilan resmi.'],
                            ];
                        @endphp

                        @foreach($tujuanList as [$num, $title, $desc])
                            <div class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex items-start gap-3">
                                <span class="text-xl font-black text-indigo-300 dark:text-indigo-600 leading-none flex-shrink-0">{{ $num }}</span>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">{{ $title }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-xs">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 4: PENYIMPANAN & KEAMANAN DATA ─── --}}
                <section id="penyimpanan" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">4</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Penyimpanan & Keamanan Data</h2>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white mb-1">Enkripsi Database</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-xs">Kata sandi dan data kredensial dienkripsi dengan algoritma hash yang kuat.</p>
                            </div>

                            <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white mb-1">Koneksi SSL / TLS</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-xs">Semua jalur lalu lintas data dari browser dilindungi sertifikat HTTPS terenkripsi.</p>
                            </div>

                            <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white mb-1">Server di Indonesia</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-xs">Pusat data berlokasi di Indonesia untuk kepatuhan kedaulatan data nasional.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 5: PENGUNGKAPAN KEPADA PIHAK KETIGA ─── --}}
                <section id="pihak-ketiga" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">5</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Pengungkapan kepada Pihak Ketiga</h2>
                    </div>

                    <div class="p-3.5 rounded-xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/60 text-rose-900 dark:text-rose-200 text-xs sm:text-sm font-semibold mb-4">
                        Kami <u>TIDAK PERNAH</u> memperjualbelikan, menyewakan, atau memperdagangkan data pribadi Anda kepada pihak luar untuk keperluan pemasaran manapun.
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3">Data hanya dibagikan secara terbatas pada kondisi berikut:</p>

                    <div class="space-y-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <strong class="text-gray-900 dark:text-white block mb-0.5">Antar Pengguna yang Terlibat Penugasan:</strong>
                            Nama panggilan, nomor kontak, dan alamat tujuan bantuan hanya dibagikan kepada Mitra atau Customer yang sedang aktif dalam satu penugasan bersama.
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <strong class="text-gray-900 dark:text-white block mb-0.5">Penyedia Jasa Infrastruktur & Pembayaran:</strong>
                            Mitra teknologi perbankan atau payment gateway yang terikat perjanjian kerahasiaan ketat guna memproses transaksi pembayaran.
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <strong class="text-gray-900 dark:text-white block mb-0.5">Perintah Hukum Resmi:</strong>
                            Jika terdapat surat perintah pengadilan atau permintaan penegak hukum sah yang mewajibkan penyerahan data untuk pembuktian pidana.
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 6: HAK ANDA SEBAGAI SUBJEK DATA (UU PDP) ─── --}}
                <section id="hak-pengguna" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">6</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Hak Anda sebagai Subjek Data (UU PDP)</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">Sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi, Anda memiliki hak-hak hukum berikut:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                        @php
                            $hakPDP = [
                                ['Hak Mendapatkan Akses', 'Mengetahui dan memperoleh salinan data pribadi yang tersimpan dalam sistem kami.'],
                                ['Hak Pembaruan & Koreksi', 'Mengoreksi data yang tidak akurat, tidak lengkap, atau sudah kedaluwarsa.'],
                                ['Hak Penghapusan (Erasure)', 'Meminta pemusnahan data pribadi saat akun Anda ditutup secara permanen.'],
                                ['Hak Penolakan Pemrosesan', 'Menolak pemrosesan data untuk keperluan profil otomatis atau pemasaran.'],
                                ['Hak Portabilitas Data', 'Menerima data Anda dalam format terstruktur yang dapat dibaca mesin umum.'],
                                ['Hak Penarikan Persetujuan', 'Menarik kembali persetujuan pemrosesan data pribadi yang telah diberikan sebelumnya.'],
                            ];
                        @endphp

                        @foreach($hakPDP as [$hakTitle, $hakDesc])
                            <div class="p-3.5 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40">
                                <h4 class="font-bold text-indigo-900 dark:text-indigo-300 mb-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span>{{ $hakTitle }}</span>
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $hakDesc }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 7: COOKIES & TEKNOLOGI ─── --}}
                <section id="cookies" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">7</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Cookies & Teknologi Pelacakan</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                        Platform SayaBantu memanfaatkan cookies lokal untuk menjaga sesi login aktif, mengingat preferensi tampilan (seperti mode gelap/terang), dan melindungi formulir dari serangan CSRF.
                    </p>

                    <div class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 text-xs text-gray-600 dark:text-gray-400">
                        Kami <strong>tidak menggunakan</strong> cookies pelacak pihak ketiga (third-party tracking) untuk memata-matai riwayat penelusuran Anda di luar domain resmi {{ $appName }}.
                    </div>
                </section>

                {{-- ─── BAB 8: RETENSI & MASA SIMPAN DATA ─── --}}
                <section id="retensi" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">8</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Retensi & Masa Simpan Data</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">Kami menyimpan data pribadi Anda selama jangka waktu berikut:</p>

                    <div class="space-y-2 text-xs sm:text-sm">
                        @php
                            $retensiList = [
                                ['Profil & Identitas Akun', 'Disimpan selama akun aktif, serta hingga 2 tahun pasca penutupan akun untuk arsip audit.'],
                                ['Catatan Transaksi Keuangan & Saldo', 'Minimal 5 tahun sesuai kepatuhan regulasi pencatatan keuangan Republik Indonesia.'],
                                ['Riwayat Titik Lokasi Bantuan', '1 tahun sejak status pekerjaan bantuan dinyatakan tuntas.'],
                                ['Log Aktivitas & Riwayat Akses Sistem', 'Maksimal 90 hari untuk keperluan analisis keamanan teknis.'],
                            ];
                        @endphp

                        @foreach($retensiList as [$kategori, $durasi])
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $kategori }}</span>
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold sm:text-right">{{ $durasi }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 9: PERUBAHAN KEBIJAKAN ─── --}}
                <section id="perubahan-pp" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">9</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Perubahan Kebijakan Privasi</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                        Kebijakan Privasi ini dapat diperbarui sewaktu-waktu guna menyesuaikan pembaruan fitur teknologi atau amandemen regulasi hukum baru. Perubahan material akan diumumkan minimal <strong class="text-gray-900 dark:text-white">7 hari kerja sebelumnya</strong> melalui banner aplikasi atau surel resmi.
                    </p>
                </section>

                {{-- ─── BAB 10: KONTAK DPO & HELPDESK ─── --}}
                <section id="kontak-pp" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">10</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Kontak Data Protection Officer (DPO)</h2>
                    </div>

                    <div class="p-4 sm:p-5 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50/50 dark:from-gray-800 dark:to-gray-800/60 border border-indigo-200/70 dark:border-gray-700">
                        <p class="font-bold text-gray-900 dark:text-white text-sm mb-1">{{ $appName }} Privacy Office</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">Pengajuan permohonan hak data Anda akan direspons paling lambat dalam <strong>14 hari kerja</strong>.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div class="flex items-center gap-2.5 text-gray-700 dark:text-gray-300">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="truncate">{{ $appEmail }}</span>
                            </div>

                            <div class="flex items-center gap-2.5 text-gray-700 dark:text-gray-300">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="truncate">{{ $appAddress }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Bottom CTA Banner to Terms --}}
                <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-sky-600 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg mb-1">Pelajari Syarat & Ketentuan</h3>
                        <p class="text-xs sm:text-sm text-indigo-100 leading-relaxed">Pahami aturan main dan ketentuan transaksi antara Customer dan Mitra.</p>
                    </div>
                    <a href="{{ route('terms') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-indigo-700 font-bold text-xs sm:text-sm shadow-xs hover:bg-indigo-50 active:scale-95 transition-all flex-shrink-0">
                        <span>Baca Syarat & Ketentuan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

            </article>
        </div>
    </main>

    {{-- ══════════════════════════════════════════
         MOBILE TOC DRAWER (Bottom Sheet Modal)
    ══════════════════════════════════════════ --}}
    <div id="mobileDrawer" class="fixed inset-0 z-50 drawer-closed flex flex-col justify-end lg:hidden">
        {{-- Backdrop --}}
        <div id="drawerBackdrop" class="absolute inset-0 bg-gray-950/60 backdrop-blur-xs"></div>

        {{-- Panel Content --}}
        <div id="mobileDrawerPanel" class="relative w-full bg-white dark:bg-gray-900 rounded-t-3xl border-t border-gray-200 dark:border-gray-800 p-5 shadow-2xl max-h-[82vh] flex flex-col">
            
            {{-- Drag indicator bar --}}
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full mx-auto mb-4 flex-shrink-0"></div>

            <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Pilih Bab Kebijakan Privasi</span>
                </div>
                <button id="closeDrawerBtn" type="button" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Drawer Link Items --}}
            <div class="overflow-y-auto space-y-1.5 py-2 flex-1 overscroll-contain">
                @foreach($tocItems as $item)
                    <a href="#{{ $item['id'] }}"
                       class="drawer-link flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-gray-100 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 transition-colors">
                        <span class="truncate">{{ $item['label'] }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>

            {{-- Quick Switcher at bottom of drawer --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex-shrink-0">
                <a href="{{ route('terms') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 text-xs font-bold border border-sky-200 dark:border-sky-800">
                    <span>Buka Syarat & Ketentuan</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════ --}}
    <footer class="mt-auto border-t border-gray-200 dark:border-gray-800/80 bg-white dark:bg-gray-900 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>© {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi undang-undang.</span>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="{{ route('terms') }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Syarat & Ketentuan</a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('privacy') }}" class="text-indigo-600 dark:text-indigo-400">Kebijakan Privasi</a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('register') }}" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Daftar Akun</a>
            </div>
        </div>
    </footer>

    {{-- ══════════════════════════════════════════
         FLOATING BACK TO TOP BUTTON
    ══════════════════════════════════════════ --}}
    <button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})"
            class="fixed bottom-6 right-6 z-30 w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 flex items-center justify-center active:scale-95 transition-all"
            aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    {{-- ══════════════════════════════════════════
         SCRIPTS: THEME, PROGRESS & INTERSECTION
    ══════════════════════════════════════════ --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Theme Switcher Logic
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            themeToggleBtn?.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                const mode = isDark ? 'dark' : 'light';
                localStorage.setItem('theme', mode);
                localStorage.setItem('color-theme', mode);
                document.documentElement.style.colorScheme = mode;
            });

            // 2. Reading Progress Bar & Back-to-Top Logic
            const progressBar = document.getElementById('readingProgress');
            const percentBadge = document.getElementById('readingPercentBadge');
            const btt = document.getElementById('btt');

            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                
                if (progressBar) progressBar.style.width = progress + '%';
                if (percentBadge) percentBadge.textContent = Math.round(progress) + '%';
                if (btt) btt.classList.toggle('show', scrollTop > 320);
            }, { passive: true });

            // 3. Desktop ToC Active Link Highlighter (IntersectionObserver)
            const sections = document.querySelectorAll('section[id]');
            const tocItems = document.querySelectorAll('.toc-item');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const activeId = entry.target.getAttribute('id');
                        tocItems.forEach(item => {
                            const href = item.getAttribute('href').replace('#', '');
                            item.classList.toggle('active', href === activeId);
                        });
                    }
                });
            }, {
                rootMargin: '-10% 0px -70% 0px',
                threshold: 0.05
            });

            sections.forEach(sec => observer.observe(sec));

            // 4. Mobile Drawer Open/Close
            const mobileDrawer = document.getElementById('mobileDrawer');
            const openDrawerBtn = document.getElementById('openDrawerBtn');
            const closeDrawerBtn = document.getElementById('closeDrawerBtn');
            const drawerBackdrop = document.getElementById('drawerBackdrop');
            const drawerLinks = document.querySelectorAll('.drawer-link');

            const openDrawer = () => mobileDrawer?.classList.remove('drawer-closed');
            const closeDrawer = () => mobileDrawer?.classList.add('drawer-closed');

            openDrawerBtn?.addEventListener('click', openDrawer);
            closeDrawerBtn?.addEventListener('click', closeDrawer);
            drawerBackdrop?.addEventListener('click', closeDrawer);

            drawerLinks.forEach(link => {
                link.addEventListener('click', closeDrawer);
            });
        });
    </script>
</body>
</html>
