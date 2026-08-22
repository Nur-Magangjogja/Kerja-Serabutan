@php
    $title = 'Pengaturan';
    $breadcrumb = 'Super Admin / Pengaturan / Bantuan';
@endphp

<div class="py-2"
     x-data="{}" 
     x-on:settings-saved.window="
        $nextTick(() => {
            const el = document.getElementById('help-settings-alert') || document.getElementById('settings-form-start');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
     ">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav active="help" />

    <!-- Notifikasi Sukses / Alert Section -->
    @if(session()->has('message'))
        <div id="help-settings-alert" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl shadow-xs ring-2 ring-emerald-500/20">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-emerald-800 dark:text-emerald-300 font-semibold text-sm">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Admin Fee Revenue Chart Section -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Chart Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Pendapatan Biaya Admin</h2>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Grafik pendapatan dari biaya admin platform (tidak termasuk nominal transaksi)</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl px-4 py-2.5 border border-gray-200 dark:border-gray-700 shadow-sm sm:flex-shrink-0">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total 30 Hari</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">Rp
                            {{ number_format(collect($adminFeeChart['daily']['data'] ?? [])->sum(), 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">Hanya biaya admin</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6 sm:mb-8">
                    <!-- Total Admin Fee -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1">Total Biaya Admin</div>
                                <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">Rp
                                    {{ number_format($totalAll ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">Fee saja</div>
                            </div>
                        </div>
                    </div>

                    <!-- 30 Hari -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1">Biaya Admin 30 Hari</div>
                                <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">Rp
                                    {{ number_format($total30 ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">Fee saja</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulan Ini -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1">Biaya Admin Bulan Ini</div>
                                <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">Rp
                                    {{ number_format($totalMonth ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">Fee saja</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rata-rata -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1">Rata-rata Admin Fee</div>
                                <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($avgAdmin ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Per transaksi</div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $adminFeeChartJson = json_encode($adminFeeChart ?? ['daily' => ['labels' => [], 'data' => []], 'monthly' => ['labels' => [], 'data' => []], 'yearly' => ['labels' => [], 'data' => []]]);
                @endphp

                <!-- Alpine Chart Component -->
                <div x-data="{
                    currentRange: localStorage.getItem('superadmin.adminFeeChart.range') || 'daily',
                    chartData: {{ $adminFeeChartJson }},
                    chartInstance: null,
                    renderChart() {
                        const canvas = this.$refs.canvas;
                        if (!canvas) return;
                        const isDark = document.documentElement.classList.contains('dark');
                        const labels = this.chartData[this.currentRange]?.labels || [];
                        const data = this.chartData[this.currentRange]?.data || [];

                        const ctx = canvas.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                        if (isDark) {
                            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.85)');
                            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.3)');
                        } else {
                            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
                            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.4)');
                        }

                        if (this.chartInstance && this.chartInstance.canvas === canvas) {
                            this.chartInstance.data.labels = labels;
                            this.chartInstance.data.datasets[0].data = data;
                            this.chartInstance.data.datasets[0].backgroundColor = gradient;
                            this.chartInstance.data.datasets[0].borderColor = isDark ? 'rgba(96, 165, 250, 1)' : 'rgba(37, 99, 235, 1)';
                            if (this.chartInstance.options?.scales?.x?.ticks) {
                                this.chartInstance.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                            }
                            if (this.chartInstance.options?.scales?.y?.ticks) {
                                this.chartInstance.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                            }
                            if (this.chartInstance.options?.scales?.y?.grid) {
                                this.chartInstance.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(156, 163, 175, 0.15)';
                            }
                            this.chartInstance.update('none');
                            return;
                        }

                        if (typeof Chart === 'undefined') return;

                        if (this.chartInstance) {
                            try { this.chartInstance.destroy(); } catch(e) {}
                        }

                        this.chartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Pendapatan Biaya Admin',
                                    data: data,
                                    backgroundColor: gradient,
                                    borderColor: isDark ? 'rgba(96, 165, 250, 1)' : 'rgba(37, 99, 235, 1)',
                                    borderWidth: 1.5,
                                    borderRadius: 8,
                                    maxBarThickness: 36
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(15, 23, 42, 0.95)',
                                        padding: 12,
                                        titleColor: '#fff',
                                        bodyColor: '#e2e8f0',
                                        cornerRadius: 8,
                                        callbacks: {
                                            label: function(c) {
                                                const v = c.raw ?? c.parsed?.y ?? 0;
                                                return 'Rp ' + Number(v).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            autoSkip: true,
                                            maxRotation: 45,
                                            font: { size: 11, weight: '500' },
                                            color: isDark ? '#9ca3af' : '#6b7280'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); },
                                            font: { size: 11, weight: '500' },
                                            color: isDark ? '#9ca3af' : '#6b7280'
                                        },
                                        grid: {
                                            color: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(156, 163, 175, 0.15)'
                                        }
                                    }
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false
                            }
                        });
                    },
                    setRange(r) {
                        this.currentRange = r;
                        localStorage.setItem('superadmin.adminFeeChart.range', r);
                        this.renderChart();
                    }
                }"
                x-init="
                    $nextTick(() => { 
                        setTimeout(() => renderChart(), 20); 
                    });
                    window.addEventListener('theme-changed', () => { renderChart(); });
                "
                class="mb-6">

                    <!-- Chart Tabs -->
                    <div class="mb-4">
                        <div class="inline-flex bg-gray-100 dark:bg-gray-700/70 rounded-xl p-1 w-full sm:w-auto border border-gray-200/60 dark:border-gray-600/60 gap-1">
                            <button type="button" @click="setRange('daily')"
                                :class="currentRange === 'daily' 
                                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs' 
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                class="flex-1 sm:flex-none text-center px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                Harian
                            </button>
                            <button type="button" @click="setRange('monthly')"
                                :class="currentRange === 'monthly' 
                                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs' 
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                class="flex-1 sm:flex-none text-center px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                Bulanan
                            </button>
                            <button type="button" @click="setRange('yearly')"
                                :class="currentRange === 'yearly' 
                                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs' 
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                class="flex-1 sm:flex-none text-center px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                Tahunan
                            </button>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="w-full bg-gray-50 dark:bg-gray-900 rounded-2xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 h-64 min-h-[250px]" wire:ignore>
                        <canvas x-ref="canvas" id="adminFeeChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Breakdown by Source -->
                <div class="mt-8">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        Breakdown Sumber Biaya Admin
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Help (Bantuan) Breakdown -->
                        <div class="bg-blue-50/80 dark:bg-blue-950/30 rounded-2xl p-5 border border-blue-200 dark:border-blue-800/60">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white">Bantuan</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Fee dari pembuatan bantuan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2 pt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Total Fee:</span>
                                    <span class="text-lg font-bold text-blue-700 dark:text-blue-400">
                                        Rp {{ number_format($breakdown['help']['total'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Jumlah Transaksi:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($breakdown['help']['count'] ?? 0, 0, ',', '.') }} bantuan
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-blue-200/80 dark:border-blue-800/60">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Rata-rata Fee:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($breakdown['help']['avg'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-blue-200/80 dark:border-blue-800/60">
                                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                        <span>Kontribusi terhadap total:</span>
                                        <span class="font-bold text-blue-700 dark:text-blue-400">
                                            {{ $totalAll > 0 ? number_format(($breakdown['help']['total'] / $totalAll) * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top-up Breakdown -->
                        <div class="bg-emerald-50/80 dark:bg-emerald-950/30 rounded-2xl p-5 border border-emerald-200 dark:border-emerald-800/60">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white">Top-up Saldo</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Fee dari pengisian saldo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2 pt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Total Fee:</span>
                                    <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400">
                                        Rp {{ number_format($breakdown['topup']['total'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Jumlah Transaksi:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($breakdown['topup']['count'] ?? 0, 0, ',', '.') }} top-up
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-emerald-200/80 dark:border-emerald-800/60">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Rata-rata Fee:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($breakdown['topup']['avg'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-emerald-200/80 dark:border-emerald-800/60">
                                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                        <span>Kontribusi terhadap total:</span>
                                        <span class="font-bold text-emerald-700 dark:text-emerald-400">
                                            {{ $totalAll > 0 ? number_format(($breakdown['topup']['total'] / $totalAll) * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form Section (Combined Form) -->
    <div id="settings-form-start" class="scroll-mt-8"></div>
    <form wire:submit.prevent="save" class="space-y-8 mb-12">
        <!-- Settings flash hook for JS (used to detect Livewire updates) -->
        <div id="settingsFlash" data-message="{{ session('message') ?? '' }}" style="display:none"></div>

        <!-- 1. Konfigurasi Bantuan -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Konfigurasi Bantuan
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">Atur nominal minimal bantuan dan biaya admin per bantuan</p>
                </div>
            </div>

            <div class="p-4 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Nominal Minimal (Rp)</label>
                        <input type="number" wire:model.defer="min_help_nominal" placeholder="10000"
                            class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                        @error('min_help_nominal')
                            <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Customer tidak bisa membuat permintaan bantuan dengan nominal di bawah nilai ini.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Komisi Platform (%)</label>
                        <div class="relative">
                            <input type="number" wire:model.defer="platform_commission_rate" placeholder="10" min="0" max="100" step="0.5"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all pr-10" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">%</span>
                        </div>
                        @error('platform_commission_rate')
                            <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Persentase komisi yang dipotong dari nilai tugas saat pekerjaan selesai (Seller-Pays). Contoh: 10% dari tugas Rp 50.000 = Rp 5.000 kas platform, Rp 45.000 diterima mitra.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Konfigurasi Biaya Admin Top-Up -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Konfigurasi Biaya Admin Top-Up
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">Atur biaya admin berjenjang berdasarkan nominal top-up saldo (3 tier)</p>
            </div>

            <div class="p-4 sm:p-8 space-y-6">
                @if ($errors->hasAny(['tier1_limit', 'tier1_fee', 'tier2_limit', 'tier2_fee', 'tier3_percentage', 'tier3_max']))
                    <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 rounded-xl text-red-700 dark:text-red-300 text-sm flex items-start gap-3 shadow-xs">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-bold text-red-900 dark:text-red-100">Perhatian: Ada kolom konfigurasi biaya yang kosong atau tidak valid!</p>
                            <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">Silakan periksa kolom dengan garis merah di bawah dan pastikan semua kolom telah diisi angka yang sesuai.</p>
                        </div>
                    </div>
                @endif

                <!-- Tier 1 Settings -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 sm:p-6 bg-gray-50/80 dark:bg-gray-900/50">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
                        <span class="bg-primary-600 text-white w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shadow-xs">1</span>
                        Tier 1 - Nominal Kecil
                    </h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Batas Maksimal Tier 1 (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="tier1_limit" 
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier1_limit') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier1_limit')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Nominal top-up di bawah nilai ini menggunakan biaya tetap tier 1</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Biaya Admin Tier 1 (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="tier1_fee" 
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier1_fee') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier1_fee')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Biaya tetap untuk nominal di bawah Rp {{ number_format(is_numeric($tier1_limit) ? (float)$tier1_limit : 50000, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tier 2 Settings -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 sm:p-6 bg-gray-50/80 dark:bg-gray-900/50">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
                        <span class="bg-primary-600 text-white w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shadow-xs">2</span>
                        Tier 2 - Nominal Menengah
                    </h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Batas Maksimal Tier 2 (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="tier2_limit"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier2_limit') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier2_limit')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Nominal top-up di bawah nilai ini menggunakan biaya tetap tier 2</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Biaya Admin Tier 2 (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="tier2_fee" 
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier2_fee') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier2_fee')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Biaya tetap untuk nominal Rp {{ number_format(is_numeric($tier1_limit) ? (float)$tier1_limit : 50000, 0, ',', '.') }} - Rp {{ number_format(is_numeric($tier2_limit) ? (float)$tier2_limit : 100000, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tier 3 Settings -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 sm:p-6 bg-gray-50/80 dark:bg-gray-900/50">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
                        <span class="bg-primary-600 text-white w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shadow-xs">3</span>
                        Tier 3 - Nominal Besar (Persentase)
                    </h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Persentase Biaya Admin (%) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model.defer="tier3_percentage" placeholder="3"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier3_percentage') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier3_percentage')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Persentase dari nominal top-up (untuk nominal ≥ Rp {{ number_format(is_numeric($tier2_limit) ? (float)$tier2_limit : 100000, 0, ',', '.') }})</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Biaya Maksimal Tier 3 (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.defer="tier3_max" placeholder="15000"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border @error('tier3_max') border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 @enderror rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 transition-all" />
                            @error('tier3_max')
                                <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs font-medium">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Batas maksimal biaya admin untuk tier 3 (cap maksimal)</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods (Banks) -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 sm:p-6 bg-gray-50/80 dark:bg-gray-900/50 space-y-4">
                    <!-- Section Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-200/80 dark:border-gray-700/80">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Metode Pembayaran Top-Up (Transfer Bank)
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-900/40 text-primary-700 dark:text-primary-300 border border-blue-200 dark:border-blue-800/60">
                                    {{ count($payment_banks) }} Rekening
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Daftar rekening bank resmi platform sebagai tujuan transfer deposit saldo customer.</p>
                        </div>

                        <!-- Primary Add Bank Button -->
                        <button type="button" wire:click.prevent="addBank('', '')"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition shadow-xs self-start sm:self-auto cursor-pointer">
                            <svg wire:loading.remove wire:target="addBank" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <svg wire:loading wire:target="addBank" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>+ Tambah Rekening</span>
                        </button>
                    </div>

                    <!-- Quick Preset Chips -->
                    <div class="bg-white dark:bg-gray-800/80 rounded-xl p-3 border border-gray-200/80 dark:border-gray-700/80 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mr-1">⚡ Tambah Cepat:</span>
                        @foreach([
                            ['code' => 'bca', 'label' => '+ BCA'],
                            ['code' => 'mandiri', 'label' => '+ Mandiri'],
                            ['code' => 'bni', 'label' => '+ BNI'],
                            ['code' => 'bri', 'label' => '+ BRI'],
                            ['code' => 'bsi', 'label' => '+ BSI'],
                            ['code' => 'cimb', 'label' => '+ CIMB'],
                            ['code' => 'seabank', 'label' => '+ SeaBank'],
                            ['code' => 'jago', 'label' => '+ Jago'],
                        ] as $preset)
                            <button type="button" wire:click.prevent="addBank('{{ $preset['code'] }}')"
                                wire:loading.attr="disabled"
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-gray-100 hover:bg-primary-50 dark:bg-gray-750 dark:hover:bg-primary-950/40 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 transition cursor-pointer">
                                {{ $preset['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Bank Cards List -->
                    <div class="space-y-3" id="payment_banks_list">
                        @forelse($payment_banks as $i => $bank)
                            <div id="bank_card_{{ $i }}" class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-xs hover:shadow-md transition-all duration-300">
                                <!-- Card Header -->
                                <div class="flex items-center justify-between gap-3 pb-3 mb-3 border-b border-gray-100 dark:border-gray-700/70">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-900/40 text-primary-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($bank['code'] ?? 'BK', 0, 3)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                                {{ !empty($bank['name']) ? $bank['name'] : 'Rekening Bank #' . ($i + 1) }}
                                            </h4>
                                            <span class="text-[11px] text-gray-400 truncate block">
                                                {{ !empty($bank['account_number']) ? 'No: ' . $bank['account_number'] : 'Nomor rekening belum diisi' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <!-- Active Toggle Status -->
                                        <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-750 px-2.5 py-1 rounded-xl border border-gray-200 dark:border-gray-700">
                                            <input type="checkbox" wire:model.defer="payment_banks.{{ $i }}.enabled" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 h-4 w-4 cursor-pointer" />
                                            <span class="text-[11px] font-bold {{ !empty($bank['enabled']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                                {{ !empty($bank['enabled']) ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </label>

                                        <!-- Delete Button -->
                                        <button type="button" wire:click.prevent="removeBank({{ $i }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-xl transition cursor-pointer"
                                            title="Hapus Rekening">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Inputs Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kode Bank *</label>
                                        <input type="text" wire:model.defer="payment_banks.{{ $i }}.code" placeholder="Contoh: bca"
                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" />
                                        @error('payment_banks.' . $i . '.code')
                                            <span class="text-[10px] text-red-500 mt-0.5 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Bank *</label>
                                        <input type="text" wire:model.defer="payment_banks.{{ $i }}.name" placeholder="Contoh: Bank Central Asia"
                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" />
                                        @error('payment_banks.' . $i . '.name')
                                            <span class="text-[10px] text-red-500 mt-0.5 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening *</label>
                                        <input type="text" wire:model.defer="payment_banks.{{ $i }}.account_number" placeholder="Contoh: 1234567890"
                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" />
                                        @error('payment_banks.' . $i . '.account_number')
                                            <span class="text-[10px] text-red-500 mt-0.5 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pemilik (a.n.) *</label>
                                        <input type="text" wire:model.defer="payment_banks.{{ $i }}.account_name" placeholder="Contoh: PT SayaBantu"
                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" />
                                        @error('payment_banks.' . $i . '.account_name')
                                            <span class="text-[10px] text-red-500 mt-0.5 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Empty State -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-dashed border-gray-300 dark:border-gray-700 text-center space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto text-xl">
                                    🏦
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Belum Ada Rekening Bank</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tambahkan minimal 1 nomor rekening bank sebagai tujuan transfer deposit customer.</p>
                                </div>
                                <button type="button" wire:click.prevent="addBank('bca')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                    + Tambah Rekening Pertama
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Submit Button Bar (Pindah ke Kanan) -->
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end">
                    <button type="submit" 
                        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm hover:shadow-md active:scale-[0.98] disabled:opacity-50 cursor-pointer w-full sm:w-auto">
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Perubahan Semua</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Saved Confirmation Modal -->
    <div id="settingsSavedModal" class="fixed inset-0 z-50 flex items-center justify-center hidden transition-opacity duration-300">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-xs transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="settingsSavedContent">
            <!-- Close Button -->
            <button id="settingsSavedClose" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Content -->
            <div class="p-6 text-center">
                <!-- Success Icon with animation -->
                <div class="mx-auto w-16 h-16 bg-emerald-100 dark:bg-emerald-950/60 rounded-2xl flex items-center justify-center mb-4 animate-bounce-once shadow-xs">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Berhasil Disimpan!</h3>
                
                <!-- Message -->
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed" id="settingsSavedMessage">
                    Perubahan pengaturan telah berhasil diterapkan dan tersimpan ke sistem.
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-once {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .animate-bounce-once {
            animation: bounce-once 0.5s ease-in-out;
        }
        #settingsSavedModal:not(.hidden) #settingsSavedContent {
            transform: scale(1);
            opacity: 1;
        }
    </style>


    <script>
        (function() {
            let localModalTimeout = null;
            
            function showSettingsSaved(message) {
                const modal = document.getElementById('settingsSavedModal');
                const msgEl = document.getElementById('settingsSavedMessage');
                if (!modal) return;
                if (!message) return;
                
                if (msgEl) msgEl.textContent = message;
                
                if (localModalTimeout) {
                    clearTimeout(localModalTimeout);
                    localModalTimeout = null;
                }
                
                modal.classList.add('hidden');
                
                setTimeout(() => {
                    modal.classList.remove('hidden');
                    localModalTimeout = setTimeout(() => {
                        modal.classList.add('hidden');
                        localModalTimeout = null;
                    }, 3000);
                }, 50);
            }

            function handleBankAdded(event) {
                const index = event[0]?.index ?? event?.index;
                setTimeout(() => {
                    let targetCard = null;
                    if (index !== undefined && index !== null) {
                        targetCard = document.getElementById(`bank_card_${index}`);
                    }
                    if (!targetCard) {
                        const list = document.getElementById('payment_banks_list');
                        if (list && list.lastElementChild) {
                            targetCard = list.lastElementChild;
                        }
                    }
                    if (targetCard) {
                        targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Add highlight glow animation
                        targetCard.classList.add('ring-2', 'ring-primary-500', 'border-primary-400');
                        setTimeout(() => {
                            targetCard.classList.remove('ring-2', 'ring-primary-500', 'border-primary-400');
                        }, 1800);

                        // Focus on first input with empty value or input
                        const inputs = targetCard.querySelectorAll('input[type="text"]');
                        for (let input of inputs) {
                            if (!input.value) {
                                input.focus();
                                break;
                            }
                        }
                    }
                }, 120);
            }

            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('settingsSaved', (event) => {
                    const message = event[0]?.message || event.message || 'Pengaturan berhasil disimpan';
                    showSettingsSaved(message);
                });
                window.Livewire.on('bankAdded', handleBankAdded);
            } else {
                document.addEventListener('livewire:init', () => {
                    Livewire.on('settingsSaved', (event) => {
                        const message = event[0]?.message || event.message || 'Pengaturan berhasil disimpan';
                        showSettingsSaved(message);
                    });
                    Livewire.on('bankAdded', handleBankAdded);
                });
            }

            document.addEventListener('click', function(e) {
                const closeBtn = e.target.closest('#settingsSavedClose');
                if (closeBtn) {
                    if (localModalTimeout) {
                        clearTimeout(localModalTimeout);
                        localModalTimeout = null;
                    }
                    const modal = document.getElementById('settingsSavedModal');
                    if (modal) modal.classList.add('hidden');
                }
            });
        })();
    </script>
</div>