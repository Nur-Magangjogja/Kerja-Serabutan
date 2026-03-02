<?php

namespace App\Livewire\Customer;

use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;

class AddBalanceModal extends Component
{
    #[Validate('required|numeric|min:1')]
    public $amount = '';

    #[Validate('nullable|string|max:255')]
    public $description = '';

    public $showModal = false;

    #[On('openAddBalance')]
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['amount', 'description']);
        $this->showModal = false;
    }

    public function addBalance()
    {
        $this->validate();

        try {
            $user = auth()->user();

            // Get or create user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            // Create transaction
            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => $this->amount,
                'type' => 'topup',
                'description' => $this->description ?: 'Topup Saldo',
                'status' => 'completed',
            ]);

            // Update balance
            $userBalance->increment('balance', $this->amount);

            session()->flash('success', 'Saldo berhasil ditambahkan!');

            $this->dispatch('balance-updated');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.customer.add-balance-modal');
    }
}
