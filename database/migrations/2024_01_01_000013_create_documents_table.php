<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('document_type', ['SPMI', 'AMI', 'Monev', 'Kebijakan', 'Manual', 'SOP', 'Formulir', 'Lainnya'])->default('SPMI');
            $table->string('academic_year', 10)->nullable(); // e.g. 2024/2025
            $table->enum('semester', ['Ganjil', 'Genap', 'Tahunan'])->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
