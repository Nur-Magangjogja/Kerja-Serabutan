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
        return redirect()->route('login');
    }

    public function render()
    {
        return redirect()->route('login');
    }
}
