<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status persetujuan (review SPMI) pada profil risiko.
     * pending  = Menunggu Review
     * revision = Perlu Revisi (SPMI memberi catatan)
     * approved = Disetujui
     */
    public function up(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('document_link');
            $table->text('status_note')->nullable()->after('status');
            $table->foreignId('validated_by')->nullable()->after('status_note')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropColumn(['status', 'status_note', 'validated_at']);
        });
    }
};