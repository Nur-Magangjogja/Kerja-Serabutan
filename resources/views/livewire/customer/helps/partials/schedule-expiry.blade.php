<!-- Jadwal Permintaan (Tanggal & Range Waktu) -->
<div id="group-schedule" class="space-y-3">
    <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Jadwal & Range Waktu Bantuan
                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional & Tidak Wajib Diisi)</span>
            </span>
        </label>
        @if ($scheduled_date || $scheduled_time)
            <button type="button" wire:click="clearSchedule" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-semibold px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 transition cursor-pointer">
                ✕ Hapus Jadwal
            </button>
        @endif
    </div>

    <!-- Bagian 1: Waktu Pelaksanaan Tugas -->
    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                Waktu Pelaksanaan Tugas (Target Mulai)
            </span>
            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">{{ $timezoneLabel }}</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <!-- Input Tanggal -->
            <div>
                <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Tugas</label>
                <input type="date" wire:model.live="scheduled_date" min="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2 text-xs rounded-lg border @error('scheduled_date') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                @error('scheduled_date')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <!-- Input Jam / Waktu (24 Jam) -->
            <div>
                <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Jam Mulai (24 Jam)</label>
                <div x-data="{
                    rawInput: @entangle('scheduled_time').live,
                    dateValue: @entangle('scheduled_date').live,
                    todayString() {
                        const d = new Date();
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        return `${yyyy}-${mm}-${dd}`;
                    },
                    ensureTodayDate() {
                        if (!this.dateValue) {
                            this.dateValue = this.todayString();
                        }
                    },
                    onInput(e) {
                        let v = e.target.value.replace(/[^0-9]/g, '');
                        if (v.length === 0) {
                            this.rawInput = null;
                            return;
                        }
                        
                        this.ensureTodayDate();

                        if (v.length > 4) v = v.slice(0, 4);

                        if (v.length <= 2) {
                            let h = parseInt(v, 10);
                            if (h > 23) v = '23';
                            this.rawInput = v;
                        } else {
                            let h = v.slice(0, 2);
                            let m = v.slice(2, 4);
                            let hNum = parseInt(h, 10);
                            if (hNum > 23) h = '23';
                            let mNum = parseInt(m, 10);
                            if (mNum > 59) m = '59';
                            this.rawInput = h + ':' + m;
                        }
                    },
                    onBlur() {
                        if (!this.rawInput) return;
                        let digits = String(this.rawInput).replace(/[^0-9]/g, '');
                        if (digits.length === 0) {
                            this.rawInput = null;
                            return;
                        }
                        
                        this.ensureTodayDate();

                        if (digits.length === 1) {
                            this.rawInput = '0' + digits + ':00';
                        } else if (digits.length === 2) {
                            let h = Math.min(23, parseInt(digits, 10));
                            this.rawInput = String(h).padStart(2, '0') + ':00';
                        } else if (digits.length === 3) {
                            let h = digits.slice(0, 2);
                            let m = digits.slice(2) + '0';
                            let hNum = Math.min(23, parseInt(h, 10));
                            let mNum = Math.min(59, parseInt(m, 10));
                            this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                        } else if (digits.length >= 4) {
                            let h = digits.slice(0, 2);
                            let m = digits.slice(2, 4);
                            let hNum = Math.min(23, parseInt(h, 10));
                            let mNum = Math.min(59, parseInt(m, 10));
                            this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                        }
                    }
                }" class="relative">
                    <div class="relative flex items-center">
                        <input type="text"
                            inputmode="numeric"
                            maxlength="5"
                            placeholder="Contoh: 09:00"
                            :value="rawInput || ''"
                            @input="onInput($event)"
                            @blur="onBlur()"
                            class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border @error('scheduled_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 font-semibold font-mono tracking-wider">
                        
                        <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                @error('scheduled_time')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Quick Schedule Presets -->
        <div class="flex items-center gap-1.5 pt-1 overflow-x-auto pb-0.5 scrollbar-hide">
            <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 flex-shrink-0">Pilihan Cepat:</span>
            <button type="button" wire:click="setPresetSchedule('plus_2h')" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-gray-100 dark:bg-gray-700/70 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                +2 Jam Lagi
            </button>
            <button type="button" wire:click="setPresetSchedule('tomorrow_morning')" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-gray-100 dark:bg-gray-700/70 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                Besok Pagi (08:00)
            </button>
            <button type="button" wire:click="setPresetSchedule('tomorrow_afternoon')" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-gray-100 dark:bg-gray-700/70 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 text-gray-600 dark:text-gray-300 transition border border-gray-200/60 dark:border-gray-700 active:scale-95 flex-shrink-0 cursor-pointer">
                Besok Siang (13:00)
            </button>
        </div>
    </div>

    @if ($scheduled_date)
        <!-- Bagian 2: Waktu Mulai Muncul di Radar / Pool Mitra -->
        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                    Waktu Sebarkan Panggilan Bantuan
                </span>
                <span class="text-[10px] text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/40 px-2 py-0.5 rounded-full font-medium">
                    Awal Kemunculan
                </span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                Tentukan kapan pesanan mulai disiarkan dan dicari oleh mitra:
            </p>

            <div class="grid grid-cols-2 gap-2 mt-1">
                <!-- Option Now -->
                <button type="button" wire:click="$set('publish_mode', 'now')"
                    class="p-2.5 rounded-lg border text-left transition cursor-pointer {{ $publish_mode === 'now' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/40 ring-1 ring-blue-600 text-blue-950 dark:text-blue-100' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/60 hover:bg-gray-100 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300' }}">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-xs font-bold flex items-center gap-1">
                            <span>⚡</span> Mulai Sekarang
                        </span>
                        @if ($publish_mode === 'now')
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">
                        Langsung cari mitra sejak pesanan dibuat
                    </p>
                </button>

                <!-- Option Custom Time -->
                <button type="button" wire:click="$set('publish_mode', 'custom')"
                    class="p-2.5 rounded-lg border text-left transition cursor-pointer {{ $publish_mode === 'custom' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/40 ring-1 ring-blue-600 text-blue-950 dark:text-blue-100' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/60 hover:bg-gray-100 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300' }}">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-xs font-bold flex items-center gap-1">
                            <span>⏰</span> Jam Tertentu
                        </span>
                        @if ($publish_mode === 'custom')
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">
                        Tentukan jam awal muncul di pool
                    </p>
                </button>
            </div>

            @if ($publish_mode === 'custom')
                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 flex items-center justify-between">
                        <span>Jam Mulai Muncul di Radar (24 Jam)</span>
                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">{{ $timezoneLabel }}</span>
                    </label>
                    <div x-data="{
                        rawInput: @entangle('publish_time').live,
                        dateValue: @entangle('scheduled_date').live,
                        todayString() {
                            const d = new Date();
                            const yyyy = d.getFullYear();
                            const mm = String(d.getMonth() + 1).padStart(2, '0');
                            const dd = String(d.getDate()).padStart(2, '0');
                            return `${yyyy}-${mm}-${dd}`;
                        },
                        ensureTodayDate() {
                            if (!this.dateValue) {
                                this.dateValue = this.todayString();
                            }
                        },
                        onInput(e) {
                            let v = e.target.value.replace(/[^0-9]/g, '');
                            if (v.length === 0) {
                                this.rawInput = null;
                                return;
                            }
                            this.ensureTodayDate();
                            if (v.length > 4) v = v.slice(0, 4);
                            if (v.length <= 2) {
                                let h = parseInt(v, 10);
                                if (h > 23) v = '23';
                                this.rawInput = v;
                            } else {
                                let h = v.slice(0, 2);
                                let m = v.slice(2, 4);
                                let hNum = parseInt(h, 10);
                                if (hNum > 23) h = '23';
                                let mNum = parseInt(m, 10);
                                if (mNum > 59) m = '59';
                                this.rawInput = h + ':' + m;
                            }
                        },
                        onBlur() {
                            if (!this.rawInput) return;
                            let digits = String(this.rawInput).replace(/[^0-9]/g, '');
                            if (digits.length === 0) { this.rawInput = null; return; }
                            this.ensureTodayDate();
                            if (digits.length === 1) {
                                this.rawInput = '0' + digits + ':00';
                            } else if (digits.length === 2) {
                                let h = Math.min(23, parseInt(digits, 10));
                                this.rawInput = String(h).padStart(2, '0') + ':00';
                            } else if (digits.length === 3) {
                                let h = digits.slice(0, 2);
                                let m = digits.slice(2) + '0';
                                let hNum = Math.min(23, parseInt(h, 10));
                                let mNum = Math.min(59, parseInt(m, 10));
                                this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                            } else if (digits.length >= 4) {
                                let h = digits.slice(0, 2);
                                let m = digits.slice(2, 4);
                                let hNum = Math.min(23, parseInt(h, 10));
                                let mNum = Math.min(59, parseInt(m, 10));
                                this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                            }
                        }
                    }" class="relative">
                        <div class="relative flex items-center">
                            <input type="text"
                                inputmode="numeric"
                                maxlength="5"
                                placeholder="Contoh: 07:00"
                                :value="rawInput || ''"
                                @input="onInput($event)"
                                @blur="onBlur()"
                                class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border @error('publish_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 font-semibold font-mono tracking-wider">
                            
                            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    @error('publish_time')
                        <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 italic">
                        *Maksimal pukul <strong class="text-blue-600 dark:text-blue-400">{{ $this->scheduleTimeline['departure_time'] ?? '--:--' }} {{ $timezoneLabel }}</strong> (tidak boleh melebihi waktu keberangkatan mitra).
                    </p>
                </div>
            @endif
        </div>

        <!-- Bagian 3: Jeda Waktu Keberangkatan Mitra -->
        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">3</span>
                    Jeda Waktu Keberangkatan Mitra
                </span>
                <span class="text-[10px] font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/60 px-2 py-0.5 rounded-full">
                    {{ (int)$early_departure_minutes === 0 ? '0 Mnt (Langsung)' : $early_departure_minutes . ' Menit Sebelum' }}
                </span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                Berapa lama sebelum jam pelaksanaan mitra dapat mulai berangkat menuju lokasi Anda:
            </p>
            <div class="grid grid-cols-6 gap-1">
                @foreach([
                    0   => '0 Mnt',
                    30  => '30 Mnt',
                    45  => '45 Mnt',
                    60  => '1 Jam',
                    90  => '1.5 Jam',
                    120 => '2 Jam'
                ] as $minutes => $label)
                    <button type="button" wire:click="$set('early_departure_minutes', {{ $minutes }})"
                        class="py-1.5 px-0.5 text-[11px] font-bold rounded-lg border transition text-center cursor-pointer {{ (int)$early_departure_minutes === $minutes ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-400 dark:text-gray-500 italic">
                *Tombol mulai berangkat mitra akan dibuka pada waktu keberangkatan yang dihitung.
            </p>
        </div>

        <!-- Bagian 4: Visualisasi Alur Range Jadwal (Live 3-Step Range Timeline Card) -->
        <div class="p-3.5 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200/70 dark:border-blue-800/60 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-900 dark:text-blue-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Ringkasan Alur & Range Waktu
                </span>
                <span class="text-[10px] text-blue-700 dark:text-blue-300 font-semibold">
                    {{ $this->scheduleTimeline['target_date'] ?? '' }}
                </span>
            </div>

            <!-- 3-Step Flow Items -->
            <div class="space-y-2 pt-1 text-xs">
                <!-- Step 1: Siar / Muncul di Radar -->
                <div class="flex items-start gap-2.5 p-2 bg-white dark:bg-gray-800/90 rounded-lg border border-blue-100 dark:border-blue-900/40">
                    <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">1</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Mulai Muncul di Pool / Radar:</span>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold">{{ $publish_mode === 'now' ? 'SEGERA' : 'TERJADWAL' }}</span>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white block">{{ $this->scheduleTimeline['publish_label'] }}</span>
                    </div>
                </div>

                <!-- Step 2: Mitra Berangkat -->
                <div class="flex items-start gap-2.5 p-2 bg-white dark:bg-gray-800/90 rounded-lg border border-amber-100 dark:border-amber-900/40">
                    <div class="w-5 h-5 rounded-full bg-amber-100 dark:bg-blue-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">2</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Mitra Mulai Berangkat:</span>
                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">{{ (int)$early_departure_minutes === 0 ? 'LANGSUNG' : '-' . $early_departure_minutes . ' MNT' }}</span>
                        </div>
                        <span class="font-bold text-amber-800 dark:text-amber-300 block">{{ $this->scheduleTimeline['departure_label'] }}</span>
                    </div>
                </div>

                <!-- Step 3: Pelaksanaan Tugas -->
                <div class="flex items-start gap-2.5 p-2 bg-white dark:bg-gray-800/90 rounded-lg border border-emerald-100 dark:border-emerald-900/40">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5">3</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Target Pelaksanaan Tugas:</span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">PELAKSANAAN</span>
                        </div>
                        <span class="font-bold text-emerald-800 dark:text-emerald-300 block">{{ $this->scheduleTimeline['target_label'] }}</span>
                    </div>
                </div>
            </div>
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

    @error('expiry_option')
        <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block font-medium">{{ $message }}</span>
    @enderror

    @if ($expiry_option === 'custom')
        <div class="mt-2.5 p-3 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200/60 dark:border-blue-800/50 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Batas</label>
                    <input type="date" wire:model.live="custom_expiry_date" min="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 text-xs rounded-lg border @error('custom_expiry_date') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    @error('custom_expiry_date')
                        <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Jam (24 Jam)</label>
                    <div x-data="{
                        rawInput: @entangle('custom_expiry_time').live,
                        dateValue: @entangle('custom_expiry_date').live,
                        todayString() {
                            const d = new Date();
                            const yyyy = d.getFullYear();
                            const mm = String(d.getMonth() + 1).padStart(2, '0');
                            const dd = String(d.getDate()).padStart(2, '0');
                            return `${yyyy}-${mm}-${dd}`;
                        },
                        ensureTodayDate() {
                            if (!this.dateValue) {
                                this.dateValue = this.todayString();
                            }
                        },
                        onInput(e) {
                            let v = e.target.value.replace(/[^0-9]/g, '');
                            if (v.length === 0) {
                                this.rawInput = null;
                                return;
                            }
                            this.ensureTodayDate();
                            if (v.length > 4) v = v.slice(0, 4);
                            if (v.length <= 2) {
                                let h = parseInt(v, 10);
                                if (h > 23) v = '23';
                                this.rawInput = v;
                            } else {
                                let h = v.slice(0, 2);
                                let m = v.slice(2, 4);
                                let hNum = parseInt(h, 10);
                                if (hNum > 23) h = '23';
                                let mNum = parseInt(m, 10);
                                if (mNum > 59) m = '59';
                                this.rawInput = h + ':' + m;
                            }
                        },
                        onBlur() {
                            if (!this.rawInput) return;
                            let digits = String(this.rawInput).replace(/[^0-9]/g, '');
                            if (digits.length === 0) { this.rawInput = null; return; }
                            this.ensureTodayDate();
                            if (digits.length === 1) {
                                this.rawInput = '0' + digits + ':00';
                            } else if (digits.length === 2) {
                                let h = Math.min(23, parseInt(digits, 10));
                                this.rawInput = String(h).padStart(2, '0') + ':00';
                            } else if (digits.length === 3) {
                                let h = digits.slice(0, 2);
                                let m = digits.slice(2) + '0';
                                let hNum = Math.min(23, parseInt(h, 10));
                                let mNum = Math.min(59, parseInt(m, 10));
                                this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                            } else if (digits.length >= 4) {
                                let h = digits.slice(0, 2);
                                let m = digits.slice(2, 4);
                                let hNum = Math.min(23, parseInt(h, 10));
                                let mNum = Math.min(59, parseInt(m, 10));
                                this.rawInput = String(hNum).padStart(2, '0') + ':' + String(mNum).padStart(2, '0');
                            }
                        }
                    }" class="relative">
                        <input type="text"
                            inputmode="numeric"
                            maxlength="5"
                            placeholder="Contoh: 23:59"
                            :value="rawInput || ''"
                            @input="onInput($event)"
                            @blur="onBlur()"
                            class="w-full px-3 py-2 text-xs rounded-lg border @error('custom_expiry_time') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-semibold font-mono tracking-wide">
                    </div>
                    @error('custom_expiry_time')
                        <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 italic">
                *Batas waktu pencarian harus melebihi waktu kemunculan order.
            </p>
        </div>
    @endif

    <!-- Status Ringkasan Batas Waktu -->
    <div class="mt-2.5 flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/90 p-2 rounded-lg border border-gray-200/70 dark:border-gray-700/60">
        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Batal otomatis jika belum ada mitra: <strong class="text-blue-600 dark:text-blue-400 font-semibold">{{ $this->expiryPreview }}</strong></span>
    </div>
</div>
