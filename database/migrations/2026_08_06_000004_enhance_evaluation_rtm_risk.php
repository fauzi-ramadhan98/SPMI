<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyempurnaan alur ED (auditee isi + analisis per indikator), RTM approval,
 * dan sambungan Risk Register ke standar + sumber risiko.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Analisis per indikator pada Evaluasi Diri (diisi prodi/unit)
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->text('root_cause')->nullable()->after('narasi');     // Akar Permasalahan
            $table->text('impact')->nullable()->after('root_cause');     // Dampak
            $table->text('mitigation')->nullable()->after('impact');     // Mitigasi
            $table->text('action_plan')->nullable()->after('mitigation');// Tindak Lanjut
            $table->date('target_date')->nullable()->after('action_plan');
        });

        // RTM — aksi persetujuan langkah perbaikan oleh pimpinan
        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->boolean('rtm_approved')->default(false)->after('semester');
            $table->foreignId('rtm_approved_by')->nullable()->after('rtm_approved');
            $table->timestamp('rtm_approved_at')->nullable()->after('rtm_approved_by');
        });

        // Ringkasan — sambungan Risk Register ke standar + sumber risiko
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->foreignId('quality_standard_id')->nullable()->after('academic_year')->constrained('quality_standards')->nullOnDelete();
            $table->enum('source', ['manual', 'temuan_ami', 'skor_ed'])->default('manual')->after('quality_standard_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->dropColumn(['root_cause', 'impact', 'mitigation', 'action_plan', 'target_date']);
        });

        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rtm_approved_by');
            $table->dropColumn(['rtm_approved', 'rtm_approved_at']);
        });

        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quality_standard_id');
            $table->dropColumn(['source']);
        });
    }
};