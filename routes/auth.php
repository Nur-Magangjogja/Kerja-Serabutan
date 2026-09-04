<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    // 1. Direct Quick Register with Email, Name, Password & Role
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    // Backward compatibility redirect for old choose-role link
    Route::get('register/choose-role', function () {
        return redirect()->route('register');
    })->name('register.choose-role');

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

// Registration Success Page (Accessible by guest and auth)
Volt::route('registration/success', 'pages.auth.registration-success')
    ->name('registration.success');

// Email Verification Link Handler (dapat dibuka langsung dari aplikasi email)
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    // 2. Email Verification Notice (Halaman tunggu verifikasi)
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    // 3. Multi-Step Onboarding & Identity (KTP) Verification Routes (Step 1 - 4)
    Volt::route('register/step1', 'pages.auth.register-step1')
        ->name('register.step1');

    Volt::route('register/step2', 'pages.auth.register-step2')
        ->name('register.step2');

    Volt::route('register/step3', 'pages.auth.register-step3')
        ->name('register.step3');

    Volt::route('register/step4', 'pages.auth.register-step4')
        ->name('register.step4');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirect all users cleanly to unified login
        return redirect()->route('login');
    })->name('logout');
});
