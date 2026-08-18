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
        // 1. Add proof_photo and completion_notes to helps table
        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if (!Schema::hasColumn('helps', 'proof_photo')) {
                    $table->string('proof_photo')->nullable()->after('photo');
                }
                if (!Schema::hasColumn('helps', 'completion_notes')) {
                    $table->text('completion_notes')->nullable()->after('proof_photo');
                }
            });
        }

        // 2. Add photo attachment to chats table
        if (Schema::hasTable('chats')) {
            Schema::table('chats', function (Blueprint $table) {
                if (!Schema::hasColumn('chats', 'photo')) {
                    $table->string('photo')->nullable()->after('message');
                }
            });
        }

        // 3. Add help_id and photo to partner_activities table
        if (Schema::hasTable('partner_activities')) {
            Schema::table('partner_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('partner_activities', 'help_id')) {
                    $table->unsignedBigInteger('help_id')->nullable()->after('user_id')->index();
                }
                if (!Schema::hasColumn('partner_activities', 'photo')) {
                    $table->string('photo')->nullable()->after('description');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if (Schema::hasColumn('helps', 'proof_photo')) {
                    $table->dropColumn('proof_photo');
                }
                if (Schema::hasColumn('helps', 'completion_notes')) {
                    $table->dropColumn('completion_notes');
                }
            });
        }

        if (Schema::hasTable('chats')) {
            Schema::table('chats', function (Blueprint $table) {
                if (Schema::hasColumn('chats', 'photo')) {
                    $table->dropColumn('photo');
                }
            });
        }

        if (Schema::hasTable('partner_activities')) {
            Schema::table('partner_activities', function (Blueprint $table) {
                if (Schema::hasColumn('partner_activities', 'help_id')) {
                    $table->dropColumn('help_id');
                }
                if (Schema::hasColumn('partner_activities', 'photo')) {
                    $table->dropColumn('photo');
                }
            });
        }
    }
};
