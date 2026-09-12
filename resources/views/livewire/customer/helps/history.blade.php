<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors">
	<div class="max-w-md mx-auto">
		<!-- Header Section -->
		<div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
			<div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
			
			<div class="relative z-10 space-y-3">
				<div class="text-white text-center">
					<h1 class="text-base font-bold truncate">Riwayat Bantuan</h1>
					<p class="text-xs text-white/90 truncate mt-0.5">Bantuan yang telah selesai & tuntas</p>
				</div>

				{{-- Stats Cards --}}
				<div class="grid grid-cols-3 gap-2 pt-1">
					<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20 shadow-2xs">
						<div class="text-lg sm:text-xl font-extrabold text-white leading-tight">{{ $completedHelps->total() ?? 0 }}</div>
						<div class="text-[11px] text-white/85 mt-0.5 font-medium">Selesai</div>
					</div>

					<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20 shadow-2xs">
						<div class="text-[11px] text-white/85 mb-0.5 font-medium">Total Nilai</div>
						<div class="text-xs font-bold text-white leading-tight truncate">Rp {{ number_format($completedHelps->sum('amount') ?? 0, 0, ',', '.') }}</div>
					</div>

					<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20 shadow-2xs">
						<div class="text-lg sm:text-xl font-extrabold text-white leading-tight">{{ $completedHelps->unique('mitra_id')->count() ?? 0 }}</div>
						<div class="text-[11px] text-white/85 mt-0.5 font-medium">Mitra</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Main Content -->
		<div class="px-5 pt-5 pb-24">
			{{-- List --}}
			@if(isset($completedHelps) && $completedHelps->isEmpty())
				<div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/70 p-6 shadow-xs">
					<div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 flex items-center justify-center text-sky-600 dark:text-sky-400 shadow-2xs">
						<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
						</svg>
					</div>
					<h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Belum Ada Riwayat</h3>
					<p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Belum ada permintaan bantuan yang diselesaikan</p>
					<a href="{{ route('customer.helps.create') }}" class="inline-flex items-center gap-1.5 text-white text-xs px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-sky-600 to-[#0077cc] hover:from-sky-700 hover:to-[#0060b0] shadow-xs hover:shadow-md transition cursor-pointer">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
						<span>Buat Bantuan Baru</span>
					</a>
				</div>
			@elseif(isset($completedHelps))
				<div class="space-y-3">
					@foreach($completedHelps as $help)
						<div x-data="{ isExpanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/70 shadow-xs hover:shadow-md transition-all overflow-hidden">
							<div class="p-4">
								<div class="flex items-start gap-3.5 mb-3">
									<div class="w-13 h-13 rounded-2xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-sky-100 to-blue-50 dark:from-sky-950/60 dark:to-blue-900/40 border border-sky-200/60 dark:border-sky-800/60 flex items-center justify-center shadow-2xs">
										@if($help->photo)
											<img src="{{ asset('storage/' . $help->photo) }}" alt="foto" class="w-full h-full object-cover">
										@else
											<svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
											</svg>
										@endif
									</div>

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
													🛠️ Kerja di Lokasi
												</span>
											@endif
										</div>

										<h3 class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
										<p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ optional($help->city)->name ?? '-' }} • {{ optional($help->updated_at)->translatedFormat('d M Y') }}</p>
									</div>

									<div class="text-right flex-shrink-0">
										<div class="text-sm font-black text-sky-600 dark:text-sky-400">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</div>
										<div class="flex flex-col items-end mt-1 gap-1">
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
										</div>
									</div>
								</div>

								<button @click="isExpanded = !isExpanded" class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sky-600 dark:text-sky-400 hover:bg-sky-50/50 dark:hover:bg-gray-750 transition cursor-pointer">
									<span x-text="isExpanded ? 'Sembunyikan Detail' : 'Lihat Detail & Penilaian'"></span>
									<svg :class="isExpanded ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
									</svg>
								</button>
							</div>

							<div x-show="isExpanded" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700/60 pt-4 space-y-3.5">
								@if($help->photo)
									<div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 max-h-52 flex items-center justify-center">
										<img src="{{ asset('storage/' . $help->photo) }}" alt="foto bantuan" class="w-full h-auto max-h-52 object-contain">
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

								{{-- Mitra Info & Finish Time --}}
								<div class="grid grid-cols-2 gap-2 text-xs">
									@if($help->mitra)
										<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
											<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Mitra Pelaksana</div>
											<div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->mitra->name }}</div>
											@if($help->mitra->phone)
												<a href="tel:{{ $help->mitra->phone }}" class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline block truncate">{{ $help->mitra->phone }}</a>
											@endif
										</div>
									@endif

									<div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
										<div class="text-[10px] text-gray-400 font-semibold mb-0.5">Waktu Selesai</div>
										<div class="font-bold text-gray-800 dark:text-gray-200">{{ optional($help->updated_at)->translatedFormat('d M Y, H:i') }} WIB</div>
									</div>
								</div>

								{{-- Completion Proof Photo (if uploaded by mitra) --}}
								@if($help->proof_photo)
									<div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60 rounded-xl space-y-2">
										<span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
											<svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
											Bukti Penyelesaian dari Mitra:
										</span>
										<a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-emerald-200/80 dark:border-emerald-800 bg-gray-900/10 max-h-48 flex items-center justify-center">
											<img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Selesai" class="w-full h-auto max-h-48 object-contain">
										</a>
										@if($help->completion_notes)
											<p class="text-xs text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-emerald-100 dark:border-emerald-900/40">"{{ $help->completion_notes }}"</p>
										@endif
									</div>
								@endif

								{{-- Rating form or previously given rating --}}
								@if($help->mitra)
									<div class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
										@livewire('customer.ratings.rate-mitra', ['helpId' => $help->id], key('rate-'.$help->id))
									</div>
								@endif
							</div>
						</div>
					@endforeach
				</div>

				<div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
					{{ $completedHelps->links('vendor.pagination.superadmin') }}
				</div>
			@else
				<div class="text-center py-8 text-gray-500 dark:text-gray-400">Data riwayat belum tersedia.</div>
			@endif
		</div>
	</div>
</div>
