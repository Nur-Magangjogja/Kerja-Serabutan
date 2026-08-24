<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </span>
                Permintaan Hapus Akun
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola dan verifikasi permintaan penutupan akun dari Mitra & Customer secara aman
            </p>
        </div>

        @if($counts['pending'] > 0)
            <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800/60 px-4 py-2 rounded-xl text-amber-700 dark:text-amber-300 text-xs font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span>{{ $counts['pending'] }} Permintaan Menunggu Peninjauan</span>
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Status Filter Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-2 sm:gap-6">
        <button wire:click="setStatusFilter('pending')"
            class="pb-3 text-sm font-semibold transition relative cursor-pointer {{ $statusFilter === 'pending' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
            Menunggu Review
            @if($counts['pending'] > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                    {{ $counts['pending'] }}
                </span>
            @endif
        </button>

        <button wire:click="setStatusFilter('approved')"
            class="pb-3 text-sm font-semibold transition relative cursor-pointer {{ $statusFilter === 'approved' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
            Disetujui & Dihapus
            <span class="ml-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300">
                {{ $counts['approved'] }}
            </span>
        </button>

        <button wire:click="setStatusFilter('rejected')"
            class="pb-3 text-sm font-semibold transition relative cursor-pointer {{ $statusFilter === 'rejected' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
            Ditolak
            <span class="ml-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                {{ $counts['rejected'] }}
            </span>
        </button>

        <button wire:click="setStatusFilter('cancelled')"
            class="pb-3 text-sm font-semibold transition relative cursor-pointer {{ $statusFilter === 'cancelled' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
            Dibatalkan Pengguna
            <span class="ml-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $counts['cancelled'] }}
            </span>
        </button>

        <button wire:click="setStatusFilter('all')"
            class="pb-3 text-sm font-semibold transition relative cursor-pointer {{ $statusFilter === 'all' ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
            Semua ({{ $counts['all'] }})
        </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari berdasarkan nama, email, nomor HP, atau alasan..."
                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition dark:text-white" />
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="sm:w-48">
            <select wire:model.live="roleFilter"
                class="w-full py-2.5 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition dark:text-white">
                <option value="">Semua Peran</option>
                <option value="customer">Customer</option>
                <option value="mitra">Mitra</option>
            </select>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Peran & Wilayah</th>
                        <th class="px-6 py-4">Alasan Pengajuan</th>
                        <th class="px-6 py-4">Sisa Saldo & Tugas</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $req->user_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $req->user_email }}</div>
                                @if($req->user_phone)
                                    <div class="text-[11px] text-gray-400">{{ $req->user_phone }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $req->role === 'mitra' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                    {{ ucfirst($req->role) }}
                                </span>
                                @if($req->city_name)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $req->city_name }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2">{{ $req->reason }}</p>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs">
                                    <span class="font-semibold {{ $req->balance_at_request > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400' }}">
                                        Rp {{ number_format($req->balance_at_request, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($req->active_helps_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 mt-1">
                                        {{ $req->active_helps_count }} Tugas Berjalan
                                    </span>
                                @else
                                    <div class="text-[11px] text-green-600 dark:text-green-400">Tidak ada tugas aktif</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $req->created_at->translatedFormat('d M Y, H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $req->status_badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button wire:click="reviewRequest({{ $req->id }})"
                                    class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition cursor-pointer">
                                    {{ $req->status === 'pending' ? 'Tinjau Permintaan' : 'Lihat Detail' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-medium">Tidak ada permintaan penghapusan akun yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Review / Detail Modal -->
    @if($showReviewModal && $selectedRequest)
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Detail Permintaan Hapus Akun
                    </h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- User Info Cards -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Nama Pengguna:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $selectedRequest->user_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Email:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedRequest->user_email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Peran Akun:</span>
                        <span class="font-bold uppercase text-primary-600">{{ $selectedRequest->role }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Wilayah / Kota:</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $selectedRequest->city_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Diajukan Pada:</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $selectedRequest->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                </div>

                <!-- Financial & Active Task Live Audit -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl border {{ $liveBalance > 0 ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200' : 'bg-gray-50 border-gray-200 text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' }}">
                        <div class="text-[11px] font-medium opacity-80">Saldo Akun Saat Ini</div>
                        <div class="text-base font-bold mt-0.5">Rp {{ number_format($liveBalance, 0, ',', '.') }}</div>
                        @if($liveBalance > 0)
                            <div class="text-[10px] mt-1 font-semibold text-amber-700 dark:text-amber-300">⚠️ Ada sisa saldo belum ditarik</div>
                        @endif
                    </div>

                    <div class="p-3 rounded-xl border {{ $liveActiveTasks > 0 ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200' : 'bg-gray-50 border-gray-200 text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' }}">
                        <div class="text-[11px] font-medium opacity-80">Tugas Aktif Berjalan</div>
                        <div class="text-base font-bold mt-0.5">{{ $liveActiveTasks }} Tugas</div>
                        @if($liveActiveTasks > 0)
                            <div class="text-[10px] mt-1 font-semibold text-red-700 dark:text-red-300">⚠️ Selesaikan/batalkan tugas dulu</div>
                        @endif
                    </div>
                </div>

                <!-- User Reason -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300">Alasan dari Pengguna:</label>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl text-xs text-gray-800 dark:text-gray-200 leading-relaxed italic">
                        "{{ $selectedRequest->reason }}"
                    </div>
                </div>

                <!-- Admin Notes / Review Note -->
                @if($selectedRequest->status === 'pending')
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">Catatan Superadmin (Opsional / Alasan Penolakan):</label>
                        <textarea wire:model="adminNotes" rows="3"
                            placeholder="Contoh: Mohon lakukan penarikan saldo Anda (Rp {{ number_format($liveBalance, 0, ',', '.') }}) terlebih dahulu sebelum akun dapat dihapus."
                            class="w-full p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button wire:click="rejectRequest"
                            class="flex-1 py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300 font-bold rounded-xl text-xs transition cursor-pointer">
                            Tolak Permintaan
                        </button>

                        <button wire:click="approveRequest"
                            onclick="return confirm('PERINGATAN: Menyetujui permintaan ini akan menghapus akun pengguna secara permanen. Lanjutkan?')"
                            class="flex-1 py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition cursor-pointer shadow-sm">
                            Setujui & Hapus Akun
                        </button>
                    </div>
                @else
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl text-xs space-y-1 border border-gray-100 dark:border-gray-600">
                        <div class="flex justify-between font-medium">
                            <span class="text-gray-500">Status Permintaan:</span>
                            <span class="font-bold {{ $selectedRequest->status === 'approved' ? 'text-green-600' : ($selectedRequest->status === 'rejected' ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $selectedRequest->status_label }}
                            </span>
                        </div>
                        @if($selectedRequest->admin_notes)
                            <div class="pt-1">
                                <span class="text-gray-500">Catatan Admin:</span>
                                <p class="text-gray-800 dark:text-gray-200 mt-0.5">{{ $selectedRequest->admin_notes }}</p>
                            </div>
                        @endif
                        @if($selectedRequest->processed_at)
                            <div class="flex justify-between text-[11px] text-gray-400 pt-1">
                                <span>Diproses pada:</span>
                                <span>{{ $selectedRequest->processed_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
