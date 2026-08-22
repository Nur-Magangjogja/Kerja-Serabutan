<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="space-y-6">
    <!-- Header Icon & Title -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-600/20 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Atur Kata Sandi Baru</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Buat kata sandi baru yang kuat dan aman untuk akun Anda.
        </p>
    </div>

    <form wire:submit="resetPassword" class="space-y-4">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Alamat Email <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                    </svg>
                </div>
                <input type="email" id="email" wire:model="email" required autofocus autocomplete="username"
                    class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs"
                    placeholder="nama@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Kata Sandi Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input type="password" id="password" wire:model="password" required autocomplete="new-password"
                    class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs"
                    placeholder="Minimal 8 karakter">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <input type="password" id="password_confirmation" wire:model="password_confirmation" required autocomplete="new-password"
                    class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs"
                    placeholder="Ulangi kata sandi baru">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                <span wire:loading.remove>Simpan Kata Sandi Baru</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
