@props([
    'route' => route('mitra.chat'),
    'class' => 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center text-white shadow-xs cursor-pointer relative',
])

<livewire:chat.chat-icon :route="$route" :class="$class" :role="'mitra'" :wire:key="'mitra-chat-icon-'.(auth()->id() ?? 'guest')" />
