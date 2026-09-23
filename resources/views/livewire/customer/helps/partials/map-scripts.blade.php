<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Map & Location Javascript (Optimized for Multi-Service Isolation & Forbidden Zones Restriction) -->
<script>
    (function() {
        let customerMap = null;
        let customerMarker = null;
        let pickupMarker = null;
        let deliveryMarker = null;
        let routePolyline = null;
        let mapResizeTimer = null;
        let latestRouteRequestId = 0;
        let routeDebounceTimer = null;
        let onsiteGeocodeController = null;
        let pickupGeocodeController = null;
        let deliveryGeocodeController = null;
        window.activeMapPoint = 'pickup';

        function waitForLeaflet(callback, maxAttempts = 60) {
            if (typeof L !== 'undefined') {
                callback();
                return;
            }
            if (!document.querySelector('script[src*="leaflet.js"]')) {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.crossOrigin = '';
                document.head.appendChild(script);
            }
            if (!document.querySelector('link[href*="leaflet.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                link.crossOrigin = '';
                document.head.appendChild(link);
            }
            let attempts = 0;
            const timer = setInterval(() => {
                attempts++;
                if (typeof L !== 'undefined') {
                    clearInterval(timer);
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(timer);
                    console.warn('Leaflet library failed to load in time.');
                }
            }, 50);
        }

        document.addEventListener('DOMContentLoaded', function() {
            waitForLeaflet(() => initializeMap());
        });
        
        document.addEventListener('livewire:navigated', function() {
            waitForLeaflet(() => initializeMap());
        });

        function isRestrictedOsmLocation(data, lat, lng) {
            // 1. Batas Teritori Indonesia (Lat: -11.5 s/d 6.5, Lng: 94.5 s/d 141.5)
            if (lat > 6.5 || lat < -11.5 || lng < 94.5 || lng > 141.5) {
                return { isRestricted: true, reason: 'Titik lokasi berada di luar batas wilayah Republik Indonesia.' };
            }
            if (!data) return { isRestricted: false };

            const addr = data.address || {};
            const country = (addr.country_code || '').toLowerCase();
            if (country && country !== 'id') {
                return { isRestricted: true, reason: 'Titik lokasi terdeteksi berada di luar wilayah Indonesia.' };
            }

            const cat = (data.category || data.class || '').toLowerCase();
            const type = (data.type || '').toLowerCase();

            const waterTypes = ['water', 'sea', 'ocean', 'bay', 'coastline', 'beach', 'strait', 'lake', 'riverbank'];
            if (cat === 'natural' && waterTypes.includes(type)) {
                return { isRestricted: true, reason: 'Titik lokasi berada di area perairan / lautan yang tidak dapat diakses rekan jasa.' };
            }
            if (cat === 'waterway' || type === 'waterway') {
                return { isRestricted: true, reason: 'Titik lokasi berada di perairan sungai / kanal.' };
            }

            const militaryTypes = ['military', 'barracks', 'danger_area', 'airfield', 'naval_base'];
            if (cat === 'military' || militaryTypes.includes(type)) {
                return { isRestricted: true, reason: 'Titik lokasi terdeteksi berada di zona instalasi militer / area terbatas khusus.' };
            }

            return { isRestricted: false };
        }

        function getPickupIcon() {
            return L.divIcon({
                className: 'custom-pickup-marker',
                html: '\x3cdiv style="width:34px;height:46px;cursor:grab;display:flex;align-items:center;justify-content:center;"\x3e' +
                      '\x3csvg width="34" height="46" viewBox="0 0 34 46" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 4px 8px rgba(37,99,235,0.45));display:block;"\x3e' +
                      '\x3cpath d="M17 0C7.6 0 0 7.6 0 17C0 27.5 15.3 44.5 16 45.2C16.5 45.7 17.5 45.7 18 45.2C18.7 44.5 34 27.5 34 17C34 7.6 26.4 0 17 0Z" fill="#2563eb" stroke="#ffffff" stroke-width="2"/\x3e' +
                      '\x3ccircle cx="17" cy="16" r="9.5" fill="#ffffff"/\x3e' +
                      '\x3ctext x="17" y="20.5" text-anchor="middle" fill="#2563eb" font-size="12" font-weight="900" font-family="system-ui, -apple-system, sans-serif"\x3e1\x3c/text\x3e' +
                      '\x3c/svg\x3e\x3c/div\x3e',
                iconSize: [34, 46],
                iconAnchor: [17, 46],
                popupAnchor: [0, -46]
            });
        }

        function getDeliveryIcon() {
            return L.divIcon({
                className: 'custom-delivery-marker',
                html: '\x3cdiv style="width:34px;height:46px;cursor:grab;display:flex;align-items:center;justify-content:center;"\x3e' +
                      '\x3csvg width="34" height="46" viewBox="0 0 34 46" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 4px 8px rgba(5,150,105,0.45));display:block;"\x3e' +
                      '\x3cpath d="M17 0C7.6 0 0 7.6 0 17C0 27.5 15.3 44.5 16 45.2C16.5 45.7 17.5 45.7 18 45.2C18.7 44.5 34 27.5 34 17C34 7.6 26.4 0 17 0Z" fill="#059669" stroke="#ffffff" stroke-width="2"/\x3e' +
                      '\x3ccircle cx="17" cy="16" r="9.5" fill="#ffffff"/\x3e' +
                      '\x3ctext x="17" y="20.5" text-anchor="middle" fill="#059669" font-size="12" font-weight="900" font-family="system-ui, -apple-system, sans-serif"\x3e2\x3c/text\x3e' +
                      '\x3c/svg\x3e\x3c/div\x3e',
                iconSize: [34, 46],
                iconAnchor: [17, 46],
                popupAnchor: [0, -46]
            });
        }

        function getOnSiteIcon() {
            return L.divIcon({
                className: 'custom-onsite-marker',
                html: '\x3cdiv style="width:34px;height:46px;cursor:grab;display:flex;align-items:center;justify-content:center;"\x3e' +
                      '\x3csvg width="34" height="46" viewBox="0 0 34 46" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 4px 8px rgba(2,132,199,0.45));display:block;"\x3e' +
                      '\x3cpath d="M17 0C7.6 0 0 7.6 0 17C0 27.5 15.3 44.5 16 45.2C16.5 45.7 17.5 45.7 18 45.2C18.7 44.5 34 27.5 34 17C34 7.6 26.4 0 17 0Z" fill="#0284c7" stroke="#ffffff" stroke-width="2"/\x3e' +
                      '\x3ccircle cx="17" cy="16" r="9.5" fill="#ffffff"/\x3e' +
                      '\x3cpath d="M20.6 12.4c-.6-.6-1.5-.7-2.2-.3l-1.7 1.7 2.4 2.4 1.7-1.7c.4-.7.3-1.6-.2-2.1zm-4.5 2l-3.9 3.9c-.3.3-.3.9 0 1.2l1.1 1.1c.3.3.9.3 1.2 0l3.9-3.9-2.3-2.3z" fill="#0284c7"/\x3e' +
                      '\x3c/svg\x3e\x3c/div\x3e',
                iconSize: [34, 46],
                iconAnchor: [17, 46],
                popupAnchor: [0, -46]
            });
        }

        function clearOnSiteLayers() {
            if (customerMarker && customerMap) {
                try { customerMap.removeLayer(customerMarker); } catch(e){}
            }
            customerMarker = null;
        }

        function clearPickupDeliveryLayers() {
            if (pickupMarker && customerMap) {
                try { customerMap.removeLayer(pickupMarker); } catch(e){}
            }
            pickupMarker = null;
            if (deliveryMarker && customerMap) {
                try { customerMap.removeLayer(deliveryMarker); } catch(e){}
            }
            deliveryMarker = null;
            if (routePolyline && customerMap) {
                try { customerMap.removeLayer(routePolyline); } catch(e){}
            }
            routePolyline = null;
        }

        function getLivewire() {
            try {
                if (typeof @this !== 'undefined' && @this && typeof @this.call === 'function') return @this;
            } catch(e) {}
            try {
                const mapEl = document.getElementById('map') || document.getElementById('group-map') || document.querySelector('[wire\\:id]');
                const root = mapEl?.closest('[wire\\:id]') || document.querySelector('[wire\\:id]');
                if (root && window.Livewire) {
                    const id = root.getAttribute('wire:id');
                    if (id) {
                        const comp = window.Livewire.find(id);
                        if (comp) return comp;
                    }
                }
            } catch(e) {}
            try {
                if (window.Livewire && typeof window.Livewire.all === 'function') {
                    const all = window.Livewire.all();
                    if (all && all.length > 0) return all[0];
                }
            } catch(e) {}
            return null;
        }

        function getCurrentServiceType() {
            const input = document.getElementById('service-type-input');
            if (input && input.value) return input.value;
            const lw = getLivewire();
            if (lw && typeof lw.get === 'function') {
                try {
                    const val = lw.get('service_type');
                    if (val) return val;
                } catch(e){}
            }
            return '{{ $service_type }}' || 'on_site_service';
        }

        function syncMapToServiceType(serviceType, payload = {}) {
            if (!customerMap || typeof L === 'undefined') return;

            if (serviceType === 'pickup_delivery') {
                clearOnSiteLayers();

                const pLat = payload.pickupLat || parseFloat(document.getElementById('pickup-latitude-input')?.value) || parseFloat(document.getElementById('latitude-input')?.value) || null;
                const pLng = payload.pickupLng || parseFloat(document.getElementById('pickup-longitude-input')?.value) || parseFloat(document.getElementById('longitude-input')?.value) || null;
                const dLat = payload.deliveryLat || parseFloat(document.getElementById('delivery-latitude-input')?.value) || null;
                const dLng = payload.deliveryLng || parseFloat(document.getElementById('delivery-longitude-input')?.value) || null;

                if (pLat && pLng) {
                    if (pickupMarker) {
                        pickupMarker.setLatLng([pLat, pLng]);
                    } else {
                        pickupMarker = L.marker([pLat, pLng], { icon: getPickupIcon(), draggable: true }).addTo(customerMap);
                        pickupMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updatePickupCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                }

                if (dLat && dLng) {
                    if (deliveryMarker) {
                        deliveryMarker.setLatLng([dLat, dLng]);
                    } else {
                        deliveryMarker = L.marker([dLat, dLng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
                        deliveryMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateDeliveryCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                }

                if (pickupMarker && deliveryMarker) {
                    try {
                        const bounds = L.latLngBounds([pickupMarker.getLatLng(), deliveryMarker.getLatLng()]);
                        customerMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 16 });
                    } catch(e){}
                    setActiveMapPoint(window.activeMapPoint || 'pickup', false);
                } else if (pLat && pLng) {
                    customerMap.panTo([pLat, pLng]);
                    setActiveMapPoint('delivery', false);
                } else {
                    setActiveMapPoint('pickup', false);
                }

                scheduleRoutePolylineUpdate();
            } else {
                clearPickupDeliveryLayers();

                const lat = payload.lat || parseFloat(document.getElementById('latitude-input')?.value) || null;
                const lng = payload.lng || parseFloat(document.getElementById('longitude-input')?.value) || null;

                if (lat && lng) {
                    if (customerMarker) {
                        customerMarker.setLatLng([lat, lng]);
                    } else {
                        customerMarker = L.marker([lat, lng], { icon: getOnSiteIcon(), draggable: true }).addTo(customerMap);
                        customerMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    customerMap.panTo([lat, lng]);
                }
            }
        }
        window.syncMapToServiceType = syncMapToServiceType;

        window.addEventListener('service-type-changed', function(event) {
            const detail = event.detail ? (Array.isArray(event.detail) ? (event.detail[0] || {}) : event.detail) : {};
            const serviceType = detail.serviceType || getCurrentServiceType();
            syncMapToServiceType(serviceType, detail);
        });

        function setActiveMapPoint(target, shouldPan = true) {
            window.activeMapPoint = target;
            window.dispatchEvent(new CustomEvent('active-point-changed', { detail: { point: target } }));

            const searchInput = document.getElementById('map-search-input');
            const gpsLabel = document.getElementById('btn-gps-label');

            if (target === 'pickup') {
                if (gpsLabel) gpsLabel.textContent = 'GPS Titik Jemput';
                if (searchInput) searchInput.placeholder = '🔍 Cari alamat Titik 1 (Jemput)...';
                if (shouldPan && pickupMarker && customerMap) {
                    customerMap.panTo(pickupMarker.getLatLng());
                }
            } else if (target === 'delivery') {
                if (gpsLabel) gpsLabel.textContent = 'GPS Titik Antar';
                if (searchInput) searchInput.placeholder = '🔍 Cari alamat Titik 2 (Antar/Tujuan)...';
                if (shouldPan && deliveryMarker && customerMap) {
                    customerMap.panTo(deliveryMarker.getLatLng());
                }
            }
        }
        window.setActiveMapPoint = setActiveMapPoint;

        function focusMapSection() {
            const mapEl = document.getElementById('group-map');
            if (mapEl) {
                const yOffset = -120;
                const y = mapEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
            }
        }
        window.focusMapSection = focusMapSection;

        function safeInvalidateSize(mapObj, containerId = 'map') {
            if (!mapObj) return;
            try {
                const el = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
                if (el && document.body.contains(el) && mapObj._mapPane && mapObj._loaded && typeof mapObj.invalidateSize === 'function') {
                    mapObj.invalidateSize();
                }
            } catch (e) {}
        }

        function scheduleRoutePolylineUpdate() {
            if (routeDebounceTimer) {
                clearTimeout(routeDebounceTimer);
            }
            routeDebounceTimer = setTimeout(() => {
                updateRoutePolyline();
            }, 100);
        }

        async function updateRoutePolyline() {
            if (!customerMap || typeof L === 'undefined') return;

            const serviceType = getCurrentServiceType();
            if (serviceType !== 'pickup_delivery') {
                if (routePolyline) {
                    try { customerMap.removeLayer(routePolyline); } catch(e){}
                    routePolyline = null;
                }
                return;
            }

            if (pickupMarker && deliveryMarker) {
                const pLatLng = pickupMarker.getLatLng();
                const dLatLng = deliveryMarker.getLatLng();
                const currentReqId = ++latestRouteRequestId;

                // 1. Coba request rute jalan raya nyata via OSRM Driving Routing API
                try {
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${pLatLng.lng},${pLatLng.lat};${dLatLng.lng},${dLatLng.lat}?overview=full&geometries=geojson`;
                    const res = await fetch(osrmUrl);
                    const json = await res.json();

                    // Guard terhadap balasan lawas atau perubahan service_type
                    if (currentReqId !== latestRouteRequestId) return;
                    if (getCurrentServiceType() !== 'pickup_delivery') return;

                    if (json && json.routes && json.routes.length > 0) {
                        const route = json.routes[0];
                        const latLngs = route.geometry.coordinates.map(c => [c[1], c[0]]);
                        const roadDistKm = parseFloat((route.distance / 1000).toFixed(2));
                        const isExceeded = roadDistKm > 40.0;

                        if (routePolyline) {
                            try { customerMap.removeLayer(routePolyline); } catch(e){}
                        }

                        routePolyline = L.polyline(latLngs, {
                            color: isExceeded ? '#ef4444' : '#2563eb',
                            weight: 5,
                            opacity: 0.85,
                            lineCap: 'round',
                            lineJoin: 'round',
                            dashArray: isExceeded ? '8, 8' : null
                        }).addTo(customerMap);

                        const lw = getLivewire();
                        if (lw && typeof lw.call === 'function') {
                            lw.call('updateRouteDistanceRoad', roadDistKm);
                        }

                        if (isExceeded) {
                            window.dispatchEvent(new CustomEvent('max-distance-exceeded', {
                                detail: {
                                    distance: roadDistKm,
                                    max: 40,
                                    message: `Jarak rute pengantaran (${roadDistKm} KM) melebihi batas maksimal 40 KM untuk armada sepeda motor.`
                                }
                            }));
                        } else {
                            window.dispatchEvent(new CustomEvent('max-distance-cleared'));
                        }

                        try {
                            customerMap.fitBounds(routePolyline.getBounds(), { padding: [45, 45], maxZoom: 16 });
                        } catch(e) {}
                        return;
                    }
                } catch (err) {
                    console.warn('OSRM road route fetch error, using fallback road estimation:', err);
                }

                if (currentReqId !== latestRouteRequestId) return;
                if (getCurrentServiceType() !== 'pickup_delivery') return;

                // Fallback: Straight line with 1.30x urban road curvature factor
                const meters = pLatLng.distanceTo(dLatLng);
                const roadDistKm = parseFloat(((meters / 1000) * 1.30).toFixed(2));
                const isExceeded = roadDistKm > 40.0;

                if (routePolyline) {
                    try { customerMap.removeLayer(routePolyline); } catch(e){}
                }
                const straightLatLngs = [pLatLng, dLatLng];
                routePolyline = L.polyline(straightLatLngs, {
                    color: isExceeded ? '#ef4444' : '#2563eb',
                    weight: 4,
                    dashArray: isExceeded ? '8, 8' : '6, 8',
                    opacity: 0.85
                }).addTo(customerMap);

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('updateRouteDistanceRoad', roadDistKm);
                }

                if (isExceeded) {
                    window.dispatchEvent(new CustomEvent('max-distance-exceeded', {
                        detail: {
                            distance: roadDistKm,
                            max: 40,
                            message: `Jarak rute pengantaran (${roadDistKm} KM) melebihi batas maksimal 40 KM untuk armada sepeda motor.`
                        }
                    }));
                } else {
                    window.dispatchEvent(new CustomEvent('max-distance-cleared'));
                }

                try {
                    const bounds = L.latLngBounds(straightLatLngs);
                    customerMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 16 });
                } catch(e) {}
            } else if (routePolyline) {
                try { customerMap.removeLayer(routePolyline); } catch(e){}
                routePolyline = null;
            }
        }

        function locateUserGPS(targetPoint = null) {
            window.dispatchEvent(new CustomEvent('cancel-active-searches'));
            if (targetPoint) {
                setActiveMapPoint(targetPoint);
            }

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                return;
            }

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = 'Mencari GPS...';
                pill.className = 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold animate-pulse';
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const serviceType = getCurrentServiceType();

                    if (lat > 6.5 || lat < -11.5 || lng < 94.5 || lng > 141.5) {
                        window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                            detail: { reason: 'Posisi GPS berada di luar batas wilayah Republik Indonesia.' }
                        }));
                        if (pill) {
                            pill.textContent = 'Luar Jangkauan';
                            pill.className = 'px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[11px] font-semibold';
                        }
                        return;
                    }

                    if (customerMap && typeof L !== 'undefined') {
                        customerMap.setView([lat, lng], 16);

                        if (serviceType === 'pickup_delivery') {
                            clearOnSiteLayers();
                            const activeMode = window.activeMapPoint || 'pickup';
                            if (activeMode === 'pickup') {
                                if (pickupMarker) {
                                    pickupMarker.setLatLng([lat, lng]);
                                } else {
                                    pickupMarker = L.marker([lat, lng], { icon: getPickupIcon(), draggable: true }).addTo(customerMap);
                                    pickupMarker.on('dragend', function(e) {
                                        const pos = e.target.getLatLng();
                                        updatePickupCoordinates(pos.lat, pos.lng, false);
                                    });
                                }
                                updatePickupCoordinates(lat, lng, true);
                            } else {
                                if (deliveryMarker) {
                                    deliveryMarker.setLatLng([lat, lng]);
                                } else {
                                    deliveryMarker = L.marker([lat, lng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
                                    deliveryMarker.on('dragend', function(e) {
                                        const pos = e.target.getLatLng();
                                        updateDeliveryCoordinates(pos.lat, pos.lng, false);
                                    });
                                }
                                updateDeliveryCoordinates(lat, lng, true);
                            }
                        } else {
                            clearPickupDeliveryLayers();
                            if (customerMarker) {
                                customerMarker.setLatLng([lat, lng]);
                            } else {
                                customerMarker = L.marker([lat, lng], { icon: getOnSiteIcon(), draggable: true }).addTo(customerMap);
                                customerMarker.on('dragend', function(e) {
                                    const pos = e.target.getLatLng();
                                    updateCoordinates(pos.lat, pos.lng, false);
                                });
                            }
                            updateCoordinates(lat, lng, true);
                        }
                    }
                },
                (error) => {
                    console.warn('GPS location error:', error.message);
                    if (pill) {
                        pill.textContent = 'Gagal Deteksi';
                        pill.className = 'px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-[11px] font-semibold';
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
        window.locateUserGPS = locateUserGPS;

        function selectMapLocation(lat, lng, displayName = '', targetPoint = null) {
            const serviceType = getCurrentServiceType();
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            if (isNaN(lat) || isNaN(lng)) return;

            if (lat > 6.5 || lat < -11.5 || lng < 94.5 || lng > 141.5) {
                window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                    detail: { reason: 'Lokasi yang dipilih berada di luar batas wilayah Republik Indonesia.' }
                }));
                return;
            }

            if (customerMap && typeof L !== 'undefined') {
                if (serviceType === 'pickup_delivery') {
                    clearOnSiteLayers();
                    const activeMode = targetPoint || window.activeMapPoint || 'pickup';
                    if (activeMode === 'pickup') {
                        if (pickupMarker) {
                            pickupMarker.setLatLng([lat, lng]);
                        } else {
                            pickupMarker = L.marker([lat, lng], { icon: getPickupIcon(), draggable: true }).addTo(customerMap);
                            pickupMarker.on('dragend', function(e) {
                                const pos = e.target.getLatLng();
                                updatePickupCoordinates(pos.lat, pos.lng, false);
                            });
                        }
                        updatePickupCoordinates(lat, lng, false, displayName);
                        setActiveMapPoint('pickup', false);
                        if (deliveryMarker) {
                            try {
                                const bounds = L.latLngBounds([pickupMarker.getLatLng(), deliveryMarker.getLatLng()]);
                                customerMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 16 });
                            } catch(e){
                                customerMap.setView([lat, lng], 16);
                            }
                        } else {
                            customerMap.setView([lat, lng], 16);
                            setActiveMapPoint('delivery', false);
                        }
                    } else {
                        if (deliveryMarker) {
                            deliveryMarker.setLatLng([lat, lng]);
                        } else {
                            deliveryMarker = L.marker([lat, lng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
                            deliveryMarker.on('dragend', function(e) {
                                const pos = e.target.getLatLng();
                                updateDeliveryCoordinates(pos.lat, pos.lng, false);
                            });
                        }
                        updateDeliveryCoordinates(lat, lng, false, displayName);
                        setActiveMapPoint('delivery', false);
                        if (pickupMarker) {
                            try {
                                const bounds = L.latLngBounds([pickupMarker.getLatLng(), deliveryMarker.getLatLng()]);
                                customerMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 16 });
                            } catch(e){
                                customerMap.setView([lat, lng], 16);
                            }
                        } else {
                            customerMap.setView([lat, lng], 16);
                        }
                    }
                } else {
                    clearPickupDeliveryLayers();
                    if (customerMarker) {
                        customerMarker.setLatLng([lat, lng]);
                    } else {
                        customerMarker = L.marker([lat, lng], { icon: getOnSiteIcon(), draggable: true }).addTo(customerMap);
                        customerMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    updateCoordinates(lat, lng, false, displayName);
                    customerMap.setView([lat, lng], 16);
                }
            }
        }
        window.selectMapLocation = selectMapLocation;
        window.selectMapLocationForPoint = function(point, lat, lng, displayName = '') {
            selectMapLocation(lat, lng, displayName, point);
        };

        function clearMapLocation(point = null) {
            window.dispatchEvent(new CustomEvent('cancel-active-searches'));
            window.dispatchEvent(new CustomEvent('max-distance-cleared'));
            const serviceType = getCurrentServiceType();
            const target = point || (serviceType === 'pickup_delivery' ? (window.activeMapPoint || 'pickup') : 'onsite');

            if (target === 'pickup') {
                if (pickupGeocodeController) {
                    pickupGeocodeController.abort();
                    pickupGeocodeController = null;
                }
                if (pickupMarker && customerMap) {
                    try { customerMap.removeLayer(pickupMarker); } catch(e){}
                }
                pickupMarker = null;

                if (routePolyline && customerMap) {
                    try { customerMap.removeLayer(routePolyline); } catch(e){}
                }
                routePolyline = null;

                const pLatInput = document.getElementById('pickup-latitude-input');
                const pLngInput = document.getElementById('pickup-longitude-input');
                const pAddrInput = document.getElementById('pickup-address-input');
                if (pLatInput) { pLatInput.value = ''; pLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (pLngInput) { pLngInput.value = ''; pLngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (pAddrInput) { pAddrInput.value = ''; pAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const mapSearchInput = document.getElementById('map-search-input');
                if (mapSearchInput && (window.activeMapPoint === 'pickup')) {
                    mapSearchInput.value = '';
                }

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('clearLocationPoint', 'pickup');
                }

                window.dispatchEvent(new CustomEvent('map-marker-cleared', { detail: { point: 'pickup' } }));

                if (deliveryMarker && customerMap) {
                    customerMap.panTo(deliveryMarker.getLatLng());
                } else {
                    const displayEl = document.getElementById('coordinates-display');
                    if (displayEl) displayEl.classList.add('hidden');
                }
            } else if (target === 'delivery') {
                if (deliveryGeocodeController) {
                    deliveryGeocodeController.abort();
                    deliveryGeocodeController = null;
                }
                if (deliveryMarker && customerMap) {
                    try { customerMap.removeLayer(deliveryMarker); } catch(e){}
                }
                deliveryMarker = null;

                if (routePolyline && customerMap) {
                    try { customerMap.removeLayer(routePolyline); } catch(e){}
                }
                routePolyline = null;

                const dLatInput = document.getElementById('delivery-latitude-input');
                const dLngInput = document.getElementById('delivery-longitude-input');
                const dAddrInput = document.getElementById('delivery-address-input');
                if (dLatInput) { dLatInput.value = ''; dLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (dLngInput) { dLngInput.value = ''; dLngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (dAddrInput) { dAddrInput.value = ''; dAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const mapSearchInput = document.getElementById('map-search-input');
                if (mapSearchInput && (window.activeMapPoint === 'delivery')) {
                    mapSearchInput.value = '';
                }

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('clearLocationPoint', 'delivery');
                }

                window.dispatchEvent(new CustomEvent('map-marker-cleared', { detail: { point: 'delivery' } }));

                if (pickupMarker && customerMap) {
                    customerMap.panTo(pickupMarker.getLatLng());
                } else {
                    const displayEl = document.getElementById('coordinates-display');
                    if (displayEl) displayEl.classList.add('hidden');
                }
            } else {
                // On-Site / customerMarker
                if (onsiteGeocodeController) {
                    onsiteGeocodeController.abort();
                    onsiteGeocodeController = null;
                }
                clearOnSiteLayers();

                const latInput = document.getElementById('latitude-input');
                const lngInput = document.getElementById('longitude-input');
                const locInput = document.getElementById('location-input');
                if (latInput) { latInput.value = ''; latInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (lngInput) { lngInput.value = ''; lngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (locInput) { locInput.value = ''; locInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const mapSearchInput = document.getElementById('map-search-input');
                if (mapSearchInput) mapSearchInput.value = '';

                const displayEl = document.getElementById('coordinates-display');
                if (displayEl) displayEl.classList.add('hidden');

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('clearLocationPoint', 'onsite');
                }

                window.dispatchEvent(new CustomEvent('map-marker-cleared', { detail: { point: 'onsite' } }));
            }
        }
        window.clearMapLocation = clearMapLocation;

        /**
         * Smart Indonesian Address & Geocoding Search Engine (High Performance Parallel Edition)
         * Handles:
         * - Hierarchical searches: nama desa, kelurahan, kecamatan, kabupaten, provinsi
         * - Street + village: nama jalan, desa
         * - Mixed permutations: desa jalan kelurahan, jl gejayan caturtunggal
         * - Comma (',') delimited OR space (' ') only queries
         * - Indonesian abbreviation expansions (jl., gg., ds., kel., kec., kab., kot., prov., diy, dki)
         * - Parallel multi-query resolution with instant AbortSignal cancellation
         * - Indonesian territory boundaries and safety checks
         * - Rich Indonesian metadata badging (Jalan, Desa/Kelurahan, Kecamatan, Kabupaten/Kota, Tempat)
         */
        window.searchIndonesianPlaces = async function(rawQuery, maxResults = 6, externalSignal = null) {
            if (!rawQuery || typeof rawQuery !== 'string' || rawQuery.trim().length < 2) {
                return [];
            }

            const cleanedRaw = rawQuery.trim();

            // 1. Expand standard Indonesian address abbreviations
            function expandIndonesianTerms(str) {
                let s = ' ' + str.toLowerCase() + ' ';
                s = s.replace(/[\.\;\:\/\\\|]/g, ' ');
                s = s.replace(/\s+/g, ' ');

                s = s.replace(/\bjl\b|\bjln\b|\bjl\.\b|\bjln\.\b/g, ' jalan ');
                s = s.replace(/\bgg\b|\bgg\.\b/g, ' gang ');
                s = s.replace(/\bds\b|\bds\.\b/g, ' desa ');
                s = s.replace(/\bkel\b|\bkel\.\b/g, ' kelurahan ');
                s = s.replace(/\bkec\b|\bkec\.\b/g, ' kecamatan ');
                s = s.replace(/\bkab\b|\bkab\.\b/g, ' kabupaten ');
                s = s.replace(/\bkot\b|\bkot\.\b|\bkotamadya\b/g, ' kota ');
                s = s.replace(/\bprov\b|\bprov\.\b/g, ' provinsi ');
                s = s.replace(/\bdiy\b|\bd\.i\.y\b|\bd\.i\. yogyakarta\b|\byogya\b|\bjogja\b/g, ' yogyakarta ');
                s = s.replace(/\bdki\b|\bd\.k\.i\b|\bdki jakarta\b/g, ' dki jakarta ');
                s = s.replace(/\bjabar\b/g, ' jawa barat ');
                s = s.replace(/\bjateng\b/g, ' jawa tengah ');
                s = s.replace(/\bjatim\b/g, ' jawa timur ');
                s = s.replace(/\bsulsel\b/g, ' sulawesi selatan ');
                s = s.replace(/\bsumut\b/g, ' sumatera utara ');
                s = s.replace(/\bsumbar\b/g, ' sumatera barat ');
                s = s.replace(/\bsumsel\b/g, ' sumatera selatan ');
                s = s.replace(/\bkalsel\b/g, ' kalimantan selatan ');
                s = s.replace(/\bkaltim\b/g, ' kalimantan timur ');
                return s.trim().replace(/\s+/g, ' ');
            }

            // 2. Parse into Indonesian semantic address tokens
            function extractAddressTokens(normalizedStr) {
                const tokens = {
                    street: '',
                    village: '',
                    district: '',
                    regency: '',
                    province: '',
                    extras: []
                };

                const hasCommas = cleanedRaw.includes(',');
                let segments = [];

                if (hasCommas) {
                    segments = cleanedRaw.split(',').map(s => expandIndonesianTerms(s)).filter(Boolean);
                } else {
                    const words = normalizedStr.split(' ');
                    let currentPrefix = null;
                    let currentBuffer = [];

                    for (let i = 0; i < words.length; i++) {
                        const word = words[i];
                        if (['jalan', 'gang', 'desa', 'kelurahan', 'kecamatan', 'kabupaten', 'kota', 'provinsi'].includes(word)) {
                            if (currentBuffer.length > 0) {
                                segments.push({ prefix: currentPrefix, text: currentBuffer.join(' ') });
                                currentBuffer = [];
                            }
                            currentPrefix = word;
                        } else {
                            currentBuffer.push(word);
                        }
                    }
                    if (currentBuffer.length > 0) {
                        segments.push({ prefix: currentPrefix, text: currentBuffer.join(' ') });
                    }
                }

                segments.forEach(seg => {
                    const txt = typeof seg === 'string' ? seg : (seg.prefix ? `${seg.prefix} ${seg.text}` : seg.text);
                    const lower = txt.toLowerCase();

                    if (lower.startsWith('jalan ') || lower.startsWith('gang ')) {
                        tokens.street = txt;
                    } else if (lower.startsWith('desa ') || lower.startsWith('kelurahan ')) {
                        tokens.village = txt;
                    } else if (lower.startsWith('kecamatan ')) {
                        tokens.district = txt;
                    } else if (lower.startsWith('kabupaten ') || lower.startsWith('kota ')) {
                        tokens.regency = txt;
                    } else if (lower.startsWith('provinsi ') || lower.includes('yogyakarta') || lower.includes('jakarta') || lower.includes('jawa')) {
                        tokens.province = txt;
                    } else {
                        tokens.extras.push(txt);
                    }
                });

                return tokens;
            }

            const normalized = expandIndonesianTerms(cleanedRaw);
            const tokens = extractAddressTokens(normalized);

            // 3. Generate Multi-Strategy Query Variations
            const queryCandidates = [];

            // Strategy A: Direct query with comma-cleaning and Indonesia suffix
            const directClean = cleanedRaw.replace(/[,;]+/g, ', ').replace(/\s+/g, ' ').trim();
            const directWithId = directClean.toLowerCase().includes('indonesia') ? directClean : `${directClean}, Indonesia`;
            queryCandidates.push(directWithId);

            // Strategy B: Canonical Indonesian Hierarchy
            const canonicalParts = [];
            if (tokens.street) canonicalParts.push(tokens.street);
            if (tokens.village) canonicalParts.push(tokens.village);
            if (tokens.district) canonicalParts.push(tokens.district);
            if (tokens.regency) canonicalParts.push(tokens.regency);
            if (tokens.province) canonicalParts.push(tokens.province);
            tokens.extras.forEach(ext => canonicalParts.push(ext));

            if (canonicalParts.length > 1) {
                const canonicalQuery = canonicalParts.join(', ') + ', Indonesia';
                if (!queryCandidates.includes(canonicalQuery)) {
                    queryCandidates.push(canonicalQuery);
                }
            }

            // Strategy C: Space-separated words to comma-separated tokens
            if (!cleanedRaw.includes(',')) {
                const words = cleanedRaw.split(/\s+/).filter(w => w.length > 1);
                if (words.length >= 2) {
                    const commaDelimited = words.join(', ') + ', Indonesia';
                    if (!queryCandidates.includes(commaDelimited)) {
                        queryCandidates.push(commaDelimited);
                    }
                }
            }

            // Strategy D: Prefix-stripped clean named entities
            const strippedNames = normalized
                .replace(/\b(desa|kelurahan|kecamatan|kabupaten|kota|provinsi)\b/gi, '')
                .replace(/\s+/g, ' ')
                .trim();
            if (strippedNames && strippedNames !== normalized) {
                const strippedQuery = `${strippedNames}, Indonesia`;
                if (!queryCandidates.includes(strippedQuery)) {
                    queryCandidates.push(strippedQuery);
                }
            }

            // 4. OSM Geocoding Fetcher with AbortSignal
            async function fetchOsmSearch(queryStr) {
                const url = `https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&addressdetails=1&extratags=1&limit=6&q=${encodeURIComponent(queryStr)}`;
                try {
                    const resp = await fetch(url, {
                        signal: externalSignal || null,
                        headers: { 'Accept-Language': 'id, en;q=0.8' }
                    });
                    if (!resp.ok) return [];
                    const data = await resp.json();
                    return Array.isArray(data) ? data : [];
                } catch (e) {
                    return [];
                }
            }

            // Execute top queries in PARALLEL to avoid slow serial delays
            const primaryQueries = queryCandidates.slice(0, 2);
            const queryResults = await Promise.allSettled(primaryQueries.map(q => fetchOsmSearch(q)));

            const seenKeys = new Set();
            const finalResults = [];

            for (const res of queryResults) {
                if (res.status === 'fulfilled' && Array.isArray(res.value)) {
                    for (const item of res.value) {
                        const placeId = item.place_id || (parseFloat(item.lat).toFixed(4) + '_' + parseFloat(item.lon).toFixed(4));
                        if (!seenKeys.has(placeId)) {
                            seenKeys.add(placeId);
                            const lat = parseFloat(item.lat);
                            const lon = parseFloat(item.lon);
                            if (lat <= 6.5 && lat >= -11.5 && lon >= 94.5 && lon <= 141.5) {
                                finalResults.push(formatIndonesianResult(item));
                                if (finalResults.length >= maxResults) break;
                            }
                        }
                    }
                }
                if (finalResults.length >= maxResults) break;
            }

            // Fallback: If no results found in primary queries, test secondary candidates
            if (finalResults.length === 0 && queryCandidates.length > 2) {
                const fallbackQueries = queryCandidates.slice(2, 4);
                const fallbackRes = await Promise.allSettled(fallbackQueries.map(q => fetchOsmSearch(q)));
                for (const res of fallbackRes) {
                    if (res.status === 'fulfilled' && Array.isArray(res.value)) {
                        for (const item of res.value) {
                            const placeId = item.place_id || (parseFloat(item.lat).toFixed(4) + '_' + parseFloat(item.lon).toFixed(4));
                            if (!seenKeys.has(placeId)) {
                                seenKeys.add(placeId);
                                const lat = parseFloat(item.lat);
                                const lon = parseFloat(item.lon);
                                if (lat <= 6.5 && lat >= -11.5 && lon >= 94.5 && lon <= 141.5) {
                                    finalResults.push(formatIndonesianResult(item));
                                    if (finalResults.length >= maxResults) break;
                                }
                            }
                        }
                    }
                    if (finalResults.length >= maxResults) break;
                }
            }

            // 5. Result Formatter & Indonesian Metadata Badging
            function formatIndonesianResult(item) {
                const addr = item.address || {};
                const road = addr.road || addr.street || addr.footway || addr.pedestrian || addr.path || addr.highway || '';
                const village = addr.village || addr.hamlet || addr.quarter || addr.suburb || addr.neighbourhood || '';
                const district = addr.city_district || addr.district || addr.municipality || addr.subdistrict || '';
                const regency = addr.city || addr.town || addr.county || addr.regency || '';
                const province = addr.state || addr.province || '';
                const name = item.name || (item.display_name ? item.display_name.split(',')[0].trim() : '');

                let badge = 'Lokasi';
                let badgeIcon = '📍';
                let badgeClass = 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300';
                let mainTitle = name;

                if (road && (name === road || item.class === 'highway')) {
                    badge = 'Jalan';
                    badgeIcon = '🛣️';
                    badgeClass = 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300';
                    mainTitle = road.toLowerCase().startsWith('jl') ? road : `Jl. ${road}`;
                } else if (item.type === 'village' || item.type === 'hamlet' || item.type === 'suburb' || (village && name === village)) {
                    badge = 'Desa / Kelurahan';
                    badgeIcon = '🏘️';
                    badgeClass = 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300';
                    mainTitle = village ? `Desa/Kel. ${village}` : name;
                } else if (item.type === 'district' || item.type === 'subdistrict' || item.type === 'city_district' || (district && name === district)) {
                    badge = 'Kecamatan';
                    badgeIcon = '🏛️';
                    badgeClass = 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300';
                    mainTitle = district ? `Kec. ${district}` : name;
                } else if (item.type === 'city' || item.type === 'town' || item.type === 'county' || (regency && name === regency)) {
                    badge = 'Kabupaten / Kota';
                    badgeIcon = '🏢';
                    badgeClass = 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300';
                    mainTitle = regency;
                } else if (item.class === 'amenity' || item.class === 'shop' || item.class === 'tourism' || item.class === 'building') {
                    badge = 'Tempat / Bangunan';
                    badgeIcon = '🏢';
                    badgeClass = 'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300';
                    mainTitle = name;
                }

                // Subtitle hierarchy: [Desa/Kelurahan], Kec. [Kecamatan], [Kabupaten/Kota], [Provinsi]
                const subParts = [];
                if (road && mainTitle !== road && !mainTitle.includes(road)) subParts.push(`Jl. ${road}`);
                if (village && !mainTitle.includes(village)) subParts.push(village);
                if (district && !mainTitle.includes(district)) subParts.push(`Kec. ${district}`);
                if (regency && !mainTitle.includes(regency)) subParts.push(regency);
                if (province && !mainTitle.includes(province)) subParts.push(province);

                const hierarchySubtitle = subParts.length > 0 ? subParts.join(', ') : item.display_name;

                return {
                    place_id: item.place_id,
                    osm_id: item.osm_id,
                    lat: item.lat,
                    lon: item.lon,
                    display_name: item.display_name,
                    main_title: mainTitle,
                    hierarchy_subtitle: hierarchySubtitle,
                    badge: badge,
                    badge_icon: badgeIcon,
                    badge_class: badgeClass,
                    address: addr,
                    raw: item
                };
            }

            return finalResults;
        };

        function updateCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = lat.toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = lng.toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS Realtime' : 'Titik Peta';
                pill.className = isGPS 
                    ? 'px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold'
                    : 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold';
            }

            const latInput = document.getElementById('latitude-input');
            const lngInput = document.getElementById('longitude-input');
            if (latInput) { latInput.value = lat; latInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (lngInput) { lngInput.value = lng; lngInput.dispatchEvent(new Event('input', { bubbles: true })); }

            // Jika fallbackAddr tersedia (dipilih dari dropdown pencarian), langsung sinkron tanpa reverse geocoding ganda
            if (fallbackAddr) {
                const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                if (onsiteGeocodeController) {
                    onsiteGeocodeController.abort();
                    onsiteGeocodeController = null;
                }
                const locInput = document.getElementById('location-input');
                if (locInput) {
                    locInput.value = fallbackAddr;
                    locInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncOnSiteLocation', lat, lng, fallbackAddr);
                }
                window.dispatchEvent(new CustomEvent('map-address-updated', {
                    detail: { point: 'onsite', address: fallbackAddr, lat, lng }
                }));
                return;
            }

            // --- Instant Synchronous Sync to Livewire & UI (Mencegah data tidak tercatat saat klik langsung) ---
            const locInput = document.getElementById('location-input');
            const initialAddr = locInput?.value?.trim() || `Titik Lokasi (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
            if (locInput && !locInput.value.trim()) {
                locInput.value = initialAddr;
                locInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const lw = getLivewire();
            if (lw && typeof lw.call === 'function') {
                lw.call('syncOnSiteLocation', lat, lng, initialAddr);
            }

            window.dispatchEvent(new CustomEvent('map-address-updated', {
                detail: { point: 'onsite', address: initialAddr, lat, lng }
            }));

            // --- Asynchronous Reverse Geocoding via Nominatim ---
            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (onsiteGeocodeController) {
                onsiteGeocodeController.abort();
            }
            onsiteGeocodeController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: onsiteGeocodeController.signal,
                headers: { 'Accept-Language': 'id' }
            })
            .then(r => r.json())
            .then(data => {
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');

                // Validasi Zona Terlarang / Perairan
                const safety = isRestrictedOsmLocation(data, lat, lng);
                if (safety.isRestricted) {
                    clearOnSiteLayers();
                    if (latInput) { latInput.value = ''; latInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (lngInput) { lngInput.value = ''; lngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (locInput) { locInput.value = ''; locInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (displayEl) displayEl.classList.add('hidden');

                    window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                        detail: { reason: safety.reason }
                    }));
                    return;
                }

                if (data) {
                    const addr = data.address || {};
                    const cityName = addr.city || addr.town || addr.county || addr.city_district || '';
                    const districtName = addr.municipality || addr.city_district || addr.suburb || addr.district || addr.quarter || addr.village || '';
                    const provinceName = addr.state || addr.province || '';
                    const fullAddress = data.display_name || initialAddr;

                    if (locInput) {
                        locInput.value = fullAddress;
                        locInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    const livewireInstance = getLivewire();
                    if (livewireInstance && typeof livewireInstance.call === 'function') {
                        livewireInstance.call('syncOnSiteLocation', lat, lng, fullAddress, cityName, districtName, provinceName);
                    }

                    window.dispatchEvent(new CustomEvent('map-address-updated', {
                        detail: { point: 'onsite', address: fullAddress, lat, lng }
                    }));
                }
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
            });
        }

        function updatePickupCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = lat.toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = lng.toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS (Titik 1 Jemput)' : 'Titik 1 Jemput';
                pill.className = 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold';
            }

            const pLatInput = document.getElementById('pickup-latitude-input');
            const pLngInput = document.getElementById('pickup-longitude-input');
            const latInput  = document.getElementById('latitude-input');
            const lngInput  = document.getElementById('longitude-input');
            if (pLatInput) { pLatInput.value = lat; pLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (pLngInput) { pLngInput.value = lng; pLngInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (latInput)  { latInput.value  = lat; latInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (lngInput)  { lngInput.value  = lng; lngInput.dispatchEvent(new Event('input', { bubbles: true })); }

            // Jika fallbackAddr tersedia (dipilih dari dropdown pencarian), langsung sinkron tanpa reverse geocoding ganda
            if (fallbackAddr) {
                const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                if (pickupGeocodeController) {
                    pickupGeocodeController.abort();
                    pickupGeocodeController = null;
                }
                const pAddrInput = document.getElementById('pickup-address-input');
                const locInput   = document.getElementById('location-input');
                if (pAddrInput) { pAddrInput.value = fallbackAddr; pAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (locInput)   { locInput.value   = fallbackAddr; locInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncPickupLocation', lat, lng, fallbackAddr);
                }
                window.dispatchEvent(new CustomEvent('map-address-updated', {
                    detail: { point: 'pickup', address: fallbackAddr, lat, lng }
                }));
                scheduleRoutePolylineUpdate();
                return;
            }

            // --- Instant Synchronous Sync to Livewire & UI (Mencegah data hilang saat klik langsung di peta) ---
            const pAddrInput = document.getElementById('pickup-address-input');
            const locInput   = document.getElementById('location-input');
            const initialAddr = pAddrInput?.value?.trim() || `Titik Jemput (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
            if (pAddrInput && !pAddrInput.value.trim()) {
                pAddrInput.value = initialAddr;
                pAddrInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (locInput && !locInput.value.trim()) {
                locInput.value = initialAddr;
                locInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const lw = getLivewire();
            if (lw && typeof lw.call === 'function') {
                lw.call('syncPickupLocation', lat, lng, initialAddr);
            }

            window.dispatchEvent(new CustomEvent('map-address-updated', {
                detail: { point: 'pickup', address: initialAddr, lat, lng }
            }));

            scheduleRoutePolylineUpdate();

            // --- Asynchronous Reverse Geocoding via Nominatim ---
            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (pickupGeocodeController) {
                pickupGeocodeController.abort();
            }
            pickupGeocodeController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: pickupGeocodeController.signal,
                headers: { 'Accept-Language': 'id' }
            })
            .then(r => r.json())
            .then(data => {
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');

                // Validasi Zona Terlarang / Perairan
                const safety = isRestrictedOsmLocation(data, lat, lng);
                if (safety.isRestricted) {
                    if (pickupMarker && customerMap) {
                        try { customerMap.removeLayer(pickupMarker); } catch(e){}
                        pickupMarker = null;
                    }
                    if (routePolyline && customerMap) {
                        try { customerMap.removeLayer(routePolyline); } catch(e){}
                        routePolyline = null;
                    }
                    if (pLatInput) { pLatInput.value = ''; pLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (pLngInput) { pLngInput.value = ''; pLngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (pAddrInput) { pAddrInput.value = ''; pAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (displayEl) displayEl.classList.add('hidden');

                    window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                        detail: { reason: safety.reason }
                    }));
                    return;
                }

                const fullAddress = data?.display_name || initialAddr;
                const addr = data?.address || {};
                const cityName = addr.city || addr.town || addr.county || addr.city_district || '';
                const districtName = addr.municipality || addr.city_district || addr.suburb || addr.district || addr.quarter || addr.village || '';
                const provinceName = addr.state || addr.province || '';

                if (pAddrInput) { pAddrInput.value = fullAddress; pAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }
                if (locInput)   { locInput.value   = fullAddress; locInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const livewireInstance = getLivewire();
                if (livewireInstance && typeof livewireInstance.call === 'function') {
                    livewireInstance.call('syncPickupLocation', lat, lng, fullAddress, cityName, districtName, provinceName);
                }

                window.dispatchEvent(new CustomEvent('map-address-updated', {
                    detail: { point: 'pickup', address: fullAddress, lat, lng }
                }));
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
            });

            scheduleRoutePolylineUpdate();
        }

        function updateDeliveryCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = lat.toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = lng.toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS (Titik 2 Antar)' : 'Titik 2 Antar';
                pill.className = 'px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold';
            }

            const dLatInput = document.getElementById('delivery-latitude-input');
            const dLngInput = document.getElementById('delivery-longitude-input');
            if (dLatInput) { dLatInput.value = lat; dLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (dLngInput) { dLngInput.value = lng; dLngInput.dispatchEvent(new Event('input', { bubbles: true })); }

            // Jika fallbackAddr tersedia (dipilih dari dropdown pencarian), langsung sinkron tanpa reverse geocoding ganda
            if (fallbackAddr) {
                const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                if (deliveryGeocodeController) {
                    deliveryGeocodeController.abort();
                    deliveryGeocodeController = null;
                }
                const dAddrInput = document.getElementById('delivery-address-input');
                if (dAddrInput) { dAddrInput.value = fallbackAddr; dAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncDeliveryLocation', lat, lng, fallbackAddr);
                }
                window.dispatchEvent(new CustomEvent('map-address-updated', {
                    detail: { point: 'delivery', address: fallbackAddr, lat, lng }
                }));
                scheduleRoutePolylineUpdate();
                return;
            }

            // --- Instant Synchronous Sync to Livewire & UI (Mencegah data hilang saat klik langsung di peta) ---
            const dAddrInput = document.getElementById('delivery-address-input');
            const initialAddr = dAddrInput?.value?.trim() || `Titik Antar (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
            if (dAddrInput && !dAddrInput.value.trim()) {
                dAddrInput.value = initialAddr;
                dAddrInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const lw = getLivewire();
            if (lw && typeof lw.call === 'function') {
                lw.call('syncDeliveryLocation', lat, lng, initialAddr);
            }

            window.dispatchEvent(new CustomEvent('map-address-updated', {
                detail: { point: 'delivery', address: initialAddr, lat, lng }
            }));

            scheduleRoutePolylineUpdate();

            // --- Asynchronous Reverse Geocoding via Nominatim ---
            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (deliveryGeocodeController) {
                deliveryGeocodeController.abort();
            }
            deliveryGeocodeController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: deliveryGeocodeController.signal,
                headers: { 'Accept-Language': 'id' }
            })
            .then(r => r.json())
            .then(data => {
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');

                // Validasi Zona Terlarang / Perairan
                const safety = isRestrictedOsmLocation(data, lat, lng);
                if (safety.isRestricted) {
                    if (deliveryMarker && customerMap) {
                        try { customerMap.removeLayer(deliveryMarker); } catch(e){}
                        deliveryMarker = null;
                    }
                    if (routePolyline && customerMap) {
                        try { customerMap.removeLayer(routePolyline); } catch(e){}
                        routePolyline = null;
                    }
                    if (dLatInput) { dLatInput.value = ''; dLatInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (dLngInput) { dLngInput.value = ''; dLngInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (dAddrInput) { dAddrInput.value = ''; dAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (displayEl) displayEl.classList.add('hidden');

                    window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                        detail: { reason: safety.reason }
                    }));
                    return;
                }

                const fullAddress = data?.display_name || initialAddr;

                if (dAddrInput) { dAddrInput.value = fullAddress; dAddrInput.dispatchEvent(new Event('input', { bubbles: true })); }

                const livewireInstance = getLivewire();
                if (livewireInstance && typeof livewireInstance.call === 'function') {
                    livewireInstance.call('syncDeliveryLocation', lat, lng, fullAddress);
                }

                window.dispatchEvent(new CustomEvent('map-address-updated', {
                    detail: { point: 'delivery', address: fullAddress, lat, lng }
                }));
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
            });

            scheduleRoutePolylineUpdate();
        }

        document.addEventListener('map-location-resolved', function(event) {
            const data = event.detail ? (event.detail[0] || event.detail) : {};
            const territoryEl = document.getElementById('territory-display');
            const territoryNameEl = document.getElementById('territory-name-display');
            if (territoryEl && territoryNameEl) {
                const parts = [];
                if (data.districtName && data.districtName !== '-') parts.push('Kec. ' + data.districtName);
                if (data.cityName && data.cityName !== '-') parts.push(data.cityName);
                
                if (parts.length > 0) {
                    territoryNameEl.textContent = parts.join(', ');
                    territoryEl.classList.remove('hidden');
                }
            }
        });

        window.addEventListener('restricted-location-detected', function(event) {
            const detail = event.detail ? (Array.isArray(event.detail) ? event.detail[0] : event.detail) : {};
            const point = detail.point || 'onsite';

            if (point === 'pickup') {
                if (pickupMarker && customerMap) {
                    try { customerMap.removeLayer(pickupMarker); } catch(e){}
                    pickupMarker = null;
                }
            } else if (point === 'delivery') {
                if (deliveryMarker && customerMap) {
                    try { customerMap.removeLayer(deliveryMarker); } catch(e){}
                    deliveryMarker = null;
                }
            } else {
                clearOnSiteLayers();
            }

            if (routePolyline && customerMap) {
                try { customerMap.removeLayer(routePolyline); } catch(e){}
                routePolyline = null;
            }
        });

        function initializeMap() {
            const mapContainer = document.getElementById('map');
            if (!mapContainer) return;

            if (typeof L === 'undefined') {
                waitForLeaflet(() => initializeMap());
                return;
            }

            if (mapResizeTimer) {
                clearTimeout(mapResizeTimer);
                mapResizeTimer = null;
            }

            if (customerMap) {
                clearOnSiteLayers();
                clearPickupDeliveryLayers();
                try { customerMap.remove(); } catch(e){}
                customerMap = null;
            }

            if (mapContainer._leaflet_id) {
                mapContainer._leaflet_id = null;
            }
            
            const defaultLocation = [-7.7956, 110.3695]; // Yogyakarta
            const serviceType = getCurrentServiceType();
            const existingLat = parseFloat(document.getElementById('latitude-input')?.value) || null;
            const existingLng = parseFloat(document.getElementById('longitude-input')?.value) || null;
            const pLat = parseFloat(document.getElementById('pickup-latitude-input')?.value) || existingLat;
            const pLng = parseFloat(document.getElementById('pickup-longitude-input')?.value) || existingLng;
            const dLat = parseFloat(document.getElementById('delivery-latitude-input')?.value) || null;
            const dLng = parseFloat(document.getElementById('delivery-longitude-input')?.value) || null;

            const centerLat = pLat || existingLat || defaultLocation[0];
            const centerLng = pLng || existingLng || defaultLocation[1];

            try {
                customerMap = L.map(mapContainer, {
                    center: [centerLat, centerLng],
                    zoom: (centerLat && centerLng) ? 14 : 13,
                    scrollWheelZoom: true,
                    zoomControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(customerMap);
                
                mapResizeTimer = setTimeout(() => {
                    safeInvalidateSize(customerMap, mapContainer);
                }, 250);

                if (serviceType === 'pickup_delivery') {
                    if (pLat && pLng) {
                        pickupMarker = L.marker([pLat, pLng], { icon: getPickupIcon(), draggable: true }).addTo(customerMap);
                        pickupMarker.on('dragstart', function() {
                            window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                        });
                        pickupMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updatePickupCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    if (dLat && dLng) {
                        deliveryMarker = L.marker([dLat, dLng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
                        deliveryMarker.on('dragstart', function() {
                            window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                        });
                        deliveryMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateDeliveryCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    if (pickupMarker && deliveryMarker) {
                        try {
                            const bounds = L.latLngBounds([pickupMarker.getLatLng(), deliveryMarker.getLatLng()]);
                            customerMap.fitBounds(bounds, { padding: [45, 45], maxZoom: 16 });
                        } catch(e){}
                    }
                    scheduleRoutePolylineUpdate();
                } else {
                    if (existingLat && existingLng) {
                        customerMarker = L.marker([existingLat, existingLng], { icon: getOnSiteIcon(), draggable: true }).addTo(customerMap);
                        customerMarker.on('dragstart', function() {
                            window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                        });
                        customerMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateCoordinates(pos.lat, pos.lng, false);
                        });
                        updateCoordinates(existingLat, existingLng, false);
                    }
                }

                customerMap.on('click', function(e) {
                    window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    const currentType = getCurrentServiceType();

                    // Check bounds bounding box Indonesia
                    if (lat > 6.5 || lat < -11.5 || lng < 94.5 || lng > 141.5) {
                        window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                            detail: { reason: 'Titik yang diklik berada di luar batas wilayah Republik Indonesia.' }
                        }));
                        return;
                    }

                    if (currentType === 'pickup_delivery') {
                        clearOnSiteLayers();
                        const activeMode = window.activeMapPoint || 'pickup';
                        if (activeMode === 'pickup') {
                            if (pickupMarker) {
                                pickupMarker.setLatLng([lat, lng]);
                            } else {
                                pickupMarker = L.marker([lat, lng], { icon: getPickupIcon(), draggable: true }).addTo(customerMap);
                                pickupMarker.on('dragstart', function() {
                                    window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                                });
                                pickupMarker.on('dragend', function(evt) {
                                    const pos = evt.target.getLatLng();
                                    updatePickupCoordinates(pos.lat, pos.lng, false);
                                });
                            }
                            updatePickupCoordinates(lat, lng, false);
                            
                            // Auto-advance ke Titik 2 jika Titik 2 belum ditentukan
                            if (!deliveryMarker) {
                                setActiveMapPoint('delivery', false);
                            }
                        } else {
                            if (deliveryMarker) {
                                deliveryMarker.setLatLng([lat, lng]);
                            } else {
                                deliveryMarker = L.marker([lat, lng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
                                deliveryMarker.on('dragstart', function() {
                                    window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                                });
                                deliveryMarker.on('dragend', function(evt) {
                                    const pos = evt.target.getLatLng();
                                    updateDeliveryCoordinates(pos.lat, pos.lng, false);
                                });
                            }
                            updateDeliveryCoordinates(lat, lng, false);
                        }
                    } else {
                        clearPickupDeliveryLayers();
                        if (customerMarker) {
                            customerMarker.setLatLng([lat, lng]);
                        } else {
                            customerMarker = L.marker([lat, lng], { icon: getOnSiteIcon(), draggable: true }).addTo(customerMap);
                            customerMarker.on('dragstart', function() {
                                window.dispatchEvent(new CustomEvent('cancel-active-searches'));
                            });
                            customerMarker.on('dragend', function(evt) {
                                const pos = evt.target.getLatLng();
                                updateCoordinates(pos.lat, pos.lng, false);
                            });
                        }
                        updateCoordinates(lat, lng, false);
                    }
                });
            } catch (err) {
                console.error('Error initializing customer map:', err);
            }
        }

        document.addEventListener('livewire:navigating', () => {
            if (mapResizeTimer) {
                clearTimeout(mapResizeTimer);
                mapResizeTimer = null;
            }
            if (routeDebounceTimer) {
                clearTimeout(routeDebounceTimer);
                routeDebounceTimer = null;
            }
            if (customerMap) {
                clearOnSiteLayers();
                clearPickupDeliveryLayers();
                try { customerMap.remove(); } catch(e){}
                customerMap = null;
            }
            const mapEl = document.getElementById('map');
            if (mapEl && mapEl._leaflet_id) {
                mapEl._leaflet_id = null;
            }
        });

        window.addEventListener('city-selected', (e) => {
            const map = customerMap;
            if (e.detail && e.detail.cityName && map) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(e.detail.cityName + ', Indonesia')}&limit=1`)
                    .then(r => r.json())
                    .then(results => {
                        if (results && results.length > 0) {
                            const cLat = parseFloat(results[0].lat);
                            const cLng = parseFloat(results[0].lon);
                            map.setView([cLat, cLng], 13);
                        }
                    }).catch(() => {});
            }
        });

        window.scrollToFirstError = function() {
            const findAndScroll = () => {
                // Cari semua elemen penanda error, abaikan floating banner atas
                const errorElements = Array.from(document.querySelectorAll(
                    '.field-error-message, ' +
                    '#group-title .field-error-message, #title-input.border-red-500, ' +
                    'input.border-red-500, textarea.border-red-500, select.border-red-500, ' +
                    '[class*="border-red-500"], [class*="ring-red-500"], ' +
                    '#group-map .border-red-500'
                )).filter(el => !el.closest('#error-banner-top'));

                if (!errorElements || errorElements.length === 0) {
                    return false;
                }

                // Ambil error pertama sesuai urutan vertikal dokumen
                let firstEl = null;
                let minTop = Infinity;
                for (const el of errorElements) {
                    const rect = el.getBoundingClientRect();
                    const docY = rect.top + window.pageYOffset;
                    if (docY < minTop) {
                        minTop = docY;
                        firstEl = el;
                    }
                }

                if (!firstEl) firstEl = errorElements[0];

                // Arahkan ke LABEL error yang bersangkutan sesuai permintaan pengguna
                let targetLabel = null;
                const groupContainer = firstEl.closest(
                    '#group-title, #group-amount, #group-map, #group-pickup-address, #group-delivery-address, ' +
                    '#group-onsite-location, #group-location, #group-full-address, #group-schedule, ' +
                    '#group-description, #group-photo, [id^="group-"], .space-y-3, .space-y-2, .space-y-4'
                );

                if (groupContainer) {
                    targetLabel = groupContainer.querySelector('label');
                }

                if (!targetLabel) {
                    let curr = firstEl;
                    while (curr && curr !== document.body && !targetLabel) {
                        if (curr.tagName === 'LABEL') {
                            targetLabel = curr;
                            break;
                        }
                        targetLabel = curr.querySelector && curr.querySelector('label');
                        if (!targetLabel && curr.previousElementSibling && curr.previousElementSibling.tagName === 'LABEL') {
                            targetLabel = curr.previousElementSibling;
                        }
                        curr = curr.parentElement;
                    }
                }

                // Target scroll adalah label error (atau elemen error sebagai fallback)
                const scrollTarget = targetLabel || firstEl;
                const yOffset = -90; // Jarak nyaman di bawah header atas
                const targetY = scrollTarget.getBoundingClientRect().top + window.pageYOffset + yOffset;

                window.scrollTo({
                    top: Math.max(0, targetY),
                    behavior: 'smooth'
                });

                // Berikan animasi penekanan visual pada label error
                if (targetLabel) {
                    targetLabel.classList.add('text-red-600', 'dark:text-red-400', 'transition-all');
                    targetLabel.style.transition = 'transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.25s ease';
                    targetLabel.style.transform = 'scale(1.03)';
                    setTimeout(() => {
                        targetLabel.style.transform = 'scale(1)';
                    }, 350);
                    setTimeout(() => {
                        targetLabel.classList.remove('text-red-600', 'dark:text-red-400');
                        targetLabel.style.transition = '';
                        targetLabel.style.transform = '';
                    }, 3500);
                }

                // Fokus pada input/textarea terkait dan beri highlight ring
                const parentForInput = groupContainer || (targetLabel ? targetLabel.parentElement : null) || firstEl.parentElement;
                if (parentForInput) {
                    const input = parentForInput.querySelector('input:not([type="hidden"]), textarea, select');
                    if (input && typeof input.focus === 'function') {
                        input.focus({ preventScroll: true });
                        input.classList.add('ring-4', 'ring-red-400/60', 'transition-all');
                        setTimeout(() => {
                            input.classList.remove('ring-4', 'ring-red-400/60');
                        }, 3500);
                    }
                }

                return true;
            };

            // Jalankan segera dan beberapa kali jeda singkat agar sinkron dengan morphing DOM Livewire
            findAndScroll();
            setTimeout(findAndScroll, 60);
            setTimeout(findAndScroll, 180);
            setTimeout(findAndScroll, 350);
        };

        window.addEventListener('scroll-to-first-error', () => {
            if (window.scrollToFirstError) window.scrollToFirstError();
        });
        document.addEventListener('scroll-to-first-error', () => {
            if (window.scrollToFirstError) window.scrollToFirstError();
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.on('scroll-to-first-error', () => {
                if (window.scrollToFirstError) window.scrollToFirstError();
            });
        }
    })();

    // Listeners untuk integrasi Livewire lifecycle
    document.addEventListener('livewire:initialized', () => {
        if (typeof Livewire !== 'undefined') {
            Livewire.on('scroll-to-first-error', () => {
                if (window.scrollToFirstError) window.scrollToFirstError();
            });
            if (Livewire.hook) {
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                        setTimeout(() => {
                            const hasErrors = document.querySelector('.field-error-message, input.border-red-500, textarea.border-red-500, [class*="border-red-500"]');
                            if (hasErrors && window.scrollToFirstError) {
                                window.scrollToFirstError();
                            }
                        }, 80);
                    });
                });
            }
        }
    });
</script>
