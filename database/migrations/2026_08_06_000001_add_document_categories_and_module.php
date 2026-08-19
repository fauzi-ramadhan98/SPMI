<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master kategori dokumen mutu + perluasan tabel documents
     * untuk mendukung modul Dokumen Mutu, Surat Tugas AMI, dan Panel RTM.
     */
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('module', ['dokumen_mutu', 'surat_tugas', 'rtm', 'evaluasi_diri', 'rtl', 'lainnya'])
                ->default('dokumen_mutu');
            $table->string('target_roles')->nullable(); // spmi|prodi|unit|pimpinan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('module', ['dokumen_mutu', 'surat_tugas', 'rtm', 'evaluasi_diri', 'rtl', 'lainnya'])
                ->default('dokumen_mutu')->after('document_type');
            $table->foreignId('document_category_id')->nullable()->after('module')
                ->constrained('document_categories')->nullOnDelete();
            $table->foreignId('audit_cycle_id')->nullable()->after('unit_id')
                ->constrained('audit_cycles')->nullOnDelete();
            $table->date('doc_date')->nullable()->after('audit_cycle_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('audit_cycle_id');
            $table->dropConstrainedForeignId('document_category_id');
            $table->dropColumn(['module', 'document_category_id', 'audit_cycle_id', 'doc_date']);
        });

        Schema::dropIfExists('document_categories');
    }
};
