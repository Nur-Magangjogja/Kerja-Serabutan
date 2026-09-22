<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable()->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 1. Sinkronisasi data dari reg_provinces jika tabel tersebut ada
        if (Schema::hasTable('reg_provinces')) {
            $regProvs = DB::table('reg_provinces')->get();
            foreach ($regProvs as $rp) {
                DB::table('provinces')->updateOrInsert(
                    ['id' => (int) $rp->id],
                    [
                        'code'       => (string) $rp->id,
                        'name'       => $rp->name,
                        'is_active'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 2. Lengkapi data provinsi dari tabel cities jika ada yang belum terdaftar
        if (Schema::hasTable('cities')) {
            $cityProvs = DB::table('cities')
                ->select('province_id', 'province')
                ->whereNotNull('province')
                ->whereNotNull('province_id')
                ->distinct()
                ->get();

            foreach ($cityProvs as $cp) {
                if (is_numeric($cp->province_id)) {
                    DB::table('provinces')->updateOrInsert(
                        ['id' => (int) $cp->province_id],
                        [
                            'code'       => (string) $cp->province_id,
                            'name'       => $cp->province,
                            'is_active'  => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
