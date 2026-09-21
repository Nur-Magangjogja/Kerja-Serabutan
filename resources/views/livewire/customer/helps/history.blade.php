<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors" x-data="{ previewPhotoUrl: null }">
	<div class="max-w-md mx-auto">
		<!-- Header Section -->
		<div class="px-5 pt-4 pb-5 relative overflow-hidden bg-[#0098e7] rounded-b-2xl shadow-sm text-white">
			<div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
			
			<div class="relative z-10 space-y-3">
				<div class="flex items-center justify-between min-h-[40px] text-white">
					<div class="w-10 flex items-center">
						<a href="{{ route('customer.helps.index') }}" wire:navigate aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
							</svg>
						</a>
					</div>

					<div class="text-center flex-1 min-w-0 px-2">
						<h1 class="text-base font-bold truncate">Riwayat Bantuan</h1>
						<p class="text-xs text-white/90 font-medium truncate mt-0.5">Daftar tugas yang telah selesai maupun dibatalkan</p>
					</div>

					<div class="w-10 flex items-center justify-end"></div>
				</div>

				{{-- Stats Cards (Isolated strictly per customer) --}}
				<div class="grid grid-cols-3 gap-2 pt-1">
					<div wire:click="setStatusFilter('all')" class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border {{ $statusFilter === 'all' ? 'border-white bg-white/25 ring-2 ring-white/50' : 'border-white/20' }} shadow-2xs cursor-pointer transition active:scale-95">
						<div class="text-[11px] text-white/85 mb-0.5 font-medium">Total Nilai</div>
						<div class="text-xs font-bold text-white leading-tight truncate">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
					</div>
					
					<div wire:click="setStatusFilter('selesai')" class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border {{ $statusFilter === 'selesai' ? 'border-white bg-white/25 ring-2 ring-white/50' : 'border-white/20' }} shadow-2xs cursor-pointer transition active:scale-95">
						<div class="text-lg sm:text-xl font-extrabold text-white leading-tight">{{ $totalSelesai }}</div>
						<div class="text-[11px] text-white/85 mt-0.5 font-medium">✅ Selesai</div>
					</div>

					<div wire:click="setStatusFilter('dibatalkan')" class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border {{ $statusFilter === 'dibatalkan' ? 'border-white bg-white/25 ring-2 ring-white/50' : 'border-white/20' }} shadow-2xs cursor-pointer transition active:scale-95">
						<div class="text-lg sm:text-xl font-extrabold text-white leading-tight">{{ $totalBatal }}</div>
						<div class="text-[11px] text-white/85 mt-0.5 font-medium">❌ Dibatalkan</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Main Content -->
		<div class="px-5 pt-4 pb-24 space-y-4">

			{{-- Search & Tab Filter Bar --}}
			<div class="space-y-2.5">
				{{-- Search Input --}}
				<div class="relative">
					<input wire:model.live.debounce.300ms="search" type="text"
						placeholder="Cari riwayat tugas, lokasi, mitra..."
						class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-500 shadow-2xs transition">
					<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
						<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
					</div>
					@if($search)
						<button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer text-xs">
							&times;
						</button>
					@endif
				</div>

				{{-- Tab Filter Pill Buttons --}}
				<div class="grid grid-cols-3 gap-1.5 p-1 bg-gray-200/70 dark:bg-gray-800/80 rounded-xl text-xs font-bold">
					<button type="button" wire:click="setStatusFilter('all')"
						class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer {{ $statusFilter === 'all' ? 'bg-white dark:bg-gray-700 text-sky-600 dark:text-sky-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
						Semua ({{ $totalHistory }})
					</button>
					<button type="button" wire:click="setStatusFilter('selesai')"
						class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer {{ $statusFilter === 'selesai' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
						Selesai ({{ $totalSelesai }})
					</button>
					<button type="button" wire:click="setStatusFilter('dibatalkan')"
						class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer {{ $statusFilter === 'dibatalkan' ? 'bg-white dark:bg-gray-700 text-rose-600 dark:text-rose-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400' }}">
						Batal ({{ $totalBatal }})
					</button>
				</div>
			</div>

			{{-- History List --}}
			@if($helps->isEmpty())
				<div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/70 p-6 shadow-xs">
					<div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 flex items-center justify-center text-sky-600 dark:text-sky-400 shadow-2xs">
						<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
						</svg>
					</div>
					<h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
						@if($statusFilter === 'selesai')
							Belum Ada Riwayat Selesai
						@elseif($statusFilter === 'dibatalkan')
							Belum Ada Riwayat Pembatalan
						@else
							Belum Ada Riwayat Tugas
						@endif
					</h3>
					<p class="text-xs text-gray-500 dark:text-gray-400 mb-5">
						@if($search)
							Tidak ada riwayat yang cocok dengan pencarian "{{ $search }}".
						@elseif($statusFilter === 'selesai')
							Belum ada bantuan yang telah selesai dikerjakan.
						@elseif($statusFilter === 'dibatalkan')
							Tidak ada bantuan yang dibatalkan.
						@else
							Belum ada permintaan bantuan yang telah selesai atau dibatalkan.
						@endif
					</p>
					<a href="{{ route('customer.helps.create') }}" wire:navigate class="inline-flex items-center gap-1.5 text-white text-xs px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-sky-600 to-[#0077cc] hover:from-sky-700 hover:to-[#0060b0] shadow-xs hover:shadow-md transition cursor-pointer">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
						<span>Buat Bantuan Baru</span>
					</a>
				</div>
			@else
				<div class="space-y-3">
					@foreach($helps as $help)
						@php
							$isCancelled = in_array($help->status, [\App\Models\Help::STATUS_DIBATALKAN, 'batal', 'cancelled', 'canceled', 'partner_cancel_requested', 'customer_cancel_requested']);
							$cancelReq = $help->latestCancelRequest ?? ($help->cancelRequests?->first());
							$report = $help->latestPartnerReport ?? ($help->reports?->first());
							$evidencePhoto = $cancelReq?->evidence_photo ?: ($report?->evidence_photo ?: $help->cancel_evidence_photo);
							$clarificationPhoto = $cancelReq?->partner_clarification_photo;
						@endphp
						<div x-data="{ isExpanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $isCancelled ? 'border-rose-100 dark:border-rose-950/60' : 'border-gray-100 dark:border-gray-700/70' }} shadow-xs hover:shadow-md transition-all overflow-hidden">
							<div class="p-4">
								<div class="flex items-start justify-between gap-3 mb-3">
									<div class="flex-1 min-w-0">
										<div class="flex items-center gap-1.5 flex-wrap mb-1">
											@if($help->isPickup())
												@if($help->service_category === 'passenger')
													<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
														👥 Antar Penumpang
													</span>
												@else
													<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200">
														📦 Barang & Dokumen
													</span>
												@endif
											@else
												<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
													🛠️ Kerja Serabutan
												</span>
											@endif
										</div>

										<h3 class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
										<p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
											{{ optional($help->district)->name ? 'Kec. ' . $help->district->name : (optional($help->city)->name ?? '-') }} • {{ optional($help->updated_at)->translatedFormat('d M Y') }}
										</p>
									</div>

									<div class="text-right flex-shrink-0">
										<div class="text-sm font-black {{ $isCancelled ? 'text-gray-400 line-through' : 'text-sky-600 dark:text-sky-400' }}">
											Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}
										</div>
										<div class="flex flex-col items-end mt-1 gap-1">
											@if($isCancelled)
												<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[10px] font-bold border border-rose-200 dark:border-rose-800/60">
													❌ Dibatalkan
												</span>
											@else
												<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">
													<svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
													</svg>
													Selesai
												</span>

												@if($help->rating)
													<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-50 dark:bg-gray-750 text-gray-700 dark:text-gray-300 text-[10px] font-bold border border-gray-100 dark:border-gray-700">
														<svg class="w-3 h-3 text-amber-400 fill-current" viewBox="0 0 20 20">
															<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
														</svg>
														<span>{{ number_format($help->rating->rating, 1) }}</span>
													</span>
												@else
													<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-medium">
														Belum dinilai
													</span>
												@endif
											@endif
										</div>
									</div>
								</div>

								<button @click="isExpanded = !isExpanded" class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 {{ $isCancelled ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-50/50' : 'text-sky-600 dark:text-sky-400 hover:bg-sky-50/50' }} dark:hover:bg-gray-750 transition cursor-pointer">
									<span x-text="isExpanded ? 'Sembunyikan Detail' : 'Lihat Detail {{ $isCancelled ? 'Pembatalan' : '& Penilaian' }}'"></span>
									<svg :class="isExpanded ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
									</svg>
								</button>
							</div>

							<div x-show="isExpanded" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700/60 pt-4 space-y-3.5">
								
								{{-- CANCELLATION DETAILS BOX (FOR CANCELLED TASKS) --}}
								@if($isCancelled)
									<div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 rounded-2xl space-y-2.5 text-xs">
										<div class="flex items-center justify-between font-bold text-rose-800 dark:text-rose-300">
											<span class="flex items-center gap-1.5">
												<span>⚠️</span> Rincian Pembatalan Tugas
											</span>
											<span class="text-[10px] font-medium text-rose-600 dark:text-rose-400">
												{{ optional($help->updated_at)->translatedFormat('d M Y, H:i') }} WIB
											</span>
										</div>

										@php
											$reasonText = $cancelReq?->reason ?: ($report ? ($report->report_type_label . ($report->title ? ': ' . $report->display_title : '')) : ($help->partner_cancel_reason ?: 'Dibatalkan oleh pengguna / sistem'));
											$notesText = $cancelReq?->notes ?: ($report?->message ?: $help->partner_cancel_notes);
											$requesterType = $cancelReq?->requester_type ?: ($report ? ($report->reporter_type === 'partner' ? 'partner' : 'customer') : ($help->cancel_requested_by ?: 'customer'));
										@endphp

										<div class="bg-white/80 dark:bg-gray-800/80 p-2.5 rounded-xl border border-rose-100 dark:border-rose-900/40 space-y-1.5">
											<div class="flex items-center justify-between text-[11px]">
												<span class="text-gray-500 dark:text-gray-400">Alasan:</span>
												<span class="font-bold text-gray-900 dark:text-white">{{ $reasonText }}</span>
											</div>
											@if($notesText)
												<div class="pt-1 text-[11px] text-gray-600 dark:text-gray-300 italic border-t border-gray-100 dark:border-gray-700">
													"{{ $notesText }}"
												</div>
											@endif
											<div class="flex items-center justify-between text-[10px] pt-1 text-gray-400 border-t border-gray-100 dark:border-gray-700">
												<span>Diajukan oleh:</span>
												<span class="font-semibold text-gray-600 dark:text-gray-300 uppercase">
													{{ $requesterType === 'partner' ? 'Mitra Pelaksana' : 'Customer (Pemohon)' }}
												</span>
											</div>
										</div>

										{{-- Foto Bukti Pembatalan / Kendala --}}
										@if($evidencePhoto)
											<div class="bg-white/90 dark:bg-gray-800/90 p-2.5 rounded-xl border border-rose-200/80 dark:border-rose-900/50 space-y-1.5">
												<div class="flex items-center justify-between text-[11px] font-bold text-rose-900 dark:text-rose-200">
													<span class="flex items-center gap-1">
														<span>📸</span> Foto Bukti Kendala / Pembatalan:
													</span>
													<span class="text-[10px] text-rose-600 dark:text-rose-400 font-normal">Ketuk untuk memperbesar</span>
												</div>
												<div class="rounded-xl overflow-hidden border border-rose-200 dark:border-rose-800/80 bg-black/5 dark:bg-black/20 cursor-pointer group relative max-h-48 flex items-center justify-center"
												     @click="previewPhotoUrl = '{{ asset('storage/' . $evidencePhoto) }}'">
													<img src="{{ asset('storage/' . $evidencePhoto) }}" alt="Bukti Pembatalan" class="w-full h-auto max-h-48 object-contain group-hover:scale-105 transition-transform duration-200">
													<div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
														<span class="bg-black/70 text-white text-[11px] font-medium px-2.5 py-1 rounded-lg backdrop-blur-xs flex items-center gap-1">
															<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
															Lihat Foto
														</span>
													</div>
												</div>
											</div>
										@endif

										{{-- Foto Klarifikasi Mitra (Jika Ada) --}}
										@if($clarificationPhoto)
											<div class="bg-white/90 dark:bg-gray-800/90 p-2.5 rounded-xl border border-blue-200/80 dark:border-blue-900/50 space-y-1.5">
												<div class="flex items-center justify-between text-[11px] font-bold text-blue-900 dark:text-blue-200">
													<span class="flex items-center gap-1">
														<span>📸</span> Foto Bukti Klarifikasi Mitra:
													</span>
													<span class="text-[10px] text-blue-600 dark:text-blue-400 font-normal">Ketuk untuk memperbesar</span>
												</div>
												<div class="rounded-xl overflow-hidden border border-blue-200 dark:border-blue-800/80 bg-black/5 dark:bg-black/20 cursor-pointer group relative max-h-48 flex items-center justify-center"
												     @click="previewPhotoUrl = '{{ asset('storage/' . $clarificationPhoto) }}'">
													<img src="{{ asset('storage/' . $clarificationPhoto) }}" alt="Bukti Klarifikasi Mitra" class="w-full h-auto max-h-48 object-contain group-hover:scale-105 transition-transform duration-200">
													<div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
														<span class="bg-black/70 text-white text-[11px] font-medium px-2.5 py-1 rounded-lg backdrop-blur-xs flex items-center gap-1">
															<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
															Lihat Foto
														</span>
													</div>
												</div>
											</div>
										@endif

										<div class="flex items-start gap-1.5 text-[11px] text-emerald-800 dark:text-emerald-300 bg-emerald-50/80 dark:bg-emerald-950/40 p-2 rounded-xl border border-emerald-200 dark:border-emerald-800/60">
											<span>🛡️</span>
											<span>Saldo pembayaran sebesar <strong>Rp {{ number_format($help->total_amount ?: $help->amount, 0, ',', '.') }}</strong> telah dikembalikan ke Saldo Dompet Anda.</span>
										</div>
									</div>
								@endif

								{{-- Foto Permintaan Bantuan (Saat Dibuat) --}}
								@if($help->photo)
									<div class="space-y-1">
										<div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center justify-between">
											<span>Foto Permintaan Bantuan</span>
											<span class="text-[10px] text-gray-400 font-normal lowercase">ketuk untuk perbesar</span>
										</div>
										<div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 max-h-52 flex items-center justify-center cursor-pointer group relative"
										     @click="previewPhotoUrl = '{{ asset('storage/' . $help->photo) }}'">
											<img src="{{ asset('storage/' . $help->photo) }}" alt="foto bantuan" class="w-full h-auto max-h-52 object-contain group-hover:scale-105 transition-transform duration-200">
											<div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
												<span class="bg-black/60 text-white text-[11px] font-medium px-2.5 py-1 rounded-lg backdrop-blur-xs flex items-center gap-1">
													<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
													Lihat Foto
												</span>
											</div>
										</div>
									</div>
								@endif

								@if($help->isScheduled() && $help->scheduled_at)
									<div class="p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60 text-xs flex items-center justify-between">
										<span class="text-[11px] text-amber-800 dark:text-amber-300 font-medium">Jadwal Keberangkatan:</span>
										<span class="font-bold text-amber-900 dark:text-amber-200">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }} WIB</span>
									</div>
								@endif

								@if($help->description)
									<div>
										<h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-1">Deskripsi</h4>
										<p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50/70 dark:bg-gray-750/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60">{{ $help->description }}</p>
									</div>
								@endif

								{{-- Service Specific Route / Location Section --}}
								@if($help->isPickup())
									<div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60 space-y-2.5">
										<div class="flex items-center justify-between">
											<span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Rute Perjalanan (2 Titik)</span>
											@if($help->service_route_distance_km)
												<span class="text-[11px] font-bold text-sky-600 dark:text-sky-400">± {{ number_format($help->service_route_distance_km, 1) }} KM</span>
											@endif
										</div>
										<div class="space-y-2 text-xs">
											<div class="flex items-start gap-2">
												<span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 shrink-0"></span>
												<div class="min-w-0">
													<div class="text-[10px] text-gray-400 font-semibold">Titik Jemput:</div>
													<div class="font-bold text-gray-800 dark:text-gray-200 break-words">{{ $help->pickup_address ?: ($help->location ?: 'Sesuai titik jemput') }}</div>
												</div>
											</div>
											<div class="flex items-start gap-2">
												<span class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1 shrink-0"></span>
												<div class="min-w-0">
													<div class="text-[10px] text-gray-400 font-semibold">Titik Antar:</div>
													<div class="font-bold text-gray-800 dark:text-gray-200 break-words">{{ $help->delivery_address ?: ($help->full_address ?: 'Sesuai titik tujuan') }}</div>
												</div>
											</div>
										</div>
									</div>
								@else
									<div class="grid grid-cols-2 gap-2 text-xs">
										<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
											<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Lokasi</div>
											<div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->location ?? optional($help->city)->name ?? '-' }}</div>
											@if($help->full_address)
												<div class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">{{ $help->full_address }}</div>
											@endif
										</div>

										@if(!empty($help->equipment_provided))
											<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
												<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Perlengkapan</div>
												<div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->equipment_provided }}</div>
											</div>
										@endif
									</div>
								@endif

								{{-- Mitra Info & Finish / Cancellation Time --}}
								<div class="grid grid-cols-2 gap-2 text-xs">
									@if($help->mitra)
										<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
											<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Mitra Pelaksana</div>
											<div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->mitra->name }}</div>
											@if($help->isPickup())
												<div class="mt-1 flex items-center gap-1.5 flex-wrap">
													<span class="text-[10px] text-gray-500 dark:text-gray-400">🛵 {{ $help->mitra->vehicle_display_name }}</span>
													@if(!empty($help->mitra->vehicle_plate_number))
														<span class="px-1.5 py-0.2 bg-zinc-900 dark:bg-zinc-950 text-white rounded font-mono text-[10px] font-bold tracking-wider">{{ $help->mitra->vehicle_plate_number }}</span>
													@endif
												</div>
											@endif
											@if($help->mitra->phone)
												<a href="tel:{{ $help->mitra->phone }}" class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline block truncate mt-0.5">{{ $help->mitra->phone }}</a>
											@endif
										</div>
									@else
										<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
											<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Mitra Pelaksana</div>
											<div class="font-semibold text-gray-500 dark:text-gray-400 text-xs">Tidak ada mitra</div>
										</div>
									@endif

									<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
										<div class="text-[10px] text-gray-400 font-semibold mb-0.5">{{ $isCancelled ? 'Waktu Dibatalkan' : 'Waktu Selesai' }}</div>
										<div class="font-bold text-gray-800 dark:text-gray-200">{{ optional($help->updated_at)->translatedFormat('d M Y, H:i') }} WIB</div>
									</div>
								</div>

								{{-- Completion Proof Photo (if completed and uploaded by mitra) --}}
								@if(!$isCancelled && $help->proof_photo)
									<div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60 rounded-xl space-y-2">
										<div class="flex items-center justify-between">
											<span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
												<svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
												Bukti Penyelesaian dari Mitra:
											</span>
											<span class="text-[10px] text-emerald-600 dark:text-emerald-400">Ketuk untuk perbesar</span>
										</div>
										<div class="rounded-xl overflow-hidden border border-emerald-200/80 dark:border-emerald-800 bg-gray-900/10 max-h-48 flex items-center justify-center cursor-pointer group relative"
										     @click="previewPhotoUrl = '{{ asset('storage/' . $help->proof_photo) }}'">
											<img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Selesai" class="w-full h-auto max-h-48 object-contain group-hover:scale-105 transition-transform duration-200">
											<div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
												<span class="bg-black/60 text-white text-[11px] font-medium px-2.5 py-1 rounded-lg backdrop-blur-xs flex items-center gap-1">
													<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
													Lihat Foto
												</span>
											</div>
										</div>
										@if($help->completion_notes)
											<p class="text-xs text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-emerald-100 dark:border-emerald-900/40">"{{ $help->completion_notes }}"</p>
										@endif
									</div>
								@endif

								{{-- Rating form or previously given rating (for completed tasks with mitra) --}}
								@if(!$isCancelled && $help->mitra)
									<div class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
										@livewire('customer.ratings.rate-mitra', ['helpId' => $help->id], key('rate-'.$help->id))
									</div>
								@endif
							</div>
						</div>
					@endforeach
				</div>

				<div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
					{{ $helps->links('vendor.pagination.superadmin') }}
				</div>
			@endif
		</div>
	</div>

	<!-- Lightbox Modal Foto Bukti / Preview -->
	<div x-show="previewPhotoUrl" 
	     x-cloak 
	     @click.self="previewPhotoUrl = null"
	     class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
	     x-transition:enter="transition ease-out duration-200"
	     x-transition:enter-start="opacity-0"
	     x-transition:enter-end="opacity-100"
	     x-transition:leave="transition ease-in duration-150"
	     x-transition:leave-start="opacity-100"
	     x-transition:leave-end="opacity-0"
	     @keydown.escape.window="previewPhotoUrl = null">
		<div class="relative max-w-lg w-full bg-transparent p-2 text-center">
			<button type="button" @click="previewPhotoUrl = null" class="absolute -top-10 right-0 text-white bg-gray-800/80 hover:bg-gray-700 rounded-full p-2 cursor-pointer transition">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
			<img :src="previewPhotoUrl" alt="Preview Foto Bukti" class="max-h-[80vh] w-auto mx-auto rounded-2xl shadow-2xl object-contain border border-white/20">
		</div>
	</div>
</div>
