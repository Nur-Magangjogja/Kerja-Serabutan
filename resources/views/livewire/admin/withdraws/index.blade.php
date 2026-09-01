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

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Persetujuan Penarikan Dana (Withdraw)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola dan verifikasi transfer pencairan dana mitra secara real-time</p>
        </div>
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($withdraws->total()) }} Permintaan
        </span>
    </div>

    {{-- ===== Realtime Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Rekening / Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama mitra, bank, nomor rekening..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Peran Pengguna</label>
                <select wire:model.live="roleFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Peran (Mitra & Customer)</option>
                    <option value="mitra">Mitra (Relawan)</option>
                    <option value="customer">Customer (Pemohon)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status Permintaan</label>
                <select wire:model.live="status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu Transfer (Pending)</option>
                    <option value="completed">Selesai Ditransfer</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($withdraws->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    💳
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Permintaan Penarikan Dana</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Semua permintaan penarikan dana telah diproses.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">ID / Pengguna</th>
                            <th class="px-4 py-3">Rekening Tujuan</th>
                            <th class="px-4 py-3">Nominal Tarik</th>
                            <th class="px-4 py-3">Biaya Admin</th>
                            <th class="px-4 py-3">Jumlah Bersih (Net)</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Waktu Pengajuan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach ($withdraws as $wd)
                            @php $u = $wd->user; @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900 dark:text-white text-xs">#WD-{{ $wd->id }}</span>
                                    <div class="mt-1">
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $u?->name ?? 'User' }}</p>
                                            @if($u?->role === 'customer')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700">Customer</span>
                                            @elseif($u?->role === 'mitra')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">Mitra</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-gray-400">{{ $u?->email }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="font-bold text-gray-900 dark:text-white uppercase">{{ $wd->bank_code }}</p>
                                    <p class="font-mono text-gray-700 dark:text-gray-300 text-[11px]">{{ $wd->account_number }}</p>
                                    <p class="text-[10px] text-gray-400">a.n. {{ $wd->account_name ?: ($u?->name ?? '-') }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-gray-900 dark:text-white text-xs">
                                    Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-500 text-xs">
                                    Rp {{ number_format($wd->admin_fee ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">
                                    Rp {{ number_format($wd->net_amount ?: ($wd->amount - ($wd->admin_fee ?? 0)), 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($wd->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300">
                                            Menunggu Transfer
                                        </span>
                                    @elseif ($wd->status === 'completed' || $wd->status === 'success')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            ✅ Selesai
                                        </span>
                                    @elseif ($wd->status === 'rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            ❌ Ditolak
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-gray-400 text-[11px]">
                                    {{ $wd->created_at->format('d M Y • H:i') }}
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if ($wd->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" wire:click="openApproveModal({{ $wd->id }})"
                                                class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition shadow-xs cursor-pointer">
                                                ✓ Transfer & Setujui
                                            </button>
                                            <button type="button" wire:click="openRejectModal({{ $wd->id }})"
                                                class="px-2 py-1.5 bg-gray-100 hover:bg-rose-50 dark:bg-gray-700 text-gray-700 hover:text-rose-600 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                                Tolak
                                            </button>
                                        </div>
                                    @elseif ($wd->proof_of_transfer)
                                        <a href="{{ asset('storage/' . $wd->proof_of_transfer) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-primary-600 hover:underline">
                                            <span>📷 Lihat Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $withdraws->links('vendor.pagination.superadmin') }}
            </div>
        @endif
    </div>

    {{-- MODAL APPROVAL & BUKTI TRANSFER --}}
    @if($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>💳</span> Konfirmasi Transfer Pencairan Dana
                </h3>

                <div class="p-3.5 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 text-xs space-y-1">
                    <p class="text-emerald-900 dark:text-emerald-200">
                        Penerima: <strong>{{ $selectedWithdraw?->account_name ?: $selectedWithdraw?->user?->name }}</strong>
                    </p>
                    <p class="text-emerald-900 dark:text-emerald-200 font-mono">
                        Bank: <strong>{{ $selectedWithdraw?->bank_code }}</strong> ({{ $selectedWithdraw?->account_number }})
                    </p>
                    <p class="text-emerald-950 dark:text-emerald-100 font-bold text-sm pt-1">
                        Jumlah Transfer Bersih: Rp {{ number_format($selectedWithdraw?->net_amount ?: $selectedWithdraw?->amount, 0, ',', '.') }}
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Unggah Struk / Bukti Transfer Bank</label>
                    <input type="file" wire:model="proofPhoto" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    @error('proofPhoto') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeApproveModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="button" wire:click="submitApprove" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs">Setujui & Selesaikan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL REJECTION --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>❌</span> Tolak Pencairan Dana
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-300">
                    Saldo sebesar <strong>Rp {{ number_format($selectedWithdraw?->amount ?? 0, 0, ',', '.') }}</strong> akan dikembalikan otomatis ke saldo dompet user.
                </p>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan</label>
                    <textarea wire:model="rejectReason" rows="3" placeholder="Contoh: Nomor rekening tidak sesuai dengan nama pemilik akun..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    @error('rejectReason') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeRejectModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="button" wire:click="submitReject" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs">Tolak Pencairan</button>
                </div>
            </div>
        </div>
    @endif
</div>