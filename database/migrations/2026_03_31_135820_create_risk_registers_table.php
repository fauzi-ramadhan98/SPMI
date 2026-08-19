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
        Schema::create('risk_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->string('academic_year', 10);
            $table->text('risk_description');
            $table->integer('probability')->comment('1-5');
            $table->integer('impact')->comment('1-5');
            $table->integer('risk_score');
            $table->enum('risk_level', ['Low', 'Medium', 'High']);
            $table->text('mitigation_plan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_registers');
    }
};
