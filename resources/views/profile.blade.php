<x-app-layout>
    <x-slot name="title">Profile</x-slot>

    <style>
        @keyframes ripple {
            0% {
                transform: scale(0);
                opacity: 0.6;
            }
            100% {
                transform: scale(4);
                opacity: 0;
            }
        }

        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes bounce-gentle {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .menu-card {
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.5s ease-out backwards;
        }

        .menu-card:nth-child(1) { animation-delay: 0.1s; }
        .menu-card:nth-child(2) { animation-delay: 0.15s; }
        .menu-card:nth-child(3) { animation-delay: 0.2s; }
        .menu-card:nth-child(4) { animation-delay: 0.25s; }
        .menu-card:nth-child(5) { animation-delay: 0.3s; }
        .menu-card:nth-child(6) { animation-delay: 0.35s; }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(0, 152, 231, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .menu-card:active::before {
            width: 300px;
            height: 300px;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(0, 152, 231, 0.3);
        }

        .menu-card:active {
            transform: translateY(-2px) scale(0.98);
        }

        .icon-gradient {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-card:hover .icon-gradient {
            transform: rotate(5deg) scale(1.1);
        }

        .menu-card:active .icon-gradient {
            transform: rotate(0deg) scale(0.95);
        }

        .stats-card {
            animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
        }

        .avatar-container {
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
        }

        .logout-modal-content {
            animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:active::after {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(220, 38, 38, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }
    </style>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        @php
            $user = auth()->user();
            
            // Riwayat Bantuan (bantuan yang telah selesai dan tampil di riwayat)
            $completedHelps = \App\Models\Help::where('user_id', $user->id)
                ->whereIn('status', ['selesai', 'completed'])
                ->count();

            // Bantuan yang sedang aktif (menunggu mitra, sedang dikerjakan mitra, menunggu konfirmasi customer)
            $activeHelps = \App\Models\Help::where('user_id', $user->id)
                ->whereIn('status', ['menunggu_mitra', 'memperoleh_mitra', 'waiting_customer_confirmation'])
                ->count();

            // Total ulasan yang diberikan customer kepada mitra
            $reviewsGivenCount = \App\Models\Rating::where(function ($q) use ($user) {
                $q->where('rater_id', $user->id)
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('user_id', $user->id)
                         ->where('type', 'customer_to_mitra');
                  });
            })->count();
        @endphp

        <div class="max-w-md mx-auto">
            <!-- Header - BRImo Style -->
            <div class="px-5 pt-5 pb-28 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between text-white mb-6">
                        <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <h1 class="text-lg font-bold">Profil Saya</h1>

                        <a href="{{ route('customer.notifications.index') }}" title="Notifikasi" class="p-2 hover:bg-white/20 rounded-lg transition cursor-pointer relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @php
                                $unreadNotif = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                            @endphp
                            @if($unreadNotif > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </a>
                    </div>

                    <!-- Profile Avatar & Info -->
                    <div class="text-center avatar-container">
                        <div class="relative inline-block">
                            @php
                                $__avatar = optional($user)->selfie_photo ?? optional($user)->photo ?? null;
                            @endphp
                            @if($__avatar)
                                <img src="{{ asset('storage/' . $__avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover mx-auto ring-4 ring-white/30 shadow-xl">
                            @else
                                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl font-bold mx-auto ring-4 ring-white/30 shadow-xl">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif

                            <button onclick="Livewire.dispatch('openModal')" class="absolute bottom-0 right-0 bg-white p-1.5 rounded-full shadow-lg hover:scale-110 active:scale-95 transition-transform duration-200 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>

                        <h2 class="text-lg font-bold text-white mt-3">{{ $user->name }}</h2>
                        <p class="text-xs text-white/80 mt-0.5">{{ $user->email }}</p>

                        @if($user->is_verified ?? false)
                            <div class="inline-flex items-center gap-1 bg-green-500/20 backdrop-blur-sm px-2.5 py-1 rounded-full mt-2">
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-2.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-[11px] font-semibold text-white">Terverifikasi</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1 bg-yellow-500/20 backdrop-blur-sm px-2.5 py-1 rounded-full mt-2">
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-[11px] font-semibold text-white">Belum Verifikasi</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Curved separator -->
                <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" class="fill-gray-50 dark:fill-gray-900"></path>
                </svg>
            </div>

            <!-- Stats Card (overlapping header) -->
            <div class="px-5 -mt-16 relative z-20">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 stats-card">
                    <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700">
                        <x-profile-stat-card :value="$completedHelps" label="Riwayat Bantuan" colorClass="text-primary-600 dark:text-primary-400" rounded="rounded-l-2xl" />
                        <x-profile-stat-card :value="$activeHelps" label="Bantuan Aktif" colorClass="text-amber-500 dark:text-amber-400" />
                        <x-profile-stat-card :value="$reviewsGivenCount" label="Ulasan Saya" colorClass="text-yellow-500" rounded="rounded-r-2xl" :isRating="true" />
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="px-5 pt-5 pb-24 space-y-4">
                @livewire('customer.update-profile-photo')

                <!-- Section: Akun & Aktivitas -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                    <x-profile-menu-item :href="route('profile.edit')" title="Edit Profil" subtitle="Ubah nama, email, & nomor kontak" iconBg="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </x-profile-menu-item>

                    <x-profile-menu-item :href="route('customer.transactions.index')" title="Dompet & Saldo" subtitle="Riwayat transaksi, top up, & mutasi saldo" iconBg="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </x-profile-menu-item>

                    <x-profile-menu-item :href="route('customer.helps.history')" title="Riwayat Bantuan" subtitle="Daftar seluruh bantuan yang telah selesai" iconBg="bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </x-profile-menu-item>

                    <x-profile-menu-item :href="route('customer.ratings')" title="Ulasan Saya" subtitle="Rating & ulasan yang Anda berikan ke mitra" iconBg="bg-amber-50 dark:bg-amber-900/30 text-amber-500 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-profile-menu-item>
                </div>

                <!-- Section: Preferensi & Dukungan -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                    <x-profile-menu-item :href="route('profile.settings')" title="Pengaturan" subtitle="Tema tampilan, notifikasi, & keamanan" iconBg="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-profile-menu-item>

                    <x-profile-menu-item :href="route('customer.help-support')" title="Bantuan & Dukungan" subtitle="Pusat bantuan & FAQ layanan" iconBg="bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-profile-menu-item>
                </div>

                <!-- Logout Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <x-profile-menu-item :onClick="'document.getElementById(\'logout-modal\').classList.remove(\'hidden\')'" title="Keluar Akun" subtitle="Keluar dari sesi akun SayaBantu" iconBg="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400" :danger="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </x-profile-menu-item>
                </div>
            </div>

            <!-- Logout Modal -->
            <div id="logout-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-5" onclick="if(event.target === this) this.classList.add('hidden')">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-sm w-full p-6 transform transition-all border border-gray-100 dark:border-gray-700">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Logout Akun</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Apakah Anda yakin ingin keluar dari aplikasi?</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="space-y-2.5">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-red-600/30 cursor-pointer text-sm">
                            Ya, Logout
                        </button>
                        <button type="button" onclick="document.getElementById('logout-modal').classList.add('hidden')" class="w-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer text-sm">
                            Batal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>