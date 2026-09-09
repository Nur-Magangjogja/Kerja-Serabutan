<!-- 1. Pilihan 2 Jenis Layanan -->
<div class="space-y-2" id="group-service-type">
    <div class="flex items-center justify-between">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            Pilih Jenis Layanan <span class="text-red-500">*</span>
        </label>
    </div>
    <div class="grid grid-cols-2 gap-2.5">
        <!-- Tab 1: On-Site -->
        <button type="button" wire:click="setServiceType('on_site_service')"
            class="p-3.5 rounded-xl border text-left transition-all cursor-pointer {{ $service_type === 'on_site_service' ? 'border-blue-500 bg-blue-50/80 dark:bg-blue-950/50 ring-2 ring-blue-500/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300' }}">
            <div class="text-2xl mb-1.5">🛠️</div>
            <div class="text-xs font-bold text-gray-900 dark:text-white">Kerja di Lokasi</div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">On-Site bantuan serabutan di tempat</div>
        </button>

        <!-- Tab 2: Pickup / Antar-Jemput -->
        <button type="button" wire:click="setServiceType('pickup_delivery')"
            class="p-3.5 rounded-xl border text-left transition-all cursor-pointer {{ $service_type === 'pickup_delivery' ? 'border-blue-500 bg-blue-50/80 dark:bg-blue-950/50 ring-2 ring-blue-500/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300' }}">
            <div class="text-2xl mb-1.5">📦</div>
            <div class="text-xs font-bold text-gray-900 dark:text-white">Antar / Jemput</div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">Kirim & ambil barang / dokumen</div>
        </button>
    </div>
</div>
