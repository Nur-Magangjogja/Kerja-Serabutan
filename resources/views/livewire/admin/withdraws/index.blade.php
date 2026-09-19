<div class="space-y-5">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
    @endphp

    {{-- ===== Flash Notification ===== --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Persetujuan Penarikan Dana (Withdraw)</h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola dan verifikasi transfer pencairan dana mitra secara real-time</p>
        </div>
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($withdraws->total()) }} Permintaan
        </span>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-3.5 sm:p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div class="relative w-full">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Cari Rekening / Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama, bank, no. rekening..."
                        class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                </div>
            </div>

            @if($isSuperAdmin)
                <div class="w-full">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Wilayah / Kecamatan</label>
                    <select wire:model.live="districtFilter"
                        class="w-full py-2 pl-3 pr-8 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer">
                        <option value="all">Semua Wilayah (Nasional)</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}">Kec. {{ $district->name }} ({{ $district->city?->name ?? 'Kota' }})</option>
                        @endforeach
                    </select>
                </div>
            @elseif($districts->count() > 1)
                <div class="w-full">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Wilayah / Kecamatan</label>
                    <select wire:model.live="districtFilter"
                        class="w-full py-2 pl-3 pr-8 text-xs border border-primary-200 dark:border-primary-800 rounded-xl bg-primary-50/50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 font-bold focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer">
                        <option value="all">Semua Wilayah Saya ({{ $districts->count() }} Kec.)</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}">Kec. {{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($districts->count() === 1)
                <div class="w-full">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Wilayah Wewenang</label>
                    <div class="py-2 px-3 text-xs font-semibold bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 rounded-xl truncate">
                        📍 Kec. {{ $districts->first()->name }}
                    </div>
                </div>
            @endif

            <div class="w-full">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Peran Pengguna</label>
                <select wire:model.live="roleFilter"
                    class="w-full py-2 pl-3 pr-8 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer">
                    <option value="all">Semua Peran (Mitra & Customer)</option>
                    <option value="mitra">Mitra (Relawan)</option>
                    <option value="customer">Customer (Pemohon)</option>
                </select>
            </div>

            <div class="w-full">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Status Permintaan</label>
                <select wire:model.live="status"
                    class="w-full py-2 pl-3 pr-8 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu Transfer (Pending)</option>
                    <option value="completed">Selesai Ditransfer</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== List Container (Desktop Table + Mobile Cards) ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        @if ($withdraws->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    💳
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Permintaan Penarikan Dana</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Semua permintaan penarikan dana telah diproses atau tidak ditemukan data sesuai filter.
                </p>
            </div>
        @else
            {{-- 1. Desktop & Tablet Table (md:block) --}}
            <div class="hidden md:block overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Pengguna & Wilayah</th>
                            <th class="px-4 py-3">Rekening Tujuan</th>
                            <th class="px-4 py-3">Rincian Pencairan</th>
                            <th class="px-4 py-3">Status & Waktu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70 text-gray-700 dark:text-gray-200">
                        @foreach ($withdraws as $wd)
                            @php
                                $u = $wd->user;
                                $districtName = $u?->district?->name ?? ($u?->kecamatan ?? null);
                                $cityName = $u?->city_name ?? (is_object($u?->city) ? $u?->city?->name : ($u?->city ?? null));
                                $netAmount = $wd->net_amount ?: ($wd->amount - ($wd->admin_fee ?? 0));
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-gray-900 dark:text-white text-xs">{{ $u?->name ?? 'User' }}</span>
                                            @if($u?->role === 'customer')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700">Customer</span>
                                            @elseif($u?->role === 'mitra')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">Mitra</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 mt-0.5 flex-wrap">
                                            @if($districtName)
                                                <span class="text-primary-600 dark:text-primary-400 font-semibold">📍 Kec. {{ $districtName }}</span>
                                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                            @endif
                                            <span>{{ $u?->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 font-extrabold rounded text-[10px] uppercase border border-primary-200 dark:border-primary-800">
                                            {{ $wd->bank_code }}
                                        </span>
                                        <span class="font-mono text-gray-900 dark:text-white text-xs font-bold">{{ $wd->account_number }}</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">a.n. {{ $wd->account_name ?: ($u?->name ?? '-') }}</p>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
                                        <span class="text-[9px] text-emerald-700 dark:text-emerald-400 font-semibold">(Bersih)</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        Tarik: Rp {{ number_format($wd->amount, 0, ',', '.') }} <span class="text-gray-300 dark:text-gray-600">•</span> Fee: Rp {{ number_format($wd->admin_fee ?? 0, 0, ',', '.') }}
                                    </p>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($wd->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 inline-block">
                                            Menunggu Transfer
                                        </span>
                                    @elseif ($wd->status === 'completed' || $wd->status === 'success')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 inline-block">
                                            ✅ Selesai
                                        </span>
                                    @elseif ($wd->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 inline-block">
                                            ❌ Ditolak
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-gray-400 block mt-0.5">{{ $wd->created_at->format('d M Y • H:i') }}</span>
                                </td>

                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($wd->status === 'pending')
                                        <button type="button" wire:click="openReviewModal({{ $wd->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            <span>Tinjau</span>
                                        </button>
                                    @elseif ($wd->proof_of_transfer)
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ asset('storage/' . $wd->proof_of_transfer) }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-lg text-[11px] font-bold transition border border-emerald-200 dark:border-emerald-800" title="Lihat Bukti Transfer">
                                                <span>🔍 Bukti</span>
                                            </a>
                                            <button type="button" wire:click="openEditProofModal({{ $wd->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 rounded-lg text-[11px] font-bold transition border border-amber-200 dark:border-amber-800 cursor-pointer active:scale-95" title="Perbarui / Ganti Foto Bukti Transfer">
                                                <span>✏️ Edit</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 2. Mobile Card View (md:hidden) --}}
            <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($withdraws as $wd)
                    @php
                        $u = $wd->user;
                        $districtName = $u?->district?->name ?? ($u?->kecamatan ?? null);
                        $cityName = $u?->city_name ?? (is_object($u?->city) ? $u?->city?->name : ($u?->city ?? null));
                        $netAmount = $wd->net_amount ?: ($wd->amount - ($wd->admin_fee ?? 0));
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition">
                        {{-- Top Header: User & Status --}}
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $u?->name ?? 'User' }}</h4>
                                    @if($u?->role === 'customer')
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700">Customer</span>
                                    @elseif($u?->role === 'mitra')
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">Mitra</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 block mt-0.5">{{ $u?->email }}</span>
                            </div>

                            <div class="text-right shrink-0">
                                @if ($wd->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 inline-block">
                                        Menunggu Transfer
                                    </span>
                                @elseif ($wd->status === 'completed' || $wd->status === 'success')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 inline-block">
                                        ✅ Selesai
                                    </span>
                                @elseif ($wd->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 inline-block">
                                        ❌ Ditolak
                                    </span>
                                @endif
                                <span class="text-[10px] text-gray-400 block mt-1">{{ $wd->created_at->format('d M Y • H:i') }}</span>
                            </div>
                        </div>

                        {{-- Rekening & Lokasi Info Box --}}
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-600/60 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between flex-wrap gap-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 font-extrabold rounded text-[10px] uppercase border border-primary-200 dark:border-primary-800">
                                        {{ $wd->bank_code }}
                                    </span>
                                    <span class="font-mono text-gray-900 dark:text-white font-bold select-all">{{ $wd->account_number }}</span>
                                </div>
                                <span class="text-[11px] text-gray-600 dark:text-gray-300 font-medium">
                                    a.n. {{ $wd->account_name ?: ($u?->name ?? '-') }}
                                </span>
                            </div>
                            @if($districtName || $cityName)
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1 pt-1 border-t border-gray-200/60 dark:border-gray-600/40">
                                    <span>📍</span>
                                    <span>{{ $districtName ? 'Kec. ' . $districtName : $cityName }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Nominal Breakdown Grid --}}
                        <div class="grid grid-cols-3 gap-2 text-center bg-gray-50/50 dark:bg-gray-700/20 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="text-gray-400 text-[9px] font-medium block">Nominal</span>
                                <span class="font-bold text-gray-900 dark:text-white text-xs">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[9px] font-medium block">Biaya Admin</span>
                                <span class="font-bold text-gray-500 text-xs">Rp {{ number_format($wd->admin_fee ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-lg p-0.5 border border-emerald-200 dark:border-emerald-800/60">
                                <span class="text-emerald-700 dark:text-emerald-400 text-[9px] font-extrabold block">Dana Bersih</span>
                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-1">
                            @if ($wd->status === 'pending')
                                <button type="button" wire:click="openReviewModal({{ $wd->id }})"
                                    class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-[0.99]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <span>Tinjau Permintaan Penarikan</span>
                                </button>
                            @elseif ($wd->proof_of_transfer)
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ asset('storage/' . $wd->proof_of_transfer) }}" target="_blank"
                                        class="py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-xl text-xs font-bold transition border border-emerald-200 dark:border-emerald-800 flex items-center justify-center gap-1">
                                        <span>🔍 Lihat Bukti</span>
                                    </a>
                                    <button type="button" wire:click="openEditProofModal({{ $wd->id }})"
                                        class="py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-bold transition border border-amber-200 dark:border-amber-800 flex items-center justify-center gap-1 cursor-pointer active:scale-[0.99]">
                                        <span>✏️ Edit Bukti</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $withdraws->links('vendor.pagination.superadmin') }}
            </div>
        @endif
    </div>

    {{-- ===== MODAL TINJAUAN PENARIKAN DANA (GABUNGAN SETUJUI & TOLAK) ===== --}}
    @if($showReviewModal)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" wire:click="closeReviewModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 z-10 max-h-[90vh] overflow-y-auto">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>💳</span> Tinjauan Permintaan Penarikan Dana
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Verifikasi data rekening dan tentukan keputusan pencairan
                        </p>
                    </div>
                    <button type="button" wire:click="closeReviewModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold leading-none p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">&times;</button>
                </div>

                @php
                    $u = $selectedWithdraw?->user;
                    $districtName = $u?->district?->name ?? ($u?->kecamatan ?? null);
                    $cityName = $u?->city_name ?? (is_object($u?->city) ? $u?->city?->name : ($u?->city ?? null));
                    $netAmount = $selectedWithdraw?->net_amount ?: (($selectedWithdraw?->amount ?? 0) - ($selectedWithdraw?->admin_fee ?? 0));
                    $refundTotal = ($selectedWithdraw?->amount ?? 0) + ($selectedWithdraw?->admin_fee ?? 0);
                @endphp

                {{-- Detail Rekening & Finansial Card --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 text-xs space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-3 border-b border-gray-200 dark:border-gray-600">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider block mb-0.5">Pemohon</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-gray-900 dark:text-white text-xs">{{ $u?->name ?? '-' }}</span>
                                @if($u?->role === 'customer')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300">Customer</span>
                                @elseif($u?->role === 'mitra')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">Mitra</span>
                                @endif
                            </div>
                            <span class="text-[10px] text-gray-400 block mt-0.5">{{ $u?->email }}</span>
                            @if($districtName)
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 inline-flex items-center gap-1">
                                    <span>📍</span> Kec. {{ $districtName }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider block mb-0.5">Rekening Tujuan</span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 font-extrabold rounded text-[10px] uppercase border border-primary-300 dark:border-primary-700">
                                    {{ $selectedWithdraw?->bank_code }}
                                </span>
                                <span class="font-mono font-black text-gray-900 dark:text-white text-xs select-all">
                                    {{ $selectedWithdraw?->account_number }}
                                </span>
                            </div>
                            <span class="text-[11px] text-gray-600 dark:text-gray-300 font-semibold block mt-1">
                                a.n. {{ $selectedWithdraw?->account_name ?: ($u?->name ?? '-') }}
                            </span>
                        </div>
                    </div>

                    {{-- Breakdown Nominal --}}
                    <div class="grid grid-cols-3 gap-2 text-center bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200 dark:border-gray-600/80">
                        <div>
                            <span class="text-gray-400 text-[10px] font-medium block">Nominal Tarik</span>
                            <span class="font-bold text-gray-900 dark:text-white text-xs">Rp {{ number_format($selectedWithdraw?->amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] font-medium block">Biaya Admin</span>
                            <span class="font-bold text-gray-500 text-xs">Rp {{ number_format($selectedWithdraw?->admin_fee ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-lg p-1 border border-emerald-200 dark:border-emerald-800/60">
                            <span class="text-emerald-700 dark:text-emerald-400 text-[10px] font-extrabold block">Dana Bersih (Net)</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs">Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Switcher / Segmented Tabs --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Keputusan Admin:</label>
                    <div class="grid grid-cols-2 gap-2 bg-gray-100 dark:bg-gray-700/60 p-1 rounded-2xl border border-gray-200 dark:border-gray-600">
                        <button type="button" wire:click="switchReviewTab('approve')"
                            class="py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $reviewTab === 'approve' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                            <span>✅</span>
                            <span>Setujui & Selesaikan</span>
                        </button>
                        <button type="button" wire:click="switchReviewTab('reject')"
                            class="py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $reviewTab === 'reject' ? 'bg-rose-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400' }}">
                            <span>❌</span>
                            <span>Tolak Penarikan</span>
                        </button>
                    </div>

                    {{-- Form: Setujui (Approve) --}}
                    @if($reviewTab === 'approve')
                        <div class="p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 space-y-3">
                            <div class="flex items-start gap-2 text-xs text-emerald-800 dark:text-emerald-200">
                                <span class="text-base leading-none">ℹ️</span>
                                <p class="leading-relaxed">
                                    Pastikan transfer dana bersih sebesar <strong class="font-extrabold text-emerald-700 dark:text-emerald-300">Rp {{ number_format($netAmount, 0, ',', '.') }}</strong> ke rekening di atas telah berhasil, kemudian lampirkan foto struk bukti transfer di bawah.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Unggah Struk / Bukti Transfer Bank <span class="text-rose-500">*</span></label>
                                <input type="file" wire:model="proofPhoto" accept="image/*"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer">
                                @error('proofPhoto') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Form: Tolak (Reject) --}}
                    @if($reviewTab === 'reject')
                        <div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/30 rounded-2xl border border-rose-200 dark:border-rose-800/60 space-y-3">
                            <div class="flex items-start gap-2 text-xs text-rose-800 dark:text-rose-200">
                                <span class="text-base leading-none">⚠️</span>
                                <p class="leading-relaxed">
                                    Saldo sebesar <strong class="font-extrabold text-rose-700 dark:text-rose-300">Rp {{ number_format($refundTotal, 0, ',', '.') }}</strong> akan dikembalikan otomatis ke saldo dompet akun pengguna.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                <textarea wire:model="rejectReason" rows="2" placeholder="Contoh: Nomor rekening tidak valid atau nama pemilik berbeda..."
                                    class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                                @error('rejectReason') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeReviewModal" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold text-center cursor-pointer transition">
                        Batal
                    </button>

                    @if($reviewTab === 'approve')
                        <button type="button" wire:click="submitApprove" wire:loading.attr="disabled" wire:target="submitApprove, proofPhoto"
                            class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs text-center flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span wire:loading.remove wire:target="submitApprove, proofPhoto">✅ Konfirmasi Setujui & Selesaikan</span>
                            <span wire:loading wire:target="submitApprove, proofPhoto">Memproses Transfer...</span>
                        </button>
                    @elseif($reviewTab === 'reject')
                        <button type="button" wire:click="submitReject" wire:loading.attr="disabled" wire:target="submitReject"
                            class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs text-center flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span wire:loading.remove wire:target="submitReject">❌ Konfirmasi Tolak & Refund Saldo</span>
                            <span wire:loading wire:target="submitReject">Memproses Penolakan...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endteleport
    @endif

    {{-- ===== MODAL EDIT / PERBARUI BUKTI TRANSFER ===== --}}
    @if($showEditProofModal)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" wire:click="closeEditProofModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 z-10 max-h-[90vh] overflow-y-auto">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>✏️</span> Perbarui Bukti Transfer Bank
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Ganti foto struk / bukti pembayaran bila terjadi kesalahan unggah
                        </p>
                    </div>
                    <button type="button" wire:click="closeEditProofModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold leading-none p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">&times;</button>
                </div>

                @php
                    $u = $selectedWithdraw?->user;
                    $netAmount = $selectedWithdraw?->net_amount ?: (($selectedWithdraw?->amount ?? 0) - ($selectedWithdraw?->admin_fee ?? 0));
                @endphp

                {{-- Ringkasan Penarikan --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Penerima Dana:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $u?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Rekening Tujuan:</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">
                            {{ $selectedWithdraw?->bank_code }} • {{ $selectedWithdraw?->account_number }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-gray-200/70 dark:border-gray-600/70">
                        <span class="text-emerald-700 dark:text-emerald-400 font-bold">Dana Bersih (Net):</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">
                            Rp {{ number_format($netAmount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Bukti Saat Ini & Unggah Bukti Baru --}}
                <div class="space-y-3">
                    @if($selectedWithdraw?->proof_of_transfer)
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bukti Transfer Saat Ini:</label>
                            <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 max-h-40 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $selectedWithdraw->proof_of_transfer) }}" alt="Bukti Transfer" class="max-h-40 w-auto object-contain">
                                <a href="{{ asset('storage/' . $selectedWithdraw->proof_of_transfer) }}" target="_blank"
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                    <span>🔍 Buka Ukuran Penuh</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Unggah Foto Bukti Transfer Pengganti <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" wire:model="editProofPhoto" accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700 file:cursor-pointer">
                        @error('editProofPhoto') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                        
                        @if ($editProofPhoto)
                            <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60">
                                <span class="text-[10px] text-amber-700 dark:text-amber-300 font-bold block mb-1">Pratinjau Foto Baru:</span>
                                <img src="{{ $editProofPhoto->temporaryUrl() }}" alt="Preview" class="max-h-36 rounded-lg object-contain mx-auto">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeEditProofModal" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold text-center cursor-pointer transition">
                        Batal
                    </button>

                    <button type="button" wire:click="submitUpdateProof" wire:loading.attr="disabled" wire:target="submitUpdateProof, editProofPhoto"
                        class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs text-center flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <span wire:loading.remove wire:target="submitUpdateProof, editProofPhoto">💾 Simpan Bukti Baru</span>
                        <span wire:loading wire:target="submitUpdateProof, editProofPhoto">Menyimpan Foto...</span>
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>