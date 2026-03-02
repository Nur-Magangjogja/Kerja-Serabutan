<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Auth\AdminLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    // Choose role before starting registration
    Volt::route('register/choose-role', 'pages.auth.register-choose-role')
        ->name('register.choose-role');

    // Multi-step Registration Routes
    Volt::route('register/step1', 'pages.auth.register-step1')
        ->name('register.step1');

    Volt::route('register/step2', 'pages.auth.register-step2')
        ->name('register.step2');

    Volt::route('register/step3', 'pages.auth.register-step3')
        ->name('register.step3');

    Volt::route('register/step4', 'pages.auth.register-step4')
        ->name('register.step4');

    // Registration Success Page (masih guest karena baru register)
    Volt::route('registration/success', 'pages.auth.registration-success')
        ->name('registration.success');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // Admin Login - separate route using Livewire component
    Route::get('admin/login', AdminLogin::class)
        ->name('admin.login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    // Admin: registration verification page
    Volt::route('admin/verification', 'pages.admin.verification')
        ->name('admin.verification');

    Route::post('logout', function () {
        $userRole = auth()->user()->role ?? '';
        $isAdmin = in_array($userRole, ['admin', 'super_admin']);

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirect admins to admin login, others to public login
        return redirect($isAdmin ? route('admin.login') : route('login'));
    })->name('logout');
});
