<div>
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Verifikasi KTP Mitra</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tinjau dan verifikasi dokumen identitas mitra baru</p>
        </div>
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 rounded-lg">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            Memproses...
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.debounce.400ms="search" placeholder="Cari nama, email, atau NIK..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <select wire:model="statusFilter"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Terverifikasi</option>
                <option value="rejected">Ditolak</option>
            </select>

            <select wire:model="perPage"
                class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>
        </div>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mitra</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Dokumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Waktu</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($verifications as $v)
                    @php
                    $statusConfig = match($v->status ?? '') {
                        'approved' => ['class' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400', 'label' => 'Terverifikasi'],
                        'rejected' => ['class' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',       'label' => 'Ditolak'],
                        default    => ['class' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',   'label' => 'Pending'],
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="px-4 py-3.5 text-xs font-medium text-gray-400 dark:text-gray-500">#{{ $v->id }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($v->full_name ?? ($v->name ?? 'U'), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $v->full_name ?? ($v->name ?? '—') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $v->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 font-mono text-xs hidden md:table-cell">{{ $v->nik ?? '—' }}</td>
                        <td class="px-4 py-3.5 hidden sm:table-cell">
                            <div class="flex items-center gap-1.5">
                                @if(!empty($v->selfie_url))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Selfie</span>
                                @endif
                                @if(!empty($v->ktp_url))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">KTP</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 hidden lg:table-cell whitespace-nowrap">
                            {{ optional($v->created_at)->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <button type="button" wire:click="viewKtp({{ $v->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada data verifikasi</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba ubah filter pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>

    {{-- ===== Detail Modal ===== --}}
    @if($showModal && $selected)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" role="dialog" aria-modal="true" wire:click="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl shadow-2xl max-h-[90vh] flex flex-col overflow-hidden" wire:click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($selected->full_name ?? ($selected->name ?? 'U'), 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $selected->full_name ?? ($selected->name ?? '—') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">ID: #{{ $selected->id }} • {{ ucfirst($selected->role ?? 'customer') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(($selected->status ?? '') === 'approved')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">Terverifikasi</span>
                    @elseif(($selected->status ?? '') === 'rejected')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400">Ditolak</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">Pending</span>
                    @endif
                    <button type="button" wire:click="closeModal" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Info Pribadi Section --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-3">Informasi Pribadi</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">NIK</p><p class="text-sm font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $selected->nik ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Nama Lengkap</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->full_name ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Tempat Lahir</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->place_of_birth ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Tanggal Lahir</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->date_of_birth ? $selected->date_of_birth->format('d M Y') : '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Jenis Kelamin</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->gender ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Agama</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->religion ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Status Pernikahan</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->marital_status ?? '—' }}</p></div>
                                <div><p class="text-xs text-gray-400 dark:text-gray-500">Pekerjaan</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $selected->occupation ?? '—' }}</p></div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-3">Alamat Lengkap</h4>
                            <p class="text-sm text-gray-800 dark:text-gray-200 mb-2">{{ $selected->address ?? '—' }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                <div><span class="text-gray-400">RT/RW:</span> <span class="font-medium text-gray-700 dark:text-gray-300">{{ $selected->rt ?? '-' }}/{{ $selected->rw ?? '-' }}</span></div>
                                <div><span class="text-gray-400">Kelurahan:</span> <span class="font-medium text-gray-700 dark:text-gray-300">{{ $selected->kelurahan ?? '-' }}</span></div>
                                <div><span class="text-gray-400">Kecamatan:</span> <span class="font-medium text-gray-700 dark:text-gray-300">{{ $selected->kecamatan ?? '-' }}</span></div>
                                <div><span class="text-gray-400">Kota:</span> <span class="font-medium text-gray-700 dark:text-gray-300">{{ $selected->city ?? '-' }}</span></div>
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen Section --}}
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-3">Foto Selfie</h4>
                            @if(!empty($selected->selfie_url))
                                <a href="{{ $selected->selfie_url }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 group">
                                    <img src="{{ $selected->selfie_url }}" alt="Selfie" class="w-full h-44 object-cover group-hover:scale-105 transition duration-300" />
                                </a>
                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <a href="{{ $selected->selfie_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">Buka tab baru</a>
                                    <a href="{{ $selected->selfie_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline">Unduh</a>
                                </div>
                            @else
                                <div class="w-full h-44 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xs text-gray-400">Tidak ada selfie</div>
                            @endif
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-3">Foto KTP</h4>
                            @if(!empty($selected->ktp_url))
                                <a href="{{ $selected->ktp_url }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 group bg-white">
                                    <img src="{{ $selected->ktp_url }}" alt="KTP" class="w-full h-44 object-contain group-hover:scale-105 transition duration-300 bg-white dark:bg-gray-900" />
                                </a>
                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <a href="{{ $selected->ktp_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">Buka tab baru</a>
                                    <a href="{{ $selected->ktp_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline">Unduh</a>
                                </div>
                            @else
                                <div class="w-full h-44 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xs text-gray-400">Tidak ada foto KTP</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Reject form inline inside modal if triggered --}}
                @if($showRejectModal)
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 space-y-3">
                    <h4 class="text-sm font-bold text-rose-800 dark:text-rose-300">Alasan Penolakan</h4>
                    <textarea wire:model.defer="rejectReason" maxlength="500" rows="3" placeholder="Contoh: Foto blur / NIK tidak cocok"
                        class="w-full px-3 py-2 text-sm border border-rose-200 dark:border-rose-700 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    @error('rejectReason') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancelReject" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Batal</button>
                        <button type="button" wire:click="confirmReject" class="px-3 py-1.5 text-xs font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700">Konfirmasi Tolak</button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-between gap-3">
                <span class="text-xs text-gray-400 dark:text-gray-500">Pastikan dokumen terlihat jelas dan sesuai data</span>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Tutup
                    </button>
                    @if(($selected->status ?? '') !== 'approved')
                        <button type="button" wire:click="openRejectModal({{ $selected->id }})" class="px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                            Tolak
                        </button>
                        <button type="button" wire:click="approveKtp({{ $selected->id }})" class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            Verifikasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
