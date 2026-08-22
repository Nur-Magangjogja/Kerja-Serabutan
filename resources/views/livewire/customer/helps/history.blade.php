<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
		<div class="max-w-md mx-auto">
			<!-- Header Section -->
			<div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
				<div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
				
				<div class="relative z-10 space-y-3">
					<div class="flex items-center justify-between text-white">
						<button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
							</svg>
						</button>

						<div class="text-center flex-1 min-w-0 px-2">
							<h1 class="text-base font-bold truncate">Riwayat Bantuan</h1>
							<p class="text-xs text-white/90 truncate mt-0.5">Bantuan yang telah selesai</p>
						</div>

						<div class="w-9 flex-shrink-0"></div>
					</div>

					{{-- Stats Cards --}}
					<div class="grid grid-cols-3 gap-2 pt-1">
						<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
							<div class="text-xl font-extrabold text-white leading-tight">{{ $completedHelps->total() ?? 0 }}</div>
							<div class="text-[11px] text-white/85 mt-0.5">Selesai</div>
						</div>

						<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
							<div class="text-[11px] text-white/85 mb-0.5">Total Nilai</div>
							<div class="text-xs font-bold text-white leading-tight">Rp {{ number_format($completedHelps->sum('amount') ?? 0, 0, ',', '.') }}</div>
						</div>

						<div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
							<div class="text-xl font-extrabold text-white leading-tight">{{ $completedHelps->unique('mitra_id')->count() ?? 0 }}</div>
							<div class="text-[11px] text-white/85 mt-0.5">Mitra</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Main Content -->
			<div class="px-5 pt-5 pb-24">

					{{-- List --}}
					@if(isset($completedHelps) && $completedHelps->isEmpty())
						<div class="text-center py-16">
							<div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
								<svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
								</svg>
							</div>
							<h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Riwayat</h3>
							<p class="text-sm text-gray-500 mb-6">Belum ada bantuan yang selesai</p>
							<a href="{{ route('customer.helps.create') }}" class="inline-block text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition" style="background: linear-gradient(to bottom right, #0098e7, #0060b0);">
								Buat Bantuan Baru
							</a>
						</div>
					@elseif(isset($completedHelps))
						<div class="space-y-3">
							@foreach($completedHelps as $help)
								<div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
									<div class="p-4">
										<div class="flex items-center gap-3 mb-3">
											<div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-gray-100 to-gray-50">
												@if($help->photo)
													<img src="{{ asset('storage/' . $help->photo) }}" alt="foto" class="w-full h-full object-cover">
												@else
													<div class="w-full h-full flex items-center justify-center">
														<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
														</svg>
													</div>
												@endif
											</div>

											<div class="flex-1 min-w-0">
												<h3 class="font-semibold text-gray-900 truncate">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
												<p class="text-xs text-gray-500 truncate">{{ optional($help->city)->name }} • {{ optional($help->updated_at)->format('d M Y') }}</p>
											</div>

											<div class="text-right flex-shrink-0">
												<div class="text-sm font-bold" style="color: #0098e7;">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</div>
												<div class="flex flex-col items-end mt-1 gap-1">
													<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
														<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
															<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
														</svg>
														Selesai
													</span>

													@if($help->rating)
														<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">
															<svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20">
																<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
															</svg>
															<span>{{ number_format($help->rating->rating, 1) }} (Dinilai)</span>
														</span>
													@else
														<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-medium">
															Belum dinilai
														</span>
													@endif
												</div>
											</div>
										</div>

										<button @click="open = !open" class="w-full flex items-center justify-center gap-2 text-sm font-semibold py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition" style="color: #0098e7;">
											<span x-text="open ? 'Sembunyikan Detail' : 'Lihat Detail & Penilaian'"></span>
											<svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
											</svg>
										</button>
									</div>

									<div x-show="open" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 mt-3 pt-4">
										<div class="space-y-4">
											@if($help->photo)
												<img src="{{ asset('storage/' . $help->photo) }}" alt="foto bantuan" class="w-full h-48 object-cover rounded-xl">
											@endif

											@if($help->description)
												<div>
													<h4 class="text-sm font-semibold text-gray-900 mb-2">Deskripsi</h4>
													<p class="text-sm text-gray-700 leading-relaxed">{{ $help->description }}</p>
												</div>
											@endif

											<div class="grid grid-cols-2 gap-3">
												<div>
													<div class="text-xs text-gray-500 mb-1">Lokasi</div>
													<div class="text-sm text-gray-900">{{ $help->full_address ?? optional($help->city)->name ?? '-' }}</div>
												</div>

												@if($help->mitra)
													<div>
														<div class="text-xs text-gray-500 mb-1">Mitra</div>
														<div class="text-sm text-gray-900">{{ $help->mitra->name }}</div>
														@if($help->mitra->phone)
															<a href="tel:{{ $help->mitra->phone }}" class="text-xs font-semibold" style="color: #0098e7;">{{ $help->mitra->phone }}</a>
														@endif
													</div>
												@endif
											</div>

											<div class="text-xs text-gray-500">
												Selesai pada: <span class="text-gray-700 font-medium">{{ optional($help->updated_at)->format('d M Y H:i') }}</span>
											</div>

											{{-- Rating form or previously given rating --}}
											@if($help->mitra)
												<div class="pt-3 border-t border-gray-100">
													@livewire('customer.ratings.rate-mitra', ['helpId' => $help->id], key('rate-'.$help->id))
												</div>
											@endif
										</div>
									</div>
								</div>
							@endforeach
						</div>

						@if($completedHelps->hasPages())
							<div class="mt-6">
								{{ $completedHelps->links() }}
							</div>
						@endif
					@else
						<div class="text-center py-8 text-gray-600">Data riwayat belum tersedia.</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
