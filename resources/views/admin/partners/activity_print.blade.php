<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Aktivitas Mitra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>

<body class="bg-white text-gray-900">
    <div class="max-w-6xl mx-auto py-8 px-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Laporan Aktivitas Mitra</h1>
                <p class="text-xs text-gray-500 mt-1">Dicetak pada {{ now()->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>

        <table class="w-full text-xs border border-gray-200 border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-200 px-2 py-1 text-left">User</th>
                    <th class="border border-gray-200 px-2 py-1 text-left">Aktivitas</th>
                    <th class="border border-gray-200 px-2 py-1 text-left">Deskripsi</th>
                    <th class="border border-gray-200 px-2 py-1 text-left">IP</th>
                    <th class="border border-gray-200 px-2 py-1 text-left">User Agent</th>
                    <th class="border border-gray-200 px-2 py-1 text-left">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $a)
                    <tr>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->user->name ?? '-' }}</td>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->activity_type }}</td>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->description ?? '-' }}</td>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->ip_address ?? '-' }}</td>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->user_agent ?? '-' }}</td>
                        <td class="border border-gray-200 px-2 py-1">{{ $a->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border border-gray-200 px-2 py-4 text-center text-gray-500">Tidak ada
                            data aktivitas untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <script>
            window.print();
        </script>
    </div>
</body>

</html>
