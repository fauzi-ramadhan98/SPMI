<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bukti perbaikan per temuan (RTL) — diunggah oleh auditee (prodi/unit)
     * sebagai dokumentasi perbaikan + notulen evaluasi internal.
     */
    public function up(): void
    {
        Schema::create('finding_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_finding_id')->constrained('audit_findings')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('category')->nullable(); // Dokumentasi Perbaikan | Notulen Evaluasi | dll
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finding_attachments');
    }
};