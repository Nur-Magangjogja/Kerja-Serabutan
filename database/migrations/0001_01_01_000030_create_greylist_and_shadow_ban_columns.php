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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_greylisted')) {
                $table->boolean('is_greylisted')->default(false)->after('status');
            }
            if (!Schema::hasColumn('users', 'greylisted_at')) {
                $table->timestamp('greylisted_at')->nullable()->after('is_greylisted');
            }
            if (!Schema::hasColumn('users', 'greylist_reason')) {
                $table->text('greylist_reason')->nullable()->after('greylisted_at');
            }
            if (!Schema::hasColumn('users', 'is_shadow_banned')) {
                $table->boolean('is_shadow_banned')->default(false)->after('greylist_reason');
            }
            if (!Schema::hasColumn('users', 'shadow_banned_at')) {
                $table->timestamp('shadow_banned_at')->nullable()->after('is_shadow_banned');
            }
            if (!Schema::hasColumn('users', 'warning_level')) {
                $table->unsignedTinyInteger('warning_level')->default(0)->after('shadow_banned_at'); // 0=none, 1=SP1, 2=SP2, 3=SP3
            }
            if (!Schema::hasColumn('users', 'latest_warning_message')) {
                $table->text('latest_warning_message')->nullable()->after('warning_level');
            }
            if (!Schema::hasColumn('users', 'latest_warning_at')) {
                $table->timestamp('latest_warning_at')->nullable()->after('latest_warning_message');
            }
        });

        if (!Schema::hasTable('user_greylist_logs')) {
            Schema::create('user_greylist_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('action', ['greylist_add', 'greylist_remove', 'warning_issued', 'shadow_ban_enabled', 'shadow_ban_disabled']);
                $table->unsignedTinyInteger('warning_level')->nullable();
                $table->text('reason')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_greylist_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_greylisted',
                'greylisted_at',
                'greylist_reason',
                'is_shadow_banned',
                'shadow_banned_at',
                'warning_level',
                'latest_warning_message',
                'latest_warning_at',
            ]);
        });
    }
};
