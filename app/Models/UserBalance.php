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
     * Menerapkan denda (penalty) kepada pengguna akibat pelanggaran.
     *
     * Berbeda dari deductBalance(), transaksi ini bertipe 'penalty' agar
     * jelas bahwa ini adalah denda — bukan potongan biasa.
     * Uang denda ini masuk ke kas administrasi sistem.
     *
     * @param  float       $amount       Nominal denda
     * @param  string|null $description  Keterangan denda (misal: "Denda Pembatalan Bantuan")
     * @param  mixed|null  $referenceId  ID bantuan / referensi terkait
     * @param  string|null $orderId      Order ID bantuan
     * @return $this
     */
    public function applyPenalty($amount, $description = null, $referenceId = null, $orderId = null)
    {
        $this->decrement('balance', $amount);

        BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $amount,
            'type'         => 'penalty',
            'description'  => $description ?? 'Penyesuaian Administrasi',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $this;
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
     *
     * Dipanggil saat customer membuat tugas baru (model v2).
     * Mengurangi saldo customer dan mencatat transaksi bertipe 'escrow_lock'.
     *
     * @param  float       $amount       Nominal tugas yang ditahan
     * @param  mixed|null  $referenceId  ID bantuan
     * @param  string|null $orderId      Order ID bantuan
     * @param  string|null $description  Deskripsi
     * @return BalanceTransaction        Transaksi escrow yang dibuat
     */
    public function lockForEscrow(float $amount, $referenceId = null, $orderId = null, ?string $description = null): BalanceTransaction
    {
        if ($this->balance < $amount) {
            throw new \RuntimeException(
                "Saldo tidak mencukupi. Saldo Anda: Rp " . number_format($this->balance, 0, ',', '.') .
                ", dibutuhkan: Rp " . number_format($amount, 0, ',', '.')
            );
        }

        $this->decrement('balance', $amount);

        $tx = BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $amount,
            'type'         => 'escrow_lock',
            'description'  => $description ?? 'Dana Ditahan untuk Permintaan Bantuan',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $tx;
    }

    /**
     * Terima pendapatan bersih dari Holding ke saldo Mitra (Earning).
     *
     * Dipanggil saat tugas selesai dikonfirmasi (model v2).
     * Menambah saldo mitra dengan nominal BERSIH (setelah potong komisi platform).
     *
     * @param  float       $netAmount    Nominal bersih setelah potong komisi
     * @param  mixed|null  $referenceId  ID bantuan
     * @param  string|null $description  Deskripsi
     * @param  string|null $orderId      Order ID bantuan
     * @return $this
     */
    public function receiveEarning(float $netAmount, $referenceId = null, ?string $description = null, $orderId = null): self
    {
        $this->increment('balance', $netAmount);

        BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $netAmount,
            'type'         => 'earning',
            'description'  => $description ?? 'Pendapatan Bantuan (Bersih setelah Komisi Platform)',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $this;
    }

    /**
     * Kembalikan dana dari Holding ke saldo Customer (Refund 100%).
     *
     * Dipanggil saat tugas dibatalkan (customer cancel atau accept cancel mitra).
     * Platform TIDAK memotong komisi apapun dari transaksi yang gagal/batal.
     *
     * @param  float       $amount       Nominal yang dikembalikan (harus = escrow amount)
     * @param  mixed|null  $referenceId  ID bantuan
     * @param  string|null $orderId      Order ID bantuan
     * @param  string|null $description  Deskripsi
     * @return $this
     */
    public function refundToCustomer(float $amount, $referenceId = null, $orderId = null, ?string $description = null): self
    {
        $this->increment('balance', $amount);

        BalanceTransaction::create([
            'user_id'      => $this->user_id,
            'amount'       => $amount,
            'type'         => 'refund',
            'description'  => $description ?? 'Pengembalian Dana 100% (Bantuan Dibatalkan)',
            'reference_id' => $referenceId,
            'order_id'     => $orderId,
            'status'       => 'completed',
        ]);

        return $this;
    }
}

