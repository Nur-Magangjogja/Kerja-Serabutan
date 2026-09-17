<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Header Section -->
    <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between min-h-[40px] text-white">
                <div class="w-10 flex items-center">
                    <a href="{{ $help_id ? route('mitra.helps.detail', ['id' => $help_id]) : route('mitra.dashboard') }}" wire:navigate aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>

                <div class="text-center flex-1 min-w-0 px-2">
                    <h1 class="text-base font-bold truncate">Buat Laporan Aduan</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Laporkan masalah atau kendala yang Anda alami</p>
                </div>

                <div class="w-10 flex items-center justify-end"></div>
            </div>
        </div>
    </div>

    <!-- Content container -->
    <div class="px-5 pt-5 pb-8 max-w-md mx-auto">
        <div class="max-w-md mx-auto space-y-4">
            @if (session('message'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 rounded-xl shadow-xs">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-200">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <!-- Form Card -->
            <form wire:submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Form Header -->
                <div class="bg-gray-50/80 dark:bg-gray-750 px-5 py-3.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Formulir Aduan
                    </h2>
                    @if($selectedUser || $selectedHelp)
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2.5 py-1 rounded-full border border-blue-200 dark:border-blue-900/60">
                            🛡️ Otomatis Terhubung
                        </span>
                    @endif
                </div>

                <div class="p-5 space-y-5">
                    {{-- Prefilled Context Card (Jika datang dari chat / bantuan terkait) --}}
                    @if ($selectedUser || $selectedHelp)
                        <div class="p-3.5 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/60 rounded-xl space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-blue-900 dark:text-blue-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Pihak & Tugas yang Dilaporkan
                                </span>
                                <span class="text-[10px] text-blue-700/80 dark:text-blue-300 font-medium">Terisi Otomatis</span>
                            </div>

                            <div class="bg-white/90 dark:bg-gray-800/90 rounded-lg p-3 border border-blue-100 dark:border-gray-700/60 space-y-2">
                                @if($selectedUser)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $selectedUser->name }}</p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $selectedUser->email ?: 'Customer SayaBantu' }}</p>
                                        </div>
                                        <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 rounded font-semibold flex-shrink-0">Customer</span>
                                    </div>
                                @endif

                                @if($selectedHelp)
                                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex items-start gap-2 text-xs">
                                        <span class="text-gray-400 flex-shrink-0 mt-0.5">📋</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">{{ $selectedHelp->title }}</p>
                                            <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                <span>Status: <strong class="text-gray-700 dark:text-gray-300 font-medium">{{ ucfirst(str_replace('_', ' ', $selectedHelp->status)) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Dropdown Pilih Bantuan jika tidak dibuka dari context chat --}}
                        <div class="space-y-1.5">
                            <label for="help_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Tugas Terkait <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <select id="help_id" wire:model.live="help_id"
                                class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                                <option value="" class="text-gray-400">-- Pilih Tugas Terkait (Jika Ada) --</option>
                                @foreach ($helps as $h)
                                    <option value="{{ $h->id }}">
                                        {{ Str::limit($h->title, 40) }} (Customer: {{ $h->user->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Judul Laporan -->
                    <div class="space-y-1.5">
                        <label for="title" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                Judul Laporan
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="text" id="title" wire:model="title"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('title') border-red-500 bg-red-50 dark:bg-red-950/20 @enderror"
                            placeholder="Judul Alasan Pengaduan">
                        @error('title')
                            <p class="mt-1 text-[11px] text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Jenis Laporan -->
                    <div x-data="{ reportType: @entangle('report_type') }" class="space-y-1.5">
                        <label for="report_type" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Jenis Laporan
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <select id="report_type" wire:model="report_type"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('report_type') border-red-500 bg-red-50 dark:bg-red-950/20 @enderror">
                            <option value="" class="text-gray-400">Pilih Jenis Laporan</option>
                            @foreach ($reportTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('report_type')
                            <p class="mt-1 text-[11px] text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        
                        <!-- Custom Report Type (conditional) -->
                        <div x-show="reportType === 'lainnya'" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-2.5 p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl">
                            <label for="custom_report_type" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                Sebutkan Jenis Laporan Anda
                            </label>
                            <input type="text" id="custom_report_type" wire:model="custom_report_type"
                                class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('custom_report_type') border-red-500 bg-red-50 dark:bg-red-950/20 @enderror"
                                placeholder="Tulis jenis laporan yang Anda maksud">
                            @error('custom_report_type')
                                <p class="mt-1 text-[11px] text-red-600 flex items-center gap-1">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Jika tidak ada prefilled user/help dan tidak memilih dari dropdown, sediakan input manual sebagai fallback --}}
                    @if (!$selectedUser && !$selectedHelp)
                        <div class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <!-- Customer yang Dilaporkan -->
                            <div class="space-y-1.5">
                                <label for="reported_user_text" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Customer yang Dilaporkan <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" id="reported_user_text" wire:model="reported_user_text"
                                    class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    placeholder="Nama atau kontak customer (jika terkait customer tertentu)">
                            </div>
                        </div>
                    @endif

                    <!-- Detail Laporan -->
                    <div class="space-y-1.5">
                        <label for="message" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Detail Penjelasan Laporan
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <textarea id="message" wire:model="message" rows="5"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all resize-none @error('message') border-red-500 bg-red-50 dark:bg-red-950/20 @enderror"
                            placeholder="Jelaskan masalah yang Anda alami secara detail. Semakin lengkap informasi yang Anda berikan, semakin cepat kami dapat membantu menyelesaikan masalah Anda..."></textarea>
                        @error('message')
                            <p class="mt-1 text-[11px] text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Minimal 10 karakter, maksimal 2000 karakter</p>
                    </div>

                    <!-- Info Box Tips -->
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl p-3.5">
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-blue-900 dark:text-blue-200 mb-0.5">Tips Laporan yang Baik</h4>
                                <ul class="text-[11px] text-blue-700 dark:text-blue-300 space-y-0.5">
                                    <li>• Berikan informasi dan kronologi masalah secara jelas.</li>
                                    <li>• Tim Admin akan memverifikasi riwayat chat dan status tugas.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Footer / Action Buttons -->
                <div class="bg-gray-50 dark:bg-gray-750 px-5 py-3.5 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ $reported_help_id ? route('mitra.helps.detail', ['id' => $reported_help_id]) : ($reported_user_id ? route('mitra.chat', ['user_id' => $reported_user_id]) : route('mitra.dashboard')) }}"
                            wire:navigate
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>