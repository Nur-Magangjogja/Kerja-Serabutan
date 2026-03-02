<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            if (!Schema::hasColumn('helps', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('helps', 'admin_fee')) {
                $table->decimal('admin_fee', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('helps', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->nullable()->after('admin_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            if (Schema::hasColumn('helps', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('helps', 'admin_fee')) {
                $table->dropColumn('admin_fee');
            }
            if (Schema::hasColumn('helps', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
