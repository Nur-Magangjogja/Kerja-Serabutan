<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('city');
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });

        // Attempt to map existing registrations.city (string) to cities.id
        $cities = DB::table('cities')->select('id', 'name')->get();

        foreach ($cities as $city) {
            DB::table('registrations')
                ->whereRaw('LOWER(city) = ?', [strtolower($city->name)])
                ->update(['city_id' => $city->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop foreign key then column
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'city_id')) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            }
        });
    }
};
