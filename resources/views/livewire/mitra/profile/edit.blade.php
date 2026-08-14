@props([])

<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 bg-black bg-opacity-40 backdrop-blur-sm">
            <div class="fixed inset-0 flex items-end md:items-center justify-center">
                <div
                    class="bg-white w-full md:max-w-3xl rounded-t-xl md:rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto p-4 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                @if(auth()->user()?->selfie_photo)
                                    <img src="{{ asset('storage/' . auth()->user()->selfie_photo) }}" alt="avatar"
                                        class="w-full h-full object-cover">
                                @else
                                    <span
                                        class="text-gray-500 font-bold">{{ strtoupper(substr(auth()->user()->name ?? '', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Edit Profil</h3>
                                <p class="text-xs text-gray-500">Perbarui informasi profil Anda</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1 flex flex-col items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-28 h-28 rounded-full overflow-hidden bg-white shadow-sm">
                                @if(auth()->user()?->selfie_photo)
                                    <img src="{{ asset('storage/' . auth()->user()->selfie_photo) }}" alt="avatar"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-2xl text-gray-400">
                                        {{ strtoupper(substr(auth()->user()->name ?? '', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <button type="button" onclick="(window.openMitraPhotoModal || (()=>{}))()"
                                class="text-sm px-3 py-2 bg-white border rounded shadow-sm">Ubah Foto</button>
                            <p class="text-xs text-gray-500 text-center">Foto profil akan ditampilkan pada halaman publik
                                mitra.
                            </p>
                        </div>

                        <div class="md:col-span-2 p-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama</label>
                                    <input type="text" wire:model.defer="name"
                                        class="mt-1 block w-full border rounded px-3 py-2" />
                                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telepon</label>
                                    <input type="text" wire:model.defer="phone"
                                        class="mt-1 block w-full border rounded px-3 py-2" />
                                    @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700">Kota</label>
                                <select wire:model.defer="city_id" class="mt-1 block w-full border rounded px-3 py-2">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @error('city_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700">Bio / Deskripsi singkat</label>
                                <textarea wire:model.defer="bio" class="mt-1 block w-full border rounded px-3 py-2"
                                    rows="4"></textarea>
                                @error('bio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <button type="button" wire:click="closeModal"
                                    class="px-4 py-2 border rounded">Batal</button>
                                <button type="button" wire:click="save"
                                    class="px-4 py-2 bg-primary-600 text-white rounded">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    @endif
    </div>