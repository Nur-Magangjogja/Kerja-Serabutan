@php
    $title = 'Approval Top-Up Saldo';
    $breadcrumb = 'Admin / Approval Top-Up';
@endphp

<div x-data="adminApprovalModal()" @confirm-approve.window="openFromEvent($event)">
    <div wire:poll.15s.visible class="space-y-5">
        {{-- ===== Page Header ===== --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Approval Top-Up Saldo</h1>
                    @if($adminCityName)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Wilayah: {{ $adminCityName }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            Semua Wilayah
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Verifikasi bukti transfer QRIS dan setujui / tolak top-up customer wilayah Anda</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-semibold">Auto-refresh 15 dtk</span>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl text-sm shadow-xs">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="flex items-center gap-2.5 px-4 py-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-2xl text-sm shadow-xs">
                <svg class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===== Summary Stat Cards ===== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div wire:click="filterByStatus('waiting_approval')" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $filterStatus === 'waiting_approval' ? 'border-amber-400 dark:border-amber-500 ring-2 ring-amber-400/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-4 flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Menunggu (Pending)</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 truncate">{{ $totalPending }}</p>
                </div>
            </div>

            <div wire:click="filterByStatus('completed')" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $filterStatus === 'completed' ? 'border-emerald-400 dark:border-emerald-500 ring-2 ring-emerald-400/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-4 flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Disetujui / Selesai</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 truncate">{{ $totalCompleted }}</p>
                </div>
            </div>

            <div wire:click="filterByStatus('cancelled')" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $filterStatus === 'cancelled' ? 'border-purple-400 dark:border-purple-500 ring-2 ring-purple-400/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-4 flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Dibatalkan / Fraud</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-purple-600 dark:text-purple-400 truncate">{{ $totalCancelled }}</p>
                </div>
            </div>

            <div wire:click="filterByStatus('rejected')" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $filterStatus === 'rejected' ? 'border-rose-400 dark:border-rose-500 ring-2 ring-rose-400/20' : 'border-gray-100 dark:border-gray-700' }} shadow-xs p-4 flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Ditolak</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 truncate">{{ $totalRejected }}</p>
                </div>
            </div>
        </div>

        {{-- ===== Filter Tabs & Search Bar ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 scrollbar-none">
                <button wire:click="filterByStatus('waiting_approval')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'waiting_approval' ? 'bg-amber-500 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Menunggu ({{ $totalPending }})
                </button>
                <button wire:click="filterByStatus('completed')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Disetujui ({{ $totalCompleted }})
                </button>
                <button wire:click="filterByStatus('cancelled')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'cancelled' ? 'bg-purple-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Dibatalkan / Fraud ({{ $totalCancelled }})
                </button>
                <button wire:click="filterByStatus('rejected')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Ditolak ({{ $totalRejected }})
                </button>
                <button wire:click="filterByStatus('all')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ $filterStatus === 'all' ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Semua ({{ $totalAll }})
                </button>
            </div>

            <div class="relative w-full md:w-72">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama, email..."
                    class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        {{-- ===== Table Card ===== --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            @if($transactions->isEmpty())
                <div class="px-4 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3 text-2xl">
                        💳
                    </div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ada Transaksi Ditemukan</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        @if($adminCityName)
                            Tidak ada pengajuan top-up customer untuk wilayah {{ $adminCityName }} pada filter ini.
                        @else
                            Tidak ada pengajuan top-up pada filter ini.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5">#ID / Tanggal</th>
                                <th class="px-4 py-3.5">Customer</th>
                                <th class="px-4 py-3.5 hidden md:table-cell">Kode Request</th>
                                <th class="px-4 py-3.5 text-right">Nominal</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 hidden lg:table-cell">Petugas Approval</th>
                                <th class="px-4 py-3.5 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                            @foreach($transactions as $tx)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">#{{ $tx->id }}</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $tx->created_at->format('d M Y • H:i') }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($tx->user->name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 dark:text-white truncate">{{ $tx->user->name ?? $tx->customer_name }}</p>
                                            <p class="text-[10px] text-gray-400 truncate">{{ $tx->customer_phone ?? $tx->user->phone ?? '-' }}</p>
                                            @if($tx->user?->city_name)
                                                <span class="text-[9px] text-primary-600 dark:text-primary-400 font-semibold">{{ $tx->user->city_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-mono text-gray-600 dark:text-gray-300 font-bold hidden md:table-cell">
                                    {{ $tx->request_code ?? '#'.$tx->id }}
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-black text-gray-900 dark:text-white text-sm">
                                        Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($tx->status === 'waiting_approval')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                    @elseif($tx->status === 'completed' || $tx->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Disetujui
                                        </span>
                                    @elseif($tx->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-300">
                                            Dibatalkan
                                        </span>
                                    @elseif($tx->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-300">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap hidden lg:table-cell">
                                    @if($tx->approvedBy)
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $tx->approvedBy->name }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $tx->approved_at ? \Carbon\Carbon::parse($tx->approved_at)->format('d M Y H:i') : '-' }}</p>
                                    @else
                                        <span class="text-gray-400 text-[10px]">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Tombol Detail --}}
                                        <button type="button" wire:click="viewDetail({{ $tx->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl transition cursor-pointer shadow-2xs"
                                            title="Lihat Detail & Bukti Transfer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detail</span>
                                        </button>

                                        @if($tx->status === 'waiting_approval')
                                            {{-- Tombol Setujui / Terima --}}
                                            <button type="button" @click="openModal({ id: {{ $tx->id }}, code: '{{ $tx->request_code ?? '#'.$tx->id }}', amount: 'Rp {{ number_format($tx->amount, 0, ',', '.') }}', name: '{{ addslashes($tx->user->name ?? $tx->customer_name ?? 'Customer') }}' })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition cursor-pointer shadow-2xs"
                                                title="Setujui Top-Up Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Setujui</span>
                                            </button>

                                            {{-- Tombol Tolak --}}
                                            <button type="button" wire:click="openRejectModal({{ $tx->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-xl transition cursor-pointer shadow-2xs"
                                                title="Tolak Request Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Tolak</span>
                                            </button>
                                        @elseif($tx->status === 'completed' || $tx->status === 'approved')
                                            {{-- Tombol Batal Setujui (Fraud / Koreksi) --}}
                                            <button type="button" wire:click="openCancelApprovalModal({{ $tx->id }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-purple-600 dark:text-purple-400 hover:underline cursor-pointer"
                                                title="Batalkan approval dan tarik saldo kembali">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                <span>Batal Approve</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

        {{-- ===== Detail Modal ===== --}}
        @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white">Detail Pengajuan Top-Up #{{ $selectedTransaction->id }}</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Kode: {{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}</p>
                    </div>
                    <button wire:click="closeModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4">
                    {{-- Info Card --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-600 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-400 block">Customer</span>
                            <span class="font-bold text-gray-900 dark:text-white mt-0.5 block">{{ $selectedTransaction->user->name ?? $selectedTransaction->customer_name }}</span>
                            <span class="text-[10px] text-gray-500">{{ $selectedTransaction->customer_phone ?? $selectedTransaction->user->phone ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 block">Wilayah / Kota</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400 mt-0.5 block">{{ $selectedTransaction->user?->city_name ?? 'Tidak Ada Wilayah' }}</span>
                            <span class="text-[10px] text-gray-500">{{ $selectedTransaction->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-[10px] text-gray-400 block">Nominal Top-Up</span>
                            <span class="font-black text-gray-900 dark:text-white text-sm">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-[10px] text-gray-400 block">Status Saat Ini</span>
                            <span class="font-bold text-xs {{ $selectedTransaction->status === 'waiting_approval' ? 'text-amber-600' : ($selectedTransaction->status === 'completed' ? 'text-emerald-600' : 'text-rose-600') }}">
                                {{ ucfirst($selectedTransaction->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Informasi Singkat Saat Menyetujui Top Up --}}
                    @if($selectedTransaction->status === 'waiting_approval')
                        <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2.5 shadow-2xs">
                            <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-bold">Informasi Persetujuan Top-Up:</p>
                                <p class="text-emerald-700/90 dark:text-emerald-300/90">Pastikan uang mutasi QRIS telah masuk ke rekening. Menyetujui request ini akan langsung menambahkan saldo sebesar <strong>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</strong> ke akun customer.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Bukti Transfer / Gambar --}}
                    <div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white block mb-2">Foto Struk / Bukti Transfer Customer:</span>
                        @if($selectedTransaction->proof_of_payment)
                            <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-900/5 dark:bg-gray-950 p-2 text-center">
                                <img src="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" 
                                    class="max-h-72 w-auto mx-auto rounded-xl object-contain shadow-sm"
                                    alt="Bukti Transfer QRIS"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2214%22 fill=%22%239ca3af%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
                                <div class="mt-2 text-center">
                                    <a href="{{ asset('storage/' . $selectedTransaction->proof_of_payment) }}" target="_blank" download class="text-[11px] font-semibold text-primary-600 hover:underline">
                                        🔗 Buka Gambar Penuh / Unduh File
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                Customer belum mengunggah foto bukti pembayaran.
                            </div>
                        @endif
                    </div>

                    @if($selectedTransaction->rejection_reason)
                        <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-2xl text-xs space-y-1">
                            <span class="font-bold text-rose-800 dark:text-rose-300 block">Catatan / Alasan Penolakan:</span>
                            <p class="text-rose-700 dark:text-rose-400">{{ $selectedTransaction->rejection_reason }}</p>
                        </div>
                    @endif
                </div>

                <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        Tutup
                    </button>

                    @if($selectedTransaction->status === 'waiting_approval')
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="openRejectModal({{ $selectedTransaction->id }})"
                                class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                                Tolak Top-Up
                            </button>
                            <button type="button" @click="closeModal(); openModal({ id: {{ $selectedTransaction->id }}, code: '{{ $selectedTransaction->request_code ?? '#'.$selectedTransaction->id }}', amount: 'Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}', name: '{{ addslashes($selectedTransaction->user->name ?? $selectedTransaction->customer_name ?? 'Customer') }}' })"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                                Setujui & Tambah Saldo
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ===== Reject Modal ===== --}}
        @if($showRejectModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white">Tolak Pengajuan Top-Up</h2>
                    <button wire:click="closeModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <div class="p-5 space-y-3">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-xl text-xs text-amber-800 dark:text-amber-300">
                        Top-up milik <strong>{{ $selectedTransaction->user->name ?? 'Customer' }}</strong> sebesar <strong>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</strong> akan ditolak.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Alasan Penolakan *</label>
                        <textarea wire:model="rejectionReason" rows="3" placeholder="Contoh: Bukti transfer tidak valid / mutasi rekening tidak masuk / nominal berbeda..."
                            class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        @error('rejectionReason') <span class="text-rose-500 text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="reject"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                        Konfirmasi Tolak Top-Up
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== Cancel Approval Modal ===== --}}
        @if($showCancelApprovalModal && $selectedTransaction)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white text-purple-700 dark:text-purple-400">Batalkan Approval (Koreksi Saldo)</h2>
                    <button wire:click="closeModal" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <div class="p-5 space-y-3">
                    <div class="p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 rounded-xl text-xs text-purple-800 dark:text-purple-300 space-y-1">
                        <p class="font-bold">⚠️ Perhatian Tindakan Koreksi Saldo:</p>
                        <p>Saldo customer <strong>{{ $selectedTransaction->user->name ?? 'Customer' }}</strong> sebesar <strong>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</strong> akan otomatis ditarik / dikurangi kembali dari akun.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Alasan Pembatalan / Indikasi Fraud *</label>
                        <textarea wire:model="cancellationReason" rows="3" placeholder="Jelaskan alasan pembatalan approval ini secara detail..."
                            class="w-full px-3.5 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        @error('cancellationReason') <span class="text-rose-500 text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="cancelApproval"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                        Tarik Saldo & Batalkan
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== Alpine Approval Confirmation Modal (with Brief Information) ===== --}}
        <template x-teleport="body">
            <div x-cloak x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" @click="close()" aria-hidden="true"></div>

                <div x-transition class="relative w-full max-w-sm mx-auto">
                    <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 relative">
                            <button @click="close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="flex flex-col items-center text-center gap-3">
                                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <h3 class="text-base font-bold">Konfirmasi Approval Top-Up</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pastikan dana telah masuk ke mutasi rekening sebelum menyetujui:</p>

                                {{-- Brief Info Box --}}
                                <div class="w-full bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-3.5 border border-gray-100 dark:border-gray-700/60 space-y-0.5">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="name"></div>
                                    <div class="text-base font-black text-primary-600 dark:text-primary-400" x-text="amount"></div>
                                    <div class="text-[11px] font-mono text-gray-400 dark:text-gray-400" x-text="code"></div>
                                </div>

                                <div class="w-full p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-left text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Saldo customer akan langsung bertambah secara instan sejumlah nominal di atas.</span>
                                </div>

                                <div class="mt-2 flex w-full items-center justify-between gap-2.5">
                                    <button @click="close()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer transition">Batal</button>
                                    <button @click="$wire.approve(id); close()" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 cursor-pointer shadow-xs transition">Ya, Setujui</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function adminApprovalModal() {
    return {
        show: false,
        id: null,
        name: '',
        amount: '',
        code: '',
        openModal(data) {
            this.id = data.id ?? null;
            this.name = data.name ?? '';
            this.amount = data.amount ?? '';
            this.code = data.code ?? '';
            this.show = true;
        },
        openFromEvent(e) {
            const d = e.detail || {};
            this.id = d.id ?? null;
            this.name = d.name ?? '';
            this.amount = d.amount ?? '';
            this.code = d.code ?? '';
            this.show = true;
        },
        close() {
            this.show = false;
            this.id = null;
        }
    }
}
</script>

