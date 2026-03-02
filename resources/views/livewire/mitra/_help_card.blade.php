@php
    $avatar = $help->user->selfie_photo ?? $help->user->photo ?? null;
    $name = $help->user->name ?? 'Pengguna';
    $cardImage = $help->photo ?? $avatar;
    $catId = optional($help->category)->id ?? 0;
    $colors = ['bg-pink-100 text-pink-600','bg-green-100 text-green-600','bg-yellow-100 text-yellow-600','bg-blue-100 text-blue-600'];
    $color = $colors[$catId % count($colors)];
    $price = $help->estimated_price ?? $help->amount ?? 0;
@endphp

<div class="block w-full bg-white rounded-xl border border-gray-100 shadow-sm p-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ explode(' ', $color)[0] }}">
            @if($cardImage)
                <img src="{{ asset('storage/' . $cardImage) }}" alt="image" class="w-10 h-10 object-cover rounded-lg">
            @else
                <div class="w-8 h-8 rounded-md bg-white/30 flex items-center justify-center text-sm font-bold text-gray-700">{{ strtoupper(substr($name,0,1)) }}</div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ $help->title ?? Str::limit($help->description, 60) }}</div>
                <div class="text-sm font-bold text-gray-900 ml-3 whitespace-nowrap">Rp {{ number_format($price, 0, ',', '.') }}</div>
            </div>
            <div class="flex items-center justify-between mt-1">
                <div class="text-xs text-gray-500 truncate">{{ $help->city->name ?? ($help->location ?? '-') }}</div>
                <div class="text-xs text-gray-400">{{ $help->created_at->diffForHumans() }}</div>
            </div>
            @if($help->scheduled_at)
                <div class="mt-2 text-xs text-gray-500">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
            @endif
        </div>
    </div>
</div>