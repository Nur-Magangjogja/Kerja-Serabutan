<div>
    <div class="p-6 space-y-6">
        <!-- Top Title -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Ringkasan aktivitas platform dan pemantauan bantuan di wilayah Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                    {{ auth()->user()->city->name ?? 'Semua Wilayah' }}
                </span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Bantuan</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalHelps) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Menunggu Bantuan</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($pendingHelps) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Bantuan Aktif</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($activeHelps) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Mitra Terverifikasi</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($verifiedMitras) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Quick Links & Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.helps') }}" class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">Moderasi Permintaan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $pendingHelps }} bantuan perlu persetujuan</p>
                </div>
            </a>

            <a href="{{ route('admin.verifications') }}" class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-purple-100 transition group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition">Verifikasi Mitra KTP</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $pendingVerifications }} pendaftaran menunggu</p>
                </div>
            </a>

            <a href="{{ route('admin.topup.approvals') }}" class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600 transition">Persetujuan Top-Up</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $pendingTopups }} permintaan isi saldo</p>
                </div>
            </a>
        </div>

        <!-- System Health Overview -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Status Sistem
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="p-3.5 bg-gray-50 rounded-xl flex items-center justify-between">
                    <span class="text-gray-600">Koneksi Database</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($health['database']['status'] ?? '') === 'ok' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ strtoupper($health['database']['status'] ?? 'UNKNOWN') }}
                    </span>
                </div>
                <div class="p-3.5 bg-gray-50 rounded-xl flex items-center justify-between">
                    <span class="text-gray-600">Antrian Jobs</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                        {{ $health['queue']['pending'] ?? 0 }} pending
                    </span>
                </div>
                <div class="p-3.5 bg-gray-50 rounded-xl flex items-center justify-between">
                    <span class="text-gray-600">Kapasitas Disk</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($health['disk']['usage'] ?? 0) > 85 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $health['disk']['usage'] ?? 0 }}% terpakai
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
