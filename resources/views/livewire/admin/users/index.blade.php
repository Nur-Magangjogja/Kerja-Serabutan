@extends('layouts.admin')

@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data mitra, customer, dan admin di wilayah Anda.</p>
        </div>
        <div>
            <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Tambah Pengguna
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-80">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Cari nama, email, atau no. telp..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="roleFilter" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    <option value="customer">Customer</option>
                    <option value="mitra">Mitra</option>
                    <option value="admin">Admin</option>
                </select>

                <select wire:model.live="statusFilter" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                    <option value="blocked">Diblokir</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-100">
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Kota</th>
                        <th class="px-6 py-3.5">Rating / Aktivitas</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Bergabung</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/75 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-sm flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->phone ?: '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'mitra')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Mitra</span>
                                @elseif($user->role === 'customer')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">Customer</span>
                                @elseif($user->role === 'admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-200">Admin</span>
                                @elseif($user->role === 'super_admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200">Super Admin</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst($user->role) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $user->city_name ?? $user->city?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'mitra')
                                    <div class="flex items-center gap-1 text-xs text-amber-600 font-semibold">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span>{{ $user->average_rating ?: 'Belum ada rating' }}</span>
                                    </div>
                                @endif
                                <div class="text-xs text-gray-400 mt-0.5">{{ $user->helps_count ?? 0 }} bantuan</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status === 'active')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @elseif($user->status === 'blocked')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200">Diblokir</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $user->created_at?->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="openEditModal({{ $user->id }})"
                                    class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                                    Edit
                                </button>
                                <button wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus pengguna ini?"
                                    class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                Tidak ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- User Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Role</label>
                            <select wire:model="role" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="customer">Customer</option>
                                <option value="mitra">Mitra</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kota</label>
                            <select wire:model="city_id" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Kota --</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                            <select wire:model="status" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                                <option value="blocked">Diblokir</option>
                            </select>
                            @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password {{ $editMode ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                        <input type="password" wire:model="password" class="w-full px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
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
