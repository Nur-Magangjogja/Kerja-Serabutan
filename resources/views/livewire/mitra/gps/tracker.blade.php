<div x-data="gpsTracker(@entangle('helpId'), @entangle('isTracking'))" x-init="init()" class="gps-tracker">
    @if($isTracking)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold text-blue-900">GPS Tracking Aktif</span>
                </div>
                <span class="text-xs text-blue-600" x-text="'Update: ' + lastUpdate"></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="bg-white rounded p-2">
                    <div class="text-gray-600 text-xs mb-1">Status</div>
                    <div class="font-semibold text-gray-900" x-text="statusText"></div>
                </div>
                
                <div class="bg-white rounded p-2" x-show="distanceToCustomer !== null">
                    <div class="text-gray-600 text-xs mb-1">Jarak ke Lokasi</div>
                    <div class="font-semibold text-gray-900" x-text="distanceToCustomer + ' meter'"></div>
                </div>
            </div>

            @if($currentStatus === 'taken')
                <div class="mt-3 text-xs text-blue-700">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Status akan otomatis berubah saat Anda bergerak 20+ meter dari titik awal
                </div>
            @elseif($currentStatus === 'partner_on_the_way')
                <div class="mt-3 text-xs text-blue-700">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Status akan otomatis berubah saat Anda tiba dalam radius 50 meter dari lokasi customer
                </div>
            @elseif($currentStatus === 'partner_arrived')
                <div class="mt-3 text-xs text-green-700">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Anda telah tiba di lokasi customer
                </div>
            @endif
        </div>
    @endif
</div>

@script
<script>
Alpine.data('gpsTracker', (helpId, isTracking) => ({
    helpId: helpId,
    isTracking: isTracking,
    watchId: null,
    lastUpdate: '-',
    statusText: 'Menunggu GPS...',
    distanceFromInitial: null,
    distanceToCustomer: null,
    updateInterval: null,

    init() {
        if (this.isTracking) {
            this.startTracking();
        }

        // Listen untuk event mulai tracking
        window.addEventListener('start-gps-tracking', (event) => {
            if (event.detail.helpId === this.helpId) {
                this.isTracking = true;
                this.startTracking();
            }
        });

        // Listen untuk status changed
        this.$wire.on('status-changed', (event) => {
            this.updateStatusText(event.newStatus);
            this.showNotification(event.newStatus);
        });

        // Listen untuk tracking stopped
        this.$wire.on('tracking-stopped', () => {
            this.stopTracking();
        });
    },

    startTracking() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung GPS');
            return;
        }

        console.log('Memulai GPS tracking untuk help ID:', this.helpId);

        // Request permission dan mulai tracking
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                console.log('GPS Update:', lat, lng);
                
                // Kirim ke server
                this.$wire.updateLocation(lat, lng).then((result) => {
                    if (result.success) {
                        this.lastUpdate = new Date().toLocaleTimeString('id-ID');
                        this.distanceFromInitial = result.distance_from_initial;
                        this.distanceToCustomer = result.distance_to_customer;
                        this.updateStatusText(result.status);
                    }
                });
            },
            (error) => {
                console.error('GPS Error:', error);
                this.handleGpsError(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 5000
            }
        );
    },

    stopTracking() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        this.isTracking = false;
        console.log('GPS tracking dihentikan');
    },

    updateStatusText(status) {
        const statusMap = {
            'taken': 'Bantuan Diambil',
            'partner_on_the_way': 'Rekan Jasa Menuju Lokasi',
            'partner_arrived': 'Rekan Jasa Tiba di Lokasi',
            'in_progress': 'Sedang Dikerjakan',
            'completed': 'Selesai'
        };
        this.statusText = statusMap[status] || status;
    },

    showNotification(newStatus) {
        const messages = {
            'partner_on_the_way': 'Status berubah: Anda menuju lokasi customer',
            'partner_arrived': 'Status berubah: Anda telah tiba di lokasi customer'
        };

        if (messages[newStatus]) {
            // Show browser notification if permitted
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('GPS Tracking Update', {
                    body: messages[newStatus],
                    icon: '/images/logo.png'
                });
            }
            
            // Show in-app alert
            alert(messages[newStatus]);
        }
    },

    handleGpsError(error) {
        let message = '';
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = 'Izin GPS ditolak. Mohon aktifkan izin lokasi untuk aplikasi ini.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Informasi lokasi tidak tersedia.';
                break;
            case error.TIMEOUT:
                message = 'Request GPS timeout.';
                break;
            default:
                message = 'Terjadi kesalahan GPS.';
                break;
        }
        console.error('GPS Error:', message);
    }
}));
</script>
@endscript

