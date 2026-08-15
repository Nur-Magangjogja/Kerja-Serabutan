@extends('layouts.admin')

@section('page-title', 'Manajemen Kota & Wilayah')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Kota & Wilayah</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola cakupan area layanan dan penugasan admin per kota.</p>
        </div>
        <div>
            <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kota
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="relative w-full md:w-80">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Cari nama kota atau provinsi..."
                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-100">
                        <th class="px-6 py-3.5">Nama Kota</th>
                        <th class="px-6 py-3.5">Provinsi</th>
                        <th class="px-6 py-3.5">Admin Penanggung Jawab</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($cities as $city)
                        <tr class="hover:bg-gray-50/75 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $city->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $city->province }}
                            </td>
                            <td class="px-6 py-4">
                                @if($city->admin)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        {{ $city->admin->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($city->is_active)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="openEditModal({{ $city->id }})"
                                    class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                                    Edit
                                </button>
                                <button wire:click="deleteCity({{ $city->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus kota ini?"
                                    class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                Belum ada data kota/wilayah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cities->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $cities->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $editMode ? 'Edit Kota' : 'Tambah Kota' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Kota/Kabupaten</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Provinsi</label>
                        <input type="text" wire:model="province" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        @error('province') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Admin Wilayah</label>
                        <select wire:model="admin_id" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Admin (Opsional) --</option>
                            @foreach($admins as $adm)
                                <option value="{{ $adm->id }}">{{ $adm->name }} ({{ $adm->email }})</option>
                            @endforeach
                        </select>
                        @error('admin_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="city_is_active" class="rounded text-blue-600 focus:ring-blue-500">
                        <label for="city_is_active" class="text-xs text-gray-700 font-medium">Kota Aktif untuk Layanan</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
