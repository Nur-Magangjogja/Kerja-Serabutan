<div x-data="gpsSimulator(@entangle('helpId'), @entangle('isSimulating'), @entangle('currentLat'), @entangle('currentLng'), @entangle('targetLat'), @entangle('targetLng'))" 
     x-init="init()" 
     class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border-2 border-purple-200">
    
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-purple-900">GPS Simulator</h3>
                <p class="text-xs text-purple-600">Testing tanpa bergerak</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            @if($isSimulating)
                <div class="flex items-center gap-1.5 bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    Simulasi Aktif
                </div>
            @else
                <div class="flex items-center gap-1.5 bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                    Siap
                </div>
            @endif
        </div>
    </div>

    {{-- Info --}}
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="bg-white rounded-lg p-2.5 border border-purple-100">
            <p class="text-xs text-gray-600 mb-1">Lokasi Saat Ini</p>
            <p class="text-xs font-mono text-purple-700">{{ number_format($currentLat, 6) }}, {{ number_format($currentLng, 6) }}</p>
        </div>
        <div class="bg-white rounded-lg p-2.5 border border-purple-100">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs text-gray-600">Jarak ke Target</p>
                @if($this->distanceToTarget < 50 && $isSimulating)
                    <span class="text-xs text-green-600 font-bold">✓ Dekat</span>
                @endif
            </div>
            @php
                $distance = $this->distanceToTarget;
                
                // Jika simulasi belum dimulai dan jarak = 0, tampilkan placeholder
                if (!$isSimulating && $distance < 1) {
                    $distanceText = 'Klik Mulai';
                    $colorClass = 'text-gray-500';
                } elseif ($distance >= 1000) {
                    $distanceText = number_format($distance / 1000, 2) . ' km';
                    $colorClass = 'text-orange-600';
                } elseif ($distance >= 100) {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-blue-600';
                } elseif ($distance >= 50) {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-yellow-600';
                } else {
                    $distanceText = round($distance) . ' m';
                    $colorClass = 'text-green-600';
                }
            @endphp
            <p class="text-sm font-bold {{ $colorClass }}" wire:poll.5s.visible>{{ $distanceText }}</p>
        </div>
    </div>

    {{-- Controls --}}
    <div class="space-y-2">
        {{-- Speed Control --}}
        <div class="bg-white rounded-lg p-3 border border-purple-100">
            <label class="text-xs font-semibold text-gray-700 mb-2 block">
                Kecepatan Simulasi: <span x-text="speed + ' detik/update'"></span>
            </label>
            <input type="range" min="1" max="10" x-model="speed" 
                   @input="$wire.setSpeed(speed)"
                   class="w-full h-2 bg-purple-200 rounded-lg appearance-none cursor-pointer accent-purple-500">
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>Cepat</span>
                <span>Lambat</span>
            </div>
        </div>

        {{-- Step Size Control --}}
        <div class="bg-white rounded-lg p-3 border border-purple-100">
            <label class="text-xs font-semibold text-gray-700 mb-2 block">
                Langkah Gerak: <span x-text="stepSize + ' meter'"></span>
            </label>
            <input type="range" min="10" max="100" step="10" x-model="stepSize"
                   @input="$wire.setStepSize(stepSize)"
                   class="w-full h-2 bg-purple-200 rounded-lg appearance-none cursor-pointer accent-purple-500">
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>10m</span>
                <span>50m</span>
                <span>100m</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-3 gap-2">
            <button @click="startSimulation()" 
                    :disabled="isSimulating"
                    :class="isSimulating ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                    class="bg-green-500 text-white px-3 py-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                Mulai
            </button>

            <button @click="stopSimulation()"
                    :disabled="!isSimulating"
                    :class="!isSimulating ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600'"
                    class="bg-red-500 text-white px-3 py-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
                </svg>
                Stop
            </button>

            <button @click="teleport()"
                    class="bg-purple-500 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-purple-600 transition flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                Teleport
            </button>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-lg p-3 border border-purple-100">
            <p class="text-xs font-semibold text-gray-700 mb-2">Quick Actions:</p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="quickMove(20)"
                        class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                    Gerak 20m
                </button>
                <button @click="quickMove(50)"
                        class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                    Gerak 50m
                </button>
            </div>
        </div>
    </div>

    {{-- Info Panel --}}
    <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
        <p class="text-xs text-blue-800 leading-relaxed">
            <strong>💡 Tips:</strong> Gunakan simulator ini untuk testing GPS tracking tanpa harus bergerak fisik. 
            Klik <strong>Mulai</strong> untuk generate lokasi random (500-1000m dari target) dan auto-simulate pergerakan menuju lokasi customer.
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
