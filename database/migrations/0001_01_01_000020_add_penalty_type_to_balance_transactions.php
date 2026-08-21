<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan tipe 'penalty' ke kolom ENUM 'type' pada tabel balance_transactions.
     *
     * Tipe 'penalty' digunakan khusus untuk mencatat denda yang dikenakan kepada mitra
     * akibat pelanggaran (misalnya pembatalan pekerjaan yang sudah diambil).
     * Uang denda ini masuk ke kas administrasi sistem, sehingga berbeda dari 'deduction'
     * yang merupakan potongan saldo biasa.
     */
    public function up(): void
    {
        // MySQL / MariaDB: ubah definisi ENUM dengan ALTER TABLE
        DB::statement("
            ALTER TABLE balance_transactions
            MODIFY COLUMN `type`
                ENUM('topup','withdraw','payment','refund','service_fee','earning','deduction','penalty')
                NOT NULL
        ");
    }

    /**
     * Reverse the migration.
     * Catatan: jika sudah ada data bertipe 'penalty', rollback ini akan gagal.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM tanpa 'penalty'
        // Pastikan tidak ada baris dengan type='penalty' sebelum rollback
        DB::statement("
            ALTER TABLE balance_transactions
            MODIFY COLUMN `type`
                ENUM('topup','withdraw','payment','refund','service_fee','earning','deduction')
                NOT NULL
        ");
    }
};
