@extends('layouts.admin')

@section('page-title', 'Moderasi Bantuan')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Moderasi Bantuan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan tinjau permintaan bantuan yang masuk dari customer di wilayah Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.helps.approved') }}" class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-xl hover:bg-emerald-100 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Lihat Disetujui
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
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

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Menunggu Persetujuan</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($pendingHelps) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Bantuan Selesai</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($completedHelps) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Alert Flash -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-sm font-medium">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full md:w-80">
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari judul atau deskripsi..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="statusFilter" class="px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="active">Aktif / Disetujui</option>
                    <option value="completed">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>

                <select wire:model.live="perPage" class="px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-100">
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Bantuan</th>
                        <th class="px-6 py-3.5">Customer</th>
                        <th class="px-6 py-3.5">Kota / Lokasi</th>
                        <th class="px-6 py-3.5">Nominal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($helps as $help)
                        <tr class="hover:bg-gray-50/75 transition">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $help->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $help->title }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($help->description, 60) }}</div>
                                @if($help->category)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700">
                                        {{ $help->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $help->customer->name ?? $help->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $help->customer->phone ?? $help->user->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-700">{{ $help->city->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($help->status === 'active' || $help->status === 'disetujui')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @elseif($help->status === 'pending' || $help->status === 'menunggu')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200">Menunggu</span>
                                @elseif($help->status === 'completed' || $help->status === 'selesai')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">Selesai</span>
                                @elseif($help->status === 'rejected' || $help->status === 'ditolak')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200">Ditolak</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst($help->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $help->created_at?->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($help->status === 'pending' || $help->status === 'menunggu')
                                    <button wire:click="approveHelp({{ $help->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition">
                                        Setujui
                                    </button>
                                    <button wire:click="rejectHelp({{ $help->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition">
                                        Tolak
                                    </button>
                                @else
                                    <button wire:click="viewHelp({{ $help->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Tidak ada data bantuan yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($helps->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $helps->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
