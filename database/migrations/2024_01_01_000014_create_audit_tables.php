<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "AMI Semester Ganjil 2024/2025"
            $table->string('academic_year', 10);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_cycle_id')->constrained('audit_cycles')->cascadeOnDelete();
            $table->enum('auditor_type', ['Auditor Internal', 'Auditor External'])->default('Auditor Internal');
            $table->string('auditor_name');
            $table->string('auditor_nidn')->nullable();
            $table->foreignId('academic_program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->enum('status', ['pending', 'berlangsung', 'selesai'])->default('pending');
            $table->timestamps();

            // We can't guarantee auditor name and cycle uniquely constraints the assignment if they are names like 'Budi'.
            // Remove unique constraint for now or adjust it. 
            // We'll just remove it as they requested simple inputs.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignments');
        Schema::dropIfExists('audit_cycles');
    }
};
