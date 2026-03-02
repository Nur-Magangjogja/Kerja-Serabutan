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
        Schema::table('partner_reports', function (Blueprint $table) {
            // Reporter (siapa yang melapor)
            $table->foreignId('reporter_id')->nullable()->after('user_id')->constrained('users')->onDelete('cascade');
            
            // User yang dilaporkan
            $table->foreignId('reported_user_id')->nullable()->after('reporter_id')->constrained('users')->onDelete('cascade');
            
            // Help yang dilaporkan (jika ada)
            $table->foreignId('reported_help_id')->nullable()->after('reported_user_id')->constrained('helps')->onDelete('cascade');
            
            // Jenis laporan
            $table->string('report_type')->nullable()->after('reported_help_id');
            
            // Kategori (dari customer atau dari mitra)
            $table->string('category')->nullable()->after('report_type');
            
            // Catatan admin
            $table->text('admin_notes')->nullable()->after('status');
            
            // Resolved info
            $table->timestamp('resolved_at')->nullable()->after('admin_notes');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            $table->dropForeign(['reporter_id']);
            $table->dropForeign(['reported_user_id']);
            $table->dropForeign(['reported_help_id']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn([
                'reporter_id',
                'reported_user_id',
                'reported_help_id',
                'report_type',
                'category',
                'admin_notes',
                'resolved_at',
                'resolved_by'
            ]);
        });
    }
};
