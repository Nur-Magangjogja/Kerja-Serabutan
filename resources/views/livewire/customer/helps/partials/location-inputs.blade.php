<!-- Detail Alamat Lokasi (Otomatis Terisi dari Peta) -->
<div id="group-location" class="space-y-3">
    @if($service_type === 'pickup_delivery')
        <!-- Mode Antar / Jemput: 2 Alamat (Titik Jemput & Titik Antar) -->
        <div class="space-y-3">
            <!-- Titik 1: Jemput (Pickup) -->
            <div class="p-3 bg-blue-50/60 dark:bg-gray-800/80 rounded-xl border border-blue-200/80 dark:border-gray-700 space-y-1.5 shadow-2xs">
                <div class="flex items-center justify-between flex-wrap gap-1">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                        Alamat Titik Jemput (Pickup) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setActiveMapPoint('pickup'); focusMapSection();"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-800/60 border border-blue-200 dark:border-blue-800 transition cursor-pointer flex items-center gap-1">
                            <span>🎯 Tentukan di Peta</span>
                        </button>
                        <button type="button" onclick="locateUserGPS('pickup')"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-600 text-white hover:bg-blue-700 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                            <span>📍 GPS</span>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="pickup_address" id="pickup-address-input"
                        placeholder="Contoh: Jl. Kaliurang KM 5 No. 12 (Titik barang diambil)"
                        class="w-full px-3 py-2.5 text-xs rounded-lg border @error('pickup_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                @error('pickup_address')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <!-- Titik 2: Antar (Tujuan) -->
            <div class="p-3 bg-emerald-50/60 dark:bg-gray-800/80 rounded-xl border border-emerald-200/80 dark:border-gray-700 space-y-1.5 shadow-2xs">
                <div class="flex items-center justify-between flex-wrap gap-1">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                        Alamat Titik Antar (Tujuan) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setActiveMapPoint('delivery'); focusMapSection();"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-800/60 border border-emerald-200 dark:border-emerald-800 transition cursor-pointer flex items-center gap-1">
                            <span>🎯 Tentukan di Peta</span>
                        </button>
                        <button type="button" onclick="locateUserGPS('delivery')"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                            <span>📍 GPS</span>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="delivery_address" id="delivery-address-input"
                        placeholder="Contoh: Jl. Gejayan No. 45 (Titik barang diserahkan)"
                        class="w-full px-3 py-2.5 text-xs rounded-lg border @error('delivery_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                @if((float) $route_distance_km > 0)
                    @php
                        $estTravelMin = (new \App\Services\GeoService())->getRouteDurationMinutes((float)$route_distance_km, 25.0, 3);
                    @endphp
                    <div class="flex items-center justify-between font-semibold text-emerald-700 dark:text-emerald-300 text-xs bg-white dark:bg-emerald-950/40 px-2.5 py-2 rounded-xl border border-emerald-200/80 dark:border-emerald-900/60 mt-1.5 shadow-2xs flex-wrap gap-2">
                        <div class="flex items-center gap-1.5">
                            <span>🛣️</span>
                            <span>Jarak Rute: <strong>{{ number_format((float)$route_distance_km, 1, ',', '.') }} KM</strong> (dibulatkan {{ ceil((float)$route_distance_km) }} KM)</span>
                        </div>
                        <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                            <span>⏱️</span>
                            <span>Estimasi: <strong>~{{ $estTravelMin }} Menit</strong></span>
                        </div>
                        <span class="font-bold text-emerald-800 dark:text-emerald-200">Rp {{ number_format(app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float)$route_distance_km), 0, ',', '.') }}</span>
                    </div>
                @endif
                @error('delivery_address')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] block font-medium">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @else
        <!-- Mode Kerja di Lokasi (On-Site): 1 Alamat Tunggal -->
        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                <span class="flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Alamat / Nama Lokasi
                        <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">(Otomatis Terisi)</span>
                    </span>
                    <span id="reverse-geocode-indicator" class="hidden text-[11px] text-blue-600 dark:text-blue-400 animate-pulse font-normal">
                        📍 Mendeteksi alamat...
                    </span>
                </span>
            </label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="location" id="location-input"
                    placeholder="Alamat akan terisi otomatis saat Anda memilih titik peta..."
                    class="w-full px-4 py-3 text-sm rounded-lg border @error('location') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
            </div>
            @error('location')
                <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    {{ $message }}
                </span>
            @enderror
        </div>
    @endif
</div>
