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
        // 1. Add district_id to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('city_id')->constrained('districts')->nullOnDelete();
                $table->index('district_id');
                $table->index(['role', 'district_id']);
                $table->index(['status', 'district_id']);
            }
        });

        // 2. Add district_id to helps
        Schema::table('helps', function (Blueprint $table) {
            if (!Schema::hasColumn('helps', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('city_id')->constrained('districts')->nullOnDelete();
                $table->index('district_id');
                $table->index(['status', 'district_id']);
            }
        });

        // 3. Add district_id to registrations if exists
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('registrations', 'district_id')) {
                    $table->foreignId('district_id')->nullable()->after('city_id')->constrained('districts')->nullOnDelete();
                    $table->index('district_id');
                }
            });
        }

        // 4. Create admin_district pivot table
        if (!Schema::hasTable('admin_district')) {
            Schema::create('admin_district', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['district_id', 'user_id']);
                $table->index(['user_id', 'district_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_district');

        if (Schema::hasTable('registrations') && Schema::hasColumn('registrations', 'district_id')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('district_id');
            });
        }

        if (Schema::hasTable('helps') && Schema::hasColumn('helps', 'district_id')) {
            Schema::table('helps', function (Blueprint $table) {
                $table->dropConstrainedForeignId('district_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'district_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('district_id');
            });
        }
    }
};
