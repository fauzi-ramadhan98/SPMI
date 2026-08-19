<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Struktur peran baru sesuai Permendiktisaintek No. 39 Tahun 2025:
     * SPMI, Prodi, Unit, Auditor, Pimpinan, Administrator.
     * super_admin dipecah menjadi role khusus (administrator/spmi/pimpinan).
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('category', ['administratif_umum', 'layanan_akademik', 'penunjang'])
                ->default('layanan_akademik');
            $table->string('head_name')->nullable();
            $table->string('head_nidn')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('academic_program_id')->constrained('units')->nullOnDelete();
            $table->enum('pimpinan_level', ['ketua', 'wakil'])->nullable()->after('unit_id');
        });

        // Audit assignments: auditee bisa berupa prodi ATAU unit kerja
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropForeign(['academic_program_id']);
        });
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_program_id')->nullable()->change();
            $table->foreign('academic_program_id')->references('id')->on('academic_programs')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('academic_program_id')->constrained('units')->nullOnDelete();
        });

        // Risk registers: bisa dimiliki prodi ATAU unit kerja
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropForeign(['academic_program_id']);
        });
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_program_id')->nullable()->change();
            $table->foreign('academic_program_id')->references('id')->on('academic_programs')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('academic_program_id')->constrained('units')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('academic_program_id')->constrained('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropForeign(['academic_program_id']);
            $table->unsignedBigInteger('academic_program_id')->nullable(false)->change();
            $table->foreign('academic_program_id')->references('id')->on('academic_programs')->cascadeOnDelete();
        });

        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropForeign(['academic_program_id']);
            $table->unsignedBigInteger('academic_program_id')->nullable(false)->change();
            $table->foreign('academic_program_id')->references('id')->on('academic_programs')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'pimpinan_level']);
        });

        Schema::dropIfExists('units');
    }
};
