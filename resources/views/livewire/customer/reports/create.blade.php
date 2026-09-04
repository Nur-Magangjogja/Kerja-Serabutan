<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 pb-24">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ $help_id ? route('customer.helps.detail', ['id' => $help_id]) : route('customer.dashboard') }}"
                    class="inline-flex items-center text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline mb-2">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">Formulir Laporan & Aduan Layanan</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Laporkan pelanggaran mitra atau ajukan klaim pengembalian dana</p>
            </div>
        </div>

        @if (session('message'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        {{-- Detail Bantuan Terkait & Masa Garansi 1x24 Jam --}}
        @if ($selectedHelp)
            <div class="bg-gradient-to-br from-white to-blue-50/40 dark:from-gray-800 dark:to-gray-800/80 rounded-2xl shadow-xs border border-blue-100 dark:border-gray-700 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3 mb-3 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                            📋
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Bantuan Terpilih</span>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedHelp->title }}</h3>
                        </div>
                    </div>

                    @php
                        $isWithin24H = $selectedHelp->completed_at && $selectedHelp->completed_at->addHours(24)->isFuture();
                    @endphp

                    @if ($isWithin24H)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            🛡️ Garansi 1x24 Jam Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                            Status: {{ ucfirst($selectedHelp->status) }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Rekan Jasa:</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $selectedHelp->mitra->name ?? 'Belum ada mitra' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Total Dana Tugas:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedHelp->total_amount ?: $selectedHelp->amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Waktu Selesai:</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ $selectedHelp->completed_at ? $selectedHelp->completed_at->format('d M Y, H:i') : '-' }}
                        </span>
                    </div>
                </div>

                @if ($isWithin24H)
                    <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200/80 dark:border-amber-900/60 text-[11px] text-amber-900 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Masa garansi pengembalian dana berlaku sampai <strong>{{ \Carbon\Carbon::parse($selectedHelp->completed_at)->addHours(24)->translatedFormat('d M Y, H:i') }} WIB</strong>. Jika mitra tidak bekerja atau berbohong, ajukan laporan untuk diperiksa admin.</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Form -->
        <form wire:submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700 p-5 sm:p-6 space-y-5">
            <!-- Bantuan yang Dilaporkan -->
            <div>
                <label for="help_id" class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                    Pilih Bantuan Terkait <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <select id="help_id" wire:model.live="help_id"
                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">-- Pilih Permintaan Bantuan Anda --</option>
                    @foreach ($helps as $h)
                        <option value="{{ $h->id }}">
                            {{ Str::limit($h->title, 40) }} (Rp {{ number_format($h->total_amount ?: $h->amount, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jenis Laporan -->
            <div>
                <label for="report_type" class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                    Jenis Laporan / Klaim <span class="text-red-500">*</span>
                </label>
                <select id="report_type" wire:model.live="report_type"
                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('report_type') border-red-500 @enderror">
                    <option value="">-- Pilih Jenis Laporan --</option>
                    @foreach ($reportTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('report_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Checkbox Klaim Pengembalian Dana (Refund) -->
            @php
                $isEligible = $this->isRefundEligible;
            @endphp
            <div class="p-3.5 rounded-2xl border transition {{ $isEligible ? 'bg-blue-50/60 dark:bg-blue-950/30 border-blue-200/70 dark:border-blue-900/40' : 'bg-gray-100/70 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 opacity-90' }}">
                <label class="flex items-start gap-2.5 {{ $isEligible ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                    <input type="checkbox" wire:model="is_refund_request" {{ $isEligible ? '' : 'disabled' }}
                        class="mt-0.5 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 disabled:opacity-40 disabled:cursor-not-allowed">
                    <div class="text-xs space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold {{ $isEligible ? 'text-blue-950 dark:text-blue-200' : 'text-gray-600 dark:text-gray-400' }}">
                                Ajukan Pengembalian Dana 100% (Refund)
                            </span>
                            @if (!$help_id)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    Pilih Bantuan Terlebih Dahulu
                                </span>
                            @elseif (!$isEligible)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    🔒 Garansi 1x24 Jam Sudah Habis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    🛡️ Garansi Aktif
                                </span>
                            @endif
                        </div>
                        @if (!$isEligible && $selectedHelp && in_array($selectedHelp->status, ['completed', 'selesai']))
                            <p class="text-rose-600 dark:text-rose-400 font-medium text-[11px]">
                                Masa garansi asuransi pengembalian dana 1x24 jam untuk bantuan ini telah berakhir (Waktu Selesai: {{ $selectedHelp->completed_at ? $selectedHelp->completed_at->format('d M Y, H:i') : '-' }} WIB). Anda tetap dapat mengirimkan laporan aduan untuk peninjauan admin, namun opsi klaim refund 100% sudah dinonaktifkan.
                            </p>
                        @else
                            <span class="text-blue-800/80 dark:text-blue-300 block">
                                Centang opsi ini jika Anda meminta dana pesanan dikembalikan utuh ke saldo akun Anda karena mitra tidak mengerjakan tugas atau melanggar kesepakatan.
                            </span>
                        @endif
                    </div>
                </label>
            </div>

            <!-- Judul Laporan -->
            <div>
                <label for="title" class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                    Judul Laporan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" wire:model="title"
                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('title') border-red-500 @enderror"
                    placeholder="Contoh: Mitra menekan selesai tapi tidak ada di lokasi">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Detail Pesan Laporan -->
            <div>
                <label for="message" class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                    Penjelasan Lengkap Masalah <span class="text-red-500">*</span>
                </label>
                <textarea id="message" wire:model="message" rows="5"
                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('message') border-red-500 @enderror"
                    placeholder="Jelaskan secara runtut apa yang terjadi, apa yang tidak diselesaikan oleh mitra, serta bukti percakapan atau kondisi di lapangan..."></textarea>
                @error('message')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-400">Minimal 10 karakter</p>
            </div>

            <!-- Upload Foto Bukti Pendukung -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                    Upload Foto Bukti Pendukung <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>

                @if ($evidence_photo)
                    <div class="relative rounded-xl overflow-hidden border-2 border-primary-500 bg-gray-50 dark:bg-gray-800 mb-2">
                        @php
                            $canPreview = false;
                            try {
                                $canPreview = method_exists($evidence_photo, 'temporaryUrl') && $evidence_photo->isPreviewable();
                            } catch (\Throwable $e) {
                                $canPreview = false;
                            }
                        @endphp
                        @if ($canPreview)
                            <img src="{{ $evidence_photo->temporaryUrl() }}" alt="Preview Bukti" class="w-full max-h-48 object-cover">
                        @else
                            <div class="w-full h-32 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 text-xs">
                                {{ $evidence_photo->getClientOriginalName() }}
                            </div>
                        @endif
                        <button type="button" wire:click="$set('evidence_photo', null)" class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full shadow hover:bg-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @else
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50/50 dark:hover:bg-gray-700 transition">
                        <div class="flex flex-col items-center justify-center p-3 text-center">
                            <svg class="w-7 h-7 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Upload Foto Bukti (Kondisi/Screenshot Chat)</p>
                            <p class="text-[10px] text-gray-400">PNG, JPG, JPEG (Maks. 5MB)</p>
                        </div>
                        <input type="file" wire:model="evidence_photo" accept="image/png, image/jpeg, image/jpg" class="hidden">
                    </label>
                @endif

                <div wire:loading wire:target="evidence_photo" class="text-xs text-blue-600 mt-1 flex items-center gap-1.5">
                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Mengunggah foto...
                </div>

                @error('evidence_photo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ $help_id ? route('customer.helps.detail', ['id' => $help_id]) : route('customer.dashboard') }}"
                    class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition flex items-center gap-1.5 disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="submit">Kirim Laporan & Aduan</span>
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-1">
                        <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Mengirim...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>