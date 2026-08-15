<div wire:poll.30s="loadData" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">1 Jam Terakhir</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($newLastHour) }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">Hari Ini</p>
        <p class="text-xl font-bold text-blue-600 mt-1">{{ number_format($todayCount) }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">User Baru (24j)</p>
        <p class="text-xl font-bold text-indigo-600 mt-1">{{ number_format($newUsers24h) }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">Mitra Aktif</p>
        <p class="text-xl font-bold text-emerald-600 mt-1">{{ number_format($activeMitra) }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">Perlu Tanggapan</p>
        <p class="text-xl font-bold text-amber-600 mt-1">{{ number_format($unresolved) }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <p class="text-[11px] font-semibold text-gray-500 uppercase">Rata-rata Selesai</p>
        <p class="text-xl font-bold text-purple-600 mt-1">{{ $avgCompletionHours !== null ? $avgCompletionHours . ' jam' : '-' }}</p>
    </div>
</div>
