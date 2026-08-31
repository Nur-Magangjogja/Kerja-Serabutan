<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BalanceTransaction;

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
    public function addBalance($amount, $description = null, $referenceId = null, $orderId = null)
    {
        // Do not increment balance here — creation of a topup BalanceTransaction
        // will be processed by BalanceTransactionObserver which increments the balance.
        BalanceTransaction::create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'type' => 'topup',
            'description' => $description,
            'reference_id' => $referenceId,
            'order_id' => $orderId,
            'status' => 'completed',
        ]);
        return $this;
    }

    public function deductBalance($amount, $description = null, $referenceId = null, $orderId = null)
    {
        $this->decrement('balance', $amount);

        BalanceTransaction::create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'type' => 'deduction',
            'description' => $description,
            'reference_id' => $referenceId,
            'order_id' => $orderId,
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * Menerapkan pencatatan pembatalan tugas pengguna.
     *
     * @param  float       $amount       Nominal transaksi
     * @param  string|null $description  Keterangan (misal: "Pembatalan Tugas Bantuan")
     * @param  mixed|null  $referenceId  ID bantuan / referensi terkait
     * @param  string|null $orderId      Order ID bantuan
     * @return $this
     */
    public function applyCancellation($amount, $description = null, $referenceId = null, $orderId = null)
    {
        $this->decrement('balance', $amount);

        BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $amount,
            'type'         => 'cancellation',
            'description'  => $description ?? 'Pembatalan Tugas Bantuan',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $this;
    }

    /**
     * Alias backward-compatibility untuk applyCancellation.
     */
    public function applyPenalty($amount, $description = null, $referenceId = null, $orderId = null)
    {
        return $this->applyCancellation($amount, $description, $referenceId, $orderId);
    }

    /**
     * Potong saldo untuk penarikan dana (Withdraw) yang disetujui admin.
     * Mencatat transaksi bertipe 'withdraw' agar sinkron dengan laporan keuangan.
     */
    public function withdrawBalance(float $amount, $referenceId = null, $orderId = null, ?string $description = null): BalanceTransaction
    {
        $this->decrement('balance', $amount);

        $transaction = BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $amount,
            'type'         => 'withdraw',
            'description'  => $description ?? 'Penarikan Dana (Withdraw) → Rekening Bank/E-Wallet',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $transaction;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MODEL V2: Escrow / Commission-Based Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tahan dana customer ke Holding (Escrow Lock).
     */
    public function lockForEscrow(float $amount, $referenceId = null, $orderId = null, ?string $description = null, ?string $idempotencyKey = null): BalanceTransaction
    {
        if ($this->balance < $amount) {
            throw new \RuntimeException(
                "Saldo tidak mencukupi. Saldo Anda: Rp " . number_format($this->balance, 0, ',', '.') .
                ", dibutuhkan: Rp " . number_format($amount, 0, ',', '.')
            );
        }

        $this->decrement('balance', $amount);

        $tx = BalanceTransaction::create([
            'idempotency_key' => $idempotencyKey ?? ($referenceId ? "help:{$referenceId}:escrow_lock:{$this->user_id}" : null),
            'user_id'         => $this->user_id,
            'amount'          => $amount,
            'direction'       => 'debit',
            'type'            => 'escrow_lock',
            'description'     => $description ?? 'Dana Ditahan untuk Permintaan Bantuan',
            'reference_id'    => $referenceId,
            'reference_type'  => $referenceId ? 'help' : null,
            'order_id'        => $orderId,
            'status'          => 'completed',
        ]);

        return $tx;
    }

    /**
     * Terima pendapatan bersih dari Holding ke saldo Mitra (Earning).
     */
    public function receiveEarning(float $netAmount, $referenceId = null, ?string $description = null, $orderId = null, ?string $idempotencyKey = null): self
    {
        $this->increment('balance', $netAmount);

        BalanceTransaction::create([
            'idempotency_key' => $idempotencyKey ?? ($referenceId ? "help:{$referenceId}:earning:{$this->user_id}" : null),
            'user_id'         => $this->user_id,
            'amount'          => $netAmount,
            'direction'       => 'credit',
            'type'            => 'earning',
            'description'     => $description ?? 'Pendapatan Bantuan (Bersih setelah Komisi Platform)',
            'reference_id'    => $referenceId,
            'reference_type'  => $referenceId ? 'help' : null,
            'order_id'        => $orderId,
            'status'          => 'completed',
        ]);

        return $this;
    }

    /**
     * Kembalikan dana dari Holding ke saldo Customer (Refund 100%).
     */
    public function refundToCustomer(float $amount, $referenceId = null, $orderId = null, ?string $description = null, ?string $idempotencyKey = null): self
    {
        $this->increment('balance', $amount);

        BalanceTransaction::create([
            'idempotency_key' => $idempotencyKey ?? ($referenceId ? "help:{$referenceId}:refund:{$this->user_id}" : null),
            'user_id'         => $this->user_id,
            'amount'          => $amount,
            'direction'       => 'credit',
            'type'            => 'refund',
            'description'     => $description ?? 'Pengembalian Dana (Bantuan Dibatalkan / Sengketa)',
            'reference_id'    => $referenceId,
            'reference_type'  => $referenceId ? 'help' : null,
            'order_id'        => $orderId,
            'status'          => 'completed',
        ]);

        return $this;
    }
}

