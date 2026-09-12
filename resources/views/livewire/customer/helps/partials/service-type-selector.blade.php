<!-- 1. Pilihan 2 Jenis Layanan -->
<div class="space-y-2.5" id="group-service-type">
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
            <div class="text-2xl mb-1.5">🛵</div>
            <div class="text-xs font-bold text-gray-900 dark:text-white">Antar / Jemput</div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">Antar penumpang / barang (Armada Motor &le; 40KM)</div>
        </button>
    </div>

    <!-- Subkategori Khusus Antar / Jemput -->
    @if($service_type === 'pickup_delivery')
        <div class="pt-2 space-y-2 animate-fade-in">
            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">
                Pilih Kategori Antar / Jemput:
            </label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" wire:click="$set('service_category', 'passenger')"
                    class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer transition-all {{ ($service_category ?? '') === 'passenger' ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 ring-2 ring-blue-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                    <span>👥</span> Antar Penumpang
                </button>
                <button type="button" wire:click="$set('service_category', 'goods_document')"
                    class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer transition-all {{ ($service_category ?? 'goods_document') === 'goods_document' || empty($service_category) || $service_category === 'general' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 ring-2 ring-emerald-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                    <span>📦</span> Barang & Dokumen
                </button>
            </div>

            <!-- Penjelasan Ketentuan Layanan Sesuai Kategori -->
            @if(($service_category ?? '') === 'passenger')
                <div class="p-3.5 bg-blue-600 dark:bg-blue-900 border border-blue-500 dark:border-blue-700 rounded-xl text-xs space-y-1.5 text-white shadow-sm">
                    <div class="flex items-center gap-1.5 font-bold text-white text-xs">
                        <span>👥</span>
                        <span>Ketentuan Layanan Antar Penumpang:</span>
                    </div>
                    <ul class="text-[11px] space-y-1 text-white pl-3.5 list-disc leading-relaxed font-medium">
                        <li>Menggunakan armada <strong class="text-white font-bold underline decoration-white/40">sepeda motor</strong> dengan kapasitas maksimal <strong class="text-white font-bold">1 orang penumpang</strong>.</li>
                        <li>Jarak tempuh maksimal perjalanan adalah <strong class="text-white font-bold underline decoration-white/40">40 KM</strong> demi keselamatan berkendara rekan mitra.</li>
                        <li>Penumpang wajib menggunakan helm standar demi keselamatan di jalan raya.</li>
                    </ul>
                </div>
            @else
                <div class="p-3.5 bg-emerald-700 dark:bg-emerald-900 border border-emerald-600 dark:border-emerald-700 rounded-xl text-xs space-y-1.5 text-white shadow-sm">
                    <div class="flex items-center gap-1.5 font-bold text-white text-xs">
                        <span>📦</span>
                        <span>Ketentuan Layanan Pengantaran Barang & Dokumen:</span>
                    </div>
                    <ul class="text-[11px] space-y-1 text-white pl-3.5 list-disc leading-relaxed font-medium">
                        <li>Jarak rute pengantaran maksimal <strong class="text-white font-bold underline decoration-white/40">40 KM</strong> untuk armada sepeda motor.</li>
                        <li>Berat beban barang <strong class="text-white font-bold underline decoration-white/40">maksimal 20 KG</strong> demi kestabilan dan keamanan berkendara mitra.</li>
                        <li>Dimensi barang dalam batas wajar yang aman diangkut dengan sepeda motor (bukan barang berbahaya, mudah meledak, atau zat terlarang).</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>

