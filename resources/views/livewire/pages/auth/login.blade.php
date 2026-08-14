<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        // Redirect admins to admin login page
        if (in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            Auth::logout();
            $this->addError('email', 'Admin users must login at /admin/login');
            return;
        }

        Session::regenerate();

        // Determine redirect destination based on user role
        $user = auth()->user();

        // Redirect to role-specific dashboard
        if ($user->role === 'mitra') {
            $redirect = route('mitra.dashboard', absolute: false);
        } elseif ($user->role === 'kustomer') {
            $redirect = route('customer.dashboard', absolute: false);
        } else {
            // Default fallback
            $redirect = route('dashboard', absolute: false);
        }

        $this->redirectIntended(default: $redirect, navigate: false);
    }
}; ?>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="px-6 pt-6 pb-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-800">Masuk ke sayabantu</h2>
        <p class="text-xs text-gray-500 mt-1">Silakan masuk menggunakan akun Anda</p>
    </div>

    <div class="px-6 py-6">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-4">
                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-2">Username Or Email</label>
                    <input wire:model="form.email" id="email" type="email" name="email" required autofocus
                        autocomplete="username" placeholder="example@example.com"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input wire:model="form.password" id="password" type="password" name="password" required
                            autocomplete="current-password" placeholder="••••••••"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <!-- Log In Button -->
                <button type="submit" wire:loading.attr="disabled" wire:target="login"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-50 mt-6 flex items-center justify-center gap-3">
                    <svg wire:loading wire:target="login" class="animate-spin -ml-1 mr-0 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span wire:loading.remove wire:target="login">Log In</span>
                    <span wire:loading wire:target="login">Memproses...</span>
                </button>

                <!-- Forgot Password -->
                @if (Route::has('password.request'))
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" wire:navigate
                            class="text-sm text-gray-700 hover:text-gray-900 font-semibold">
                            Forgot Password?
                        </a>
                    </div>
                @endif

                <!-- Sign Up Button -->
                <a href="{{ route('register') }}" wire:navigate
                    class="block w-full bg-white hover:bg-gray-100 text-gray-700 font-bold py-3.5 rounded-full text-center transition shadow-sm border border-gray-200">
                    Sign Up
                </a>

                <!-- Small Admin Login button (centered pill) -->
                <div class="text-center mt-3">
                    <a href="/admin/login" class="inline-block text-xs text-gray-600 hover:text-primary-600 border border-gray-200 px-3 py-2 rounded-full">Admin Login</a>
                </div>

                <!-- Sign Up Link -->
                <div class="text-center pt-4">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" wire:navigate class="text-primary-600 font-semibold hover:text-primary-700">Sign Up</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>