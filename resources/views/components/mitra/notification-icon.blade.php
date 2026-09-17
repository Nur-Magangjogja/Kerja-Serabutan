@props([
    'route' => route('mitra.notifications.index'),
    'class' => 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 transition-colors duration-200 flex items-center justify-center text-white shadow-xs cursor-pointer relative',
])

<livewire:notifications.notification-icon :route="$route" :class="$class" :role="'mitra'" :wire:key="'mitra-notif-icon-'.(auth()->id() ?? 'guest')" />
