<div id="user-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" id="modal-backdrop"></div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full z-10 overflow-hidden border border-gray-100 dark:border-gray-700 max-h-[90vh] flex flex-col">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($user->role) }} • ID: #{{ $user->id }}</p>
                </div>
            </div>
            <button type="button" id="modal-close-btn" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-5">
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

            {{-- Account Information Grid --}}
            <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div><p class="text-gray-400 dark:text-gray-500">Email</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $user->email }}</p></div>
                <div><p class="text-gray-400 dark:text-gray-500">Kota Operasional</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $user->city_name ?? '—' }}</p></div>
                <div><p class="text-gray-400 dark:text-gray-500">Status Akun</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'blocked' ? 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400' : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                <div><p class="text-gray-400 dark:text-gray-500">Terdaftar</p><p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ optional($user->created_at)->format('d M Y, H:i') ?? '—' }}</p></div>
                <div><p class="text-gray-400 dark:text-gray-500">Nomor Telepon</p><p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->phone ?? '—' }}</p></div>
                <div><p class="text-gray-400 dark:text-gray-500">Status KTP</p><p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->verified ? 'Terverifikasi' : 'Belum Verifikasi' }}</p></div>
            </div>

            {{-- KTP Details & Photos --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700 space-y-2.5 text-xs">
                    <h4 class="font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-2">Data KTP</h4>
                    <div><span class="text-gray-400">NIK:</span> <span class="font-mono font-bold text-gray-800 dark:text-gray-100">{{ $user->nik ?? '—' }}</span></div>
                    <div><span class="text-gray-400">Nama di KTP:</span> <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $user->full_name ?? $user->name ?? '—' }}</span></div>
                    <div><span class="text-gray-400">Jenis Kelamin:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->gender ?? '—' }}</span></div>
                    <div><span class="text-gray-400">Alamat KTP:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->full_address }}</span></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Selfie --}}
                    <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Selfie</p>
                        @if(!empty($selfieUrl))
                            <a href="{{ $selfieUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                <img src="{{ $selfieUrl }}" alt="Selfie" class="w-full h-28 object-cover hover:scale-105 transition duration-200" />
                            </a>
                        @else
                            <div class="w-full h-28 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xs text-gray-400">Tidak ada</div>
                        @endif
                    </div>

                    {{-- KTP --}}
                    <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Foto KTP</p>
                        @if(!empty($ktpUrl))
                            <a href="{{ $ktpUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 bg-white">
                                <img src="{{ $ktpUrl }}" alt="KTP" class="w-full h-28 object-contain hover:scale-105 transition duration-200 bg-white dark:bg-gray-900" />
                            </a>
                        @else
                            <div class="w-full h-28 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xs text-gray-400">Tidak ada</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="button" id="modal-close-btn-2" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>