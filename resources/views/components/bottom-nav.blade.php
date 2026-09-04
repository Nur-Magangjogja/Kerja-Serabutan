@auth
    @if(auth()->user()->role === 'mitra')
        <x-mitra.bottom-nav />
    @elseif(auth()->user()->role === 'customer')
        <x-customer.bottom-nav />
    @endif
@endauth
