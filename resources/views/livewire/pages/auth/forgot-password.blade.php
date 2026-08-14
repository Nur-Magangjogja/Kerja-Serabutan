<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Mobile Container -->
    <div class="max-w-md mx-auto min-h-screen flex flex-col">

        <!-- Logo Section (Same as Welcome Page) -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-8">
            <!-- Logo Icon -->
            <div class="mb-4">
                <svg class="w-24 h-24 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <!-- Hands Helping Icon -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>

            <!-- App Name -->
            <h1 class="text-3xl font-bold text-primary-500 mb-6">sayabantu</h1>

            <!-- Header with Gradient -->
            <div
                class="bg-gradient-to-br from-primary-400 via-primary-500 to-primary-600 px-6 pt-6 pb-8 shadow-lg rounded-2xl w-full">
                <div class="flex flex-col items-center text-white">
                    <!-- Icon -->
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-4 mb-3">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <!-- Title -->
                    <h2 class="text-xl font-bold mb-2">Forgot Password?</h2>
                    <p class="text-xs text-white/90 text-center leading-relaxed">
                        No problem. Just let us know your email address and we will email you a password reset link.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 px-6 py-8">

            <!-- Success Message -->
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 border-2 border-green-200 rounded-xl flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-green-700 font-medium">{{ session('status') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="sendPasswordResetLink" class="space-y-5">
                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 mb-1.5">
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            Email Address
                            <span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <div class="relative">
                        <input type="email" id="email" wire:model="email" required autofocus
                            class="w-full px-3.5 py-3.5 text-sm rounded-xl border-2 border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-200 transition bg-white shadow-sm pl-11"
                            placeholder="your@email.com">
                        <div class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </div>
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold text-sm py-3.5 rounded-xl hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:from-blue-600 hover:to-blue-700">
                    <span wire:loading.remove wire:target="sendPasswordResetLink"
                        class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Email Password Reset Link
                    </span>
                    <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center justify-center">
                        <svg class="animate-spin h-4 w-4 mr-2 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Sending...
                    </span>
                </button>

                <!-- Back to Login -->
                <div class="text-center pt-2">
                    <a href="{{ route('login') }}"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Login
                    </a>
                </div>
            </form>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 border-2 border-blue-100 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Check Your Email</h4>
                        <p class="text-xs text-blue-700 leading-relaxed">
                            We'll send you a secure link to reset your password. The link will expire in 60 minutes for
                            security reasons.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>