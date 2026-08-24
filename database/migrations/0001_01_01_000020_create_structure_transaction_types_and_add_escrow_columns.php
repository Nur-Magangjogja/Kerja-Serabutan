<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Restrukturisasi Arsitektur Transaksi — Double-Entry & Escrow System
 *
 * Perubahan:
 * 1. Tambah tipe transaksi baru ke ENUM balance_transactions.type
 * 2. Tambah kolom komisi & escrow ke tabel helps
 *
 * Aturan: Logika baru HANYA berlaku untuk helps dengan model_version = 2.
 * Data lama (model_version = 1 / NULL) tetap menggunakan logika lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah tipe transaksi baru ke ENUM
        DB::statement("
            ALTER TABLE balance_transactions
            MODIFY COLUMN `type`
                ENUM(
                    'topup',
                    'withdraw',
                    'payment',
                    'refund',
                    'service_fee',
                    'earning',
                    'deduction',
                    'penalty',
                    'escrow_lock',
                    'platform_fee',
                    'pg_fee_topup',
                    'pg_fee_withdraw'
                )
                NOT NULL
        ");

        // 2. Tambah kolom ke tabel helps untuk model v2 (Commission-Based)
        Schema::table('helps', function (Blueprint $table) {
            // Versi model bisnis: NULL/1 = lama (Buyer-Pays), 2 = baru (Seller-Pays/Commission)
            $table->unsignedTinyInteger('model_version')->default(1)->after('status');

            // Snapshot komisi saat tugas dibuat (agar tidak berubah jika setting diubah kemudian)
            $table->decimal('platform_commission_rate', 5, 2)->default(0)->after('model_version')
                  ->comment('Persentase komisi platform saat tugas dibuat (snapshot), contoh: 10.00 = 10%');

            // Nominal hasil kalkulasi (disimpan saat tugas dibuat untuk transparansi)
            $table->decimal('platform_fee_amount', 12, 2)->default(0)->after('platform_commission_rate')
                  ->comment('Nominal komisi platform = amount * platform_commission_rate / 100');
            $table->decimal('mitra_earning', 12, 2)->default(0)->after('platform_fee_amount')
                  ->comment('Nominal bersih mitra = amount - platform_fee_amount');

            // Tracking escrow
            $table->unsignedBigInteger('escrow_transaction_id')->nullable()->after('mitra_earning')
                  ->comment('FK ke balance_transactions (type=escrow_lock) saat tugas dibuat');
            $table->timestamp('escrow_locked_at')->nullable()->after('escrow_transaction_id')
                  ->comment('Waktu dana customer ditahan ke Holding');

            $table->index('model_version');
        });
    }

    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn([
                'model_version',
                'platform_commission_rate',
                'platform_fee_amount',
                'mitra_earning',
                'escrow_transaction_id',
                'escrow_locked_at',
            ]);
        });

        // Kembalikan ENUM tanpa tipe baru (hati-hati jika sudah ada data)
        DB::statement("
            ALTER TABLE balance_transactions
            MODIFY COLUMN `type`
                ENUM('topup','withdraw','payment','refund','service_fee','earning','deduction','penalty')
                NOT NULL
        ");
    }
};
