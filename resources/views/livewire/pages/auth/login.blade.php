<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.blank')] class extends Component {
    public LoginForm $form;

    public function mount(): void
    {
        // If already authenticated, redirect directly to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'super_admin') {
                $this->redirect(route('superadmin.dashboard', absolute: false), navigate: false);
                return;
            } elseif ($user->role === 'admin') {
                $this->redirect(route('admin.dashboard', absolute: false), navigate: false);
                return;
            } elseif ($user->role === 'mitra') {
                $this->redirect(route('mitra.dashboard', absolute: false), navigate: false);
                return;
            } else {
                $this->redirect(route('customer.dashboard', absolute: false), navigate: false);
                return;
            }
        }
    }

    /**
     * Handle an incoming unified authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        // Redirect based on user role
        if ($user->role === 'super_admin') {
            $redirect = route('superadmin.dashboard', absolute: false);
        } elseif ($user->role === 'admin') {
            $redirect = route('admin.dashboard', absolute: false);
        } elseif ($user->role === 'mitra') {
            $redirect = route('mitra.dashboard', absolute: false);
        } elseif ($user->role === 'customer') {
            $redirect = route('customer.dashboard', absolute: false);
        } else {
            $redirect = route('dashboard', absolute: false);
        }

        $this->redirectIntended(default: $redirect, navigate: false);
    }
}; ?>

@php
    $siteLogo = \App\Models\AppSetting::get('app_logo');
@endphp

<div x-data="{ showPassword: false }" class="relative min-h-screen flex flex-col justify-center overflow-x-hidden font-sans">

    <!-- Reusable Splash Screen Component -->
    <x-splash-screen duration="850" />

    <!-- Main Split Login Container -->
    <div class="min-h-screen flex flex-col lg:flex-row w-full bg-gray-50 dark:bg-gray-900">
        
        <!-- Left Side - Feature Showcase & Branding (Desktop/Tablet) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-primary-700 p-12 lg:p-16 flex-col justify-between text-white shadow-2xl">
            <!-- Top Brand Header -->
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center p-2 shadow-inner">
                        @if ($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="w-full h-full object-contain" />
                        @else
                            <svg class="w-6 h-6 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @endif
                    </div>
                    <x-brand-title as="span" size="2xl" theme="light" withDot="true" />
                </div>
                <p class="text-sm text-primary-100 font-medium ml-1">
                    {{ \App\Models\AppSetting::get('app_tagline', 'Platform Bantuan Sosial & Layanan') }}
                </p>
            </div>

            <!-- Middle Feature Badges -->
            <div class="relative z-10 my-auto py-8 max-w-lg space-y-6">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight mb-3 tracking-tight">
                        Mudahkan Urusan Anda Bersama Kami
                    </h2>
                    <p class="text-white/90 text-sm leading-relaxed">
                        {{ \App\Models\AppSetting::get('app_description', 'Solusi bantuan cepat, praktis, dan terpercaya untuk menyelesaikan berbagai kebutuhan harian Anda.') }}
                    </p>
                </div>

                <div class="space-y-3.5 pt-2">
                    <!-- Feature 1 -->
                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-white/10 border border-white/15 transition-all hover:bg-white/15">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Bantuan Cepat & Praktis</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Temukan bantuan untuk berbagai kebutuhan atau tawarkan keahlian Anda dengan mudah kapan saja.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-white/10 border border-white/15 transition-all hover:bg-white/15">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Komunitas Saling Membantu</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Terhubung langsung dengan rekan terpercaya di sekitar Anda dalam lingkungan yang saling mendukung.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-white/10 border border-white/15 transition-all hover:bg-white/15">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Transparan & Real-Time</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Pantau status permintaan bantuan, pesan percakapan, dan perkembangan aktivitas secara langsung.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Footer -->
            <div class="relative z-10 text-xs text-white/80 flex items-center justify-between border-t border-white/15 pt-4">
                <span>&copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', 'SayaBantu') }}. All rights reserved.</span>
                <span class="inline-flex items-center gap-1.5 font-medium text-white">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Online
                </span>
            </div>
        </div>

        <!-- Right Side - Login Form Card -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-5 sm:p-10 lg:p-14 min-h-screen">
            <div class="w-full max-w-md">
                
                <!-- Mobile Branding (Visible only on mobile/tablet) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex w-14 h-14 rounded-2xl bg-primary-600 p-2 text-white shadow-md shadow-primary-600/20 mb-3 items-center justify-center">
                        @if ($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="w-full h-full object-contain" />
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <x-brand-title as="h1" size="2xl" theme="dark" withDot="true" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">{{ \App\Models\AppSetting::get('app_tagline', 'Platform Bantuan Sosial') }}</p>
                    </div>
                </div>

                <!-- Main Login Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-7 sm:p-9">
                    
                    <!-- Card Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Masuk ke Akun</h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1.5">
                            Silakan masukkan email/username dan kata sandi Anda.
                        </p>
                    </div>

                    <!-- Session Status Alert -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Login Form -->
                    <form wire:submit="login" class="space-y-4">
                        
                        <!-- Email / Username Field -->
                        <div>
                            <label for="login_identifier" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wider">
                                Email atau Username
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input 
                                    wire:model="form.email" 
                                    id="login_identifier" 
                                    type="text" 
                                    required 
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nama@email.com atau username"
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 text-sm focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all shadow-2xs font-medium"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
                        </div>

                        <!-- Password Field with Visibility Toggle -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="login_password" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Kata Sandi
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-primary-600 dark:text-sky-400 hover:underline font-semibold">
                                        Lupa kata sandi?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input 
                                    wire:model="form.password" 
                                    id="login_password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-11 py-3 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 text-sm focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all shadow-2xs font-medium"
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                    <!-- Eye Icon (Show) -->
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <!-- Eye Slash Icon (Hide) -->
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="flex items-center pt-1">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                <input 
                                    wire:model="form.remember" 
                                    id="remember_me" 
                                    type="checkbox" 
                                    class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 transition"
                                />
                                <span class="ml-2 text-xs font-semibold text-gray-600 dark:text-gray-400 select-none">
                                    Ingat saya di perangkat ini
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="login"
                                class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-50"
                            >
                                <svg wire:loading wire:target="login" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="login">Masuk ke Aplikasi</span>
                                <span wire:loading wire:target="login">Memverifikasi...</span>
                            </button>
                        </div>
                    </form>

                    <!-- Register Section -->
                    <div class="mt-7 pt-5 border-t border-gray-100 dark:border-gray-700/80 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Belum memiliki akun?
                            <a href="{{ route('register') }}" wire:navigate 
                               class="font-bold text-primary-600 dark:text-sky-400 hover:text-primary-700 dark:hover:text-sky-300 hover:underline ml-1">
                                Daftar Sekarang
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Footer Copyright for Mobile -->
                <p class="lg:hidden text-center text-xs text-gray-400 dark:text-gray-500 mt-6 font-medium">
                    &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', 'SayaBantu') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>