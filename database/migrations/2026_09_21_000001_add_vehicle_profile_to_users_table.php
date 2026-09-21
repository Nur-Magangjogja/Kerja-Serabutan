<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom-kolom data kendaraan mitra dan status verifikasinya
     * untuk keamanan layanan Antar & Jemput (pickup_delivery).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Data Identitas Kendaraan & Legalitas Dokumen
            $table->string('vehicle_plate_number', 20)->nullable()->after('notification_settings')
                ->comment('Nomor plat kendaraan mitra (contoh: AB 1234 CD)');
            $table->string('vehicle_sim_number', 50)->nullable()->after('vehicle_plate_number')
                ->comment('Nomor SIM C (motor)');
            $table->string('vehicle_sim_photo')->nullable()->after('vehicle_sim_number')
                ->comment('Path file foto SIM C');
            $table->string('vehicle_stnk_number', 50)->nullable()->after('vehicle_sim_photo')
                ->comment('Nomor STNK');
            $table->string('vehicle_stnk_photo')->nullable()->after('vehicle_stnk_number')
                ->comment('Path file foto STNK');
            $table->string('vehicle_brand', 50)->nullable()->after('vehicle_stnk_photo')
                ->comment('Merek kendaraan (opsional, contoh: Honda, Yamaha)');
            $table->string('vehicle_model', 50)->nullable()->after('vehicle_brand')
                ->comment('Tipe/model kendaraan (opsional, contoh: Beat, Vario)');
            $table->string('vehicle_color', 30)->nullable()->after('vehicle_model')
                ->comment('Warna kendaraan (opsional)');

            // Status Verifikasi Dokumen Kendaraan oleh Admin
            $table->string('vehicle_verification_status', 20)->default('unsubmitted')->after('vehicle_color')
                ->comment('Status verifikasi: unsubmitted, pending, verified, rejected');
            $table->boolean('vehicle_verified')->default(false)->after('vehicle_verification_status')
                ->comment('Apakah data kendaraan sudah disetujui admin');
            $table->timestamp('vehicle_verified_at')->nullable()->after('vehicle_verified')
                ->comment('Waktu data kendaraan disetujui');
            $table->foreignId('vehicle_verified_by')->nullable()->after('vehicle_verified_at')
                ->constrained('users')->nullOnDelete()
                ->comment('Admin yang memverifikasi data kendaraan');
            $table->text('vehicle_rejection_reason')->nullable()->after('vehicle_verified_by')
                ->comment('Alasan penolakan jika verifikasi kendaraan ditolak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'vehicle_verified_by')) {
                $table->dropForeign(['vehicle_verified_by']);
                $table->dropColumn('vehicle_verified_by');
            }

            $columns = [
                'vehicle_plate_number',
                'vehicle_sim_number',
                'vehicle_sim_photo',
                'vehicle_stnk_number',
                'vehicle_stnk_photo',
                'vehicle_brand',
                'vehicle_model',
                'vehicle_year',
                'vehicle_color',
                'vehicle_verification_status',
                'vehicle_verified',
                'vehicle_verified_at',
                'vehicle_rejection_reason',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
