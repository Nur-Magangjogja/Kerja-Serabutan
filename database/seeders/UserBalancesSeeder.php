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
        $customers = User::where('role', 'customer')->get();
        $mitraUsers = User::where('role', 'mitra')->get();
        $primaryCustomer = User::where('email', 'customer@sayabantu.com')->first() ?? $customers->first();
        $primaryMitra    = User::where('email', 'mitra@sayabantu.com')->first() ?? $mitraUsers->first();

        // ─────────────────────────────────────────────────────────────────────
        // 1. TRANSAKSI AWAL TOP-UP CUSTOMER
        // ─────────────────────────────────────────────────────────────────────
        foreach ($customers as $cust) {
            // Top-Up 1 (Deposit Utama Rp 1.000.000 via QRIS)
            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $cust->id,
                    'order_id' => 'TOPUP-CUST-' . $cust->id . '-001',
                ],
                [
                    'amount'          => 1000000.00,
                    'direction'       => 'credit',
                    'admin_fee'       => 0.00,
                    'total_payment'   => 1000000.00,
                    'type'            => 'topup',
                    'description'     => 'Top-Up Saldo Akun via QRIS',
                    'payment_method'  => 'qris',
                    'customer_name'   => $cust->name,
                    'customer_email'  => $cust->email,
                    'customer_phone'  => $cust->phone ?? '081234567890',
                    'status'          => 'completed',
                    'processed_at'    => now()->subDays(6),
                    'created_at'      => now()->subDays(6),
                ]
            );

            // Top-Up 2 (Deposit Tambahan Rp 500.000 via QRIS untuk primary customer)
            if ($primaryCustomer && $cust->id === $primaryCustomer->id) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'  => $cust->id,
                        'order_id' => 'TOPUP-CUST-' . $cust->id . '-002',
                    ],
                    [
                        'amount'          => 500000.00,
                        'direction'       => 'credit',
                        'admin_fee'       => 0.00,
                        'total_payment'   => 500000.00,
                        'type'            => 'topup',
                        'description'     => 'Top-Up Saldo Akun via QRIS',
                        'payment_method'  => 'qris',
                        'customer_name'   => $cust->name,
                        'customer_email'  => $cust->email,
                        'customer_phone'  => $cust->phone ?? '081234567890',
                        'status'          => 'completed',
                        'processed_at'    => now()->subDays(3),
                        'created_at'      => now()->subDays(3),
                    ]
                );
            }

            // Escrow lock untuk bantuan yang aktif
            $activeHelps = Help::where('user_id', $cust->id)->whereIn('status', [Help::STATUS_MENUNGGU_MITRA, Help::STATUS_TAKEN])->get();
            foreach ($activeHelps as $h) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);
                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $cust->id,
                        'reference_id' => $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'amount'         => $totalEscrow,
                        'direction'      => 'debit',
                        'description'    => "Dana Ditahan untuk Permintaan Bantuan '{$h->title}' (Nilai Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Biaya Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'         => 'completed',
                        'processed_at'   => now()->subDays(3),
                        'created_at'     => now()->subDays(3),
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
            if ($h->user_id) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);
                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->user_id,
                        'reference_id' => $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'amount'         => $totalEscrow,
                        'direction'      => 'debit',
                        'description'    => "Dana Ditahan untuk Permintaan Bantuan '{$h->title}' (Nilai Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Biaya Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'         => 'completed',
                        'processed_at'   => $h->taken_at ?? now()->subDays(2),
                        'created_at'     => $h->taken_at ?? now()->subDays(2),
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }

            // Net Earning Mitra
            if ($h->mitra_id && $h->mitra_earning > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->mitra_id,
                        'reference_id' => $h->id,
                        'type'         => 'earning',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'amount'         => $h->mitra_earning,
                        'direction'      => 'credit',
                        'description'    => "Pendapatan Bantuan '{$h->title}'",
                        'status'         => 'completed',
                        'processed_at'   => $h->completed_at ?? now()->subDays(1),
                        'created_at'     => $h->completed_at ?? now()->subDays(1),
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
                        'order_id'       => $h->order_id,
                        'amount'         => $h->platform_fee_amount,
                        'direction'      => 'credit',
                        'description'    => "Biaya Layanan Platform Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . " dari Bantuan '{$h->title}'",
                        'status'         => 'completed',
                        'processed_at'   => $h->completed_at ?? now()->subDays(1),
                        'created_at'     => $h->completed_at ?? now()->subDays(1),
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

                $balance = max(0.00, (float) $credits - (float) $debits);

                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $balance]
                );
            }
        });

        $this->command->info('UserBalancesSeeder berhasil menyinkronkan saldo dan transaksi keuangan.');
    }
}