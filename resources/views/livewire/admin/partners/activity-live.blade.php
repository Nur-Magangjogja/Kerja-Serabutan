<div wire:poll.7s="loadData" class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500">Mitra Aktif</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $mitraActive }}</p>
            <p class="text-xs text-gray-400">(role = mitra, status = active)</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500">Permintaan Baru (Pending)</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $pendingHelps }}</p>
            <p class="text-xs text-gray-400">Help items available (approved, not taken)</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500">Tugas Sedang Berjalan</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $inProgress }}</p>
            <p class="text-xs text-gray-400">helps with mitra assigned / in progress</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Live Feed (recent partner activity)</h3>
        <ul class="space-y-2 text-sm text-gray-700">
            @forelse($feed as $f)
                <li class="flex items-start justify-between">
                    <div>
                        <div class="text-xs text-gray-500">{{ $f['time'] }}</div>
                        <div class="font-medium">{{ $f['user'] }}</div>
                        <div class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($f['desc'] ?? '-', 80) }}</div>
                    </div>
                    <div class="text-xs text-gray-400">{{ $f['type'] }}</div>
                </li>
            @empty
                <li class="text-xs text-gray-500">No recent activity.</li>
            @endforelse
        </ul>
    </div>
</div>