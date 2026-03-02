<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BalanceTransaction;
use Livewire\Livewire;

class UserBalance extends Model
{
    /** @use HasFactory<\Database\Factories\UserBalanceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->user->transactions();
    }

    // Helper methods
    public function addBalance($amount, $description = null, $referenceId = null)
    {
        // Do not increment balance here — creation of a topup BalanceTransaction
        // will be processed by BalanceTransactionObserver which increments the balance.
        BalanceTransaction::create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'type' => 'topup',
            'description' => $description,
            'reference_id' => $referenceId,
            'status' => 'completed',
        ]);
        return $this;
    }

    public function deductBalance($amount, $description = null, $referenceId = null)
    {
        $this->decrement('balance', $amount);

        BalanceTransaction::create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'type' => 'deduction',
            'description' => $description,
            'reference_id' => $referenceId,
            'status' => 'completed',
        ]);

        // Emit Livewire event so dashboard components can refresh immediately
        try {
            Livewire::emit('balance-updated');
        } catch (\Throwable $e) {
            // ignore if Livewire not available in this context
        }

        return $this;
    }
}
