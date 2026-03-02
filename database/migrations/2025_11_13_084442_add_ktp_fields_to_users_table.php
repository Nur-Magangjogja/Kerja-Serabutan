<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Data KTP
            $table->string('nik', 16)->nullable()->after('email');
            $table->string('place_of_birth', 100)->nullable()->after('nik');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable()->after('date_of_birth');
            $table->string('rt', 5)->nullable()->after('address');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('kelurahan', 100)->nullable()->after('rw');
            $table->string('kecamatan', 100)->nullable()->after('kelurahan');
            $table->string('city', 100)->nullable()->after('kecamatan');
            $table->string('province', 100)->nullable()->after('city');
            $table->string('religion', 50)->nullable()->after('province');
            $table->enum('marital_status', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable()->after('religion');
            $table->string('occupation', 100)->nullable()->after('marital_status');

            // Foto KTP dan Selfie
            $table->string('ktp_photo')->nullable()->after('ktp_path');
            $table->string('selfie_photo')->nullable()->after('ktp_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'place_of_birth',
                'date_of_birth',
                'gender',
                'rt',
                'rw',
                'kelurahan',
                'kecamatan',
                'city',
                'province',
                'religion',
                'marital_status',
                'occupation',
                'ktp_photo',
                'selfie_photo'
            ]);
        });
    }
};
