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
        let geocodeAbortController = null;
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
                if (typeof @this !== 'undefined' && @this) return @this;
            } catch(e) {}
            try {
                const root = document.getElementById('map')?.closest('[wire\\:id]');
                if (root && window.Livewire) {
                    const id = root.getAttribute('wire:id');
                    if (id) return window.Livewire.find(id);
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

                        if (routePolyline) {
                            try { customerMap.removeLayer(routePolyline); } catch(e){}
                        }

                        routePolyline = L.polyline(latLngs, {
                            color: '#2563eb',
                            weight: 5,
                            opacity: 0.85,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(customerMap);

                        const roadDistKm = parseFloat((route.distance / 1000).toFixed(2));

                        const lw = getLivewire();
                        if (lw && typeof lw.call === 'function') {
                            lw.call('updateRouteDistanceRoad', roadDistKm);
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
                if (routePolyline) {
                    try { customerMap.removeLayer(routePolyline); } catch(e){}
                }
                const straightLatLngs = [pLatLng, dLatLng];
                routePolyline = L.polyline(straightLatLngs, {
                    color: '#2563eb',
                    weight: 4,
                    dashArray: '6, 8',
                    opacity: 0.85
                }).addTo(customerMap);

                const meters = pLatLng.distanceTo(dLatLng);
                const roadDistKm = parseFloat(((meters / 1000) * 1.30).toFixed(2));
                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('updateRouteDistanceRoad', roadDistKm);
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

        function selectMapLocation(lat, lng, displayName = '') {
            const serviceType = getCurrentServiceType();

            if (lat > 6.5 || lat < -11.5 || lng < 94.5 || lng > 141.5) {
                window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                    detail: { reason: 'Lokasi yang dipilih berada di luar batas wilayah Republik Indonesia.' }
                }));
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
                        updatePickupCoordinates(lat, lng, false, displayName);
                        if (!deliveryMarker) {
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
                }
            }
        }
        window.selectMapLocation = selectMapLocation;

        function updateCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = parseFloat(lat).toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = parseFloat(lng).toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS Realtime' : 'Titik Peta';
                pill.className = isGPS 
                    ? 'px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold'
                    : 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold';
            }

            const latInput = document.getElementById('latitude-input');
            const lngInput = document.getElementById('longitude-input');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (geocodeAbortController) {
                geocodeAbortController.abort();
            }
            geocodeAbortController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: geocodeAbortController.signal,
                headers: { 'Accept-Language': 'id' }
            })
            .then(r => r.json())
            .then(data => {
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');

                // Validasi Zona Terlarang / Perairan
                const safety = isRestrictedOsmLocation(data, lat, lng);
                if (safety.isRestricted) {
                    clearOnSiteLayers();
                    if (latInput) latInput.value = '';
                    if (lngInput) lngInput.value = '';
                    const locInput = document.getElementById('location-input');
                    if (locInput) locInput.value = '';
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
                    const fullAddress = data.display_name || fallbackAddr || '';

                    const locInput = document.getElementById('location-input');
                    if (locInput && fullAddress) locInput.value = fullAddress;

                    const lw = getLivewire();
                    if (lw && typeof lw.call === 'function') {
                        lw.call('syncOnSiteLocation', lat, lng, fullAddress, cityName, districtName, provinceName);
                    }
                }
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                console.warn('Reverse geocode error:', err);
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                
                const locInput = document.getElementById('location-input');
                if (locInput && fallbackAddr) locInput.value = fallbackAddr;

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncOnSiteLocation', lat, lng, fallbackAddr);
                }
            });
        }

        function updatePickupCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = parseFloat(lat).toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = parseFloat(lng).toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS (Titik 1 Jemput)' : 'Titik 1 Jemput';
                pill.className = 'px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold';
            }

            const pLatInput = document.getElementById('pickup-latitude-input');
            const pLngInput = document.getElementById('pickup-longitude-input');
            const latInput  = document.getElementById('latitude-input');
            const lngInput  = document.getElementById('longitude-input');
            if (pLatInput) pLatInput.value = lat;
            if (pLngInput) pLngInput.value = lng;
            if (latInput)  latInput.value  = lat;
            if (lngInput)  lngInput.value  = lng;

            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (geocodeAbortController) {
                geocodeAbortController.abort();
            }
            geocodeAbortController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: geocodeAbortController.signal,
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
                    if (pLatInput) pLatInput.value = '';
                    if (pLngInput) pLngInput.value = '';
                    const pAddrInput = document.getElementById('pickup-address-input');
                    if (pAddrInput) pAddrInput.value = '';
                    if (displayEl) displayEl.classList.add('hidden');

                    window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                        detail: { reason: safety.reason }
                    }));
                    return;
                }

                const fullAddress = data?.display_name || fallbackAddr || '';
                const addr = data?.address || {};
                const cityName = addr.city || addr.town || addr.county || addr.city_district || '';
                const districtName = addr.municipality || addr.city_district || addr.suburb || addr.district || addr.quarter || addr.village || '';
                const provinceName = addr.state || addr.province || '';

                const pAddrInput = document.getElementById('pickup-address-input');
                const locInput   = document.getElementById('location-input');
                if (pAddrInput && fullAddress) pAddrInput.value = fullAddress;
                if (locInput && fullAddress)   locInput.value   = fullAddress;

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncPickupLocation', lat, lng, fullAddress, cityName, districtName, provinceName);
                }
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                
                const pAddrInput = document.getElementById('pickup-address-input');
                if (pAddrInput && fallbackAddr) pAddrInput.value = fallbackAddr;

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncPickupLocation', lat, lng, fallbackAddr);
                }
            });

            scheduleRoutePolylineUpdate();
        }

        function updateDeliveryCoordinates(lat, lng, isGPS = false, fallbackAddr = '') {
            const displayEl = document.getElementById('coordinates-display');
            if (displayEl) displayEl.classList.remove('hidden');

            const latEl = document.getElementById('lat-display');
            if (latEl) latEl.textContent = parseFloat(lat).toFixed(6);

            const lngEl = document.getElementById('lng-display');
            if (lngEl) lngEl.textContent = parseFloat(lng).toFixed(6);

            const pill = document.getElementById('gps-status-pill');
            if (pill) {
                pill.textContent = isGPS ? 'GPS (Titik 2 Antar)' : 'Titik 2 Antar';
                pill.className = 'px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold';
            }

            const dLatInput = document.getElementById('delivery-latitude-input');
            const dLngInput = document.getElementById('delivery-longitude-input');
            if (dLatInput) dLatInput.value = lat;
            if (dLngInput) dLngInput.value = lng;

            const geocodeIndicator = document.getElementById('reverse-geocode-indicator');
            if (geocodeIndicator) geocodeIndicator.classList.remove('hidden');

            if (geocodeAbortController) {
                geocodeAbortController.abort();
            }
            geocodeAbortController = new AbortController();

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                signal: geocodeAbortController.signal,
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
                    if (dLatInput) dLatInput.value = '';
                    if (dLngInput) dLngInput.value = '';
                    const dAddrInput = document.getElementById('delivery-address-input');
                    if (dAddrInput) dAddrInput.value = '';
                    if (displayEl) displayEl.classList.add('hidden');

                    window.dispatchEvent(new CustomEvent('restricted-location-detected', {
                        detail: { reason: safety.reason }
                    }));
                    return;
                }

                const fullAddress = data?.display_name || fallbackAddr || '';

                const dAddrInput = document.getElementById('delivery-address-input');
                if (dAddrInput && fullAddress) dAddrInput.value = fullAddress;

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncDeliveryLocation', lat, lng, fullAddress);
                }
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                if (geocodeIndicator) geocodeIndicator.classList.add('hidden');
                
                const dAddrInput = document.getElementById('delivery-address-input');
                if (dAddrInput && fallbackAddr) dAddrInput.value = fallbackAddr;

                const lw = getLivewire();
                if (lw && typeof lw.call === 'function') {
                    lw.call('syncDeliveryLocation', lat, lng, fallbackAddr);
                }
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
                        pickupMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updatePickupCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    if (dLat && dLng) {
                        deliveryMarker = L.marker([dLat, dLng], { icon: getDeliveryIcon(), draggable: true }).addTo(customerMap);
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
                        customerMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            updateCoordinates(pos.lat, pos.lng, false);
                        });
                        updateCoordinates(existingLat, existingLng, false);
                    }
                }

                customerMap.on('click', function(e) {
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

        function scrollToFirstError() {
            setTimeout(() => {
                const errorEl = document.querySelector('.field-error-message, #group-title .field-error-message, #title-input.border-red-500, input.border-red-500, textarea.border-red-500');
                if (errorEl) {
                    const yOffset = -140;
                    const y = errorEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });

                    const parentGroup = errorEl.closest('div[id^="group-"]') || errorEl.closest('div');
                    if (parentGroup) {
                        const input = parentGroup.querySelector('input:not([type="hidden"]), textarea, select');
                        if (input) {
                            input.focus();
                            input.classList.add('ring-4', 'ring-red-400', 'transition-all');
                            setTimeout(() => input.classList.remove('ring-4', 'ring-red-400'), 3500);
                        }
                    }
                }
            }, 80);
        }

        window.addEventListener('scroll-to-first-error', scrollToFirstError);
    })();
</script>
