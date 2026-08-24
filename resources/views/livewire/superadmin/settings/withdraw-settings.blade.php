@php
    $title = 'Pengaturan Biaya & Rekening Withdraw';
    $breadcrumb = 'Super Admin / Pengaturan / Withdraw';
@endphp

<div class="py-2 space-y-6"
     x-data="{}"
     x-on:settings-saved.window="
        $nextTick(() => {
            const el = document.getElementById('withdraw-settings-alert');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
     ">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav active="withdraw" />

    <!-- Notifikasi Sukses / Alert Section -->
    @if(session()->has('message'))
        <div id="withdraw-settings-alert" class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl shadow-xs ring-2 ring-emerald-500/20 flex items-center justify-between gap-3">
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

    <!-- Stat Cards Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Bank Terdaftar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Bank & E-Wallet</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $stats['total_banks'] }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-sky-400 flex items-center justify-center text-xl">
                    🏦
                </div>
            </div>
            <div class="mt-3 text-[11px] text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $stats['active_banks'] }} aktif dapat dipilih pengguna</span>
            </div>
        </div>

        <!-- Rekening Utama Platform (Gratis) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Rekening Platform</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $stats['platform_accounts'] }} Rekening
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl">
                    ✨
                </div>
            </div>
            <div class="mt-3 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                Bebas biaya admin (Rp 0) untuk pengguna
            </div>
        </div>

        <!-- Biaya Admin Default -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Biaya Antar Bank</span>
                    <div class="text-2xl font-black text-primary-600 dark:text-sky-400 mt-1">
                        Rp {{ number_format($default_other_fee, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-sky-400 flex items-center justify-center text-xl">
                    ⚡
                </div>
            </div>
            <div class="mt-3 text-[11px] text-gray-400 dark:text-gray-500">
                Tarif standar transfer BI-FAST / beda bank
            </div>
        </div>

        <!-- Total Pencairan Berhasil -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Pencairan Sukses</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        Rp {{ number_format($stats['total_withdraw_amount'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl">
                    💰
                </div>
            </div>
            <div class="mt-3 text-[11px] text-gray-400 dark:text-gray-500">
                Dari total {{ $stats['total_withdraw_count'] }} permohonan withdraw
            </div>
        </div>
    </div>

    <!-- General Settings Form -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-sky-400 flex items-center justify-center text-sm font-bold">
                    ⚙️
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Pengaturan Umum & Kebijakan Biaya</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Atur batas nominal dan mekanisme pemotongan biaya transfer perbankan</p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="saveGeneralSettings" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Minimum Amount -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Batas Minimum Penarikan (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                        <input
                            type="number"
                            wire:model.defer="min_amount"
                            step="100"
                            min="100"
                            class="w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-700/70 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                    @error('min_amount') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    <p class="text-[11px] text-gray-400 mt-1">Nominal saldo paling sedikit yang dapat ditarik pengguna.</p>
                </div>

                <!-- Default Other Fee -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Biaya Admin Default / Bank Lainnya (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                        <input
                            type="number"
                            wire:model.defer="default_other_fee"
                            step="100"
                            min="0"
                            class="w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-700/70 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                    @error('default_other_fee') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    <p class="text-[11px] text-gray-400 mt-1">Dikenakan saat pengguna memilih bank di luar daftar khusus.</p>
                </div>

                <!-- Fee Mode -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Mekanisme Pemotongan Biaya
                    </label>
                    <select
                        wire:model.defer="fee_mode"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700/70 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="deduct_from_received">Potong dari Dana Diterima (Contoh: Tarik 50rb, biaya 2.5rb -> cair 47.5rb)</option>
                        <option value="deduct_from_balance">Potong dari Saldo Tambahan (Contoh: Tarik 50rb, saldo terpotong 52.5rb)</option>
                    </select>
                    @error('fee_mode') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    <p class="text-[11px] text-gray-400 mt-1">Sistem default menyalurkan biaya admin langsung dari nominal pencairan.</p>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan Umum</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Bank & E-Wallet Matrix Configuration -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
        <!-- Header with Search, Filter & Actions -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-800/80">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Daftar Bank, E-Wallet & Rekening Pengirim Platform</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Tentukan bank pengirim utama (Bebas Biaya/Rp 0) atau atur tarif admin per bank agar admin hanya meneruskan biaya bank.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="resetToDefaults"
                        wire:confirm="Kembalikan daftar bank dan tarif standar BI-FAST ke setelan awal?"
                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
                        title="Reset ke Daftar Rekomendasi Standar"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Standar</span>
                    </button>

                    <button
                        type="button"
                        wire:click="openAddModal"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Bank / E-Wallet</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-4 flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Cari nama bank, e-wallet, atau kode..."
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
                    />
                </div>

                <!-- Category Pills -->
                <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto">
                    <button
                        type="button"
                        wire:click="$set('filterCategory', 'all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 cursor-pointer {{ $filterCategory === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        Semua ({{ count($banks) }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filterCategory', 'Bank')"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 cursor-pointer {{ $filterCategory === 'Bank' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        Bank Transfer
                    </button>
                    <button
                        type="button"
                        wire:click="$set('filterCategory', 'E-Wallet')"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 cursor-pointer {{ $filterCategory === 'E-Wallet' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        E-Wallet
                    </button>
                </div>
            </div>
        </div>

        <!-- Bank Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/50 dark:bg-gray-900/30 text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3.5">Bank / E-Wallet</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Biaya Admin (Transfer)</th>
                        <th class="px-4 py-3.5 text-center">Rekening Utama Platform</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($filteredBanks as $index => $b)
                        @php
                            $fee = (int) ($b['fee'] ?? 0);
                            $isPlatform = !empty($b['is_platform_account']);
                            $isActive = isset($b['is_active']) ? (bool) $b['is_active'] : true;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-750/40 transition">
                            <!-- Bank Name & Code -->
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-base flex-shrink-0">
                                        {{ $b['icon'] ?? '🏦' }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $b['name'] ?? '' }}</span>
                                            @if($isPlatform)
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60" title="Akun Pengirim Platform (Bebas Biaya Admin)">
                                                    ✨ REKOMENDASI PLATFORM
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                            KODE: {{ $b['code'] ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $b['category'] ?? 'Bank' }}
                                </span>
                            </td>

                            <!-- Admin Fee -->
                            <td class="px-4 py-3.5">
                                @if($fee === 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <span>Gratis (Rp 0)</span>
                                    </span>
                                @else
                                    <div class="font-bold text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($fee, 0, ',', '.') }}
                                    </div>
                                    <span class="text-[10px] text-gray-400">Diteruskan ke bank</span>
                                @endif
                            </td>

                            <!-- Toggle Platform Account -->
                            <td class="px-4 py-3.5 text-center">
                                <button
                                    type="button"
                                    wire:click="togglePlatformAccount({{ $index }})"
                                    class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition cursor-pointer border {{ $isPlatform ? 'bg-emerald-500 text-white border-emerald-600 shadow-xs' : 'bg-gray-50 dark:bg-gray-700 text-gray-500 border-gray-200 dark:border-gray-600 hover:bg-gray-100' }}"
                                    title="Klik untuk mengubah status rekening utama platform"
                                >
                                    {{ $isPlatform ? '✓ Rekening Platform' : 'Bukan Platform' }}
                                </button>
                            </td>

                            <!-- Toggle Status Active -->
                            <td class="px-4 py-3.5 text-center">
                                <button
                                    type="button"
                                    wire:click="toggleBankStatus({{ $index }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition cursor-pointer {{ $isActive ? 'bg-blue-50 dark:bg-blue-950/60 text-primary-600 dark:text-sky-400 border border-blue-200 dark:border-blue-800/40' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 border border-gray-200 dark:border-gray-600' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-primary-500' : 'bg-gray-400' }}"></span>
                                    <span>{{ $isActive ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $index }})"
                                        class="p-2 rounded-xl text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950/50 transition cursor-pointer"
                                        title="Edit Bank"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    @if(($b['code'] ?? '') !== 'OTHER')
                                        <button
                                            type="button"
                                            wire:click="deleteBank({{ $index }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus bank {{ $b['name'] }}?"
                                            class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer"
                                            title="Hapus Bank"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                Tidak ada bank atau e-wallet yang cocok dengan pencarian "{{ $search }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Bank -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $editingIndex !== null ? 'Edit Pengaturan Bank' : 'Tambah Bank / E-Wallet Baru' }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Konfigurasi tarif admin dan status rekening platform</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-1.5 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveBank" class="p-5 space-y-4 text-xs">
                    <!-- Kode Bank -->
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Kode Singkatan (Uppercase)
                        </label>
                        <input
                            type="text"
                            wire:model.defer="bank_code"
                            placeholder="Contoh: BCA, BRI, DANA, BLU"
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-bold uppercase text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                        />
                        @error('bank_code') <span class="text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Lengkap Bank -->
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Nama Lengkap Bank / E-Wallet
                        </label>
                        <input
                            type="text"
                            wire:model.defer="bank_name"
                            placeholder="Contoh: Bank Central Asia (BCA)"
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                        />
                        @error('bank_name') <span class="text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kategori & Ikon -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Kategori
                            </label>
                            <select
                                wire:model.defer="bank_category"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            >
                                <option value="Bank">Bank Transfer</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Ikon / Emoji
                            </label>
                            <select
                                wire:model.defer="bank_icon"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            >
                                <option value="🏦">🏦 Bank</option>
                                <option value="📱">📱 E-Wallet</option>
                                <option value="💳">💳 Kartu/Lainnya</option>
                                <option value="🌐">🌐 Digital</option>
                            </select>
                        </div>
                    </div>

                    <!-- Biaya Admin -->
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Biaya Admin Transfer (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input
                                type="number"
                                wire:model.defer="bank_fee"
                                step="100"
                                min="0"
                                placeholder="0"
                                class="w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                            />
                        </div>
                        @error('bank_fee') <span class="text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-gray-400 mt-1">Isi 0 jika bank ini gratis atau merupakan rekening pengirim utama platform.</p>
                    </div>

                    <!-- Checkbox Platform Account -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model.defer="is_platform_account"
                                class="mt-0.5 rounded text-primary-600 focus:ring-primary-500"
                            />
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200 block">Jadikan Rekening Utama Platform</span>
                                <span class="text-[10px] text-gray-400 block mt-0.5">
                                    Platform memiliki akun bank/e-wallet ini sehingga transfer ke tujuan yang sama bebas biaya admin (Rp 0) dan ditandai badge rekomendasi.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold transition shadow-xs cursor-pointer"
                        >
                            Simpan Bank
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
