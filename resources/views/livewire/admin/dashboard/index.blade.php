<div class="space-y-6">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ringkasan aktivitas dan operasional moderasi di wilayah Anda</p>
        </div>
        @if(auth()->user() && (auth()->user()->city_name || auth()->user()->city_id || auth()->user()->city))
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                    {{ auth()->user()->city_name ?? (is_object(auth()->user()->city) ? auth()->user()->city->name : auth()->user()->city) }}
                </span>
            </div>
        @endif
    </div>

    {{-- ===== Top Stat Cards Grid ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2.5 sm:gap-3">
        {{-- Total Bantuan --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 16V8a2 2 0 00-2-2h-3l-2-2H10L8 6H5a2 2 0 00-2 2v8"/><rect x="3" y="8" width="18" height="10" rx="2" ry="2" fill="none"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total Bantuan</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ number_format($totalHelps) }}</p>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Pending</p>
                <p class="text-base sm:text-lg font-bold text-amber-600 dark:text-amber-400 truncate">{{ number_format($pendingHelps) }}</p>
            </div>
        </div>

        {{-- Aktif --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Aktif</p>
                <p class="text-base sm:text-lg font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ number_format($activeHelps) }}</p>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Selesai</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ number_format($completedHelps) }}</p>
            </div>
        </div>

        {{-- KTP Pending --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">KTP Pending</p>
                <p class="text-base sm:text-lg font-bold text-orange-600 dark:text-orange-400 truncate">{{ number_format($pendingVerifications) }}</p>
            </div>
        </div>

        {{-- Mitra Terverifikasi --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3.5 sm:p-4 flex items-center gap-3 transition-colors min-w-0">
            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/40 hidden sm:flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Mitra Terverifikasi</p>
                <p class="text-base sm:text-lg font-bold text-teal-600 dark:text-teal-400 truncate">{{ number_format($verifiedMitras) }}</p>
            </div>
        </div>
    </div>

    {{-- Pending Alerts Grid --}}
    @if((isset($pendingTopups) && $pendingTopups > 0) || (isset($pendingWithdraws) && $pendingWithdraws > 0))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @if(isset($pendingTopups) && $pendingTopups > 0)
        <a href="{{ route('admin.topup.approvals') }}" class="block group">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-center justify-between gap-4 group-hover:bg-amber-100/70 dark:group-hover:bg-amber-900/30 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-700 dark:text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200">{{ $pendingTopups }} Request Top-Up Menunggu Approval</h3>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">Verifikasi bukti transfer top-up saldo customer.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-800 dark:text-amber-200 group-hover:translate-x-1 transition-transform">
                    Proses <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endif

        @if(isset($pendingWithdraws) && $pendingWithdraws > 0)
        <a href="{{ route('admin.withdraws.index') }}" class="block group">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-center justify-between gap-4 group-hover:bg-blue-100/70 dark:group-hover:bg-blue-900/30 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-800/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200">{{ $pendingWithdraws }} Tarik Saldo Menunggu Transfer</h3>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">Ada pengajuan penarikan dana mitra yang perlu diproses.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-800 dark:text-blue-200 group-hover:translate-x-1 transition-transform">
                    Proses <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endif
    </div>
    @endif

    {{-- ===== Chart + Summary Cards ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Activity Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Aktivitas Permintaan Bantuan</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">7 hari terakhir</p>
                </div>
                <span class="text-xs text-primary-600 dark:text-primary-400 font-medium bg-primary-50 dark:bg-primary-900/30 px-2.5 py-1 rounded-md">Live Data</span>
            </div>
            <div class="h-64">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        {{-- Ringkasan Operasional --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Ringkasan Operasional</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Bantuan</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($totalHelps) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50/50 dark:bg-amber-900/20">
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Pending Moderasi</span>
                        <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ number_format($pendingHelps) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/20">
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Sedang Aktif</span>
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($activeHelps) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-orange-50/50 dark:bg-orange-900/20">
                        <span class="text-xs font-medium text-orange-700 dark:text-orange-400">KTP Menunggu Verifikasi</span>
                        <span class="text-sm font-bold text-orange-700 dark:text-orange-400">{{ number_format($pendingVerifications) }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <span class="text-xs text-gray-400 dark:text-gray-500">Mitra Terverifikasi</span>
                <span class="text-xs font-semibold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2.5 py-1 rounded-md">{{ number_format($verifiedMitras) }} Mitra</span>
            </div>
        </div>
    </div>

    {{-- ===== Permintaan Bantuan Terbaru ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Permintaan Bantuan Terbaru</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Daftar bantuan terbaru di wilayah Anda</p>
            </div>
            <a href="{{ route('admin.helps') }}" wire:navigate class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center gap-1">
                Lihat semua <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if(isset($latestHelps) && $latestHelps->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($latestHelps as $help)
                        @php
                        $stClass = match($help->status ?? '') {
                            'pending', 'menunggu_mitra' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            'active', 'taken', 'memperoleh_mitra', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'sedang_diproses' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            'completed', 'selesai' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            'cancelled', 'dibatalkan', 'rejected' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">#{{ $help->order_id ?? $help->id }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr(optional($help->user)->name ?? (optional($help->customer)->name ?? 'U'), 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800 dark:text-gray-100">{{ optional($help->user)->name ?? (optional($help->customer)->name ?? '—') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">
                                    {{ ucfirst($help->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-800 dark:text-gray-100">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                {{ $help->created_at ? $help->created_at->diffForHumans() : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada permintaan bantuan terbaru</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Chart.js Script with Dark Mode Awareness --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            let chart = null;

            function waitForChart(callback, maxAttempts = 50) {
                if (typeof Chart !== 'undefined') {
                    callback();
                    return;
                }
                let attempts = 0;
                const timer = setInterval(() => {
                    attempts++;
                    if (typeof Chart !== 'undefined') {
                        clearInterval(timer);
                        callback();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(timer);
                    }
                }, 60);
            }

            function initAdminChart() {
                const ctx = document.getElementById('activityChart');
                if (!ctx) return;

                const isDark = document.documentElement.classList.contains('dark');
                const data = {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Permintaan Bantuan',
                        backgroundColor: 'rgba(14,165,233,0.1)',
                        borderColor: '#0ea5e9',
                        pointBackgroundColor: '#0ea5e9',
                        data: @json($chartData),
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5
                    }]
                };

                if (chart) chart.destroy();
                chart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: isDark ? '#9ca3af' : '#6b7280', font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' },
                                ticks: { color: isDark ? '#9ca3af' : '#6b7280', precision: 0, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            function safeInit() {
                waitForChart(initAdminChart);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', safeInit);
            } else {
                safeInit();
            }
            document.addEventListener('livewire:navigated', safeInit);

            window.addEventListener('theme-changed', function(e) {
                const dark = e.detail?.isDark ?? document.documentElement.classList.contains('dark');
                if (chart && chart.options && chart.options.scales) {
                    chart.options.scales.x.ticks.color = dark ? '#9ca3af' : '#6b7280';
                    chart.options.scales.y.ticks.color = dark ? '#9ca3af' : '#6b7280';
                    chart.options.scales.y.grid.color = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                    chart.update();
                }
            });
        })();
    </script>
</div>
