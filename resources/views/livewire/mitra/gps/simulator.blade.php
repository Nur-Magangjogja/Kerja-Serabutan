<div x-data="gpsSimulator(@entangle('helpId'), @entangle('isSimulating'), @entangle('currentLat'), @entangle('currentLng'), @entangle('targetLat'), @entangle('targetLng'))" 
     x-init="init()" 
     class="bg-gradient-to-br from-slate-50 via-blue-50/40 to-indigo-50/50 dark:from-gray-800 dark:via-slate-850 dark:to-gray-850 rounded-2xl p-4 border border-slate-200/80 dark:border-gray-700/80 shadow-sm transition-colors">
    
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-blue-600 dark:bg-blue-500 rounded-lg flex items-center justify-center shadow-xs text-white">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">GPS Simulator</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Testing pergerakan tanpa bergerak fisik</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            @if($isSimulating)
                <div class="flex items-center gap-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    Simulasi Aktif
                </div>
            @else
                <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                    Siap
                </div>
            @endif
        </div>
    </div>

    {{-- Info --}}
    <div class="grid grid-cols-2 gap-2.5 mb-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-2.5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Lokasi Saat Ini</p>
            <p class="text-xs font-mono font-semibold text-blue-600 dark:text-blue-400">{{ number_format($currentLat, 6) }}, {{ number_format($currentLng, 6) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-2.5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
            <div class="flex items-center justify-between mb-0.5">
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Jarak ke Target</p>
                @if($this->distanceToTarget < 50 && $isSimulating)
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">✓ Dekat</span>
                @endif
            </div>
            @php
                $distance = $this->distanceToTarget;
                
                // Jika simulasi belum dimulai dan jarak = 0, tampilkan placeholder
                if (!$isSimulating && $distance < 1) {
                    $distanceText = 'Klik Mulai';
                    $colorClass = 'text-gray-400 dark:text-gray-500';
                } elseif ($distance >= 1000) {
                    $distanceText = number_format($distance / 1000, 2) . ' km';
                    $colorClass = 'text-amber-600 dark:text-amber-400';
                } elseif ($distance >= 100) {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-blue-600 dark:text-blue-400';
                } elseif ($distance >= 50) {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-yellow-600 dark:text-yellow-400';
                } else {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-emerald-600 dark:text-emerald-400';
                }
            @endphp
            <p class="text-sm font-bold {{ $colorClass }}" wire:poll.5s.visible>{{ $distanceText }}</p>
        </div>
    </div>

    {{-- Controls --}}
    <div class="space-y-2.5">
        {{-- Speed Control --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Interval Update
                </label>
                <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400" x-text="speed + ' detik/update'"></span>
            </div>
            <input type="range" min="1" max="10" x-model="speed" 
                   @input="$wire.setSpeed(speed)"
                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600 dark:accent-blue-500">
            <div class="flex justify-between text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                <span>⚡ Cepat (1s)</span>
                <span>Lambat (10s)</span>
            </div>
        </div>

        {{-- Step Size Control --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Langkah Gerak
                </label>
                <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400" x-text="stepSize + ' meter'"></span>
            </div>
            <input type="range" min="10" max="100" step="10" x-model="stepSize"
                   @input="$wire.setStepSize(stepSize)"
                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600 dark:accent-blue-500">
            <div class="flex justify-between text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                <span>10m</span>
                <span>50m</span>
                <span>100m</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-3 gap-2">
            <button @click="startSimulation()" 
                    :disabled="isSimulating"
                    :class="isSimulating ? 'opacity-50 cursor-not-allowed' : 'hover:bg-emerald-700 shadow-sm'"
                    class="bg-emerald-600 text-white px-3 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                Mulai
            </button>

            <button @click="stopSimulation()" 
                    :disabled="!isSimulating"
                    :class="!isSimulating ? 'opacity-50 cursor-not-allowed' : 'hover:bg-rose-700 shadow-sm'"
                    class="bg-rose-600 text-white px-3 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
                </svg>
                Stop
            </button>

            <button @click="teleport()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Teleport
            </button>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60 shadow-xs">
            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Aksi Cepat:</p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="quickMove(20)"
                        class="bg-slate-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 rounded-lg text-xs font-semibold hover:bg-slate-200 dark:hover:bg-gray-600 transition flex items-center justify-center gap-1">
                    <span>+20m</span> <span class="text-[10px] text-gray-500 dark:text-gray-400">Maju</span>
                </button>
                <button @click="quickMove(50)"
                        class="bg-slate-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-2 rounded-lg text-xs font-semibold hover:bg-slate-200 dark:hover:bg-gray-600 transition flex items-center justify-center gap-1">
                    <span>+50m</span> <span class="text-[10px] text-gray-500 dark:text-gray-400">Maju</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Info Panel --}}
    <div class="mt-3 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-800/50 rounded-xl p-3">
        <p class="text-xs text-blue-900 dark:text-blue-300 leading-relaxed">
            <strong class="font-semibold">💡 Tips:</strong> Gunakan simulator ini untuk simulasi posisi GPS tanpa harus bergerak fisik. 
            Klik <strong>Mulai</strong> untuk mensimulasikan pergerakan mitra menuju titik lokasi pesanan secara otomatis.
        </p>
    </div>
</div>

@script
<script>
Alpine.data('gpsSimulator', (helpId, isSimulating, currentLat, currentLng, targetLat, targetLng) => ({
    helpId: helpId,
    isSimulating: isSimulating,
    currentLat: parseFloat(currentLat) || 0,
    currentLng: parseFloat(currentLng) || 0,
    targetLat: parseFloat(targetLat) || 0,
    targetLng: parseFloat(targetLng) || 0,
    speed: 2,
    stepSize: 20,
    intervalId: null,
    distanceText: 'Menghitung...',

    init() {
        // Convert to float immediately
        this.currentLat = parseFloat(this.currentLat) || 0;
        this.currentLng = parseFloat(this.currentLng) || 0;
        this.targetLat = parseFloat(this.targetLat) || 0;
        this.targetLng = parseFloat(this.targetLng) || 0;
        
        // Calculate initial distance
        this.calculateDistance();
        
        // Listen to simulation events
        this.$wire.on('simulation-started', () => {
            this.startAutoUpdate();
        });

        this.$wire.on('simulation-stopped', () => {
            this.stopAutoUpdate();
        });

        // Watch for changes and convert to number
        this.$watch('currentLat', (value) => {
            this.currentLat = parseFloat(value) || 0;
            this.calculateDistance();
        });
        this.$watch('currentLng', (value) => {
            this.currentLng = parseFloat(value) || 0;
            this.calculateDistance();
        });
    },

    calculateDistance() {
        const R = 6371000; // Earth radius in meters
        const lat1 = parseFloat(this.currentLat) * Math.PI / 180;
        const lat2 = parseFloat(this.targetLat) * Math.PI / 180;
        const deltaLat = (parseFloat(this.targetLat) - parseFloat(this.currentLat)) * Math.PI / 180;
        const deltaLng = (parseFloat(this.targetLng) - parseFloat(this.currentLng)) * Math.PI / 180;

        const a = Math.sin(deltaLat/2) * Math.sin(deltaLat/2) +
                  Math.cos(lat1) * Math.cos(lat2) *
                  Math.sin(deltaLng/2) * Math.sin(deltaLng/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const distance = R * c;

        if (distance > 1000) {
            this.distanceText = (distance / 1000).toFixed(2) + ' km';
        } else {
            this.distanceText = Math.round(distance) + ' m';
        }
    },

    startSimulation() {
        console.log('Starting simulation...');
        this.$wire.startSimulation().then(() => {
            console.log('Simulation started, beginning auto-update');
            this.isSimulating = true;
            this.startAutoUpdate();
        });
    },

    stopSimulation() {
        console.log('Stopping simulation...');
        this.$wire.stopSimulation().then(() => {
            this.isSimulating = false;
            this.stopAutoUpdate();
        });
    },

    startAutoUpdate() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }

        this.intervalId = setInterval(() => {
            if (this.isSimulating) {
                this.$wire.simulateStep().then((result) => {
                    if (result && !result.still_moving) {
                        this.stopAutoUpdate();
                    }
                });
            } else {
                this.stopAutoUpdate();
            }
        }, this.speed * 1000);
    },

    stopAutoUpdate() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    },

    teleport() {
        if (confirm('Teleport langsung ke lokasi customer?')) {
            this.$wire.teleportToTarget();
        }
    },

    quickMove(meters) {
        const originalStep = this.stepSize;
        this.stepSize = meters;
        this.$wire.setStepSize(meters);
        this.$wire.simulateStep();
        
        // Reset step size after a moment
        setTimeout(() => {
            this.stepSize = originalStep;
            this.$wire.setStepSize(originalStep);
        }, 100);
    }
}));
</script>
@endscript

