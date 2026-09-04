<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Header Section -->
    <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto text-white text-center">
            <h1 class="text-base font-bold truncate">Buat Laporan Aduan</h1>
            <p class="text-xs text-white/90 truncate mt-0.5">Laporkan masalah atau kendala yang Anda alami</p>
        </div>
    </div>

    <!-- Content container -->
    <div class="px-5 pt-5 pb-8 max-w-md mx-auto">
        <div class="max-w-md mx-auto">
            @if (session('message'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-950/40 border-l-4 border-green-500 rounded-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <!-- Form Card -->
            <form wire:submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Form Header -->
            <div class="bg-gray-50 dark:bg-gray-750 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informasi Laporan
                </h2>
            </div>

            <div class="p-6 space-y-6">
                <!-- Judul -->
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-semibold text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Judul Laporan
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <input type="text" id="title" wire:model="title"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('title') border-red-500 bg-red-50 @enderror"
                        placeholder="Contoh: Customer tidak merespon">
                    @error('title')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Jenis Laporan -->
                <div x-data="{ reportType: @entangle('report_type') }" class="space-y-2">
                    <label for="report_type" class="block text-sm font-semibold text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Jenis Laporan
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <select id="report_type" wire:model="report_type"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('report_type') border-red-500 bg-red-50 @enderror">
                        <option value="">Pilih Jenis Laporan</option>
                        @foreach ($reportTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                        <option value="lainnya">Lainnya</option>
                    </select>
                    @error('report_type')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
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
                         class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl" style="display: none;">
                        <label for="custom_report_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            Sebutkan Jenis Laporan Anda
                        </label>
                        <input type="text" id="custom_report_type" wire:model="custom_report_type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('custom_report_type') border-red-500 bg-red-50 @enderror"
                            placeholder="Tulis jenis laporan yang Anda maksud">
                        @error('custom_report_type')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informasi Tambahan (Opsional)
                    </h3>
                </div>

                <!-- Jenis Bantuan -->
                <div class="space-y-2">
                    <label for="reported_help_text" class="block text-sm font-semibold text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                            </svg>
                            Jenis Bantuan
                            <span class="text-xs text-gray-500 font-normal">(Opsional)</span>
                        </span>
                    </label>
                    <input type="text" id="reported_help_text" wire:model="reported_help_text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('reported_help_text') border-red-500 bg-red-50 @enderror"
                        placeholder="Mis: Bantuan Uang, Perbaikan Rumah, Bantuan Pendidikan">
                    @error('reported_help_text')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 flex items-start gap-1">
                        <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Tuliskan jenis bantuan jika laporan terkait dengan bantuan tertentu
                    </p>
                </div>

                <!-- Customer yang Dilaporkan -->
                <div class="space-y-2">
                    <label for="reported_user_text" class="block text-sm font-semibold text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Customer yang Dilaporkan
                            <span class="text-xs text-gray-500 font-normal">(Opsional)</span>
                        </span>
                    </label>
                    <input type="text" id="reported_user_text" wire:model="reported_user_text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('reported_user_text') border-red-500 bg-red-50 @enderror"
                        placeholder="Nama atau email customer (jika terkait dengan customer tertentu)">
                    @error('reported_user_text')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 flex items-start gap-1">
                        <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Tulis nama atau email customer jika laporan terkait dengan customer tertentu
                    </p>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 pt-6"></div>

                <!-- Detail Laporan -->
                <div class="space-y-2">
                    <label for="message" class="block text-sm font-semibold text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            Detail Laporan
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <textarea id="message" wire:model="message" rows="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all resize-none @error('message') border-red-500 bg-red-50 @enderror"
                        placeholder="Jelaskan masalah yang Anda alami secara detail. Semakin lengkap informasi yang Anda berikan, semakin cepat kami dapat membantu menyelesaikan masalah Anda..."></textarea>
                    @error('message')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Minimal 10 karakter, maksimal 2000 karakter
                        </p>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">Tips Laporan yang Baik</h4>
                            <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 mt-0.5">•</span>
                                    <span>Berikan informasi selengkap mungkin</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 mt-0.5">•</span>
                                    <span>Sertakan kronologi kejadian secara berurutan</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 mt-0.5">•</span>
                                    <span>Gunakan bahasa yang jelas dan mudah dipahami</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer / Action Buttons -->
            <div class="bg-gray-50 dark:bg-gray-750 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('mitra.dashboard') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all cursor-pointer">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>