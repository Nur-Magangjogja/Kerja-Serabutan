@extends('layouts.admin')

@section('page-title', 'Manajemen Bantuan - Disetujui')

@section('content')
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Bantuan Disetujui</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Daftar bantuan yang telah disetujui (hanya lihat untuk Admin)</p>
        </div>
        <a href="{{ route('admin.helps') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Moderasi
        </a>
    </div>

    {{-- ===== Filter & Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="w-full sm:w-80">
                <div class="relative">
                    <input wire:model.debounce.500ms="search" type="text" placeholder="Cari judul atau deskripsi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3">#ID</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3 hidden md:table-cell">Kota</th>
                        <th class="px-4 py-3 hidden sm:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($helps as $help)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-400 dark:text-gray-500">#{{ $help->id }}</td>
                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white">{{ $help->title }}</td>
                            <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300">{{ $help->customer->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">{{ $help->city->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    {{ $help->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white text-right whitespace-nowrap">
                                Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 hidden lg:table-cell whitespace-nowrap">
                                {{ $help->created_at?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.helps') }}"
                                    class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center text-gray-400">
                                <p class="text-sm font-medium">Tidak ada bantuan disetujui</p>
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
