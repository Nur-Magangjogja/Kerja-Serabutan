<?php

namespace App\Livewire\Customer\Balance;

use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AddModal extends Component
{
    #[Validate('required|numeric|min:1')]
    public $amount = '';

    #[Validate('nullable|string|max:255')]
    public $description = '';

    public $showModal = false;
    public bool $isSubmitting = false;

    #[On('openAddBalance')]
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['amount', 'description', 'isSubmitting']);
        $this->showModal = false;
    }

    public function addBalance()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate();

        $user = auth()->user();
        if (!$user) {
            return;
        }

        $lock = Cache::lock("user_add_balance_submit_{$user->id}", 10);
        if (!$lock->get()) {
            return;
        }

        $this->isSubmitting = true;

        try {
            DB::beginTransaction();

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

            DB::commit();

            session()->flash('success', 'Saldo berhasil ditambahkan!');

            $this->dispatch('balance-updated');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->isSubmitting = false;
            $lock->release();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render()
    {
        return view('livewire.customer.balance.add-modal');
    }
}
