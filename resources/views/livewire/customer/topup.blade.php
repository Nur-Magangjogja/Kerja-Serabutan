@php
    $user = auth()->user();
@endphp

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-md mx-auto px-4 py-6">
        <div class="bg-gradient-to-br from-primary-400 via-primary-500 to-primary-600 rounded-2xl p-4 mb-4 shadow-lg">
            <div class="flex items-center gap-3">
                <button onclick="history.back()" class="p-2 rounded-md text-white/90 bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-extrabold text-white">Top Up Saldo</h1>
                    <p class="text-sm text-white/90">Tambah saldo untuk melakukan pembayaran layanan</p>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-100 text-green-700">{{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-100 text-red-700">{{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="bg-white rounded-2xl shadow p-5">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Pilih nominal</label>
                <div class="flex gap-2">
                    <button type="button" wire:click.prevent="$set('amount', 25000)"
                        class="flex-1 px-3 py-2 rounded-lg bg-primary-50 text-primary-700 font-semibold hover:bg-primary-100 transition">Rp
                        25.000</button>
                    <button type="button" wire:click.prevent="$set('amount', 50000)"
                        class="flex-1 px-3 py-2 rounded-lg bg-primary-50 text-primary-700 font-semibold hover:bg-primary-100 transition">Rp
                        50.000</button>
                    <button type="button" wire:click.prevent="$set('amount', 100000)"
                        class="flex-1 px-3 py-2 rounded-lg bg-primary-50 text-primary-700 font-semibold hover:bg-primary-100 transition">Rp
                        100.000</button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal lain</label>
                <input id="amount" wire:model.defer="amount" name="amount" type="number" min="10000" step="1000"
                    placeholder="Masukkan nominal (mis. 50000)"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('amount') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
                <div class="text-sm text-gray-500 mt-2 text-right">Preview: <span class="font-semibold">Rp
                        {{ number_format($amount ?? 0, 0, ',', '.') }}</span></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Metode Pembayaran</label>
                <div class="grid grid-cols-1 gap-2">
                    <label
                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                        :class="{ 'border-primary-500 bg-primary-50': $wire.method === 'bank' }">
                        <input type="radio" wire:model="method" name="method" value="bank" checked>
                        <div>
                            <div class="font-semibold">Transfer Bank</div>
                            <div class="text-xs text-gray-500">BCA | BRI | Mandiri | BNI | Permata</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                        :class="{ 'border-primary-500 bg-primary-50': $wire.method === 'ewallet' }">
                        <input type="radio" wire:model="method" name="method" value="ewallet">
                        <div>
                            <div class="font-semibold">E-Wallet & QRIS</div>
                            <div class="text-xs text-gray-500">GoPay | ShopeePay | QRIS</div>
                        </div>
                    </label>
                </div>
                @error('method') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-primary-600 text-white rounded-lg px-4 py-3 font-bold hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Top Up Sekarang</span>
                    <span wire:loading>
                        <svg class="animate-spin inline-block w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>

            <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-xs text-blue-700">
                        <p class="font-semibold mb-1">Informasi Pembayaran:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pembayaran diproses secara real-time melalui Midtrans</li>
                            <li>Minimal top up adalah Rp 10.000</li>
                            <li>Saldo akan otomatis bertambah setelah pembayaran berhasil</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Midtrans Snap Script -->
<script
    src="https://{{ config('services.midtrans.is_production') ? 'app.midtrans.com' : 'app.sandbox.midtrans.com' }}/snap/snap.js"
    data-client-key="{{ config('services.midtrans.client_key') }}">
    </script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openMidtransSnap', (event) => {
            const snapToken = event.snapToken;

            if (!snapToken) {
                console.error('Snap token is missing');
                return;
            }

            // Open Midtrans Snap popup
            window.snap.pay(snapToken, {
                onSuccess: function (result) {
                    console.log('Payment success:', result);

                    // Notify server immediately so it can fetch Midtrans status and update transaction
                    try {
                        fetch('{{ route('topup.client-callback') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order_id: result.order_id })
                        }).then(resp => resp.json()).then(data => {
                            console.log('Client callback response', data);
                            // Redirect after server acknowledged (or immediately)
                            window.location.href = '{{ route("customer.topup") }}?status=success';
                        }).catch(err => {
                            console.warn('Client callback failed, redirecting anyway', err);
                            window.location.href = '{{ route("customer.topup") }}?status=success';
                        });
                    } catch (e) {
                        console.warn('Failed to call client callback', e);
                        window.location.href = '{{ route("customer.topup") }}?status=success';
                    }
                },
                onPending: function (result) {
                    console.log('Payment pending:', result);
                    window.location.href = '{{ route("customer.topup") }}?status=pending';
                },
                onError: function (result) {
                    console.error('Payment error:', result);
                    window.location.href = '{{ route("customer.topup") }}?status=error';
                },
                onClose: function () {
                    console.log('Payment popup closed');
                    // Optional: You can add a message here if needed
                }
            });
        });
    });
</script>

@push('styles')
    <style>
        /* Custom styles for Alpine.js transitions if needed */
    </style>
@endpush