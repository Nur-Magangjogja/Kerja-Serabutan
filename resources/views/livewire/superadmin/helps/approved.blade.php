@php
    $helps = $helps ?? collect();
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bantuan Disetujui</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Daftar bantuan yang sudah disetujui.</p>
        </div>
        <div class="flex items-center gap-2">
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari..." class="px-3 py-2 border rounded-lg text-sm bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100" />
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto text-sm">
            <thead>
                <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="px-3 py-2">#</th>
                    <th class="px-3 py-2">Judul</th>
                    <th class="px-3 py-2">Customer</th>
                    <th class="px-3 py-2">Kota</th>
                    <th class="px-3 py-2">Kategori</th>
                    <th class="px-3 py-2">Jumlah</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($helps as $help)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-3 py-2">{{ $help->id }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $help->title }}</td>
                        <td class="px-3 py-2">{{ $help->customer->name ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $help->city->name ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $help->category->name ?? '-' }}</td>
                        <td class="px-3 py-2 font-semibold text-emerald-600">Rp {{ number_format($help->amount ?? 0,0,',','.') }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $help->created_at?->format('Y-m-d') }}</td>
                        <td class="px-3 py-2">
                            <button wire:click="rejectHelp({{ $help->id }})" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-semibold">Tolak</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-6 text-center text-gray-500">Tidak ada bantuan disetujui.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $helps->links() }}</div>
</div>
