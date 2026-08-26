<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center justify-between">
        <div>
            <h1 class="text-base font-extrabold text-gray-900 dark:text-white">Riwayat Pencairan Saldo Mitra</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Daftar seluruh transaksi pencairan dana bantuan ke rekening Anda</p>
        </div>
        <a href="{{ route('mitra.withdraw.form') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
            + Cairkan Dana Baru
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- List Cards --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        @if($withdraws->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    💳
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Belum Ada Riwayat Pencairan Dana</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">
                    Anda belum pernah melakukan pencairan saldo penghasilan ke rekening bank.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">ID / Tanggal</th>
                            <th class="px-4 py-3">Rekening Tujuan</th>
                            <th class="px-4 py-3">Nominal Tarik</th>
                            <th class="px-4 py-3">Jumlah Bersih</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Bukti Transfer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-200">
                        @foreach($withdraws as $wd)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">#WD-{{ $wd->id }}</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $wd->created_at->format('d M Y • H:i') }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="font-bold text-gray-900 dark:text-white uppercase">{{ $wd->bank_code }}</p>
                                    <p class="font-mono text-gray-600 dark:text-gray-400 text-[11px]">{{ $wd->account_number }}</p>
                                    <p class="text-[10px] text-gray-400">a.n. {{ $wd->account_name }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($wd->net_amount ?: $wd->amount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($wd->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300">
                                            Sedang Diproses
                                        </span>
                                    @elseif($wd->status === 'completed' || $wd->status === 'success')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            ✅ Berhasil Ditransfer
                                        </span>
                                    @elseif($wd->status === 'rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            ❌ Ditolak
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($wd->proof_of_transfer)
                                        <a href="{{ asset('storage/' . $wd->proof_of_transfer) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-emerald-600 hover:underline">
                                            <span>📷 Lihat Struk</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $withdraws->links() }}
            </div>
        @endif
    </div>
</div>
