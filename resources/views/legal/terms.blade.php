@php
    $appName       = \App\Models\AppSetting::get('app_name', 'SayaBantu');
    $siteLogo      = \App\Models\AppSetting::get('app_logo');
    $fav           = \App\Models\AppSetting::get('app_favicon') ?: $siteLogo;
    $appEmail      = \App\Models\AppSetting::get('app_email', 'admin@sayabantu.id');
    $appAddress    = \App\Models\AppSetting::get('app_address', 'Indonesia');
    $effectiveDate = '1 Oktober 2026';
    $version       = '1.0';
    $tocItems = [
        ['id' => 'definisi',    'label' => '1. Definisi Istilah',             'short' => 'Definisi'],
        ['id' => 'syarat-umum', 'label' => '2. Syarat Penggunaan',           'short' => 'Syarat Umum'],
        ['id' => 'customer',    'label' => '3. Hak & Kewajiban Customer',    'short' => 'Customer'],
        ['id' => 'mitra',       'label' => '4. Hak & Kewajiban Mitra',       'short' => 'Mitra'],
        ['id' => 'transaksi',   'label' => '5. Transaksi & Saldo',           'short' => 'Transaksi'],
        ['id' => 'larangan',    'label' => '6. Larangan Penggunaan',         'short' => 'Larangan'],
        ['id' => 'sanksi',      'label' => '7. Penangguhan & Sanksi',        'short' => 'Sanksi'],
        ['id' => 'sengketa',    'label' => '8. Penyelesaian Sengketa',       'short' => 'Sengketa'],
        ['id' => 'perubahan',   'label' => '9. Perubahan Ketentuan',         'short' => 'Perubahan'],
        ['id' => 'kontak-sk',   'label' => '10. Kontak & Layanan Bantuan',   'short' => 'Kontak'],
    ];
    $larangan = [
        'Melakukan penipuan, pemalsuan identitas, atau penyalahgunaan data',
        'Menyebarkan konten yang bersifat SARA, pornografi, atau melanggar hukum',
        'Melakukan transaksi di luar mekanisme resmi Platform SayaBantu',
        'Menggunakan Platform untuk kegiatan pencucian uang atau pendanaan ilegal',
        'Melakukan manipulasi rating atau ulasan secara tidak jujur',
        'Mengakses, meretas, atau mengganggu stabilitas sistem Platform',
        'Menggunakan akun milik orang lain tanpa izin tertulis yang sah',
        'Membuat lebih dari satu akun aktif dengan identitas (NIK) yang sama',
        'Melakukan tindakan kekerasan atau intimidasi terhadap Pengguna lain',
        'Mempromosikan layanan atau bisnis ilegal yang merugikan Platform',
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title>Syarat & Ketentuan — {{ $appName }}</title>
    <meta name="description" content="Syarat dan Ketentuan resmi penggunaan platform {{ $appName }}. Baca dan pahami hak serta kewajiban Anda sebagai pengguna layanan kami.">
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
        :root {
            --brand-a: #0284c7;
            --brand-b: #0369a1;
            --brand-c: #6366f1;
        }
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
            background-color: rgba(2, 132, 199, 0.08);
            color: #0284c7;
            font-weight: 700;
            border-left-color: #0284c7;
        }
        .dark .toc-item.active {
            background-color: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            font-weight: 700;
            border-left-color: #38bdf8;
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

<body class="bg-slate-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col antialiased selection:bg-sky-500 selection:text-white">

    {{-- ══════════════════════════════════════════
         TOP READING PROGRESS BAR
    ══════════════════════════════════════════ --}}
    <div class="fixed top-0 left-0 right-0 h-1 bg-transparent z-50 pointer-events-none">
        <div id="readingProgress" class="h-full w-0 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>
    </div>

    {{-- ══════════════════════════════════════════
         STICKY HEADER (Glassmorphic)
    ══════════════════════════════════════════ --}}
    <header class="sticky top-0 z-40 bg-white/85 dark:bg-gray-900/85 backdrop-blur-xl border-b border-gray-200/80 dark:border-gray-800 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            
            {{-- Left: Back to Register Button --}}
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 hover:bg-sky-50 dark:bg-gray-800 dark:hover:bg-sky-950/40 text-gray-700 dark:text-gray-200 hover:text-sky-600 dark:hover:text-sky-400 border border-gray-200/70 dark:border-gray-700/80 transition-all font-semibold text-xs sm:text-sm group flex-shrink-0 shadow-xs active:scale-95"
                   title="Kembali ke Halaman Registrasi">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Register</span>
                    <span class="sm:hidden">Register</span>
                </a>

                {{-- Brand Logo & Name --}}
                <div class="h-5 w-px bg-gray-200 dark:bg-gray-700 hidden md:block"></div>
                <a href="{{ route('home') }}" class="hidden md:flex items-center gap-2.5 min-w-0 group">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $appName }}" class="w-7 h-7 rounded-lg object-contain bg-sky-50 dark:bg-sky-950/50 p-0.5 border border-sky-100 dark:border-sky-900">
                    @else
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-xs">
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
                        class="lg:hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-xs shadow-xs transition-all active:scale-95">
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
    <section class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-white to-slate-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-950 border-b border-gray-200/80 dark:border-gray-800/80 py-10 sm:py-14">
        {{-- Background Glow Highlights --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-48 bg-gradient-to-r from-sky-400/15 via-indigo-400/15 to-blue-400/15 blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">

            {{-- Main Title --}}
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight mb-3">
                Syarat & Ketentuan Layanan
            </h1>

            {{-- Description --}}
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed mb-6">
                Ketentuan resmi yang mengatur hak, kewajiban, dan tata kelola transaksi seluruh pengguna di platform <strong class="text-gray-900 dark:text-gray-200">{{ $appName }}</strong>.
            </p>

            {{-- Meta Badges / Info Pills --}}
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Berlaku: <strong class="text-gray-900 dark:text-white">{{ $effectiveDate }}</strong></span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Versi: <strong class="text-gray-900 dark:text-white">{{ $version }}</strong></span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Waktu Baca: <strong class="text-gray-900 dark:text-white">~6 Menit</strong></span>
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
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Daftar Bab (10)</span>
                        </div>
                        <span id="readingPercentBadge" class="text-[11px] font-bold text-sky-600 dark:text-sky-400">0%</span>
                    </div>

                    <nav class="space-y-1">
                        @foreach($tocItems as $item)
                            <a href="#{{ $item['id'] }}"
                               class="toc-item flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/60 border-l-2 border-transparent">
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    {{-- Quick Switcher to Privacy --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('privacy') }}"
                           class="flex items-center justify-between p-2.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-semibold transition-all group">
                            <span class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Kebijakan Privasi</span>
                            </span>
                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Fast Helpdesk Card --}}
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 dark:from-gray-900 dark:to-gray-800/80 rounded-2xl border border-sky-200/70 dark:border-gray-800 p-4 shadow-xs">
                    <p class="text-xs font-bold text-gray-900 dark:text-white mb-1">Ada pertanyaan hukum?</p>
                    <p class="text-[11px] text-gray-600 dark:text-gray-400 mb-3 leading-relaxed">Tim dukungan SayaBantu siap melayani klarifikasi terkait ketentuan layanan.</p>
                    <a href="mailto:{{ $appEmail }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">
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
                <div class="bg-sky-50/90 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/70 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-xs">
                    <div class="w-9 h-9 rounded-xl bg-sky-500/15 dark:bg-sky-400/20 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs sm:text-sm text-sky-900 dark:text-sky-200 leading-relaxed min-w-0">
                        <strong class="font-bold text-sky-950 dark:text-white block mb-0.5">Persetujuan Pengguna:</strong>
                        Dengan mendaftar, mengakses, atau menggunakan layanan <strong class="font-semibold">{{ $appName }}</strong>, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan yang tercantum dalam dokumen ini.
                    </div>
                </div>

                {{-- ─── BAB 1: DEFINISI ─── --}}
                <section id="definisi" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">1</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Definisi Istilah</h2>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        @php
                            $definisiList = [
                                ['Platform', 'Aplikasi web, sistem digital, dan seluruh layanan SayaBantu yang dikelola oleh Pengelola Platform.'],
                                ['Pengelola', 'Pihak manajemen atau entitas hukum resmi yang mengoperasikan dan memelihara Platform SayaBantu.'],
                                ['Pengguna', 'Setiap individu yang telah melakukan registrasi dan terdaftar, baik berstatus sebagai Customer maupun Mitra.'],
                                ['Customer / Penerima Bantuan', 'Pengguna terdaftar yang mengajukan permintaan permohonan bantuan kebutuhan sehari-hari atau teknis melalui Platform.'],
                                ['Mitra / Pemberi Bantuan', 'Pengguna terdaftar dan terverifikasi yang bersedia menerima serta menyelesaikan penugasan bantuan kepada Customer.'],
                                ['Bantuan', 'Bentuk layanan, pekerjaan serabutan, atau pertolongan sah yang disepakati oleh Customer dan Mitra melalui mekanisme Platform.'],
                                ['Saldo Akun', 'Nilai nominal dana elektronik Pengguna yang tercatat dalam sistem Platform untuk pembayaran bantuan maupun pencairan.'],
                                ['Top Up', 'Proses pengisian Saldo Akun melalui kanal pembayaran resmi yang disediakan oleh Platform.'],
                                ['Withdraw (Pencairan)', 'Proses penarikan saldo penghasilan Mitra ke rekening bank resmi yang telah terdaftar dan tervalidasi.'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach($definisiList as [$istilah, $deskripsi])
                                <div class="p-3.5 rounded-xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800/80 flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3">
                                    <span class="font-bold text-gray-900 dark:text-white sm:w-44 flex-shrink-0 text-sky-600 dark:text-sky-400">{{ $istilah }}</span>
                                    <span class="text-gray-600 dark:text-gray-300 leading-relaxed min-w-0">{{ $deskripsi }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 2: SYARAT PENGGUNAAN ─── --}}
                <section id="syarat-umum" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">2</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Syarat Penggunaan Platform</h2>
                    </div>

                    <div class="space-y-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        @php
                            $syaratUmum = [
                                ['2.1 Persyaratan Usia & Identitas', [
                                    'Pengguna wajib berusia minimal <strong>17 tahun</strong> atau telah memiliki Kartu Tanda Penduduk (KTP) yang sah di wilayah Republik Indonesia.',
                                    'Pendaftaran akun memerlukan NIK valid dan unggahan foto KTP asli yang jelas dan tidak buram.',
                                    'Satu NIK hanya diperbolehkan untuk mendaftarkan 1 (satu) akun aktif di Platform SayaBantu.',
                                ]],
                                ['2.2 Proses Validasi & Verifikasi Akun', [
                                    'Setiap akun baru akan melalui tahapan verifikasi dokumen identitas oleh Tim Admin SayaBantu.',
                                    'Platform berhak menolak pendaftaran atau menangguhkan akun jika data identitas tidak valid atau diragukan keasliannya.',
                                    'Akses terhadap fitur permohonan dan penerimaan bantuan hanya terbuka penuh setelah verifikasi dinyatakan lolos.',
                                ]],
                                ['2.3 Keamanan Kredensial & Akun', [
                                    'Pengguna bertanggung jawab penuh menjaga kerahasiaan kata sandi dan keamanan akses ke perangkat masing-masing.',
                                    'Segala aktivitas yang terjadi melalui akun Pengguna dianggap dilakukan secara sah oleh pemilik akun yang bersangkutan.',
                                ]],
                            ];
                        @endphp

                        @foreach($syaratUmum as [$title, $items])
                            <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2 text-sm">{{ $title }}</h3>
                                <ul class="space-y-1.5">
                                    @foreach($items as $item)
                                        <li class="flex items-start gap-2">
                                            <span class="text-sky-500 flex-shrink-0 mt-0.5 font-bold">›</span>
                                            <span class="min-w-0">{!! $item !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 3: HAK & KEWAJIBAN CUSTOMER ─── --}}
                <section id="customer" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">3</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Hak & Kewajiban Customer</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Hak Customer --}}
                        <div class="p-4 sm:p-5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60">
                            <div class="flex items-center gap-2 mb-3 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Hak Customer</span>
                            </div>
                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Mengajukan kebutuhan bantuan sesuai kategori resmi yang tersedia di Platform.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Melihat rincian profil singkat dan reputasi rating Mitra sebelum menerima penugasan.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Memberikan rating dan ulasan jujur setelah bantuan selesai dituntaskan.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Mengajukan sanggahan atau laporan insiden jika terjadi kendala pada layanan.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Kewajiban Customer --}}
                        <div class="p-4 sm:p-5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60">
                            <div class="flex items-center gap-2 mb-3 text-amber-800 dark:text-amber-300 font-bold text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Kewajiban Customer</span>
                            </div>
                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Memberikan deskripsi kebutuhan bantuan dan lokasi yang jujur, jelas, dan akurat.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Memastikan kecukupan Saldo sebelum membuat permintaan bantuan berbayar.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Memperlakukan Mitra secara sopan, manusiawi, dan bebas dari intimidasi.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Tidak meminta pekerjaan yang membahayakan nyawa atau melanggar hukum RI.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 4: HAK & KEWAJIBAN MITRA ─── --}}
                <section id="mitra" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">4</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Hak & Kewajiban Mitra</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Hak Mitra --}}
                        <div class="p-4 sm:p-5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60">
                            <div class="flex items-center gap-2 mb-3 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Hak Mitra</span>
                            </div>
                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Menerima kompensasi imbalan penuh sesuai nilai yang disepakati pada Platform.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Memilih dan menerima pekerjaan yang cocok dengan keahlian dan ketersediaan waktu.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Melakukan penarikan saldo (Withdraw) ke rekening bank yang telah terverifikasi.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Mendapatkan mediasi adil dari pihak Platform jika terjadi sengketa penugasan.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Kewajiban Mitra --}}
                        <div class="p-4 sm:p-5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60">
                            <div class="flex items-center gap-2 mb-3 text-amber-800 dark:text-amber-300 font-bold text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Kewajiban Mitra</span>
                            </div>
                            <ul class="space-y-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Menyelesaikan bantuan secara profesional, beritikad baik, dan tepat waktu.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Menjaga kerahasiaan data dan privasi tempat tinggal Customer yang dibantu.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Dilarang keras meminta pungutan tambahan di luar tagihan resmi Platform.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>Memperbarui status pekerjaan secara akurat melalui aplikasi.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 5: TRANSAKSI & SALDO ─── --}}
                <section id="transaksi" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">5</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Transaksi & Saldo</h2>
                    </div>

                    <div class="space-y-3.5 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1.5 text-sm">5.1 Pengisian Saldo (Top Up)</h3>
                            <p>Top Up saldo dilakukan melalui metode pembayaran resmi yang disediakan oleh Platform. Saldo yang telah berhasil ditambahkan tidak dapat diuangkan kembali (non-refundable) kecuali dalam kondisi khusus yang disetujui pengelola.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1.5 text-sm">5.2 Penarikan Saldo Mitra (Withdrawal)</h3>
                            <p>Penarikan dana imbalan Mitra hanya dapat dikirimkan ke rekening bank resmi atas nama Mitra yang bersangkutan. Proses pencairan dana membutuhkan waktu 1-3 hari kerja tergantung operasional jaringan perbankan.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1.5 text-sm">5.3 Batas Waktu Pelaporan Masalah Transaksi</h3>
                            <p>Keluhan atau kendala pemotongan saldo wajib dilaporkan ke Customer Service SayaBantu selambat-lambatnya <strong>3 × 24 jam</strong> sejak transaksi dilakukan.</p>
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 6: LARANGAN PENGGUNAAN ─── --}}
                <section id="larangan" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">6</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Larangan Penggunaan</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">Seluruh Pengguna dilarang keras melakukan hal-hal berikut saat menggunakan Platform {{ $appName }}:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($larangan as $item)
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40">
                                <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-snug">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 7: SANKSI & PENANGGUHAN ─── --}}
                <section id="sanksi" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">7</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Penangguhan & Sanksi</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">Pelanggaran terhadap ketentuan layanan ini akan dikenakan tindakan tegas secara bertahap:</p>

                    <div class="space-y-2.5">
                        @php
                            $sanksiList = [
                                ['Teguran Tertulis', 'Diberikan untuk pelanggaran administratif ringan atau peringatan awal.', 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'],
                                ['Suspensi Sementara', 'Pembekuan akses akun selama kurun waktu evaluasi dan investigasi internal.', 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300'],
                                ['Pemblokiran Permanen', 'Penutupan akun permanen dan pembekuan hak atas akun akibat pelanggaran berat.', 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'],
                                ['Tuntutan Hukum', 'Pelaporan tindak pidana kepada kepolisian dan penegak hukum yang berwenang.', 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300'],
                            ];
                        @endphp

                        @foreach($sanksiList as [$title, $desc, $badgeClass])
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold w-fit {{ $badgeClass }}">{{ $title }}</span>
                                <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed min-w-0">{{ $desc }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ─── BAB 8: PENYELESAIAN SENGKETA ─── --}}
                <section id="sengketa" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">8</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Penyelesaian Sengketa</h2>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-xs flex items-center justify-center flex-shrink-0">1</span>
                            <div>
                                <strong class="text-gray-900 dark:text-white block mb-0.5">Musyawarah Mufakat</strong>
                                Setiap perselisihan diselesaikan secara kekeluargaan dan musyawarah terlebih dahulu antar pihak.
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-xs flex items-center justify-center flex-shrink-0">2</span>
                            <div>
                                <strong class="text-gray-900 dark:text-white block mb-0.5">Mediasi Platform</strong>
                                Tim SayaBantu bertindak sebagai penengah netral berdasar bukti rekaman chat dan riwayat transaksi resmi.
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                            <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-xs flex items-center justify-center flex-shrink-0">3</span>
                            <div>
                                <strong class="text-gray-900 dark:text-white block mb-0.5">Yurisdiksi Hukum</strong>
                                Sengketa yang tidak menemukan kesepakatan akan diserahkan kepada Pengadilan Negeri di Indonesia.
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ─── BAB 9: PERUBAHAN KETENTUAN ─── --}}
                <section id="perubahan" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">9</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Perubahan Ketentuan</h2>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                        Pengelola berhak memperbarui Syarat & Ketentuan ini sewaktu-waktu. Pemberitahuan perubahan akan disampaikan melalui:
                    </p>

                    <ul class="space-y-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>Notifikasi pengumuman resmi di dalam aplikasi.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>Pembaruan tanggal efektif dan versi dokumen pada halaman ini.</span>
                        </li>
                    </ul>
                </section>

                {{-- ─── BAB 10: KONTAK & LAYANAN BANTUAN ─── --}}
                <section id="kontak-sk" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/90 dark:border-gray-800 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">10</span>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Kontak & Layanan Bantuan</h2>
                    </div>

                    <div class="p-4 sm:p-5 rounded-xl bg-gradient-to-br from-sky-50 to-indigo-50/50 dark:from-gray-800 dark:to-gray-800/60 border border-sky-200/70 dark:border-gray-700">
                        <p class="font-bold text-gray-900 dark:text-white text-sm mb-3">{{ $appName }} Helpdesk</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div class="flex items-center gap-2.5 text-gray-700 dark:text-gray-300">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="truncate">{{ $appEmail }}</span>
                            </div>

                            <div class="flex items-center gap-2.5 text-gray-700 dark:text-gray-300">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
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

                {{-- Bottom CTA Banner to Privacy Policy --}}
                <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-sky-600 via-blue-600 to-indigo-600 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg mb-1">Pelajari Kebijakan Privasi</h3>
                        <p class="text-xs sm:text-sm text-sky-100 leading-relaxed">Pahami bagaimana data pribadi Anda dilindungi sesuai UU No. 27/2022.</p>
                    </div>
                    <a href="{{ route('privacy') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-sky-700 font-bold text-xs sm:text-sm shadow-xs hover:bg-sky-50 active:scale-95 transition-all flex-shrink-0">
                        <span>Baca Kebijakan Privasi</span>
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
                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Pilih Bab Dokumen</span>
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
                       class="drawer-link flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 hover:bg-sky-50 dark:hover:bg-sky-950/40 border border-gray-100 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 transition-colors">
                        <span class="truncate">{{ $item['label'] }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>

            {{-- Quick Switcher at bottom of drawer --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex-shrink-0">
                <a href="{{ route('privacy') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-bold border border-indigo-200 dark:border-indigo-800">
                    <span>Buka Kebijakan Privasi</span>
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
                <a href="{{ route('terms') }}" class="text-sky-600 dark:text-sky-400">Syarat & Ketentuan</a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('privacy') }}" class="text-gray-500 hover:text-sky-600 dark:text-gray-400 dark:hover:text-sky-400 transition-colors">Kebijakan Privasi</a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('register') }}" class="text-gray-500 hover:text-sky-600 dark:text-gray-400 dark:hover:text-sky-400 transition-colors">Daftar Akun</a>
            </div>
        </div>
    </footer>

    {{-- ══════════════════════════════════════════
         FLOATING BACK TO TOP BUTTON
    ══════════════════════════════════════════ --}}
    <button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})"
            class="fixed bottom-6 right-6 z-30 w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-lg shadow-sky-500/25 flex items-center justify-center active:scale-95 transition-all"
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
