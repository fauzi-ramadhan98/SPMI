<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_standard_applicability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->constrained()->cascadeOnDelete();
            $table->enum('target_type', ['prodi', 'unit']);
            $table->foreignId('academic_program_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Composite unique constraint
            $table->unique(
                ['quality_standard_id', 'target_type', 'academic_program_id', 'unit_id'],
                'qsa_unique_mapping'
            );

            // Index for performance
            $table->index(['quality_standard_id', 'target_type'], 'qsa_std_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_standard_applicability');
    }
};