<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// 1. Quick Register Pages (Blocked for logged-in admin, superadmin, & verified users)
Route::middleware(['guest', 'block_admin_registration'])->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    // Backward compatibility redirect for old choose-role link
    Route::get('register/choose-role', function () {
        return redirect()->route('register');
    })->name('register.choose-role');
});

// 2. Authentication & Password Recovery Pages (Accessible by all guests)
Route::middleware('guest')->group(function () {
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // Admin Login - aliases directly to unified login
    Route::get('admin/login', function () {
        return redirect()->route('login');
    })->name('admin.login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

// 3. Registration Success Page (Accessible by unapproved/pending users only)
Volt::route('registration/success', 'pages.auth.registration-success')
    ->middleware('block_admin_registration')
    ->name('registration.success');

// 4. Email Verification Link Handler (Direct email link)
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');

// 5. Registration Onboarding & Identity Verification (Step 1 - 4 & Email Notice)
Route::middleware(['auth', 'block_admin_registration'])->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Volt::route('register/step1', 'pages.auth.register-step1')
        ->name('register.step1');

    Volt::route('register/step2', 'pages.auth.register-step2')
        ->name('register.step2');

    Volt::route('register/step3', 'pages.auth.register-step3')
        ->name('register.step3');

    Volt::route('register/step4', 'pages.auth.register-step4')
        ->name('register.step4');

    Route::post('register/cancel', function (\App\Livewire\Actions\CancelRegistration $cancelRegistration) {
        $cancelRegistration();
        return redirect()->route('login');
    })->name('register.cancel');
});

// 6. Authenticated Global Actions (Password confirmation & Logout)
Route::middleware('auth')->group(function () {
    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('registration_uuid'));
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('registration_role'));
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('registration_step1_draft'));
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('sb_register_draft'));
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('sb_register_leave_time'));

        // Redirect all users cleanly to unified login
        return redirect()->route('login');
    })->name('logout');
});
