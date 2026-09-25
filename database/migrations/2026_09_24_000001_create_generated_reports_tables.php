<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generate Laporan AMI — daftar laporan + lampiran terurut + arsip PDF.
     *
     * generated_reports              : satu baris per laporan yang dibuat dari
     *                                   halaman Laporan (siklus + jenjang + jenis),
     *                                   berstatus draft sampai di-generate.
     * generated_report_attachments   : lampiran terurut (SK, dokumen, bukti ED,
     *                                   temuan, atau upload manual) — urutannya
     *                                   menjadi urutan halaman di PDF akhir.
     */
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_cycle_id')->constrained('audit_cycles')->cascadeOnDelete();
            $table->string('level')->default('institusi');        // institusi | prodi
            $table->foreignId('academic_program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->string('jenis')->default('klasik');           // klasik | berbasis-risiko
            $table->string('status')->default('draft');           // draft | generated
            $table->string('file_path')->nullable();              // arsip PDF hasil generate (disk: local)
            $table->string('file_name')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['audit_cycle_id', 'level', 'jenis', 'status']);
        });

        Schema::create('generated_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_report_id')->constrained('generated_reports')->cascadeOnDelete();
            $table->string('title');
            $table->string('source_label')->nullable();           // SK Penetapan | Dokumen siklus | Evaluasi Diri — … | Upload manual
            $table->string('source_type')->nullable();            // App\Models\StandardDecree | … | 'upload'
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('disk')->default('local');             // local | public
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_report_attachments');
        Schema::dropIfExists('generated_reports');
    }
};
