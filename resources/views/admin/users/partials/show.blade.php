<div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <div class="fixed inset-0 bg-black bg-opacity-40" id="modal-backdrop"></div>

    <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Detail Pengguna</h3>
            <button type="button" id="modal-close-btn" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6">
            @php
                use Illuminate\Support\Facades\Storage;
                $ktpUrl = $user->ktp_url ?? null;
                if (!$ktpUrl) {
                    if (!empty($user->ktp_path)) $ktpUrl = Storage::url($user->ktp_path);
                    elseif (!empty($user->ktp_photo)) $ktpUrl = Storage::url($user->ktp_photo);
                }
                $selfieUrl = $user->selfie_url ?? null;
                if (!$selfieUrl && !empty($user->selfie_photo)) $selfieUrl = Storage::url($user->selfie_photo);
            @endphp

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Nama</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Role</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($user->role) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Kota</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->city_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Terdaftar</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ optional($user->created_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status Akun</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->status }}</dd>
                </div>
            </dl>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-lg p-4 border border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Informasi KTP</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-700">
                            <div>
                                <div class="text-xs text-gray-500">NIK</div>
                                <div class="font-medium">{{ $user->nik ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Nama pada KTP</div>
                                <div class="font-medium">{{ $user->full_name ?? $user->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Tempat, Tgl Lahir</div>
                                <div class="font-medium">{{ $user->place_of_birth ?? '-' }}, {{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Jenis Kelamin</div>
                                <div class="font-medium">{{ $user->gender ?? '-' }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-xs text-gray-500">Alamat</div>
                                <div class="font-medium">{{ $user->address ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 border border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Foto Selfie</h4>
                        @if(!empty($selfieUrl))
                            <a href="{{ $selfieUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-md overflow-hidden border border-gray-200 hover:border-primary-500 transition shadow-sm group">
                                <img src="{{ $selfieUrl }}" alt="Selfie" class="w-full h-40 object-cover group-hover:scale-105 transition duration-200" />
                            </a>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ $selfieUrl }}" target="_blank" class="text-sm font-medium text-primary-600 hover:text-primary-800">Buka di tab baru</a>
                                <a href="{{ $selfieUrl }}" download class="text-sm font-medium text-gray-700 hover:text-gray-900">Unduh</a>
                            </div>
                        @else
                            <div class="w-full h-40 bg-gray-50 rounded-md flex items-center justify-center text-gray-400 border-2 border-dashed border-gray-200">
                                <span class="text-sm">Tidak ada foto selfie</span>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg p-4 border border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Foto KTP</h4>
                        @if(!empty($ktpUrl))
                            <a href="{{ $ktpUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-md overflow-hidden border border-gray-200 hover:border-primary-500 transition shadow-sm group bg-white">
                                <img src="{{ $ktpUrl }}" alt="KTP" class="w-full h-40 object-contain group-hover:scale-105 transition duration-200 bg-white" />
                            </a>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ $ktpUrl }}" target="_blank" class="text-sm font-medium text-primary-600 hover:text-primary-800">Buka di tab baru</a>
                                <a href="{{ $ktpUrl }}" download class="text-sm font-medium text-gray-700 hover:text-gray-900">Unduh</a>
                            </div>
                        @else
                            <div class="w-full h-40 bg_gray-50 rounded-md flex items-center justify-center text-gray-400 border-2 border-dashed border-gray-200">
                                <span class="text-sm">Tidak ada foto KTP</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 text-right">
            <button type="button" id="modal-close-btn-2"
                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl">Tutup</button>
        </div>
    </div>
</div>