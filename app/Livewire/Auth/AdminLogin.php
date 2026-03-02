<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.blank')]
class AdminLogin extends Component
{
    public LoginForm $form;

    public function mount()
    {
        // Initialize form if user is already logged in as admin
        if (auth()->check() && auth()->user()->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        } elseif (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login()
    {
        \Log::info('Admin login attempt started');

        $this->validate();

        $this->form->authenticate();

        \Log::info('Authentication successful for user: ' . auth()->user()->email);
        \Log::info('User role: ' . auth()->user()->role);

        // Check if user is admin or super_admin
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            \Log::warning('User is not admin, logging out');
            Auth::logout();
            $this->addError('email', 'Unauthorized. Only admins can login here.');
            return;
        }

        \Log::info('Admin login successful, redirecting to dashboard');
        \Log::info('Current user role for redirect: ' . auth()->user()->role);

        Session::regenerate();

        // Redirect based on role
        if (auth()->user()->role === 'super_admin') {
            \Log::info('Redirecting to superadmin dashboard');
            return redirect()->route('superadmin.dashboard');
        } else {
            \Log::info('Redirecting to admin dashboard');
            return redirect()->route('admin.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.auth.admin-login');
    }
}
