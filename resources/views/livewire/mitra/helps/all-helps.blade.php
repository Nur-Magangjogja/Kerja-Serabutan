@php
    $viewMode = $viewMode ?? 'list';
    $distanceRadius = $distanceRadius ?? 'all';
    $sortBy = $sortBy ?? 'nearby';
    $mapHelps = $mapHelps ?? [];
    $userCity = $userCity ?? null;
    $mitraLat = $mitraLat ?? null;
    $mitraLng = $mitraLng ?? null;
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        .card-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-shadow-hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .focus-ring:focus { outline: none; box-shadow: 0 0 0 3px rgba(14,165,164,0.2); }
        
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

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header - BRImo Style -->
        <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-3">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1">
                        <h1 class="text-lg font-bold">Semua Bantuan</h1>
                        <p class="text-xs text-white/90 mt-0.5">Cari bantuan yang tersedia</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('components.notification-icon', ['route' => route('mitra.notifications.index')])
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="relative mt-3">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama bantuan, lokasi, atau customer..."
                        class="w-full px-4 py-2.5 rounded-xl bg-white/95 text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-white/50 outline-none transition text-sm shadow-sm">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- View Mode Switcher (Daftar / Peta Radar) -->
                <div class="flex items-center justify-between mt-3 bg-white/15 p-1 rounded-xl gap-1">
                    <button type="button" wire:click="$set('viewMode', 'list')"
                        class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $viewMode === 'list' ? 'bg-white text-primary-600 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Daftar Bantuan
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'map')"
                        class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $viewMode === 'map' ? 'bg-white text-primary-600 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Peta Radar (Live GPS)
                    </button>
                </div>

                <!-- Distance Radius Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-2 mt-1">
                    <button type="button" wire:click="$set('distanceRadius', 'all')"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $distanceRadius === 'all' ? 'bg-white text-primary-600 shadow-sm font-bold' : 'bg-white/20 text-white hover:bg-white/30' }}">
                        Semua Jarak
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '5')"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $distanceRadius === '5' ? 'bg-white text-primary-600 shadow-sm font-bold' : 'bg-white/20 text-white hover:bg-white/30' }}">
                        🎯 &lt; 5 km
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '15')"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $distanceRadius === '15' ? 'bg-white text-primary-600 shadow-sm font-bold' : 'bg-white/20 text-white hover:bg-white/30' }}">
                        🛵 &lt; 15 km
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '25')"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $distanceRadius === '25' ? 'bg-white text-primary-600 shadow-sm font-bold' : 'bg-white/20 text-white hover:bg-white/30' }}">
                        📍 &lt; 25 km
                    </button>
                    @if($userCity)
                    <button type="button" wire:click="$set('distanceRadius', 'city')"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $distanceRadius === 'city' ? 'bg-white text-primary-600 shadow-sm font-bold' : 'bg-white/20 text-white hover:bg-white/30' }}">
                        🏙️ {{ $userCity->name }}
                    </button>
                    @endif
                </div>
            </div>

            <!-- Curved separator (SVG) to create non-flat divider into content -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-6 min-h-[60vh]">
            @if(session()->has('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if(session()->has('message'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <div class="flex-1 font-medium">{{ session('message') }}</div>
                </div>
            @endif

            @if (!empty($activeTask))
                <div class="mb-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-2xl p-4 shadow-sm">
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
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-500 {{ $activeTask->progress_percentage < 100 ? 'animate-pulse' : '' }}"
                             style="width: {{ $activeTask->progress_percentage }}%;"></div>
                    </div>
                </div>
            @endif

            <!-- GPS Status Bar -->
            <div class="flex items-center justify-between mb-4 bg-gray-50 border border-gray-100 rounded-xl p-2.5 text-xs text-gray-600 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <span id="mitra-gps-indicator" class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="mitra-gps-text" class="font-medium">Mendeteksi lokasi GPS...</span>
                </div>
                <button type="button" onclick="refreshMitraGPS()" class="text-primary-600 hover:text-primary-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Perbarui GPS
                </button>
            </div>

            @if($viewMode === 'map')
                <!-- Radar Map View Container -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-800">🗺️ Peta Radar Bantuan Aktif</span>
                        <span class="text-xs text-gray-500">{{ count($mapHelps) }} titik ditemukan</span>
                    </div>
                    <div class="relative rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-gray-100">
                        <div wire:ignore id="mitraRadarMap" style="height: 440px; min-height: 440px;" class="w-full"></div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5 text-center">Sentuh pin di peta untuk melihat ringkasan tugas dan mengambil pekerjaan.</p>
                </div>
            @else
                <!-- Sort Filter in List View -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-gray-500 font-medium">Urutkan:</span>
                    <select wire:model.live="sortBy" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="nearby">📍 Terdekat (Jarak GPS)</option>
                        <option value="latest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="price_high">Harga Tertinggi</option>
                        <option value="price_low">Harga Terendah</option>
                    </select>
                </div> 

                <div class="space-y-4">
                    <div class="space-y-3.5 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none" wire:target="distanceRadius,sortBy,search,viewMode">
                        {{-- List based on filter --}}
                        @forelse($helps as $help)
                        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-all border border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 flex-shrink-0 flex items-center justify-center">
                                    @if($help->photo)
                                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xl">{{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}</span>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-bold text-sm text-gray-900 line-clamp-1">{{ $help->title }}</h3>
                                        <span class="text-xs font-extrabold whitespace-nowrap text-primary-600">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                    </div>

                                    <!-- Tags & Badges: Distance + Status -->
                                    <div class="flex items-center gap-1.5 flex-wrap mb-2">
                                        @if(isset($help->distance_km) && $help->distance_km !== null)
                                            @if($help->distance_km <= 5)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    🟢 {{ $help->distance_km }} km (Dekat)
                                                </span>
                                            @elseif($help->distance_km <= 25)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    🟡 {{ $help->distance_km }} km
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    🔴 {{ $help->distance_km }} km (Jauh)
                                                </span>
                                            @endif
                                        @endif

                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $help->city->name ?? 'Indonesia' }}
                                        </span>

                                        <span class="text-[11px] text-gray-400 ml-auto">{{ optional($help->created_at)->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-3 leading-relaxed">{{ Str::limit($help->description, 100) }}</p>

                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-2 font-medium">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif

                                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-50">
                                        <span class="text-xs text-gray-500 truncate">👤 {{ optional($help->user)->name ?? 'Customer' }}</span>
                                        <div class="flex items-center gap-2">
                                            @php 
                                                $schedLabel = $help->scheduled_at ? \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') : '';
                                                $distVal = $help->distance_km ?? null;
                                                $cityName = $help->city->name ?? 'Luar Daerah';
                                            @endphp
                                            @if(!empty($activeTask))
                                                <button type="button" onclick="showHelpPreview({{ $help->id }}, '{{ addslashes($help->title) }}', {{ $help->amount }}, '{{ addslashes($schedLabel) }}', {{ $distVal ?? 'null' }}, '{{ addslashes($cityName) }}')" class="px-3.5 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-semibold hover:bg-gray-200 transition" title="Sedang mengerjakan tugas lain">Lihat Rincian</button>
                                            @else
                                                <button type="button" onclick="showHelpPreview({{ $help->id }}, '{{ addslashes($help->title) }}', {{ $help->amount }}, '{{ addslashes($schedLabel) }}', {{ $distVal ?? 'null' }}, '{{ addslashes($cityName) }}')" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 transition">Lihat</button>
                                                <button type="button" onclick="showHelpPreview({{ $help->id }}, '{{ addslashes($help->title) }}', {{ $help->amount }}, '{{ addslashes($schedLabel) }}', {{ $distVal ?? 'null' }}, '{{ addslashes($cityName) }}')" class="px-3.5 py-1.5 bg-primary-600 text-white rounded-lg text-xs font-bold hover:bg-primary-700 shadow-sm transition">Ambil</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <!-- Empty State -->
                        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-800">{{ $search ? 'Tidak ada bantuan ditemukan' : 'Tidak ada bantuan di radius ini' }}</p>
                            <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">
                                {{ $search ? 'Coba cari dengan kata kunci lain' : 'Pilih tab "Semua Jarak" atau perbesar radius jangkauan untuk melihat bantuan lain.' }}
                            </p>
                            @if($distanceRadius !== 'all')
                            <button type="button" wire:click="$set('distanceRadius', 'all')" class="mt-4 px-4 py-2 bg-primary-50 text-primary-600 rounded-xl text-xs font-bold border border-primary-200">
                                Lihat Semua Jarak
                            </button>
                            @endif
                        </div>
                        @endforelse

                        <!-- Pagination -->
                        @if($helps->hasPages())
                            <div class="mt-6">
                                {{ $helps->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>


    <!-- Modal Preview Bantuan (Bottom Sheet Style) -->
    <div id="helpPreviewModal" class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 hidden pb-16">
        <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Detail Permintaan Bantuan</h3>
                <button type="button" onclick="closePreviewModal()" class="p-1.5 hover:bg-gray-100 rounded-full transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 pb-6">
                <!-- Distance Warning (if > 25km) -->
                <div id="previewDistanceWarning" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-3.5 mb-4 text-xs text-rose-800">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <div>
                            <p class="font-bold">Perhatian: Jarak Bantuan Cukup Jauh</p>
                            <p class="mt-0.5 text-[11px] text-rose-700" id="previewDistanceWarningText">Lokasi ini berada di luar radius normal. Pastikan Anda sanggup menjangkau lokasi sebelum menerima tugas.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3.5">
                    <p class="text-xs text-gray-500 font-semibold mb-0.5">Judul Permintaan</p>
                    <p id="previewTitle" class="text-base font-bold text-gray-900">-</p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3.5">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <p class="text-[11px] text-blue-700 font-semibold mb-0.5">Nominal Upah</p>
                        <p id="previewAmount" class="text-sm font-extrabold text-blue-900">Rp 0</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3">
                        <p class="text-[11px] text-gray-500 font-semibold mb-0.5">Estimasi Jarak</p>
                        <p id="previewDistance" class="text-sm font-bold text-gray-800">Menghitung...</p>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Jadwal Permintaan</p>
                    <div id="previewScheduled" class="text-xs text-gray-700 font-medium bg-gray-50 p-2.5 rounded-lg">-</div>
                </div>

                <!-- Notice -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600">
                    <p class="font-semibold text-gray-800 mb-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        Informasi Penugasan
                    </p>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Titik koordinat presisi, rute GPS langsung, kontak, dan alamat detail pemohon akan segera aktif di layar Anda setelah bantuan diambil.
                    </p>
                </div>
            </div>

            <!-- Sticky footer -->
            <div class="sticky bottom-0 bg-white border-t pt-3.5 px-5 pb-5">
                <div class="flex gap-2.5">
                    <button type="button" onclick="closePreviewModal()" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold text-xs hover:bg-gray-200 transition">
                        Kembali
                    </button>
                    @if(!empty($activeTask))
                        <a href="{{ route('mitra.helps.detail', $activeTask->id) }}" class="flex-[1.5] bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center text-center">
                            Selesaikan Tugas Aktif
                        </a>
                    @else
                        <button type="button" id="previewTakeBtn" onclick="takeHelpFromModal()" class="flex-[1.5] bg-primary-600 text-white py-3 rounded-xl font-bold text-xs hover:bg-primary-700 shadow-md transition">
                            Ambil Tugas Ini
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        let currentHelpId = null;
        let radarMapInstance = null;
        let userMitraLat = @json($mitraLat);
        let userMitraLng = @json($mitraLng);
        const mapHelpsData = @json($mapHelps);

        window.showHelpPreview = function(helpId, title, amount, scheduled, distanceKm, cityName) {
            currentHelpId = helpId;
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewAmount').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
            
            const distEl = document.getElementById('previewDistance');
            const warnBox = document.getElementById('previewDistanceWarning');
            const warnText = document.getElementById('previewDistanceWarningText');

            if (distanceKm !== null && distanceKm !== undefined && !isNaN(distanceKm)) {
                distEl.textContent = '📍 ' + distanceKm + ' km';
                if (distanceKm > 25) {
                    warnBox.classList.remove('hidden');
                    warnText.textContent = `Bantuan ini berjarak ${distanceKm} km di wilayah ${cityName}. Pastikan Anda memiliki transportasi yang memadai untuk menjangkau lokasi ini.`;
                } else {
                    warnBox.classList.add('hidden');
                }
            } else {
                distEl.textContent = cityName ? '📍 ' + cityName : '-';
                warnBox.classList.add('hidden');
            }

            const schedEl = document.getElementById('previewScheduled');
            if (schedEl) {
                schedEl.textContent = scheduled && scheduled.length ? '📅 ' + scheduled : '⚡ Segera / Secepatnya';
            }
            document.getElementById('helpPreviewModal').classList.remove('hidden');
        };

        window.closePreviewModal = function() {
            document.getElementById('helpPreviewModal').classList.add('hidden');
            currentHelpId = null;
        };

        window.refreshMitraGPS = function() {
            if (!navigator.geolocation) {
                alert('GPS tidak didukung oleh browser Anda.');
                return;
            }

            const indicator = document.getElementById('mitra-gps-indicator');
            const textEl = document.getElementById('mitra-gps-text');
            if (textEl) textEl.textContent = 'Memperbarui koordinat GPS...';
            if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    userMitraLat = pos.coords.latitude;
                    userMitraLng = pos.coords.longitude;

                    if (textEl) textEl.textContent = `GPS Aktif (±${Math.round(pos.coords.accuracy)}m)`;
                    if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';

                    $wire.setMitraLocation(userMitraLat, userMitraLng);
                },
                (err) => {
                    console.warn('GPS error:', err.message);
                    if (textEl) textEl.textContent = 'GPS tidak aktif / izin ditolak';
                    if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-amber-400';
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        };

        window.initRadarMap = function() {
            const mapContainer = document.getElementById('mitraRadarMap');
            if (!mapContainer) return;

            if (mapContainer._leaflet_id) {
                radarMapInstance.invalidateSize();
                return;
            }

            const defaultPos = (userMitraLat && userMitraLng) ? [userMitraLat, userMitraLng] : [-7.8664, 111.4620];

            radarMapInstance = L.map('mitraRadarMap', {
                center: defaultPos,
                zoom: 13,
                zoomControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(radarMapInstance);

            setTimeout(() => { radarMapInstance.invalidateSize(); }, 300);

            const boundsGroup = [];

            // Add Mitra Live Location Pin
            if (userMitraLat && userMitraLng) {
                const userIcon = L.divIcon({
                    className: 'custom-user-pin',
                    html: `<div class="relative flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full bg-blue-400 opacity-75"></span>
                            <div class="w-5 h-5 bg-blue-600 border-2 border-white rounded-full shadow-lg flex items-center justify-center text-white text-[10px] font-bold">🛵</div>
                           </div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                L.marker([userMitraLat, userMitraLng], { icon: userIcon })
                    .addTo(radarMapInstance)
                    .bindPopup('<b>Posisi Anda Saat Ini</b>');

                boundsGroup.push([userMitraLat, userMitraLng]);
            }

            // Add Help Markers
            if (mapHelpsData && mapHelpsData.length) {
                const hasActive = @json(!empty($activeTask));
                mapHelpsData.forEach(h => {
                    if (h.lat && h.lng) {
                        const marker = L.marker([h.lat, h.lng]).addTo(radarMapInstance);
                        const distInfo = h.distance_km ? `<span class="inline-block px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px] mt-1">🟢 ${h.distance_km} km</span>` : '';
                        const btnLabel = hasActive ? 'Lihat Rincian' : 'Lihat & Ambil';
                        
                        const popupContent = `
                            <div class="p-1 min-w-[160px]">
                                <h4 class="font-bold text-xs text-gray-900">${h.title}</h4>
                                <p class="text-xs text-blue-600 font-extrabold mt-0.5">${h.formatted_amount}</p>
                                ${distInfo}
                                <p class="text-[11px] text-gray-500 mt-1">📍 ${h.city || '-'}</p>
                                <button onclick="window.showHelpPreview(${h.id}, '${h.title.replace(/'/g, "\\'")}', ${h.amount}, '${h.scheduled || ''}', ${h.distance_km || 'null'}, '${(h.city || '').replace(/'/g, "\\'")}')" 
                                    class="mt-2 w-full py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow transition">
                                    ${btnLabel}
                                </button>
                            </div>
                        `;
                        marker.bindPopup(popupContent);
                        boundsGroup.push([h.lat, h.lng]);
                    }
                });
            }

            if (boundsGroup.length > 1) {
                radarMapInstance.fitBounds(boundsGroup, { padding: [40, 40] });
            }
        };

        window.takeHelpFromModal = function() {
            if (!currentHelpId) return;
            
            const btn = document.getElementById('previewTakeBtn');
            const originalText = btn.textContent;
            btn.textContent = 'Memproses...';
            btn.disabled = true;

            const executeTake = (lat = null, lng = null) => {
                if (lat && lng) {
                    $wire.takeHelp(currentHelpId, lat, lng);
                } else {
                    $wire.takeHelp(currentHelpId);
                }
                window.closePreviewModal();
                btn.textContent = originalText;
                btn.disabled = false;
            };

            if (userMitraLat && userMitraLng) {
                executeTake(userMitraLat, userMitraLng);
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (p) => executeTake(p.coords.latitude, p.coords.longitude),
                    () => executeTake()
                );
            } else {
                executeTake();
            }
        };

        // Auto GPS Detection
        window.refreshMitraGPS();
        if (@json($viewMode) === 'map') {
            setTimeout(window.initRadarMap, 200);
        }

        // Listen for help-taken event from Livewire
        window.addEventListener('help-taken', function(event) {
            var helpId = event?.detail?.helpId ?? event?.detail ?? null;
            if (!helpId) {
                window.location.reload();
                return;
            }
            var detailUrlTemplate = @json(route('mitra.helps.detail', ['id' => 'REPLACE_ID']));
            window.location.href = detailUrlTemplate.replace('REPLACE_ID', helpId);
        });
    </script>
    @endscript
</div>