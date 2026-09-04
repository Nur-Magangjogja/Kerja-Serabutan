@props([
    'route' => route('mitra.chat'),
    'class' => 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center text-white shadow-xs cursor-pointer relative',
])

@php
    $unread = 0;
    try {
        if (auth()->check()) {
            $mitraId = auth()->id();
            
            // 1. Unread chat from customers
            $unreadCustomer = \App\Models\Chat::where('mitra_id', $mitraId)
                ->where('sender_type', 'customer')
                ->whereNull('read_at')
                ->count();
                
            // 2. Unread messages from admin report
            $mitraReports = \App\Models\PartnerReport::where('reported_user_id', $mitraId)
                ->orWhereHas('reportedHelp', function($q) use ($mitraId) {
                    $q->where('mitra_id', $mitraId);
                })
                ->pluck('id');
                
            $unreadAdmin = \App\Models\PartnerReportMessage::whereIn('partner_report_id', $mitraReports)
                ->where('recipient_type', 'mitra')
                ->where('is_read', false)
                ->count();
                
            $unread = $unreadCustomer + $unreadAdmin;
        }
    } catch (\Exception $e) {
        $unread = 0;
    }
@endphp

<a
    href="{{ $route }}"
    wire:navigate
    title="Pesan Chat"
    aria-label="Pesan Chat"
    {{ $attributes->merge(['class' => $class]) }}
>
    <div class="relative flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>

        @if(!empty($unread) && $unread > 0)
            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm ring-2 ring-white/30">
                {{ $unread > 99 ? '99+' : $unread }}
            </span>
        @endif
    </div>
</a>
