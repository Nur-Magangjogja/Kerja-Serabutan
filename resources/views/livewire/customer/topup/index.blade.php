@php
    $user = auth()->user();
@endphp

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-md mx-auto px-4 py-6">
        <div class="bg-gradient-to-br from-primary-400 via-primary-500 to-primary-600 rounded-2xl p-4 mb-4 shadow-lg">
            <div>
                <h1 class="text-lg font-extrabold text-white">Top Up Saldo</h1>
                <p class="text-sm text-white/90">Tambah saldo untuk melakukan pembayaran layanan</p>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/60 text-green-700 dark:text-green-300 text-sm flex items-center gap-2">{{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-red-700 dark:text-red-300 text-sm flex items-center gap-2">{{ session('error') }}
            </div>
        @endif

        <form wire:submit="submit" 
              class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-100 dark:border-gray-700 p-5">
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

            <div class="mb-4" x-data="{
                rawAmount: @entangle('amount').live,
                formatRupiah(val) {
                    if (val === null || val === undefined || val === '') return '';
                    let num = parseInt(val.toString().replace(/[^0-9]/g, ''), 10);
                    if (isNaN(num)) return '';
                    return new Intl.NumberFormat('id-ID').format(num);
                },
                onInput(e) {
                    let clean = e.target.value.replace(/[^0-9]/g, '');
                    let num = clean ? parseInt(clean, 10) : '';
                    this.rawAmount = num;
                    e.target.value = this.formatRupiah(num);
                }
            }" x-init="
                $nextTick(() => {
                    if ($refs.amountInput && rawAmount) {
                        $refs.amountInput.value = formatRupiah(rawAmount);
                    }
                });
                $watch('rawAmount', val => {
                    if ($refs.amountInput && document.activeElement !== $refs.amountInput) {
                        $refs.amountInput.value = formatRupiah(val);
                    }
                });
            ">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold text-gray-700">Nominal lain</label>
                </div>
                <input id="amount"
                    x-ref="amountInput"
                    inputmode="numeric"
                    @input="onInput($event)"
                    placeholder="Masukkan nominal (mis. 50.000)"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent font-semibold">
                @error('amount') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
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
                    class="w-full bg-primary-600 text-white rounded-lg px-4 py-3 font-bold hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                    wire:loading.attr="disabled"
                    wire:target="submit">
                    <svg wire:loading wire:target="submit" class="animate-spin h-5 w-5 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="submit">Top Up Sekarang</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
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
