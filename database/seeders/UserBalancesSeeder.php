<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Seeder;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data saldo, transaksi awal, dan mutasi pembukuan escrow/earning/platform_fee.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra    = User::where('email', 'mitra@sayabantu.com')->first();

        // ─────────────────────────────────────────────────────────────────────
        // 1. TRANSAKSI AWAL CUSTOMER (Top-Up Saldo)
        // ─────────────────────────────────────────────────────────────────────
        if ($customer) {
            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $customer->id,
                    'order_id' => 'TOPUP-CUST-SLM-001',
                ],
                [
                    'amount'         => 500000.00,
                    'type'           => 'topup',
                    'description'    => 'Top-Up Saldo Akun via QRIS Midtrans',
                    'payment_method' => 'qris',
                    'status'         => 'completed',
                    'processed_at'   => now()->subDays(4),
                    'created_at'     => now()->subDays(4),
                ]
            );

            // Escrow lock untuk bantuan yang aktif
            $activeHelps = Help::where('user_id', $customer->id)->whereIn('status', [Help::STATUS_MENUNGGU_MITRA, Help::STATUS_TAKEN])->get();
            foreach ($activeHelps as $h) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);
                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $customer->id,
                        'reference_id' => $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'     => $h->order_id,
                        'amount'       => $totalEscrow,
                        'description'  => "Dana Ditahan untuk Permintaan Bantuan '{$h->title}' (Nilai Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Biaya Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'       => 'completed',
                        'processed_at' => now()->subDays(3),
                        'created_at'   => now()->subDays(3),
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. TRANSAKSI SELESAI MITRA & KAS PLATFORM (Earning & Platform Fee)
        // ─────────────────────────────────────────────────────────────────────
        $completedHelps = Help::where('status', Help::STATUS_SELESAI)->get();
        foreach ($completedHelps as $h) {
            // Escrow Lock Customer
            if ($customer) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);
                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $customer->id,
                        'reference_id' => $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'     => $h->order_id,
                        'amount'       => $totalEscrow,
                        'description'  => "Dana Ditahan untuk Permintaan Bantuan '{$h->title}' (Nilai Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Biaya Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'       => 'completed',
                        'processed_at' => $h->taken_at ?? now()->subDays(2),
                        'created_at'   => $h->taken_at ?? now()->subDays(2),
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }

            // Net Earning Mitra
            if ($mitra && $h->mitra_earning > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $mitra->id,
                        'reference_id' => $h->id,
                        'type'         => 'earning',
                    ],
                    [
                        'order_id'     => $h->order_id,
                        'amount'       => $h->mitra_earning,
                        'description'  => "Pendapatan Bantuan '{$h->title}'",
                        'status'       => 'completed',
                        'processed_at' => $h->completed_at ?? now()->subDays(1),
                        'created_at'   => $h->completed_at ?? now()->subDays(1),
                    ]
                );
            }

            // Platform Fee Kas Platform (user_id = null)
            if ($h->platform_fee_amount > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => null,
                        'reference_id' => $h->id,
                        'type'         => 'platform_fee',
                    ],
                    [
                        'order_id'     => $h->order_id,
                        'amount'       => $h->platform_fee_amount,
                        'description'  => "Biaya Layanan Platform Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . " dari Bantuan '{$h->title}'",
                        'status'       => 'completed',
                        'processed_at' => $h->completed_at ?? now()->subDays(1),
                        'created_at'   => $h->completed_at ?? now()->subDays(1),
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. SINKRONISASI SALDO DI TABEL USER_BALANCES
        // ─────────────────────────────────────────────────────────────────────
        User::query()->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $credits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::creditTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $debits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::debitTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = (float) $credits - (float) $debits;

                // Berikan saldo dasar awal yang wajar jika belum ada transaksi
                if ($balance <= 0) {
                    if ($user->isCustomer()) {
                        $balance = 500000.00; // Rp 500.000
                    } elseif ($user->isMitra()) {
                        $balance = 200000.00; // Rp 200.000
                    } else {
                        $balance = 0.00;
                    }
                }

                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $balance]
                );
            }
        });

        $this->command->info('UserBalancesSeeder berhasil menyinkronkan saldo dan transaksi keuangan Sleman.');
    }
}