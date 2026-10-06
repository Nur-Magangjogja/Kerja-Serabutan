<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->index(
                ['district_id', 'created_at'],
                'helps_district_id_created_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropIndex('helps_district_id_created_at_index');
        });
    }
};
