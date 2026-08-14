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
            border-radius: 0.5rem;
        }
    </style>

    <div id="main-content" class="min-h-screen bg-white">
        <div class="max-w-md mx-auto">
            <!-- Header - BRImo Style (sama seperti index) -->
            <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern"
                style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between text-white mb-3">
                        <button onclick="window.history.back()" aria-label="Kembali"
                            class="p-2 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div class="text-center flex-1">
                            <h1 class="text-lg font-bold">Buat Permintaan Baru</h1>
                            <p class="text-xs text-white/90 mt-0.5">Isi form di bawah untuk membuat permintaan</p>
                        </div>

                        <div class="w-9"></div>
                    </div>
                </div>

                <!-- Curved separator -->
                <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none"
                    aria-hidden="true">
                    <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-8">
                <form wire:submit.prevent="prepareConfirm" enctype="multipart/form-data" class="space-y-4"
                      onsubmit="console.log('📤 Form submitted with coordinates:', { lat: @this.get('latitude'), lng: @this.get('longitude') })">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
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
                        <input type="text" wire:model="title"
                            placeholder="Contoh: Butuh Bantuan Makanan untuk Keluarga"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white">
                        @error('title')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Amount (Nominal Uang) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                        clip-rule="evenodd" />
                                </svg>
                                Nominal Uang untuk Mitra
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-500 font-bold text-sm">Rp</span>
                            <input type="number" wire:model="amount" placeholder="50000" min="10000" step="1000"
                                class="w-full pl-12 pr-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Minimal Rp 10.000 - Maksimal Rp 100.000.000
                        </p>
                        @error('amount')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Kota
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                wire:model.live.debounce.300ms="cityQuery" 
                                placeholder="Ketik nama kota..."
                                class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white" 
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
                                <ul class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto z-50">
                                    @foreach ($searchResults as $c)
                                        <li wire:click="setCityId({{ $c['id'] }}, '{{ $c['name'] }}', '{{ $c['province'] }}')"
                                            class="px-4 py-3 text-sm hover:bg-blue-50 cursor-pointer transition border-b border-gray-100 last:border-b-0 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="flex-1">
                                                @if(!empty($c['display']))
                                                    <div class="font-semibold text-gray-900">{{ $c['display'] }}</div>
                                                @else
                                                    <div class="font-semibold text-gray-900">{{ $c['name'] }}</div>
                                                    <div class="text-xs text-gray-500">{{ $c['province'] }}</div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif (empty($searchResults) && !empty($cityQuery) && strlen($cityQuery) >= 2 && empty($city_id))
                                <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Kota tidak ditemukan
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Selected City Display -->
                        {{-- @if (!empty($city_id))
                            <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium text-blue-900">{{ $cityQuery }}</span>
                                </div>
                                <button type="button" wire:click="clearCity" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    Ubah
                                </button>
                            </div>
                        @endif

                        @error('city_id')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                        
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Ketik minimal 2 karakter untuk mencari kota
                        </p> --}}
                    </div>

                    <!-- Alamat Lengkap (Manual Input) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Alamat Lengkap
                            </span>
                        </label>
                        <textarea wire:model="full_address" rows="3"
                            placeholder="Contoh: Dukuh Sabet, Desa Sumberejo, Kecamatan Balong, Kabupaten Ponorogo, Jawa Timur"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none bg-white"></textarea>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Tulis alamat lengkap termasuk desa, kecamatan, kabupaten, provinsi
                        </p>
                        @error('full_address')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Detail Lokasi Bantuan
                            </span>
                        </label>
                        <input type="text" wire:model="location"
                            placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 05"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white">
                        @error('location')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Jadwal Permintaan (Tanggal & Jam) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 000 2h8a1 1 0 100-2H6zM4 6a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Jadwalkan Bantuan (Opsional)
                            </span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <input type="date" wire:model="scheduled_date"
                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white">
                                @error('scheduled_date')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <input type="hidden" id="scheduled-time-hidden" wire:model="scheduled_time">

                                    <input id="scheduled-time-manual" type="text" inputmode="numeric"
                                        pattern="^([01]?\d|2[0-3]):[0-5]\d$" placeholder="HH:MM"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <span id="timezone-badge"
                                        class="inline-block px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded">{{ $timezoneLabel }}</span>
                                </div>
                                @error('scheduled_time')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                                <p id="timezone-display" class="text-xs text-gray-500 mt-1">Waktu lokal: {{ $timezoneLabel }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Jika ingin bantuan di lain hari atau jam tertentu,
                            masukkan tanggal dan jam di sini.</p>
                    </div>

                    <!-- Tandai Lokasi di Peta -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Tandai Lokasi di Peta
                            </span>
                        </label>

                        <!-- Map Container -->
                        <div wire:ignore id="map" style="height: 280px; min-height: 280px;"
                            class="w-full rounded-lg border border-gray-300 mb-2 bg-gray-100"></div>

                        <!-- Koordinat Display -->
                        <div id="coordinates-display"
                            class="bg-green-50 border border-green-200 rounded-lg p-3 mb-2 hidden">
                            <p class="text-xs font-semibold text-green-800 mb-1">✓ Lokasi Ditandai:</p>
                            <p class="text-xs text-green-900 font-mono">
                                Lat: <span id="lat-display" class="font-semibold">-</span>, 
                                Lng: <span id="lng-display" class="font-semibold">-</span>
                            </p>
                        </div>

                        <!-- Hidden inputs for Livewire -->
                        <input type="hidden" wire:model="latitude" id="latitude-input">
                        <input type="hidden" wire:model="longitude" id="longitude-input">

                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Klik pada peta untuk menandai titik lokasi bantuan
                        </p>

                        @error('latitude')
                            <span class="text-red-500 text-xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
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
                        <textarea wire:model="description" rows="4"
                            placeholder="Jelaskan detail kebutuhan bantuan Anda secara lengkap..."
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none bg-white"></textarea>
                        @error('description')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
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
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                Peralatan yang Sudah Disediakan
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <textarea wire:model="equipment_provided" rows="3"
                            placeholder="Contoh: Sudah ada gerobak dorong, ember besar 2 buah, timbangan digital"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none bg-white"></textarea>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Tuliskan alat atau peralatan yang sudah Anda sediakan untuk membantu mitra
                        </p>
                        @error('equipment_provided')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
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
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Foto Pendukung
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="file" wire:model="photo" accept="image/*" id="photo-input"
                                class="hidden">
                            <label for="photo-input"
                                class="flex items-center justify-center w-full h-32 rounded-lg border-2 border-dashed border-gray-300 hover:border-blue-400 cursor-pointer transition bg-gray-50 hover:bg-blue-50 overflow-hidden relative">
                                @if ($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" alt="preview"
                                        class="w-full h-full object-cover">

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
                                        <svg class="w-6 h-6 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-600">Pilih atau ambil foto</span>
                                        <span class="text-xs text-gray-400 mt-1">Klik untuk upload gambar</span>
                                    </div>
                                @endif
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Maksimal 2MB. Format: JPG, PNG, JPEG
                        </p>
                        @error('photo')
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror

                        {{-- Preview is rendered inside the upload box above --}}
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3 pt-6">
                        <a href="{{ route('dashboard') }}"
                            class="flex-1 inline-flex items-center justify-center bg-white border border-gray-300 text-gray-700 px-5 py-3 text-sm rounded-lg font-semibold hover:bg-gray-50 transition">
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
            class="pointer-events-auto mb-6 md:mb-0 bg-white bg-opacity-95 rounded-lg px-4 py-3 flex items-center gap-3 shadow-lg">
            <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div class="text-sm font-medium text-gray-800">Mengirim...</div>
        </div>
    </div>

    <!-- Insufficient Balance Modal - Bottom Sheet Style -->
    @if ($showInsufficientModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in"
            style="background: rgba(0,0,0,0.5);" wire:click="closeInsufficientModal">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" wire:click.stop
                style="padding-bottom: env(safe-area-inset-bottom,24px);">
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Saldo Tidak Cukup</h3>
                        <button type="button" wire:click="closeInsufficientModal"
                            class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 pb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 text-center mb-6">{{ $insufficientMessage }}</p>

                    <div class="flex gap-3">
                        <button wire:click="closeInsufficientModal"
                            class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                            Tutup
                        </button>
                        <a href="{{ route('customer.topup') }}"
                            class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold text-center hover:from-blue-600 hover:to-blue-700 transition">
                            Top Up Saldo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal - Bottom Sheet Style -->
    @if ($showConfirmModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in"
            style="background: rgba(0,0,0,0.5);" wire:click="closeConfirmModal">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar animate-slide-up relative"
                wire:click.stop style="padding-bottom: env(safe-area-inset-bottom,24px);">
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Konfirmasi Permintaan</h3>
                        <button type="button" wire:click="closeConfirmModal"
                            class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-5 pb-6">
                    <p class="text-sm text-gray-600 mb-4">Periksa ringkasan pesanan sebelum mengonfirmasi.</p>

                    <!-- Saldo Info Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-4 mb-4 border border-blue-100">
                        <div class="flex items-center justify-between mb-3 pb-3 border-b border-blue-100">
                            <span class="text-xs font-semibold text-gray-600">Saldo Tersedia</span>
                            <span class="text-lg font-bold text-gray-900">Rp
                                {{ number_format($currentBalance ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-2.5">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Nominal Bantuan</span>
                                <span class="text-sm font-semibold text-gray-900">Rp
                                    {{ number_format($confirmAmount ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Biaya Admin</span>
                                <span class="text-sm font-semibold text-gray-900">Rp
                                    {{ number_format($confirmAdminFee ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-white border-2 border-blue-200 rounded-2xl p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <span class="text-xs font-semibold text-gray-600">Total Pembayaran</span>
                                <div class="text-2xl font-bold text-blue-600 mt-1">Rp
                                    {{ number_format($confirmTotal ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div
                                class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    @if ($confirmScheduled)
                        <div class="mb-4">
                            <div class="text-xs text-gray-600">Jadwal Permintaan</div>
                            <div class="text-sm font-semibold">{{ $confirmScheduled }}</div>
                        </div>
                    @endif

                    <!-- Info Box -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p class="text-xs text-amber-900 leading-relaxed">Dengan menekan
                                <strong>Konfirmasi</strong>, Anda menyetujui bahwa saldo akan dipotong sesuai total
                                pembayaran di atas.</p>
                        </div>
                    </div>
                </div>

                <!-- Sticky footer with action buttons (always visible) -->
                <div class="sticky bottom-0 bg-white border-t px-5 py-4 z-20 flex gap-3">
                    <button wire:click="closeConfirmModal" type="button"
                        class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Kembali
                    </button>
                    <button wire:click="save" type="button" wire:loading.attr="disabled"
                        class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
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

        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sebentar untuk memastikan DOM fully loaded
            setTimeout(function() {
                initializeMap();
            }, 100);
        });
        
        // Juga initialize saat Livewire navigated
        document.addEventListener('livewire:navigated', function() {
            setTimeout(function() {
                initializeMap();
            }, 100);
        });
        
        function initializeMap() {
            // Check if map already initialized
            if (document.getElementById('map')._leaflet_id) {
                console.log('Map already initialized');
                return;
            }
            
            // Default location (Ponorogo, Jawa Timur)
            const defaultLocation = [-7.8664, 111.4620];
            
            // Check if there's existing coordinates from Livewire
            const existingLat = @this.get('latitude');
            const existingLng = @this.get('longitude');

            // Initialize map
            const map = L.map('map', {
                center: defaultLocation,
                zoom: 13,
                scrollWheelZoom: true,
                zoomControl: true
            });
            
            console.log('🗺️ Map initialized');

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);
            
            // Force map to re-render after tiles loaded
            setTimeout(function() {
                map.invalidateSize();
                console.log('🗺️ Map size invalidated');
            }, 200);

            // Initialize marker variable
            let marker = null;
            
            // If there are existing coordinates, add marker
            if (existingLat && existingLng) {
                console.log('📍 Loading existing coordinates:', { lat: existingLat, lng: existingLng });
                marker = L.marker([existingLat, existingLng], {
                    draggable: true
                }).addTo(map);
                
                map.setView([existingLat, existingLng], 15);
                
                // Update display
                document.getElementById('coordinates-display').classList.remove('hidden');
                document.getElementById('lat-display').textContent = parseFloat(existingLat).toFixed(6);
                document.getElementById('lng-display').textContent = parseFloat(existingLng).toFixed(6);
                
                // Setup drag event for existing marker
                marker.on('dragend', function(event) {
                    const position = event.target.getLatLng();
                    updateCoordinates(position.lat, position.lng);
                });
            }

            // Click event on map to place marker
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Remove old marker if exists
                if (marker) {
                    map.removeLayer(marker);
                }

                // Add new marker
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);

                // Update coordinates
                updateCoordinates(lat, lng);

                // Marker drag event
                marker.on('dragend', function(event) {
                    const position = event.target.getLatLng();
                    updateCoordinates(position.lat, position.lng);
                });
            });

            // Try to get user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = [position.coords.latitude, position.coords.longitude];
                        map.setView(pos, 13);
                    },
                    () => {
                        console.log('Geolocation not available, using default location');
                    }
                );
            }

            function updateCoordinates(lat, lng) {
                // Update display
                document.getElementById('coordinates-display').classList.remove('hidden');
                document.getElementById('lat-display').textContent = lat.toFixed(6);
                document.getElementById('lng-display').textContent = lng.toFixed(6);

                // Update Livewire properties - menggunakan @this untuk set langsung ke Livewire component
                @this.set('latitude', lat);
                @this.set('longitude', lng);
                
                console.log('📍 Koordinat disimpan:', { lat: lat, lng: lng });
            }
        } // End of initializeMap function
    </script>
</div>