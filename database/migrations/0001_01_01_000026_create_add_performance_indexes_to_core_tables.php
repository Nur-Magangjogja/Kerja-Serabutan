<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->hasIndex('users', 'idx_users_role_status')) {
                    $table->index(['role', 'status'], 'idx_users_role_status');
                }
                if (!$this->hasIndex('users', 'idx_users_status_verified')) {
                    $table->index(['status', 'verified'], 'idx_users_status_verified');
                }
            });
        }

        // 2. Helps table
        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if (!$this->hasIndex('helps', 'idx_helps_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_helps_status_created');
                }
                if (!$this->hasIndex('helps', 'idx_helps_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_helps_user_status');
                }
                if (!$this->hasIndex('helps', 'idx_helps_mitra_status')) {
                    $table->index(['mitra_id', 'status'], 'idx_helps_mitra_status');
                }
                if (!$this->hasIndex('helps', 'idx_helps_category_id')) {
                    $table->index('category_id', 'idx_helps_category_id');
                }
            });
        }

        // 3. Withdraw requests table
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (!$this->hasIndex('withdraw_requests', 'idx_withdraws_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_withdraws_status_created');
                }
                if (!$this->hasIndex('withdraw_requests', 'idx_withdraws_bank_code')) {
                    $table->index('bank_code', 'idx_withdraws_bank_code');
                }
            });
        }

        // 4. Balance transactions table
        if (Schema::hasTable('balance_transactions')) {
            Schema::table('balance_transactions', function (Blueprint $table) {
                if (!$this->hasIndex('balance_transactions', 'idx_transactions_type_created')) {
                    $table->index(['type', 'created_at'], 'idx_transactions_type_created');
                }
                if (!$this->hasIndex('balance_transactions', 'idx_transactions_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_transactions_status_created');
                }
            });
        }

        // 5. App settings table
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (!$this->hasIndex('app_settings', 'idx_app_settings_key')) {
                    $table->index('key', 'idx_app_settings_key');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->hasIndex('users', 'idx_users_role_status')) $table->dropIndex('idx_users_role_status');
                if ($this->hasIndex('users', 'idx_users_status_verified')) $table->dropIndex('idx_users_status_verified');
            });
        }

        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if ($this->hasIndex('helps', 'idx_helps_status_created')) $table->dropIndex('idx_helps_status_created');
                if ($this->hasIndex('helps', 'idx_helps_user_status')) $table->dropIndex('idx_helps_user_status');
                if ($this->hasIndex('helps', 'idx_helps_mitra_status')) $table->dropIndex('idx_helps_mitra_status');
                if ($this->hasIndex('helps', 'idx_helps_category_id')) $table->dropIndex('idx_helps_category_id');
            });
        }

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if ($this->hasIndex('withdraw_requests', 'idx_withdraws_status_created')) $table->dropIndex('idx_withdraws_status_created');
                if ($this->hasIndex('withdraw_requests', 'idx_withdraws_bank_code')) $table->dropIndex('idx_withdraws_bank_code');
            });
        }

        if (Schema::hasTable('balance_transactions')) {
            Schema::table('balance_transactions', function (Blueprint $table) {
                if ($this->hasIndex('balance_transactions', 'idx_transactions_type_created')) $table->dropIndex('idx_transactions_type_created');
                if ($this->hasIndex('balance_transactions', 'idx_transactions_status_created')) $table->dropIndex('idx_transactions_status_created');
            });
        }

        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if ($this->hasIndex('app_settings', 'idx_app_settings_key')) $table->dropIndex('idx_app_settings_key');
            });
        }
    }
};
