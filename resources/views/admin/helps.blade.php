@extends('layouts.admin')

@section('page-title', 'Moderasi Bantuan')

@section('content')
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Moderasi Bantuan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau dan kelola permintaan bantuan masuk di wilayah Anda</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.helps.approved') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Lihat Disetujui
            </a>
        </div>
    </div>

    {{-- ===== Stats Overview ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Bantuan</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($pendingHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Selesai</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($completedHelps) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
        </div>
    </div>

    {{-- Alert Flash --}}
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center gap-3 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    {{-- ===== Filter & Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="w-full md:w-80">
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari judul atau deskripsi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="statusFilter"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="active">Aktif / Disetujui</option>
                    <option value="completed">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>

                <select wire:model.live="perPage"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3">#ID</th>
                        <th class="px-4 py-3">Bantuan</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3 hidden md:table-cell">Kota</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($helps as $help)
                        @php
                        $stClass = match($help->status) {
                            'active', 'disetujui'   => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            'pending', 'menunggu'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            'completed', 'selesai'  => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            'rejected', 'ditolak'   => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                            default                 => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-400 dark:text-gray-500">#{{ $help->id }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $help->title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($help->description, 50) }}</div>
                                @if($help->category)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        {{ $help->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $help->customer->name ?? $help->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ $help->customer->phone ?? $help->user->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                {{ $help->city->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white text-right whitespace-nowrap">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">
                                    {{ ucfirst($help->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 hidden lg:table-cell whitespace-nowrap">
                                {{ $help->created_at?->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                                @if($help->status === 'pending' || $help->status === 'menunggu')
                                    <button wire:click="approveHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition">
                                        Setujui
                                    </button>
                                    <button wire:click="rejectHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                                        Tolak
                                    </button>
                                @else
                                    <button wire:click="viewHelp({{ $help->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada data bantuan yang sesuai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($helps->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                {{ $helps->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
