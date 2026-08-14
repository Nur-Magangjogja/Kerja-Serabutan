<div class="min-h-screen bg-gray-50 p-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('customer.dashboard') }}"
                class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Buat Laporan Aduan</h1>
            <p class="text-sm text-gray-600 mt-1">Laporkan masalah yang Anda alami di platform</p>
        </div>

        @if (session('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl">
                {{ session('message') }}
            </div>
        @endif

        <!-- Form -->
        <form wire:submit.prevent="submit" class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 space-y-6">
            <!-- Judul -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                    Judul Laporan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" wire:model="title"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('title') border-red-500 @enderror"
                    placeholder="Contoh: Mitra tidak merespon chat">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Laporan -->
            <div>
                <label for="report_type" class="block text-sm font-semibold text-gray-700 mb-2">
                    Jenis Laporan <span class="text-red-500">*</span>
                </label>
                <select id="report_type" wire:model="report_type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('report_type') border-red-500 @enderror">
                    <option value="">Pilih Jenis Laporan</option>
                    @foreach ($reportTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('report_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @if($report_type === 'lainnya')
                    <div class="mt-3">
                        <label for="custom_help_type" class="block text-sm font-semibold text-gray-700 mb-2">Jenis Bantuan
                            (jika tidak ada di daftar)</label>
                        <input type="text" id="custom_help_type" wire:model="custom_help_type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('custom_help_type') border-red-500 @enderror"
                            placeholder="Tulis jenis bantuan yang tidak ada di pilihan">
                        @error('custom_help_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Bantuan yang Dilaporkan (Optional) -->
            <div>
                <label for="help_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Bantuan yang Dilaporkan (Opsional)
                </label>
                <select id="help_id" wire:model="help_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Pilih Bantuan (Opsional)</option>
                    @foreach ($helps as $help)
                        <option value="{{ $help->id }}">Bantuan #{{ $help->id }} - {{ Str::limit($help->title, 40) }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Pilih bantuan jika laporan terkait dengan bantuan tertentu</p>
            </div>

            <!-- User yang Dilaporkan (Optional) -->
            <div>
                <label for="reported_user_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Mitra yang Dilaporkan (Opsional)
                </label>
                <select id="reported_user_id" wire:model="reported_user_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Pilih Mitra (Opsional)</option>
                    @foreach ($mitras as $mitra)
                        <option value="{{ $mitra->id }}">{{ $mitra->name }} ({{ $mitra->email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Pilih mitra jika laporan terkait dengan mitra tertentu</p>
            </div>

            <!-- Pesan -->
            <div>
                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                    Detail Laporan <span class="text-red-500">*</span>
                </label>
                <textarea id="message" wire:model="message" rows="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('message') border-red-500 @enderror"
                    placeholder="Jelaskan masalah yang Anda alami secara detail..."></textarea>
                @error('message')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter, maksimal 2000 karakter</p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('customer.dashboard') }}"
                    class="px-6 py-3 bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-primary-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>