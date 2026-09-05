<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi transaksi riwayat Top-Up, Pembayaran Jasa/Escrow, Pendapatan Mitra, dan Penarikan Dana (Withdraw)
     * Terdistribusi lengkap untuk setiap hari dari rentang 4 Agustus 2026 s/d 3 September 2026.
     */
    public function run(): void
    {
        // Sleman Users
        $custSleman1  = User::where('email', 'customer.sleman1@sayabantu.com')->first() ?? User::where('email', 'customer@sayabantu.com')->first();
        $custSleman2  = User::where('email', 'customer.sleman2@sayabantu.com')->first();
        $mitraSleman1 = User::where('email', 'mitra.sleman1@sayabantu.com')->first() ?? User::where('email', 'mitra@sayabantu.com')->first();
        $mitraSleman2 = User::where('email', 'mitra.sleman2@sayabantu.com')->first();

        // Surakarta Users
        $custSolo1  = User::where('email', 'customer.solo1@sayabantu.com')->first();
        $custSolo2  = User::where('email', 'customer.solo2@sayabantu.com')->first();
        $mitraSolo1 = User::where('email', 'mitra.solo1@sayabantu.com')->first();
        $mitraSolo2 = User::where('email', 'mitra.solo2@sayabantu.com')->first();

        // ─────────────────────────────────────────────────────────────────────
        // 1. TRANSAKSI TOP-UP CUSTOMER (Tersebar 4 Agustus - 3 September 2026)
        // ─────────────────────────────────────────────────────────────────────
        $topupPlans = [
            // Periode Awal (04 - 10 Agustus)
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260804-SLM01', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-04 08:15:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260805-SKT01', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-05 10:20:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260806-SLM02', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BRI', 'date' => '2026-08-06 07:45:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260807-SKT02', 'amount' => 600000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-07 09:10:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260808-SLM03', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BCA', 'date' => '2026-08-08 12:30:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260809-SKT03', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-09 08:50:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260810-SLM04', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BNI', 'date' => '2026-08-10 08:00:00'],

            // Periode Tengah (11 - 20 Agustus)
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260811-SKT04', 'amount' => 500000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account Bank Jateng', 'date' => '2026-08-11 07:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260812-SLM05', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-12 11:15:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260813-SKT05', 'amount' => 250000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account Mandiri', 'date' => '2026-08-13 09:40:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260814-SLM06', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-14 14:10:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260815-SKT06', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-15 08:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260816-SLM07', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BCA', 'date' => '2026-08-16 09:15:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260817-SKT07', 'amount' => 200000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-17 07:00:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260818-SLM08', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BRI', 'date' => '2026-08-18 11:30:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260819-SKT08', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Bank Jateng', 'date' => '2026-08-19 08:00:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260820-SLM09', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BNI', 'date' => '2026-08-20 08:30:00'],

            // Periode Akhir (21 Agustus - 03 September)
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260821-SKT09', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BCA', 'date' => '2026-08-21 07:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260822-SLM10', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-22 13:00:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260823-SKT10', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account Mandiri', 'date' => '2026-08-23 09:15:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260824-SLM11', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-24 12:45:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260825-SKT11', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BNI', 'date' => '2026-08-25 13:20:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260826-SLM12', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-26 08:30:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260827-SKT12', 'amount' => 250000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BRI', 'date' => '2026-08-27 09:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260828-SLM13', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-28 08:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260829-SKT13', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BCA', 'date' => '2026-08-29 08:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260830-SLM14', 'amount' => 250000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-30 13:10:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260831-SKT14', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account Bank Jateng', 'date' => '2026-08-31 09:00:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260901-SLM15', 'amount' => 450000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-09-01 07:15:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260902-SKT15', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via Virtual Account BNI', 'date' => '2026-09-02 08:30:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260903-SLM16', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-09-03 09:45:00'],
        ];

        foreach ($topupPlans as $p) {
            $user = $p['user'];
            if (!$user) {
                continue;
            }

            $date = Carbon::parse($p['date']);

            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'order_id' => $p['order_id'],
                ],
                [
                    'amount'         => (float) $p['amount'],
                    'direction'      => 'credit',
                    'admin_fee'      => 0.00,
                    'total_payment'  => (float) $p['amount'],
                    'type'           => 'topup',
                    'description'    => $p['desc'],
                    'payment_method' => $p['method'],
                    'customer_name'  => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? '081234567890',
                    'status'         => 'completed',
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. TRANSAKSI JASA BANTUAN (ESCROW LOCK, EARNING, PLATFORM FEE)
        // ─────────────────────────────────────────────────────────────────────
        $completedHelps = Help::where('status', Help::STATUS_SELESAI)->orderBy('created_at')->get();
        foreach ($completedHelps as $h) {
            $createdAt   = $h->created_at ?? Carbon::parse('2026-08-04 09:00:00');
            $completedAt = $h->completed_at ?? $createdAt->copy()->addHours(2);

            // Escrow Lock Customer
            if ($h->user_id) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);
                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->user_id,
                        'reference_id' => (string) $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $totalEscrow,
                        'direction'      => 'debit',
                        'description'    => "Dana Ditahan untuk Bantuan '{$h->title}' (Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'         => 'completed',
                        'processed_at'   => $createdAt,
                        'created_at'     => $createdAt,
                        'updated_at'     => $createdAt,
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }

            // Earning Mitra
            if ($h->mitra_id && $h->mitra_earning > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->mitra_id,
                        'reference_id' => (string) $h->id,
                        'type'         => 'earning',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $h->mitra_earning,
                        'direction'      => 'credit',
                        'description'    => "Pendapatan Selesai Bantuan '{$h->title}'",
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }

            // Platform Service Fee
            if ($h->platform_fee_amount > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => null,
                        'reference_id' => (string) $h->id,
                        'type'         => 'platform_fee',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $h->platform_fee_amount,
                        'direction'      => 'credit',
                        'description'    => "Biaya Layanan Platform Bantuan '{$h->title}'",
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. TRANSAKSI WITHDRAW MITRA (Terdistribusi Sepanjang Bulan)
        // ─────────────────────────────────────────────────────────────────────
        $withdrawPlans = [
            // Mitra 1 Sleman (Agus Prasetyo)
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260808-SLM01', 'amount' => 100000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-08 17:00:00'],
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260816-SLM02', 'amount' => 150000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-16 16:30:00'],
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260826-SLM03', 'amount' => 120000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-26 18:00:00'],

            // Mitra 2 Sleman (Budi Santoso)
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260811-SLM04', 'amount' => 120000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-08-11 17:30:00'],
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260822-SLM05', 'amount' => 150000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-08-22 17:00:00'],
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260901-SLM06', 'amount' => 100000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-09-01 16:45:00'],

            // Mitra 1 Surakarta (Eko Saputra)
            ['user' => $mitraSolo1, 'order_id' => 'WD-20260810-SKT01', 'amount' => 100000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-08-10 17:15:00'],
            ['user' => $mitraSolo1, 'order_id' => 'WD-20260820-SKT02', 'amount' => 120000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-08-20 16:30:00'],
            ['user' => $mitraSolo1, 'order_id' => 'WD-20260831-SKT03', 'amount' => 100000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-08-31 17:45:00'],

            // Mitra 2 Surakarta (Hendra Wijaya)
            ['user' => $mitraSolo2, 'order_id' => 'WD-20260812-SKT04', 'amount' => 150000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-08-12 17:00:00'],
            ['user' => $mitraSolo2, 'order_id' => 'WD-20260824-SKT05', 'amount' => 130000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-08-24 18:15:00'],
            ['user' => $mitraSolo2, 'order_id' => 'WD-20260902-SKT06', 'amount' => 110000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-09-02 17:30:00'],
        ];

        foreach ($withdrawPlans as $wd) {
            $user = $wd['user'];
            if (!$user) {
                continue;
            }

            $amount = (float) $wd['amount'];
            $date   = Carbon::parse($wd['date']);

            $withdrawRequest = WithdrawRequest::updateOrCreate(
                [
                    'user_id'     => $user->id,
                    'external_id' => $wd['order_id'],
                ],
                [
                    'amount'         => (int) $amount,
                    'admin_fee'      => 0,
                    'net_amount'     => (int) $amount,
                    'bank_code'      => $wd['bank'],
                    'account_number' => $wd['acc_no'],
                    'account_name'   => $wd['acc_name'],
                    'status'         => 'success',
                    'description'    => "Penarikan Saldo Mitra ke Rekening Bank {$wd['bank']} - {$wd['acc_no']} a.n {$wd['acc_name']}",
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );

            BalanceTransaction::updateOrCreate(
                [
                    'user_id'      => $user->id,
                    'reference_id' => (string) $withdrawRequest->id,
                    'type'         => 'withdraw',
                ],
                [
                    'order_id'       => $wd['order_id'],
                    'reference_type' => 'withdraw_request',
                    'amount'         => $amount,
                    'direction'      => 'debit',
                    'admin_fee'      => 0.00,
                    'total_payment'  => $amount,
                    'payment_method' => 'bank_transfer',
                    'customer_name'  => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? '081234567890',
                    'description'    => "Penarikan Saldo Mitra ke Rekening Bank {$wd['bank']} - {$wd['acc_no']} a.n {$wd['acc_name']}",
                    'status'         => 'completed',
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 4. SINKRONISASI SALDO DI TABEL USER_BALANCES
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

        $this->command->info('UserBalancesSeeder berhasil membuat transaksi keuangan harian (4 Agt - 3 Sept 2026) dan menyinkronkan saldo seluruh akun.');
    }
}