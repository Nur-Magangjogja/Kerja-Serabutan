<!-- Jadwal Permintaan (Tanggal & Jam) -->
<div id="group-schedule">
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            <span class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 000 2h8a1 1 0 100-2H6zM4 6a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                </svg>
                Jadwalkan Waktu Bantuan
                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional)</span>
            </span>
        </label>
        @if ($scheduled_date || $scheduled_time)
            <button type="button" wire:click="clearSchedule" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-semibold px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 transition cursor-pointer">
                ✕ Hapus Jadwal
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-2">
        <!-- Input Tanggal -->
        <div>
            <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
            <input type="date" wire:model.live="scheduled_date" min="{{ date('Y-m-d') }}"
                class="w-full px-3 py-2.5 text-sm rounded-lg border @error('scheduled_date') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            @error('scheduled_date')
                <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
            @enderror
        </div>

        <!-- Input Jam / Waktu -->
        <div>
            <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1 flex items-center justify-between">
                <span>Jam / Pukul</span>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">{{ $timezoneLabel }}</span>
            </label>
            <input type="time" wire:model.live="scheduled_time"
                class="w-full px-3 py-2.5 text-sm rounded-lg border @error('scheduled_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            @error('scheduled_time')
                <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Quick Schedule Presets -->
    <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-hide">
        <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 flex-shrink-0">Pilihan:</span>
        <button type="button" wire:click="setPresetSchedule('plus_2h')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
            +2 Jam Lagi
        </button>
        <button type="button" wire:click="setPresetSchedule('tomorrow_morning')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
            Besok Pagi (08:00)
        </button>
        <button type="button" wire:click="setPresetSchedule('tomorrow_afternoon')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
            Besok Siang (13:00)
        </button>
    </div>

    @if ($scheduled_date)
        <!-- Opsi Jeda Waktu Keberangkatan Mitra -->
        <div class="mt-3 p-3 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200/60 dark:border-blue-800/50">
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-blue-900 dark:text-blue-200">
                    Waktu Mulai Berangkat Mitra
                </label>
                <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/60 px-2 py-0.5 rounded-full">
                    Jeda Keberangkatan
                </span>
            </div>
            <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 mb-2">
                Pilih berapa menit sebelum jadwal tugas mitra dapat mulai berangkat menuju lokasi Anda:
            </p>
            <div class="grid grid-cols-5 gap-1.5">
                @foreach([
                    30 => '30 Mnt',
                    45 => '45 Mnt',
                    60 => '1 Jam',
                    90 => '1.5 Jam',
                    120 => '2 Jam'
                ] as $minutes => $label)
                    <button type="button" wire:click="$set('early_departure_minutes', {{ $minutes }})"
                        class="py-1.5 px-1 text-[11px] font-bold rounded-lg border transition text-center cursor-pointer {{ (int)$early_departure_minutes === $minutes ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 italic">
                *Tombol keberangkatan mitra akan dikunci hingga {{ $early_departure_minutes }} menit sebelum jadwal pelaksanaan.
            </p>
        </div>
    @endif
</div>

<!-- Batas Waktu Kadaluwarsa Pencarian Rekan Jasa -->
<div id="group-expiry" class="p-3.5 bg-gray-50/80 dark:bg-gray-800/40 rounded-xl border border-gray-200/70 dark:border-gray-700/70">
    <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            <span class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Batas Waktu Pencarian Rekan Jasa
                <span class="text-red-500 ml-1">*</span>
            </span>
        </label>
        <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-900/40">
            Batal Otomatis & Refund 100%
        </span>
    </div>

    <!-- 4 Pilihan Cepat Minimalis -->
    <div class="grid grid-cols-4 gap-1.5 mt-2">
        <button type="button" wire:click="setExpiryOption('1_hour')"
            class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '1_hour' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            1 Jam
        </button>

        <button type="button" wire:click="setExpiryOption('6_hours')"
            class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '6_hours' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            6 Jam
        </button>

        <button type="button" wire:click="setExpiryOption('24_hours')"
            class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === '24_hours' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            24 Jam
        </button>

        <button type="button" wire:click="setExpiryOption('custom')"
            class="py-2 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ $expiry_option === 'custom' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            Kustom
        </button>
    </div>

    <!-- Status Ringkasan Batas Waktu -->
    <div class="mt-2.5 flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/90 p-2 rounded-lg border border-gray-200/70 dark:border-gray-700/60">
        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Batal otomatis jika belum ada mitra: <strong class="text-blue-600 dark:text-blue-400 font-semibold">{{ $this->expiryPreview }}</strong></span>
    </div>
</div>
