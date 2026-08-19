<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evaluasi Diri (ED) — dipakai Prodi dan Unit (polymorphic) berupa Form + Bukti.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('academic_year', 10)->nullable();
            $table->enum('semester', ['Ganjil', 'Genap', 'Tahunan'])->nullable();
            $table->morphs('evaluable'); // AcademicProgram | Unit
            $table->enum('status', ['draft', 'submitted', 'verified'])->default('draft');
            $table->text('conclusion')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards')->nullOnDelete();
            $table->string('criteria')->nullable();
            $table->string('indicator')->nullable();
            $table->tinyInteger('score')->nullable();
            $table->text('narasi')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // evaluation | evaluation_item
            $table->string('title')->nullable();
            $table->string('category')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_attachments');
        Schema::dropIfExists('evaluation_items');
        Schema::dropIfExists('evaluations');
    }
};