<div id="help-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
    <div id="help-modal-overlay" class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="relative w-full max-w-xl mx-4 bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh]">
        <div class="h-full overflow-y-auto">
            <div class="p-4 border-b flex items-start gap-3">
                <div id="help-modal-photo-wrap" class="w-16 h-16 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100">
                    <img id="help-modal-photo" src="" alt="Foto bantuan" class="w-full h-full object-cover hidden">
                    <div id="help-modal-initial"
                        class="w-full h-full flex items-center justify-center text-white text-lg font-bold bg-gradient-to-br from-orange-300 to-orange-400">
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 id="help-modal-title" class="text-lg font-semibold text-gray-900">-</h3>
                    <div id="help-modal-meta" class="text-sm text-gray-500">-</div>
                </div>

                <button id="help-modal-close" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-3">
                <div id="help-modal-description" class="text-sm text-gray-700">-</div>

                <div class="flex items-center gap-3">
                    <span id="help-modal-amount"
                        class="text-xs px-2 py-1 rounded-md bg-green-50 text-green-700 font-semibold">-</span>

                    <div class="text-xs text-gray-500 flex items-center gap-1">
                        <svg class="w-3 h-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                        </svg>
                        <span id="help-modal-city">-</span>
                    </div>

                    <div id="help-modal-category" class="ml-auto text-xs text-gray-500">-</div>
                </div>

                <div id="help-modal-location" class="text-xs text-gray-500">-</div>

                <div class="text-xs text-gray-500 mt-2">📅 <span id="help-modal-scheduled">-</span></div>

                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div class="text-xs text-gray-600">
                        <div class="font-semibold text-gray-800">Status</div>
                        <div id="help-modal-status" class="mt-1">-</div>
                    </div>

                    <div class="text-xs text-gray-600">
                        <div class="font-semibold text-gray-800">Mitra</div>
                        <div id="help-modal-mitra" class="mt-1">-</div>
                    </div>
                </div>

                <div class="pt-2 border-t">
                    <div class="flex items-center justify-between">
                        <div id="help-modal-meta2" class="text-sm text-gray-600">-</div>
                        <div id="help-modal-ratings" class="text-sm text-gray-600">-</div>
                    </div>
                </div>

                <div class="pt-3">
                    <a id="help-modal-detail-link" href="#"
                        class="block w-full text-center bg-primary-500 text-white px-4 py-2 rounded-lg">Lihat Halaman
                        Bantuan</a>
                </div>
                <!-- Inline rating component mount point (for mitra to rate customer inside this modal) -->
                <div id="help-modal-rate-wrap" class="mt-3 px-0">
                    @livewire('mitra.rate-customer', ['helpId' => null, 'inline' => true], key('help-modal-rate'))
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('help-modal');
            const overlay = document.getElementById('help-modal-overlay');
            const closeBtn = document.getElementById('help-modal-close');

            function hide() {
                modal.classList.add('hidden');
            }

            function show() {
                modal.classList.remove('hidden');
            }

            function formatCurrency(v) {
                try {
                    return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
                } catch (e) {
                    return v;
                }
            }

            let _currentHelpId = null;

            function fill(help) {
                if (!help) return;

                // Title & meta
                document.getElementById('help-modal-title').textContent = help.title || '-';
                const user = (help.user && help.user.name) ? help.user.name : (help.user && help.user.name) || 'Unknown';
                const created = help.created_at ? new Date(help.created_at).toLocaleString() : '';
                document.getElementById('help-modal-meta').textContent = (user ? user + ' • ' : '') + (help.created_at ? timeAgo(help.created_at) : '');
                document.getElementById('help-modal-meta2').textContent = 'ID: ' + (help.id ?? '-') + (help.created_at ? ' • Dibuat: ' + (new Date(help.created_at).toLocaleString()) : '');

                // Photo or initial
                const photo = document.getElementById('help-modal-photo');
                const initial = document.getElementById('help-modal-initial');
                if (help.photo) {
                    photo.src = '/storage/' + help.photo;
                    photo.classList.remove('hidden');
                    initial.classList.add('hidden');
                } else if (help.user && help.user.photo) {
                    photo.src = '/storage/' + help.user.photo;
                    photo.classList.remove('hidden');
                    initial.classList.add('hidden');
                } else {
                    photo.classList.add('hidden');
                    initial.classList.remove('hidden');
                    const letter = (help.user && help.user.name) ? help.user.name.charAt(0).toUpperCase() : 'U';
                    initial.textContent = letter;
                }

                // Description
                document.getElementById('help-modal-description').textContent = help.description || '-';

                // Amount
                document.getElementById('help-modal-amount').textContent = formatCurrency(help.amount || help.estimated_price || 0);

                // City
                document.getElementById('help-modal-city').textContent = (help.city && help.city.name) ? help.city.name : '-';

                // Category
                document.getElementById('help-modal-category').textContent = (help.category && help.category.name) ? help.category.name : '';

                // Location
                document.getElementById('help-modal-location').textContent = help.location ? ('Lokasi: ' + help.location) : '';

                // Scheduled date/time (if provided)
                try {
                    const schedEl = document.getElementById('help-modal-scheduled');
                    if (schedEl) {
                        if (help.scheduled_at) {
                            const d = new Date(help.scheduled_at);
                            const formatted = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(d);
                            schedEl.textContent = formatted;
                        } else {
                            schedEl.textContent = '-';
                        }
                    }
                } catch (e) { console.warn('failed to set scheduled datetime', e); }

                // Status & mitra
                document.getElementById('help-modal-status').textContent = help.status || '-';
                document.getElementById('help-modal-mitra').textContent = (help.mitra && help.mitra.name) ? help.mitra.name : 'Belum ada';

                // Ratings
                document.getElementById('help-modal-ratings').textContent = 'Ulasan: ' + ((help.ratings && help.ratings.length) ? help.ratings.length : 0);

                // remember current help id for browser event handling
                _currentHelpId = help.id || null;

                // Detail link
                if (help.id) {
                    document.getElementById('help-modal-detail-link').href = '/mitra/help/' + help.id;
                }

                // If a Livewire inline rating component is present, tell it to load this help
                try {
                    if (window.Livewire && typeof window.Livewire.emitTo === 'function') {
                        // component name is 'mitra.rate-customer' and key is 'help-modal-rate'
                        window.Livewire.emitTo('mitra.rate-customer', 'loadHelp', help.id);
                    }
                } catch (e) {
                    console.warn('Failed to emit loadHelp to mitra.rate-customer', e);
                }

                show();
            }

            function timeAgo(iso) {
                try {
                    const d = new Date(iso);
                    const seconds = Math.floor((new Date() - d) / 1000);
                    const intervals = {
                        year: 31536000,
                        month: 2592000,
                        week: 604800,
                        day: 86400,
                        hour: 3600,
                        minute: 60,
                        second: 1
                    };
                    for (const i in intervals) {
                        const interval = Math.floor(seconds / intervals[i]);
                        if (interval >= 1) {
                            return interval + ' ' + i + (interval > 1 ? 's' : '') + ' yang lalu';
                        }
                    }
                } catch (e) { }
                return '';
            }

            // Listen for a DOM CustomEvent
            window.addEventListener('open-help-modal', function (e) {
                fill((e && e.detail && e.detail.help) ? e.detail.help : null);
            });

            // Livewire on() fallback
            if (window.livewire && typeof window.livewire.on === 'function') {
                window.livewire.on('open-help-modal', function (help) {
                    fill(help);
                });
            }

            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('open-help-modal', function (help) {
                    fill(help);
                });
            }

            // Update rating count when Livewire dispatches a browser event after a rating is submitted
            window.addEventListener('helpRatingUpdated', function (e) {
                try {
                    const detail = (e && e.detail) ? e.detail : e;
                    if (!detail) return;
                    const helpId = detail.helpId || detail.help_id || null;
                    const count = typeof detail.ratings_count !== 'undefined' ? detail.ratings_count : (detail.ratingsCount || null);
                    if (_currentHelpId && helpId && String(_currentHelpId) === String(helpId)) {
                        if (count !== null) {
                            const el = document.getElementById('help-modal-ratings');
                            if (el) el.textContent = 'Ulasan: ' + count;
                        }
                    }
                } catch (err) { console.warn('helpRatingUpdated handler error', err); }
            });

            // Livewire JS fallback (some versions use livewire.on)
            if (window.livewire && typeof window.livewire.on === 'function') {
                window.livewire.on('helpRatingUpdated', function (data) {
                    try {
                        const helpId = data.helpId || data.help_id || null;
                        const count = data.ratings_count || data.ratingsCount || null;
                        if (_currentHelpId && helpId && String(_currentHelpId) === String(helpId)) {
                            if (count !== null) {
                                const el = document.getElementById('help-modal-ratings');
                                if (el) el.textContent = 'Ulasan: ' + count;
                            }
                        }
                    } catch (err) { console.warn(err); }
                });
            }

            // Close handlers
            overlay.addEventListener('click', hide);
            closeBtn.addEventListener('click', hide);
        })();
    </script>
</div>